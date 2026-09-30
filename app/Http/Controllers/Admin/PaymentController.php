<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentRefundRequest;
use App\Services\OrderService;
use App\Services\PaymentSettingService;
use App\Services\SepayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private SepayService $sepayService,
        private PaymentSettingService $settingService
    ) {}

    /**
     * Display payments ledger and financial reconciliation dashboard.
     * Quy định 6: Admin xem toàn bộ dòng tiền, doanh thu, xuất file Excel.
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'transactions'); // transactions | refund_requests | cod

        // 1. KPI Financial Summaries (Toàn bộ dòng tiền)
        $kpi = [
            'total_paid' => (float) Payment::where('status', 'PAID')->sum('amount'),
            'total_pending' => (float) Payment::where('status', 'PENDING')->sum('amount'),
            'total_cod_pending' => (float) Payment::where('method', 'COD')->where('status', 'PENDING')->sum('amount'),
            'total_refunded' => (float) Payment::where('status', 'REFUNDED')->sum('amount'),
        ];

        // 2. Tab Badge Counts
        $counts = [
            'ALL' => Payment::count(),
            'PENDING' => Payment::where('status', 'PENDING')->count(),
            'PAID' => Payment::where('status', 'PAID')->count(),
            'FAILED' => Payment::where('status', 'FAILED')->count(),
            'REFUNDED' => Payment::where('status', 'REFUNDED')->count(),
            'REFUND_REQUESTS' => PaymentRefundRequest::where('status', 'PENDING')->count(),
        ];

        // 3. Transactions query
        $query = $this->buildFilteredQuery($request);
        $payments = $query->paginate(15, ['*'], 'p_page')->withQueryString();

        // 4. Refund Requests list for Admin (Chức năng 3: Phê duyệt lệnh & Quét mã chuyển tiền)
        $refundRequestsQuery = PaymentRefundRequest::with([
            'payment',
            'order.customer',
            'requestedByUser',
            'approvedByUser',
        ]);

        if ($request->filled('rf_status')) {
            $refundRequestsQuery->where('status', $request->rf_status);
        } else {
            // Default show pending first
            $refundRequestsQuery->orderByRaw("CASE WHEN status = 'PENDING' THEN 1 ELSE 2 END");
        }

        $refundRequests = $refundRequestsQuery->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'rf_page')
            ->withQueryString();

        // 5. COD Courier Debt Overview (Chức năng 4: Xem tổng kết công nợ, nhận tiền về tài khoản)
        $codDebtSummary = [
            'cod_in_transit' => (float) Payment::where('method', 'COD')
                ->whereHas('order', fn($q) => $q->where('order_status', 'SHIPPING'))
                ->sum('amount'),
            'cod_reconciled_pending_settle' => (float) Payment::where('method', 'COD')
                ->whereNotNull('cod_reconciled_at')
                ->whereNull('cod_settled_at')
                ->sum('amount'),
            'cod_settled' => (float) Payment::where('method', 'COD')
                ->whereNotNull('cod_settled_at')
                ->sum('amount'),
            'cod_total_delivered' => (float) Payment::where('method', 'COD')
                ->where('status', 'PAID')
                ->sum('amount'),
        ];

        $codQuery = Payment::with(['order.customer', 'reconciledByUser', 'settledByUser'])
            ->where('method', 'COD')
            ->whereHas('order', function ($q) {
                $q->where('order_status', '!=', 'CANCELLED');
            });

        if ($request->filled('cod_status')) {
            match ($request->cod_status) {
                'SETTLED' => $codQuery->whereNotNull('cod_settled_at'),
                'RECONCILED' => $codQuery->whereNotNull('cod_reconciled_at')->whereNull('cod_settled_at'),
                'UNRECONCILED' => $codQuery->whereNull('cod_reconciled_at'),
                default => null,
            };
        }

        if ($request->filled('cod_search')) {
            $cSearch = trim($request->cod_search);
            $codQuery->whereHas('order', function ($q) use ($cSearch) {
                $q->where('order_code', 'LIKE', "%{$cSearch}%")
                    ->orWhere('recipient_name', 'LIKE', "%{$cSearch}%")
                    ->orWhere('recipient_phone', 'LIKE', "%{$cSearch}%");
            });
        }

        $codPayments = $codQuery->orderBy('created_at', 'desc')->paginate(15, ['*'], 'cod_page')->withQueryString();

        return view('admin.payments.index', compact(
            'activeTab',
            'payments',
            'kpi',
            'counts',
            'refundRequests',
            'codDebtSummary',
            'codPayments'
        ));
    }

    /**
     * Real-time verification with SePAY API for bank transfers.
     */
    public function verifySepay(Request $request, Payment $payment)
    {
        if ($payment->status === 'PAID') {
            return redirect()->back()->with('info', 'Giao dịch này đã được ghi nhận thanh toán trước đó.');
        }

        if (!$payment->order) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin đơn hàng gắn liền với giao dịch.');
        }

        try {
            $isVerified = $this->sepayService->verifyBankTransaction($payment->order);

            if ($isVerified) {
                $payment->refresh();
                return redirect()->back()->with('success', "Đối soát SePAY thành công! Đã khớp giao dịch ngân hàng và xác nhận thanh toán đơn {$payment->order->order_code}.");
            }

            return redirect()->back()->with('error', "Chưa tìm thấy biến động số dư khớp với đơn {$payment->order->order_code} trên SePAY. Khách có thể chưa chuyển khoản hoặc chuyển sai cú pháp.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi kết nối tới cổng SePAY: ' . $e->getMessage());
        }
    }

    /**
     * Update payment status manually (Admin toàn quyền: xác nhận trực tiếp, không bắt buộc ảnh bill).
     */
    public function updateStatus(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:PENDING,PAID,FAILED,REFUNDED',
            'note' => 'nullable|string|max:500',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $proofPath = null;
            if ($request->hasFile('proof_image')) {
                $proofPath = $request->file('proof_image')->store('payments/proofs', 'public');
            }

            if ($validated['status'] === 'PAID') {
                $payment->update([
                    'status' => 'PAID',
                    'confirmed_by' => auth()->id(),
                    'paid_at' => now(),
                    'note' => $validated['note'] ?? $payment->note ?? 'Admin duyệt thanh toán trực tiếp',
                    'proof_image' => $proofPath ?: $payment->proof_image,
                ]);

                if ($payment->order) {
                    $payment->order->update(['payment_status' => 'PAID']);
                    OrderStatusHistory::create([
                        'order_id' => $payment->order->id,
                        'from_status' => $payment->order->order_status,
                        'to_status' => $payment->order->order_status,
                        'changed_by' => auth()->id(),
                        'note' => 'Admin đã xác nhận thanh toán thành công.' . ($validated['note'] ? " Ghi chú: {$validated['note']}" : ''),
                        'changed_at' => now(),
                    ]);
                }
            } elseif ($validated['status'] === 'FAILED') {
                $this->orderService->markPaymentFailed($payment);
            } elseif ($validated['status'] === 'REFUNDED') {
                $this->orderService->refundPayment($payment);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thanh toán thành công.');
    }

    /**
     * Phê duyệt lệnh hoàn tiền & Quét mã VietQR chuyển tiền (Quy định 3).
     */
    public function approveRefund(Request $request, PaymentRefundRequest $refundRequest)
    {
        if ($refundRequest->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Yêu cầu hoàn tiền này đã được xử lý trước đó.');
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $refundRequest->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_note' => $validated['admin_note'] ?? 'Admin đã duyệt và chuyển tiền hoàn',
        ]);

        $payment = $refundRequest->payment;
        if ($payment) {
            $payment->update(['status' => 'REFUNDED']);
        }

        $order = $refundRequest->order;
        if ($order) {
            $order->update([
                'payment_status' => 'REFUNDED',
                'refund_note' => $validated['admin_note'] ?? 'Đã hoàn tiền theo yêu cầu',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->order_status,
                'to_status' => $order->order_status,
                'changed_by' => auth()->id(),
                'note' => "Admin đã phê duyệt lệnh hoàn tiền (" . number_format($refundRequest->amount, 0, ',', '.') . "đ). " . ($validated['admin_note'] ?? ''),
                'changed_at' => now(),
            ]);
        }

        return redirect()->route('admin.payments.index', ['tab' => 'refund_requests'])
            ->with('success', "Đã phê duyệt và ghi nhận hoàn tiền " . number_format($refundRequest->amount, 0, ',', '.') . "đ cho đơn #{$order?->order_code}.");
    }

    /**
     * Từ chối yêu cầu hoàn tiền (Quy định 3).
     */
    public function rejectRefund(Request $request, PaymentRefundRequest $refundRequest)
    {
        if ($refundRequest->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Yêu cầu hoàn tiền này đã được xử lý trước đó.');
        }

        $validated = $request->validate([
            'admin_note' => 'required|string|min:3|max:500',
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối yêu cầu hoàn tiền để nhân viên nắm được.',
        ]);

        $refundRequest->update([
            'status' => 'REJECTED',
            'approved_by' => auth()->id(),
            'rejected_at' => now(),
            'admin_note' => $validated['admin_note'],
        ]);

        return redirect()->route('admin.payments.index', ['tab' => 'refund_requests'])
            ->with('success', "Đã từ chối yêu cầu hoàn tiền cho đơn #{$refundRequest->order?->order_code}.");
    }

    /**
     * Admin xác nhận nhận tiền COD từ ĐVVC về tài khoản shop (Quy định 4).
     */
    public function markCodSettled(Request $request, Payment $payment)
    {
        if ($payment->method !== 'COD') {
            return redirect()->back()->with('error', 'Chỉ áp dụng cho đơn COD.');
        }

        // Bắt buộc đơn phải được nhân viên đối soát thực tế với bưu cục / bưu tá trước
        if (! $payment->cod_reconciled_at) {
            return redirect()->back()->with('error', 'Đơn hàng chưa được nhân viên đối soát với bưu tá. Không thể chốt nhận tiền về tài khoản!');
        }

        $payment->update([
            'cod_settled_at' => now(),
            'cod_settled_by' => auth()->id(),
            'status' => 'PAID',
            'paid_at' => $payment->paid_at ?? now(),
        ]);

        return redirect()->route('admin.payments.index', ['tab' => 'cod'])
            ->with('success', "Đã xác nhận nhận số tiền " . number_format($payment->amount, 0, ',', '.') . "đ về tài khoản ngân hàng của shop.");
    }

    /**
     * Admin chốt nhận tiền COD hàng loạt về tài khoản.
     */
    public function bulkMarkCodSettled(Request $request)
    {
        $paymentIds = $request->input('payment_ids', []);
        if (empty($paymentIds)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một đơn COD để chốt nhận tiền.');
        }

        // Chỉ chốt nhận tiền các đơn ĐÃ ĐƯỢC ĐỐI SOÁT VỚI BƯU TÁ VÀ GIAO THÀNH CÔNG (COMPLETED)
        $validQuery = Payment::whereIn('id', $paymentIds)
            ->where('method', 'COD')
            ->whereNotNull('cod_reconciled_at')
            ->whereNull('cod_settled_at')
            ->whereHas('order', function ($q) {
                $q->where('order_status', 'COMPLETED');
            });

        $totalSettled = $validQuery->count();

        if ($totalSettled === 0) {
            return redirect()->back()->with('error', 'Các đơn được chọn chưa được nhân viên đối soát với bưu tá hoặc đã được nhận tiền trước đó.');
        }

        $validQuery->update([
            'cod_settled_at' => now(),
            'cod_settled_by' => auth()->id(),
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        return redirect()->route('admin.payments.index', ['tab' => 'cod'])
            ->with('success', "Đã chốt nhận tiền thành công cho {$totalSettled} đơn COD (đã đối soát) về tài khoản.");
    }

    /**
     * Cấu hình cổng & API thanh toán (Quy định 5: Toàn quyền đổi STK, API SePAY, Webhook).
     */
    public function settings(Request $request)
    {
        $settings = $this->settingService->getSettings();
        return view('admin.payments.settings', compact('settings'));
    }

    /**
     * Lưu cấu hình cổng thanh toán.
     */
    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'vietqr_bank_code' => 'required|string|max:50',
            'vietqr_bank_name' => 'required|string|max:100',
            'vietqr_account_number' => 'required|string|max:50',
            'vietqr_account_name' => 'required|string|max:100',
            'sepay_api_key' => 'nullable|string|max:255',
            'sepay_webhook_token' => 'nullable|string|max:255',
            'sepay_active' => 'nullable|boolean',
        ], [
            'vietqr_bank_code.required' => 'Mã ngân hàng VietQR không được để trống.',
            'vietqr_bank_name.required' => 'Tên ngân hàng không được để trống.',
            'vietqr_account_number.required' => 'Số tài khoản nhận tiền không được để trống.',
            'vietqr_account_name.required' => 'Tên chủ tài khoản không được để trống.',
        ]);

        $this->settingService->saveSettings([
            'vietqr_bank_code' => strtoupper(trim($validated['vietqr_bank_code'])),
            'vietqr_bank_name' => trim($validated['vietqr_bank_name']),
            'vietqr_account_number' => trim($validated['vietqr_account_number']),
            'vietqr_account_name' => strtoupper(trim($validated['vietqr_account_name'])),
            'sepay_api_key' => trim($validated['sepay_api_key'] ?? ''),
            'sepay_webhook_token' => trim($validated['sepay_webhook_token'] ?? ''),
            'sepay_active' => $request->has('sepay_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.payments.settings')
            ->with('success', 'Đã lưu cấu hình tài khoản VietQR và SePAY thành công! Áp dụng tức thì.');
    }

    /**
     * Xuất báo cáo giao dịch thanh toán định dạng Excel (.xls) chuẩn bảng biểu, mở trực tiếp trên Windows & Mac không lỗi font (như trang nhân viên).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->buildFilteredQuery($request);
        $fileName = 'bao_cao_thanh_toan_' . Carbon::now()->format('Ymd_His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">\r\n");
            fwrite($handle, "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\r\n");
            fwrite($handle, "<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Báo Cáo Thanh Toán</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->\r\n");
            fwrite($handle, "<style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
                th { background-color: #F5EBE1; color: #5C3219; font-weight: bold; border: 1px solid #D4C5B3; padding: 8px; font-size: 11pt; text-align: center; }
                td { border: 1px solid #E8DECB; padding: 6px; font-size: 10.5pt; vertical-align: middle; }
                .text { mso-number-format: \"\\@\"; }
                .number { mso-number-format: \"\\#\\,\\#\\#0\"; text-align: right; }
                .center { text-align: center; }
            </style></head><body>\r\n");
            fwrite($handle, "<table border=\"1\">\r\n");
            fwrite($handle, "<thead><tr>
                <th>STT</th>
                <th>Mã GD</th>
                <th>Mã Đơn Hàng</th>
                <th>Khách Hàng / Người Nhận</th>
                <th>Số Điện Thoại</th>
                <th>Phương Thức</th>
                <th>Số Tiền (VNĐ)</th>
                <th>Mã Tham Chiếu NH / Cổng</th>
                <th>Trạng Thái</th>
                <th>Thời Gian Tạo</th>
                <th>Thời Gian Thanh Toán</th>
                <th>Người Duyệt / Xác Nhận</th>
                <th>Đối Soát COD</th>
                <th>Nhận Tiền Về TK</th>
                <th>Ghi Chú</th>
            </tr></thead><tbody>\r\n");

            $stt = 0;
            $totalAmount = 0;
            $totalPaid = 0;
            $totalPending = 0;

            $query->chunk(100, function ($payments) use ($handle, &$stt, &$totalAmount, &$totalPaid, &$totalPending) {
                foreach ($payments as $payment) {
                    $stt++;
                    $amount = (float) $payment->amount;
                    $totalAmount += $amount;

                    if ($payment->status === 'PAID') {
                        $totalPaid += $amount;
                    } elseif ($payment->status === 'PENDING') {
                        $totalPending += $amount;
                    }

                    $methodLabel = match ($payment->method) {
                        'BANK_TRANSFER' => 'Chuyển khoản (VietQR / SePAY)',
                        'COD' => 'Tiền mặt khi nhận hàng (COD)',
                        'E_WALLET' => 'Ví điện tử',
                        'CARD' => 'Thẻ quốc tế / ATM',
                        default => $payment->method,
                    };

                    $statusLabel = match ($payment->status) {
                        'PAID' => 'Đã thanh toán',
                        'PENDING' => 'Chờ thanh toán',
                        'FAILED' => 'Thất bại',
                        'REFUNDED' => 'Đã hoàn tiền',
                        default => $payment->status,
                    };

                    $statusColor = match ($payment->status) {
                        'PAID' => '#059669',
                        'PENDING' => '#D97706',
                        'FAILED' => '#DC2626',
                        'REFUNDED' => '#7C3AED',
                        default => '#374151',
                    };

                    $phoneRaw = $payment->order?->recipient_phone ?? $payment->order?->customer?->phone ?? '—';
                    $gdCode = 'PAY-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT);
                    $orderCode = $payment->order?->order_code ?? '—';
                    $recipientName = $payment->order?->recipient_name ?? $payment->order?->customer?->full_name ?? 'Khách vãng lai';
                    $confirmedBy = $payment->confirmedByUser?->full_name ?? ($payment->status === 'PAID' ? 'Hệ thống tự động' : '—');
                    $codReconciled = $payment->cod_reconciled_at ? 'Đã đối soát (' . $payment->cod_reconciled_at->format('d/m/Y H:i') . ')' : '—';
                    $codSettled = $payment->cod_settled_at ? 'Đã nhận về TK (' . $payment->cod_settled_at->format('d/m/Y H:i') . ')' : '—';

                    $row = "<tr>";
                    $row .= "<td class=\"center\">{$stt}</td>";
                    $row .= "<td class=\"text center\">{$gdCode}</td>";
                    $row .= "<td class=\"text center\"><strong>{$orderCode}</strong></td>";
                    $row .= "<td>" . htmlspecialchars($recipientName) . "</td>";
                    $row .= "<td class=\"text center\">" . htmlspecialchars($phoneRaw) . "</td>";
                    $row .= "<td>" . htmlspecialchars($methodLabel) . "</td>";
                    $row .= "<td class=\"number\">" . number_format($amount, 0, ',', '.') . "</td>";
                    $row .= "<td class=\"text center\">" . htmlspecialchars($payment->transaction_ref ?: '—') . "</td>";
                    $row .= "<td class=\"center\" style=\"color: {$statusColor}; font-weight: bold;\">" . htmlspecialchars($statusLabel) . "</td>";
                    $row .= "<td class=\"center\">" . ($payment->created_at ? $payment->created_at->format('d/m/Y H:i:s') : '—') . "</td>";
                    $row .= "<td class=\"center\">" . ($payment->paid_at ? $payment->paid_at->format('d/m/Y H:i:s') : '—') . "</td>";
                    $row .= "<td>" . htmlspecialchars($confirmedBy) . "</td>";
                    $row .= "<td class=\"center\">" . htmlspecialchars($codReconciled) . "</td>";
                    $row .= "<td class=\"center\">" . htmlspecialchars($codSettled) . "</td>";
                    $row .= "<td>" . htmlspecialchars($payment->note ?: '—') . "</td>";
                    $row .= "</tr>\r\n";

                    fwrite($handle, $row);
                }
            });

            // Dòng tổng kết
            fwrite($handle, "</tbody><tfoot><tr style=\"font-weight: bold; background-color: #FAF6EE;\">");
            fwrite($handle, "<td colspan=\"3\" class=\"center\">TỔNG CỘNG</td>");
            fwrite($handle, "<td colspan=\"3\">Tổng số giao dịch: <strong>{$stt}</strong></td>");
            fwrite($handle, "<td class=\"number\" style=\"font-size: 11pt; color: #B45309;\">" . number_format($totalAmount, 0, ',', '.') . "</td>");
            fwrite($handle, "<td colspan=\"8\">Thực thu (Đã thanh toán): <strong>" . number_format($totalPaid, 0, ',', '.') . " VNĐ</strong> | Đang chờ: <strong>" . number_format($totalPending, 0, ',', '.') . " VNĐ</strong></td>");
            fwrite($handle, "</tr></tfoot></table></body></html>\r\n");

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Shared filter builder for list and export.
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = Payment::with([
            'order.customer',
            'order.details.product',
            'confirmedByUser',
            'reconciledByUser',
            'settledByUser',
            'latestRefundRequest',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_ref', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%")
                    ->orWhereHas('order', function ($orderQ) use ($search) {
                        $orderQ->where('order_code', 'LIKE', "%{$search}%")
                            ->orWhere('recipient_name', 'LIKE', "%{$search}%")
                            ->orWhere('recipient_phone', 'LIKE', "%{$search}%")
                            ->orWhereHas('customer', function ($custQ) use ($search) {
                                $custQ->where('full_name', 'LIKE', "%{$search}%")
                                    ->orWhere('phone', 'LIKE', "%{$search}%")
                                    ->orWhere('email', 'LIKE', "%{$search}%");
                            });
                    });
            });
        }

        if ($request->filled('date_preset')) {
            match ($request->date_preset) {
                'today' => $query->whereDate('created_at', Carbon::today()),
                'yesterday' => $query->whereDate('created_at', Carbon::yesterday()),
                '7days' => $query->where('created_at', '>=', Carbon::now()->subDays(7)),
                'this_month' => $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year),
                default => null,
            };
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
        }

        return $query->orderBy('created_at', 'desc');
    }
}

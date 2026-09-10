<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentRefundRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Display daily payments ledger for operations staff.
     * Quy định 6: Chỉ xem đơn hàng ngày, không thấy lợi nhuận, không thấy dòng tiền tổng.
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'transactions'); // transactions | cod | refund_requests

        // 1. Daily Operational Counters (Chỉ số vận hành hôm nay, KHÔNG lộ doanh thu/lợi nhuận toàn shop)
        $operationalStats = [
            'today_total' => Payment::whereDate('created_at', Carbon::today())->count(),
            'today_pending' => Payment::whereDate('created_at', Carbon::today())->where('status', 'PENDING')->count(),
            'today_paid' => Payment::whereDate('created_at', Carbon::today())->where('status', 'PAID')->count(),
            'cod_unreconciled' => Payment::where('method', 'COD')->whereNull('cod_reconciled_at')->count(),
            'my_pending_refunds' => PaymentRefundRequest::where('requested_by', auth()->id())->where('status', 'PENDING')->count(),
        ];

        // 2. Tab counts
        $counts = [
            'ALL' => Payment::whereDate('created_at', Carbon::today())->count(),
            'PENDING' => Payment::whereDate('created_at', Carbon::today())->where('status', 'PENDING')->count(),
            'PAID' => Payment::whereDate('created_at', Carbon::today())->where('status', 'PAID')->count(),
            'FAILED' => Payment::whereDate('created_at', Carbon::today())->where('status', 'FAILED')->count(),
            'REFUNDED' => Payment::whereDate('created_at', Carbon::today())->where('status', 'REFUNDED')->count(),
        ];

        // 3. Transactions query (Giới hạn trong ngày hôm nay)
        $query = Payment::with([
            'order.customer',
            'order.details.product',
            'confirmedByUser',
            'reconciledByUser',
            'latestRefundRequest',
        ])->whereDate('created_at', Carbon::today());

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
                                    ->orWhere('phone', 'LIKE', "%{$search}%");
                            });
                    });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15, ['*'], 'p_page')->withQueryString();

        // 4. COD Reconciliation query (Chức năng 4: Đối soát đơn COD)
        $codQuery = Payment::with(['order.customer', 'reconciledByUser'])
            ->where('method', 'COD');

        if ($request->filled('cod_status')) {
            if ($request->cod_status === 'RECONCILED') {
                $codQuery->whereNotNull('cod_reconciled_at');
            } elseif ($request->cod_status === 'UNRECONCILED') {
                $codQuery->whereNull('cod_reconciled_at');
            }
        }

        if ($request->filled('cod_search')) {
            $codSearch = trim($request->cod_search);
            $codQuery->where(function ($q) use ($codSearch) {
                $q->whereHas('order', function ($orderQ) use ($codSearch) {
                    $orderQ->where('order_code', 'LIKE', "%{$codSearch}%")
                        ->orWhere('recipient_name', 'LIKE', "%{$codSearch}%")
                        ->orWhere('recipient_phone', 'LIKE', "%{$codSearch}%");
                });
            });
        }

        $codPayments = $codQuery->orderBy('created_at', 'desc')->paginate(15, ['*'], 'cod_page')->withQueryString();

        // 5. Staff's Refund Requests (Chức năng 3: Yêu cầu hoàn tiền)
        $refundRequests = PaymentRefundRequest::with(['payment', 'order', 'approvedByUser'])
            ->where('requested_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'rf_page')
            ->withQueryString();

        return view('staff.payments.index', compact(
            'activeTab',
            'operationalStats',
            'counts',
            'payments',
            'codPayments',
            'refundRequests'
        ));
    }

    /**
     * Xác nhận thanh toán thủ công (Quy định 2: Bắt buộc nhập lý do & ảnh bill).
     */
    public function manualConfirm(Request $request, Payment $payment)
    {
        if ($payment->status === 'PAID') {
            return redirect()->back()->with('info', 'Giao dịch này đã được xác nhận thanh toán trước đó.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:500',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'reason.required' => 'Nhân viên bắt buộc phải nhập lý do xác nhận thanh toán.',
            'reason.min' => 'Lý do xác nhận thanh toán tối thiểu 3 ký tự.',
            'proof_image.required' => 'Nhân viên bắt buộc phải tải lên ảnh chụp bill chuyển khoản / chứng từ.',
            'proof_image.image' => 'File tải lên phải là hình ảnh (jpg, png, webp).',
            'proof_image.max' => 'Dung lượng ảnh không được vượt quá 5MB.',
        ]);

        $proofPath = $request->file('proof_image')->store('payments/proofs', 'public');

        $payment->update([
            'status' => 'PAID',
            'proof_image' => $proofPath,
            'note' => $validated['reason'],
            'confirmed_by' => auth()->id(),
            'paid_at' => now(),
        ]);

        if ($payment->order) {
            $payment->order->update(['payment_status' => 'PAID']);

            OrderStatusHistory::create([
                'order_id' => $payment->order->id,
                'from_status' => $payment->order->order_status,
                'to_status' => $payment->order->order_status,
                'changed_by' => auth()->id(),
                'note' => "Nhân viên đã xác nhận thanh toán thủ công. Lý do: {$validated['reason']}",
                'changed_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', "Xác nhận thanh toán thành công cho đơn #{$payment->order?->order_code} kèm chứng từ hợp lệ.");
    }

    /**
     * Tạo yêu cầu hoàn tiền (Quy định 3: Nhân viên chỉ tạo yêu cầu, chờ Admin duyệt).
     */
    public function requestRefund(Request $request, Payment $payment)
    {
        if ($payment->status !== 'PAID') {
            return redirect()->back()->with('error', 'Chỉ có thể yêu cầu hoàn tiền cho giao dịch đã thanh toán thành công.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000|max:' . $payment->amount,
            'reason' => 'required|string|min:5|max:1000',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:100',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền cần hoàn.',
            'amount.max' => 'Số tiền hoàn không được vượt quá tổng tiền thanh toán.',
            'reason.required' => 'Vui lòng nhập lý do đề xuất hoàn tiền.',
            'reason.min' => 'Lý do hoàn tiền tối thiểu 5 ký tự.',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $proofPath = $request->file('proof_image')->store('payments/refund_proofs', 'public');
        }

        $order = $payment->order;

        PaymentRefundRequest::create([
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'requested_by' => auth()->id(),
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'proof_image' => $proofPath,
            'bank_name' => $validated['bank_name'] ?: ($order?->refund_bank_name ?? 'MB'),
            'bank_account' => $validated['bank_account'] ?: ($order?->refund_bank_account ?? ''),
            'account_holder' => $validated['account_holder'] ?: ($order?->refund_account_holder ?? ''),
            'status' => 'PENDING',
        ]);

        return redirect()->route('staff.payments.index', ['tab' => 'refund_requests'])
            ->with('success', "Đã tạo yêu cầu hoàn tiền cho đơn #{$order?->order_code}. Yêu cầu đang chờ Admin (Chủ shop) phê duyệt & chuyển tiền.");
    }

    /**
     * Đánh dấu đối soát đơn COD (Quy định 4: Đối chiếu & đánh dấu đơn).
     */
    public function reconcileCod(Request $request, Payment $payment)
    {
        if ($payment->method !== 'COD') {
            return redirect()->back()->with('error', 'Chỉ áp dụng đối soát cho đơn COD.');
        }

        $payment->update([
            'cod_reconciled_at' => now(),
            'cod_reconciled_by' => auth()->id(),
            'status' => 'PAID', // Tiền COD đã thu từ shipper
            'paid_at' => $payment->paid_at ?? now(),
        ]);

        if ($payment->order && $payment->order->payment_status !== 'PAID') {
            $payment->order->update(['payment_status' => 'PAID']);
        }

        return redirect()->back()->with('success', "Đã đánh dấu đối soát thành công đơn COD #{$payment->order?->order_code}.");
    }

    /**
     * Đánh dấu hàng loạt đơn COD đã đối soát.
     */
    public function bulkReconcileCod(Request $request)
    {
        $paymentIds = $request->input('payment_ids', []);
        if (empty($paymentIds)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một đơn COD để đối soát.');
        }

        $updated = Payment::whereIn('id', $paymentIds)
            ->where('method', 'COD')
            ->whereNull('cod_reconciled_at')
            ->update([
                'cod_reconciled_at' => now(),
                'cod_reconciled_by' => auth()->id(),
                'status' => 'PAID',
                'paid_at' => now(),
            ]);

        return redirect()->back()->with('success', "Đã đối soát hàng loạt {$updated} đơn COD thành công!");
    }

    /**
     * Xuất bảng kê đơn COD để nhân viên đối soát với bưu cục / shipper (Quy định 4).
     */
    public function codExport(Request $request): StreamedResponse
    {
        $query = Payment::with(['order.customer', 'reconciledByUser'])
            ->where('method', 'COD');

        if ($request->filled('cod_status')) {
            if ($request->cod_status === 'RECONCILED') {
                $query->whereNotNull('cod_reconciled_at');
            } elseif ($request->cod_status === 'UNRECONCILED') {
                $query->whereNull('cod_reconciled_at');
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('transaction_ref', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_code', 'like', "%{$search}%")
                         ->orWhere('recipient_name', 'like', "%{$search}%")
                         ->orWhere('recipient_phone', 'like', "%{$search}%");
                  });
            });
        }

        $fileName = 'bang_ke_doi_soat_cod_' . Carbon::now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            
            // 1. UTF-8 BOM + Chỉ dẫn mở đúng cột trong Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");
            fputs($handle, "sep=,\r\n");

            // 2. Tiêu đề các cột
            fputcsv($handle, [
                'STT',
                'Mã GD',
                'Mã Đơn Hàng',
                'Ngày Đặt Hàng',
                'Khách Hàng / Người Nhận',
                'Số Điện Thoại',
                'Địa Chỉ Nhận Hàng',
                'Tiền Thu Hộ COD (VNĐ)',
                'Trạng Thái Giao Hàng',
                'Trạng Thái Đối Soát',
                'Thời Gian Đối Soát',
                'Nhân Viên Đối Soát',
                'Ghi Chú',
            ], ',', '"', '\\');

            $orderStatusLabels = [
                'PENDING' => 'Chờ xử lý',
                'CONFIRMED' => 'Đã xác nhận',
                'PREPARING' => 'Đang chuẩn bị',
                'PROCESSING' => 'Đang chuẩn bị',
                'SHIPPING' => 'Đang giao hàng',
                'SHIPPED' => 'Đang giao hàng',
                'COMPLETED' => 'Giao thành công',
                'DELIVERED' => 'Giao thành công',
                'CANCELLED' => 'Đã hủy',
                'RETURNED' => 'Đã hoàn trả',
            ];

            $cleanText = function (?string $text): string {
                if ($text === null || trim($text) === '') {
                    return '—';
                }
                // Triệt tiêu ký tự xuống dòng (\r, \n) và tab để tránh bị nhảy hàng lệch cột trên Excel
                $cleaned = preg_replace('/[\r\n\t]+/', ', ', trim($text));
                $cleaned = preg_replace('/\s+/', ' ', $cleaned);
                return trim($cleaned, " ,");
            };

            $stt = 0;
            $totalAmount = 0;
            $totalReconciled = 0;
            $totalUnreconciled = 0;

            $query->orderBy('id', 'desc')->chunk(100, function ($payments) use (
                $handle, &$stt, &$totalAmount, &$totalReconciled, &$totalUnreconciled, $orderStatusLabels, $cleanText
            ) {
                foreach ($payments as $p) {
                    $stt++;
                    $order = $p->order;
                    $amount = (float) $p->amount;
                    $totalAmount += $amount;

                    $isReconciled = ! empty($p->cod_reconciled_at);
                    if ($isReconciled) {
                        $totalReconciled++;
                    } else {
                        $totalUnreconciled++;
                    }

                    // Xử lý số điện thoại để giữ nguyên số 0 đầu trong Excel
                    $phoneRaw = $order?->recipient_phone ?? $order?->customer?->phone ?? '';
                    $phoneFormatted = ! empty($phoneRaw) ? '="' . preg_replace('/[^0-9+]/', '', $phoneRaw) . '"' : '—';

                    // Xử lý mã giao dịch và mã đơn để hiển thị dạng Text rõ ràng
                    $gdCode = '="PAY-' . str_pad($p->id, 5, '0', STR_PAD_LEFT) . '"';
                    $orderCode = $order?->order_code ? '="' . $order->order_code . '"' : '—';

                    $orderStatus = $order?->order_status ?? '';
                    $orderStatusText = $orderStatusLabels[$orderStatus] ?? ($orderStatus ?: '—');

                    fputcsv($handle, [
                        $stt,
                        $gdCode,
                        $orderCode,
                        $order?->created_at ? $order->created_at->format('d/m/Y H:i') : ($p->created_at ? $p->created_at->format('d/m/Y H:i') : '—'),
                        $cleanText($order?->recipient_name ?? $order?->customer?->full_name),
                        $phoneFormatted,
                        $cleanText($order?->recipient_address),
                        round($amount),
                        $orderStatusText,
                        $isReconciled ? 'Đã đối soát' : 'Chưa đối soát',
                        $isReconciled ? $p->cod_reconciled_at->format('d/m/Y H:i') : '—',
                        $cleanText($p->reconciledByUser?->full_name),
                        $cleanText($order?->note ?? $p->note),
                    ], ',', '"', '\\');
                }
            });

            // 3. Dòng tổng kết ở cuối bảng
            fputcsv($handle, [
                'TỔNG CỘNG',
                '',
                '',
                '',
                "Tổng số đơn: {$stt}",
                '',
                'Tổng tiền thu hộ:',
                $totalAmount,
                '',
                "Đã đối soát: {$totalReconciled} | Chưa: {$totalUnreconciled}",
                '',
                '',
                '',
            ], ',', '"', '\\');

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Cập nhật trạng thái thanh toán từ trang chi tiết đơn hàng (Hỗ trợ tương thích ngược)
     */
    public function updateStatus(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:PENDING,PAID,FAILED,REFUNDED',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $orderService = app(\App\Services\OrderService::class);
            match ($validated['status']) {
                'PAID' => $orderService->confirmPayment($payment, auth()->id()),
                'FAILED' => $orderService->markPaymentFailed($payment),
                'REFUNDED' => $orderService->refundPayment($payment),
                default => null,
            };
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thanh toán thành công.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\SepayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private SepayService $sepayService
    ) {}

    /**
     * Display payments ledger and financial reconciliation dashboard.
     */
    public function index(Request $request)
    {
        // 1. KPI Financial Summaries
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
        ];

        // 3. Build Filtered Query
        $query = $this->buildFilteredQuery($request);

        $payments = $query->paginate(15)->withQueryString();

        return view('admin.payments.index', compact('payments', 'kpi', 'counts'));
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
                // Refresh payment to get confirmed state
                $payment->refresh();
                return redirect()->back()->with('success', "Đối soát SePAY thành công! Đã khớp giao dịch ngân hàng và xác nhận thanh toán đơn {$payment->order->order_code}.");
            }

            return redirect()->back()->with('error', "Chưa tìm thấy biến động số dư khớp với đơn {$payment->order->order_code} trên SePAY. Khách có thể chưa chuyển khoản hoặc chuyển sai cú pháp.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi kết nối tới cổng SePAY: ' . $e->getMessage());
        }
    }

    /**
     * Update payment status manually (Confirm COD, Mark Failed, Refund).
     */
    public function updateStatus(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:PENDING,PAID,FAILED,REFUNDED',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            match ($validated['status']) {
                'PAID' => $this->orderService->confirmPayment($payment, auth()->id()),
                'FAILED' => $this->orderService->markPaymentFailed($payment),
                'REFUNDED' => $this->orderService->refundPayment($payment),
                default => null,
            };
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thanh toán thành công.');
    }

    /**
     * Export payment transactions to CSV file with UTF-8 BOM.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->buildFilteredQuery($request);
        $fileName = 'bao_cao_thanh_toan_' . Carbon::now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM for proper Excel Vietnamese rendering
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'Mã GD',
                'Mã Đơn Hàng',
                'Khách Hàng',
                'Số Điện Thoại',
                'Phương Thức',
                'Số Tiền (VND)',
                'Mã Tham Chiếu NH / Cổng',
                'Trạng Thái',
                'Thời Gian Tạo',
                'Thời Gian Thanh Toán',
                'Người Duyệt',
            ]);

            // Stream rows in chunks to prevent memory exhaust
            $query->chunk(100, function ($payments) use ($handle) {
                foreach ($payments as $payment) {
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

                    fputcsv($handle, [
                        '#PAY-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT),
                        $payment->order?->order_code ?? '—',
                        $payment->order?->recipient_name ?? $payment->order?->customer?->full_name ?? 'Khách vãng lai',
                        $payment->order?->recipient_phone ?? $payment->order?->customer?->phone ?? '—',
                        $methodLabel,
                        (float) $payment->amount,
                        $payment->transaction_ref ?? '—',
                        $statusLabel,
                        $payment->created_at ? $payment->created_at->format('d/m/Y H:i:s') : '—',
                        $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i:s') : '—',
                        $payment->confirmedByUser?->full_name ?? ($payment->status === 'PAID' ? 'Hệ thống tự động' : '—'),
                    ]);
                }
            });

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
            'confirmedByUser'
        ]);

        // Filter by Status Tab
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Payment Method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter by Search Keyword
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

        // Filter by Date Presets or Custom Range
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

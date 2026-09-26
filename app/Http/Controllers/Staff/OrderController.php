<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentRefundRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        // Tự động quét và từ chối các yêu cầu hủy đã quá 24h nhân viên chưa xử lý
        $this->orderService->autoRejectExpiredCancelRequests();

        $query = Order::with(['customer', 'latestPayment', 'details.product.images', 'details.productVariant']);

        // Lọc theo tab Yêu cầu hủy hoặc Cần hoàn tiền
        // Nhân viên chỉ xử lý yêu cầu hủy của đơn đang ở trạng thái "Đang chuẩn bị hàng"
        if ($request->query('tab') === 'cancel_requests') {
            $query->where('cancel_request_status', 'PENDING')
                ->whereIn('order_status', ['PREPARING', 'CONFIRMED']);
        } elseif ($request->query('tab') === 'need_refund') {
            $query->where('order_status', 'CANCELLED')->where('payment_status', 'PAID');
        } elseif ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_phone', 'like', "%{$search}%");
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [15, 30, 50, 100], true)
            ? (int) $request->input('per_page')
            : 15;

        $orders = $query->latest()->paginate($perPage);

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('order_status', 'PENDING')->count(),
            'confirmed' => Order::where('order_status', 'CONFIRMED')->count(),
            'preparing' => Order::where('order_status', 'PREPARING')->count(),
            'shipping' => Order::where('order_status', 'SHIPPING')->count(),
            'completed' => Order::where('order_status', 'COMPLETED')->count(),
            'returned' => Order::where('order_status', 'RETURNED')->count(),
            'cancelled' => Order::where('order_status', 'CANCELLED')->count(),
        ];

        // Nhân viên chỉ đếm các đơn PENDING cancel request đang ở trạng thái chuẩn bị hàng
        $pendingCancelRequestsCount = Order::where('cancel_request_status', 'PENDING')
            ->whereIn('order_status', ['PREPARING', 'CONFIRMED'])
            ->count();
        $needRefundCount = Order::where('order_status', 'CANCELLED')
            ->where('payment_status', 'PAID')
            ->count();

        return view('staff.orders.index', compact('orders', 'stats', 'pendingCancelRequestsCount', 'needRefundCount'));
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|integer|exists:orders,id',
            'target_status' => 'nullable|in:CONFIRMED,PREPARING,SHIPPING,COMPLETED',
        ]);

        $targetStatus = $validated['target_status'] ?? 'SHIPPING';
        $changedBy = auth()->id();

        try {
            $result = $this->orderService->bulkUpdateStatus(
                $validated['order_ids'],
                $targetStatus,
                $changedBy,
                'Cập nhật trạng thái hàng loạt bởi '.(auth()->user()->full_name ?? auth()->user()->name ?? 'Nhân viên')
            );

            $statusLabels = [
                'SHIPPING' => 'Đang giao hàng',
                'CONFIRMED' => 'Đã xác nhận',
                'PREPARING' => 'Chờ lấy hàng',
                'COMPLETED' => 'Đã giao thành công',
            ];
            $label = $statusLabels[$targetStatus] ?? $targetStatus;

            if ($result['updated'] > 0) {
                $msg = "Đã chuyển {$result['updated']} đơn hàng sang trạng thái '{$label}' thành công.";
                if ($result['skipped'] > 0) {
                    $msg .= " (Bỏ qua {$result['skipped']} đơn do trạng thái không phù hợp).";
                }

                return redirect()->back()->with('success', $msg);
            }

            return redirect()->back()->with('error', "Không có đơn hàng nào hợp lệ để chuyển sang trạng thái '{$label}'.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi thao tác hàng loạt: '.$e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $this->orderService->checkAndRejectIfCancelRequestExpired($order);
        $order->refresh();

        $order->load([
            'customer',
            'details.product',
            'details.productVariant',
            'payments',
            'statusHistories.changedByUser',
            'voucher',
            'latestRefundRequest.requestedByUser',
            'latestRefundRequest.approvedByUser',
        ]);

        return view('staff.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:PENDING,CONFIRMED,PREPARING,SHIPPING,COMPLETED,CANCELLED,RETURNED',
            'cancel_reason' => 'required_if:order_status,CANCELLED|nullable|string|max:255',
            'stock_returned' => 'nullable|boolean',
        ]);

        try {
            $this->orderService->updateStatus(
                $order,
                $validated['order_status'],
                auth()->id(),
                $validated['cancel_reason'] ?? match ($validated['order_status']) {
                    'SHIPPING' => 'Shop bắt đầu giao hàng.',
                    'COMPLETED' => 'Shop xác nhận đã giao hàng thành công.',
                    default => null,
                },
                (bool) ($validated['stock_returned'] ?? false)
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    /**
     * Nhân viên duyệt hủy đơn hàng (Chỉ cho phép đơn đang ở trạng thái PREPARING/CONFIRMED)
     */
    public function approveCancel(Request $request, Order $order)
    {
        if (! in_array($order->order_status, ['PREPARING', 'CONFIRMED'], true)) {
            return redirect()->back()->with('error', 'Nhân viên chỉ có thể duyệt hủy các đơn hàng đang ở trạng thái "Đang chuẩn bị hàng". Yêu cầu hủy & hoàn tiền của đơn chưa xác nhận do Quản trị viên (Admin) xử lý.');
        }

        $validated = $request->validate([
            'refund_note' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->approveCancelOrder($order, auth()->id(), $validated['refund_note'] ?? null);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đã duyệt yêu cầu hủy đơn hàng thành công!');
    }

    /**
     * Nhân viên từ chối hủy đơn hàng (Bắt buộc nhập lý do từ chối - Ví dụ đã giao cho đơn vị vận chuyển)
     */
    public function rejectCancel(Request $request, Order $order)
    {
        if (! in_array($order->order_status, ['PREPARING', 'CONFIRMED'], true)) {
            return redirect()->back()->with('error', 'Nhân viên chỉ có thể từ chối hủy các đơn hàng đang ở trạng thái "Đang chuẩn bị hàng". Yêu cầu hủy & hoàn tiền của đơn chưa xác nhận do Quản trị viên (Admin) xử lý.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ], [
            'rejection_reason.required' => 'Vui lòng nhập lý do từ chối hủy đơn.',
            'rejection_reason.min' => 'Lý do từ chối cần ít nhất 5 ký tự.',
        ]);

        try {
            $this->orderService->rejectCancelOrder($order, auth()->id(), $validated['rejection_reason']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đã từ chối yêu cầu hủy đơn hàng.');
    }

    /**
     * Nhân viên chủ động từ chối đơn hàng mới (PENDING).
     * Bắt buộc nhập lý do từ chối để gửi thông báo rõ ràng cho khách hàng.
     */
    public function rejectOrder(Request $request, Order $order)
    {
        if ($order->order_status !== 'PENDING') {
            return redirect()->back()->with('error', 'Chỉ có thể từ chối đơn hàng đang ở trạng thái chờ xác nhận.');
        }

        $validated = $request->validate([
            'reject_reason' => 'required|string|min:3|max:500',
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối đơn hàng.',
            'reject_reason.min' => 'Lý do từ chối cần có ít nhất 3 ký tự.',
        ]);

        try {
            $this->orderService->rejectOrderByShop($order, auth()->id(), $validated['reject_reason']);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Không thể từ chối đơn hàng: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', "Đã từ chối đơn hàng #{$order->order_code}. Lý do đã được cập nhật gửi tới khách hàng.");
    }

    /**
     * Nhân viên gửi yêu cầu hoàn tiền lên Admin phê duyệt
     */
    public function requestRefund(Request $request, Order $order)
    {
        if (! $order->needsRefund() && $order->payment_status !== 'PAID') {
            return redirect()->back()->with('error', 'Đơn hàng này không ở trạng thái cần hoàn tiền.');
        }

        // Kiểm tra xem đã có yêu cầu PENDING chưa
        $existingPending = PaymentRefundRequest::where('order_id', $order->id)
            ->where('status', 'PENDING')
            ->first();

        if ($existingPending) {
            return redirect()->back()->with('error', 'Đơn hàng này đã có yêu cầu hoàn tiền đang chờ Admin xử lý.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000|max:'.max((float) $order->total_amount, 1000),
            'reason' => 'required|string|min:5|max:1000',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:100',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền cần hoàn.',
            'reason.required' => 'Vui lòng nhập lý do đề xuất hoàn tiền.',
            'reason.min' => 'Lý do hoàn tiền tối thiểu 5 ký tự.',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $proofPath = $request->file('proof_image')->store('payments/refund_proofs', 'public');
        }

        $payment = $order->payments->where('status', 'PAID')->first() ?? $order->latestPayment;
        if (! $payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method ?? 'BANK_TRANSFER',
                'status' => 'PAID',
                'amount' => $order->total_amount,
                'paid_at' => now(),
            ]);
        }

        $refundReq = PaymentRefundRequest::create([
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'requested_by' => auth()->id(),
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'proof_image' => $proofPath,
            'bank_name' => $validated['bank_name'] ?: ($order->refund_bank_name ?? 'MB'),
            'bank_account' => $validated['bank_account'] ?: ($order->refund_bank_account ?? ''),
            'account_holder' => $validated['account_holder'] ?: ($order->refund_account_holder ?? ''),
            'status' => 'PENDING',
        ]);

        // Cập nhật lại thông tin ngân hàng vào đơn nếu có thay đổi
        if ($validated['bank_account'] || $validated['bank_name'] || $validated['account_holder']) {
            $order->update([
                'refund_bank_name' => $validated['bank_name'] ?: $order->refund_bank_name,
                'refund_bank_account' => $validated['bank_account'] ?: $order->refund_bank_account,
                'refund_account_holder' => $validated['account_holder'] ?: $order->refund_account_holder,
            ]);
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $order->order_status,
            'to_status' => $order->order_status,
            'changed_by' => auth()->id(),
            'note' => 'Nhân viên '.(auth()->user()->full_name ?? auth()->user()->name ?? 'CSKH').' đã gửi yêu cầu hoàn tiền ('.number_format($refundReq->amount, 0, ',', '.').'đ) lên Admin. Lý do: '.$validated['reason'],
            'changed_at' => now(),
        ]);

        return redirect()->back()->with('success', "Đã gửi yêu cầu hoàn tiền cho đơn #{$order->order_code} lên Admin thành công! Đang chờ Admin phê duyệt & chuyển khoản.");
    }

    /**
     * Nhân viên cập nhật thông tin số tài khoản của khách hàng
     */
    public function updateRefundAccount(Request $request, Order $order)
    {
        $validated = $request->validate([
            'refund_bank_name' => 'required|string|max:100',
            'refund_bank_account' => 'required|string|max:50',
            'refund_account_holder' => 'required|string|max:100',
        ], [
            'refund_bank_name.required' => 'Vui lòng chọn hoặc nhập tên ngân hàng.',
            'refund_bank_account.required' => 'Vui lòng nhập số tài khoản ngân hàng.',
            'refund_account_holder.required' => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        $order->update($validated);

        PaymentRefundRequest::where('order_id', $order->id)
            ->where('status', 'PENDING')
            ->update([
                'bank_name' => $validated['refund_bank_name'],
                'bank_account' => $validated['refund_bank_account'],
                'account_holder' => $validated['refund_account_holder'],
            ]);

        $userName = auth()->user()->full_name ?? auth()->user()->name ?? 'Nhân viên';
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $order->order_status,
            'to_status' => $order->order_status,
            'changed_by' => auth()->id(),
            'note' => "{$userName} đã cập nhật thông tin STK hoàn tiền của khách: {$validated['refund_bank_name']} - {$validated['refund_bank_account']} ({$validated['refund_account_holder']})",
            'changed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Đã lưu thông tin tài khoản ngân hàng của khách hàng thành công!');
    }
}

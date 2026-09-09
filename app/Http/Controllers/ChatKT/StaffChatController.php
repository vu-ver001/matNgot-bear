<?php

namespace App\Http\Controllers\ChatKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatKT\StaffSendMessageRequest;
use App\Models\Order;
use App\Models\SupportCase;
use App\Models\User;
use App\Services\ChatKT\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    /**
     * Hiển thị bảng điều khiển Hỗ trợ khách hàng dành cho Nhân viên (Staff) và Quản trị viên (Admin).
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $statusTab = $request->query('tab', 'all');
        $search = $request->query('q');

        $counts = $this->chatService->getStaffCounts($search, $user);
        $cases = $this->chatService->getStaffCases($statusTab, $search, 30, $user);

        // Lấy case đang được chọn hiển thị trong khung chat (chỉ khi được chọn rõ ràng qua case_id hoặc customer_id)
        $selectedCase = null;
        if ($request->filled('case_id')) {
            $selectedCase = SupportCase::with(['customer', 'assignedStaff', 'order.details', 'messages.sender'])
                ->find($request->query('case_id'));

            // Nếu người dùng là Staff (không phải Admin) và case đang được người khác xử lý: Không cho phép mở
            if ($selectedCase && $user->role !== User::ROLE_ADMIN && $selectedCase->isInProgress() && (int) $selectedCase->assigned_staff_id !== (int) $user->id) {
                $selectedCase = null;
            }
        } elseif ($request->filled('customer_id')) {
            $selectedCase = $this->chatService->findOrCreateCaseForStaff(
                $user,
                (int) $request->query('customer_id'),
                $request->filled('order_id') ? (int) $request->query('order_id') : null
            );
            $selectedCase->loadMissing(['customer', 'assignedStaff', 'order.details', 'messages.sender']);
            // Làm mới lại danh sách cases và counts để case mới xuất hiện ngay
            $counts = $this->chatService->getStaffCounts($search, $user);
            $cases = $this->chatService->getStaffCases($statusTab, $search, 30, $user);
        }

        // Đánh dấu đã đọc các tin nhắn từ khách hàng trong case đang mở (chỉ khi là người phụ trách, không đánh dấu khi đang đồng bộ ngầm bg_sync hoặc người xem không phải người phụ trách)
        if (! $request->boolean('bg_sync') && $selectedCase && (int) $selectedCase->assigned_staff_id === (int) $user->id) {
            $this->chatService->markMessagesAsReadForStaff($selectedCase);
        }

        // Xác định layout dựa trên route (Admin hay Staff)
        $isAdminRoute = str_starts_with($request->route()?->getName() ?? '', 'admin.');
        $layout = $isAdminRoute ? 'layouts.admin-dashboard' : 'layouts.staff-dashboard';
        $routePrefix = $isAdminRoute ? 'admin.support' : 'staff.support';

        $staffList = $isAdminRoute
            ? User::where('role', User::ROLE_STAFF)->where('status', User::STATUS_ACTIVE)->orderBy('full_name')->get()
            : collect();

        if ($request->expectsJson() && ! $request->hasHeader('X-Inertia')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'counts' => $counts,
                    'status_tab' => $statusTab,
                    'selected_case_id' => $selectedCase?->id,
                    'cases' => $cases->items(),
                ],
            ]);
        }

        // Đơn hàng gợi ý gửi cho khách (khi nhấn "Nhắn tin cho khách" từ trang chi tiết đơn hàng)
        $suggestedOrder = null;
        if ($request->filled('order_id')) {
            $suggestedOrder = Order::with(['details.product'])->find((int) $request->query('order_id'));
            if ($suggestedOrder && $selectedCase && (int) $suggestedOrder->customer_id !== (int) $selectedCase->customer_id) {
                $suggestedOrder = null;
            }
        }

        return view('ChatKT.staff.index', compact(
            'user',
            'statusTab',
            'search',
            'counts',
            'cases',
            'selectedCase',
            'layout',
            'routePrefix',
            'staffList',
            'suggestedOrder'
        ));
    }

    /**
     * Mở xem chi tiết một Support Case cụ thể.
     */
    public function show(Request $request, SupportCase $case): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        // Không cho phép nhân viên khác truy cập xem case đang do người khác phụ trách
        if ($user->role !== User::ROLE_ADMIN && $case->isInProgress() && (int) $case->assigned_staff_id !== (int) $user->id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuộc hỗ trợ này đang do nhân viên khác phụ trách. Bạn không có quyền truy cập.',
                ], 403);
            }

            return redirect()->route('staff.support.index')->with('warning', 'Cuộc hỗ trợ này đang do nhân viên khác phụ trách.');
        }

        if ($request->expectsJson() || $request->ajax()) {
            $case->load(['customer', 'assignedStaff', 'order.details', 'messages.sender']);
            if ((int) $case->assigned_staff_id === (int) $user->id) {
                $this->chatService->markMessagesAsReadForStaff($case);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'case' => $case,
                    'messages' => $case->messages->map(fn ($m) => [
                        'id' => $m->id,
                        'sender_id' => $m->sender_id,
                        'is_customer' => (int) $m->sender_id === (int) $case->customer_id,
                        'sender_name' => $m->sender?->full_name ?? $m->sender?->name ?? 'Người dùng',
                        'sender_avatar' => $m->sender?->avatar_url,
                        'content' => $m->content,
                        'sent_at' => $m->sent_at?->format('H:i'),
                        'date' => $m->sent_at?->format('d/m/Y'),
                        'is_read' => (bool) $m->is_read,
                    ]),
                ],
            ]);
        }

        $isAdminRoute = str_starts_with($request->route()?->getName() ?? '', 'admin.');
        $routeName = $isAdminRoute ? 'admin.support.index' : 'staff.support.index';

        $tab = match ($case->status) {
            SupportCase::STATUS_IN_PROGRESS => 'in_progress',
            SupportCase::STATUS_CLOSED => 'closed',
            default => 'waiting',
        };

        return redirect()->route($routeName, [
            'tab' => $tab,
            'case_id' => $case->id,
        ]);
    }

    /**
     * Tiếp nhận cuộc hỗ trợ (Nhận xử lý).
     */
    public function accept(Request $request, SupportCase $case): JsonResponse|RedirectResponse
    {
        $result = $this->chatService->acceptCase($request->user(), $case);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 409);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Bàn giao cuộc hỗ trợ về hàng chờ WAITING.
     */
    public function handover(Request $request, SupportCase $case): JsonResponse|RedirectResponse
    {
        $result = $this->chatService->handoverCase($request->user(), $case);

        if ($request->expectsJson() || $request->ajax()) {
            $status = $result['success'] ? 200 : ($result['status_code'] ?? 422);
            return response()->json($result, $status);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Kết thúc cuộc hỗ trợ (CLOSED).
     */
    public function close(Request $request, SupportCase $case): JsonResponse|RedirectResponse
    {
        $result = $this->chatService->closeCase($request->user(), $case);

        if ($request->expectsJson() || $request->ajax()) {
            $status = $result['success'] ? 200 : ($result['status_code'] ?? 422);
            return response()->json($result, $status);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Mở lại phiên hỗ trợ đã kết thúc (Reopen).
     */
    public function reopen(Request $request, SupportCase $case): JsonResponse|RedirectResponse
    {
        $result = $this->chatService->reopenCase($request->user(), $case);

        if ($request->expectsJson() || $request->ajax()) {
            $statusCode = $result['success'] ? 200 : ($result['status_code'] ?? 422);
            return response()->json($result, $statusCode);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Admin tiếp quản cuộc hỗ trợ.
     */
    public function takeover(Request $request, SupportCase $case): JsonResponse|RedirectResponse
    {
        $result = $this->chatService->takeoverCase($request->user(), $case);

        if ($request->expectsJson() || $request->ajax()) {
            $status = $result['success'] ? 200 : ($result['status_code'] ?? 422);
            return response()->json($result, $status);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Admin thu hồi cuộc hỗ trợ về hàng chờ WAITING.
     */
    public function revoke(Request $request, SupportCase $case): JsonResponse|RedirectResponse
    {
        $result = $this->chatService->revokeCase($request->user(), $case);

        if ($request->expectsJson() || $request->ajax()) {
            $status = $result['success'] ? 200 : ($result['status_code'] ?? 422);
            return response()->json($result, $status);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Admin chuyển cuộc hỗ trợ cho nhân viên khác.
     */
    public function assign(Request $request, SupportCase $case): JsonResponse|RedirectResponse
    {
        $request->validate([
            'staff_id' => 'required|integer|exists:users,id',
        ]);

        $result = $this->chatService->assignCase($request->user(), $case, (int) $request->input('staff_id'));

        if ($request->expectsJson() || $request->ajax()) {
            $status = $result['success'] ? 200 : ($result['status_code'] ?? 422);
            return response()->json($result, $status);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Nhân viên / Admin gửi tin nhắn phản hồi cho khách hàng.
     */
    public function sendMessage(StaffSendMessageRequest $request, SupportCase $case): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $message = $this->chatService->staffSendMessage($user, $case, $request->input('content'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Phản hồi đã được gửi thành công.',
                'data' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'is_customer' => false,
                    'sender_name' => $user->full_name ?? $user->name ?? 'Nhân viên hỗ trợ',
                    'content' => $message->content,
                    'sent_at' => $message->sent_at?->format('H:i'),
                    'date' => $message->sent_at?->format('d/m/Y'),
                    'is_read' => (bool) $message->is_read,
                ],
            ], 201);
        }

        return back()->with('success', 'Đã gửi phản hồi.');
    }

    /**
     * Polling cập nhật tin nhắn mới của case phía Staff / Admin.
     */
    public function poll(Request $request, SupportCase $case): JsonResponse
    {
        $afterId = (int) $request->query('after_id', 0);

        $newMessages = \App\Models\Message::where('conversation_id', $case->conversation_id)
            ->where('id', '>', $afterId)
            ->with('sender')
            ->orderBy('id', 'asc')
            ->get();

        $user = $request->user();
        $isClientHidden = $request->boolean('is_hidden');
        if (! $isClientHidden && $newMessages->isNotEmpty() && (int) $case->assigned_staff_id === (int) $user->id) {
            $this->chatService->markMessagesAsReadForStaff($case);
        }

        return response()->json([
            'success' => true,
            'data' => $newMessages->map(fn ($m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'is_customer' => (int) $m->sender_id === (int) $case->customer_id,
                'sender_name' => $m->sender?->full_name ?? $m->sender?->name ?? 'Người dùng',
                'sender_avatar' => $m->sender?->avatar_url,
                'content' => $m->content,
                'sent_at' => $m->sent_at?->format('H:i'),
                'date' => $m->sent_at?->format('d/m/Y'),
                'is_read' => (bool) $m->is_read,
            ]),
        ]);
    }
}

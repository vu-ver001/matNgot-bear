<?php

namespace App\Http\Controllers\ChatKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatKT\SendMessageRequest;
use App\Services\ChatKT\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    /**
     * Hiển thị giao diện Tin nhắn / Hỗ trợ của Khách hàng.
     */
    public function index(Request $request): View|JsonResponse
    {
        $customer = $request->user();
        $conversation = $this->chatService->getOrCreateCustomerConversation($customer);

        // Đánh dấu đã đọc các tin nhắn gửi từ Shop
        $this->chatService->markMessagesAsReadForCustomer($conversation);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('id', 'asc')
            ->get();

        $activeCase = $conversation->activeCase;

        if ($request->expectsJson() && ! $request->hasHeader('X-Inertia')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversation->id,
                    'active_case' => $activeCase ? [
                        'id' => $activeCase->id,
                        'case_code' => $activeCase->case_code,
                        'status' => $activeCase->status,
                        'status_label' => $activeCase->status_label,
                    ] : null,
                    'messages' => $messages->map(fn ($m) => [
                        'id' => $m->id,
                        'sender_id' => $m->sender_id,
                        'is_self' => (int) $m->sender_id === (int) $customer->id,
                        'content' => $m->content,
                        'sent_at' => $m->sent_at?->format('H:i'),
                        'date' => $m->sent_at?->format('d/m/Y'),
                        'is_read' => (bool) $m->is_read,
                    ]),
                ],
            ]);
        }

        return view('ChatKT.customer.index', compact('customer', 'conversation', 'messages', 'activeCase'));
    }

    /**
     * Khách hàng gửi tin nhắn cho Mật Ngọt Bear Support.
     */
    public function send(SendMessageRequest $request): JsonResponse|RedirectResponse
    {
        $customer = $request->user();
        $orderId = $request->filled('order_id') ? (int) $request->input('order_id') : null;

        $message = $this->chatService->customerSendMessage(
            $customer,
            $request->input('content'),
            $orderId
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tin nhắn đã được gửi thành công.',
                'data' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'is_self' => true,
                    'content' => $message->content,
                    'sent_at' => $message->sent_at?->format('H:i'),
                    'date' => $message->sent_at?->format('d/m/Y'),
                    'is_read' => (bool) $message->is_read,
                ],
            ], 201);
        }

        return back()->with('success', 'Tin nhắn đã được gửi.');
    }

    /**
     * Polling cập nhật tin nhắn mới phía khách hàng.
     */
    public function poll(Request $request): JsonResponse
    {
        $customer = $request->user();
        $conversation = $this->chatService->getOrCreateCustomerConversation($customer);

        $afterId = (int) $request->query('after_id', 0);

        $newMessages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->with('sender')
            ->orderBy('id', 'asc')
            ->get();

        if ($newMessages->isNotEmpty()) {
            $this->chatService->markMessagesAsReadForCustomer($conversation);
        }

        return response()->json([
            'success' => true,
            'data' => $newMessages->map(fn ($m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'is_self' => (int) $m->sender_id === (int) $customer->id,
                'content' => $m->content,
                'sent_at' => $m->sent_at?->format('H:i'),
                'date' => $m->sent_at?->format('d/m/Y'),
                'is_read' => (bool) $m->is_read,
            ]),
        ]);
    }
}

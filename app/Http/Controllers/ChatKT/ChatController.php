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

        $activeCase = $conversation->activeCase()->with('assignedStaff')->first();
        $isLastMsgFaqAutoReply = $this->chatService->isLastMessageFaqAutoReply($conversation);
        $shouldShowGreeting = ($activeCase === null || $activeCase->isClosed()) && ! $isLastMsgFaqAutoReply;
        $headerStatus = $this->chatService->getCustomerChatHeaderStatus($activeCase);

        $suggestedOrder = null;
        if ($request->filled('order_id')) {
            $suggestedOrder = \App\Models\Order::with(['details.product'])
                ->where('customer_id', $customer->id)
                ->find((int) $request->query('order_id'));
        }

        if ($request->expectsJson() && ! $request->hasHeader('X-Inertia')) {
            return response()->json([
                'success' => true,
                'header_status' => $headerStatus,
                'data' => [
                    'conversation_id' => $conversation->id,
                    'should_show_greeting' => $shouldShowGreeting,
                    'suggested_order' => $suggestedOrder ? [
                        'id' => $suggestedOrder->id,
                        'order_code' => $suggestedOrder->order_code,
                        'order_status' => $suggestedOrder->order_status,
                        'total_amount' => $suggestedOrder->total_amount,
                    ] : null,
                    'active_case' => $activeCase ? [
                        'id' => $activeCase->id,
                        'case_code' => $activeCase->case_code,
                        'status' => $activeCase->status,
                        'status_label' => $activeCase->status_label,
                        'staff_name' => $activeCase->assignedStaff?->full_name,
                    ] : null,
                    'messages' => $messages->map(fn ($m) => [
                        'id' => $m->id,
                        'sender_id' => $m->sender_id,
                        'is_self' => (int) $m->sender_id === (int) $customer->id,
                        'content' => $m->content,
                        'image_url' => $m->image_url,
                        'image_urls' => $m->image_urls,
                        'images' => $m->image_urls,
                        'sent_at' => $m->sent_at?->format('H:i'),
                        'date' => $m->sent_at?->format('d/m/Y'),
                        'timestamp' => $m->sent_at?->timestamp ?? now()->timestamp,
                        'is_read' => (bool) $m->is_read,
                    ]),
                ],
            ]);
        }

        $faqList = $this->chatService->getFaqData();

        return view('ChatKT.customer.index', compact('customer', 'conversation', 'messages', 'activeCase', 'suggestedOrder', 'headerStatus', 'shouldShowGreeting', 'faqList'));
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
            $orderId,
            $request->file('image'),
            $request->file('images')
        );

        $activeCase = $message->supportCase?->fresh(['assignedStaff']);
        $headerStatus = $this->chatService->getCustomerChatHeaderStatus($activeCase);
        $replyMessage = $message->relationLoaded('autoReply') ? $message->autoReply : null;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tin nhắn đã được gửi thành công.',
                'header_status' => $headerStatus,
                'data' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'is_self' => true,
                    'content' => $message->content,
                    'image_url' => $message->image_url,
                    'image_urls' => $message->image_urls,
                    'images' => $message->image_urls,
                    'sent_at' => $message->sent_at?->format('H:i'),
                    'date' => $message->sent_at?->format('d/m/Y'),
                    'timestamp' => $message->sent_at?->timestamp ?? now()->timestamp,
                    'is_read' => (bool) $message->is_read,
                ],
                'reply' => $replyMessage ? [
                    'id' => $replyMessage->id,
                    'sender_id' => $replyMessage->sender_id,
                    'is_self' => false,
                    'content' => $replyMessage->content,
                    'image_url' => $replyMessage->image_url,
                    'image_urls' => $replyMessage->image_urls,
                    'images' => $replyMessage->image_urls,
                    'sent_at' => $replyMessage->sent_at?->format('H:i'),
                    'date' => $replyMessage->sent_at?->format('d/m/Y'),
                    'timestamp' => $replyMessage->sent_at?->timestamp ?? now()->timestamp,
                    'is_read' => (bool) $replyMessage->is_read,
                ] : null,
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

        $lastMsg = $conversation->messages()->latest('id')->first();
        $isLastMsgSelf = $lastMsg && ((int) $lastMsg->sender_id === (int) $customer->id);
        $lastMsgSeen = $isLastMsgSelf && (bool) $lastMsg->is_read;

        $activeCase = $conversation->activeCase()->with('assignedStaff')->first();
        $headerStatus = $this->chatService->getCustomerChatHeaderStatus($activeCase);
        $isLastMsgFaqAutoReply = $this->chatService->isLastMessageFaqAutoReply($conversation);
        $shouldShowGreeting = ($activeCase === null || $activeCase->isClosed()) && ! $isLastMsgFaqAutoReply;

        return response()->json([
            'success' => true,
            'is_last_msg_self' => $isLastMsgSelf,
            'last_msg_seen' => $lastMsgSeen,
            'header_status' => $headerStatus,
            'should_show_greeting' => $shouldShowGreeting,
            'data' => $newMessages->map(fn ($m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'is_self' => (int) $m->sender_id === (int) $customer->id,
                'content' => $m->content,
                'image_url' => $m->image_url,
                'image_urls' => $m->image_urls,
                'images' => $m->image_urls,
                'sent_at' => $m->sent_at?->format('H:i'),
                'date' => $m->sent_at?->format('d/m/Y'),
                'timestamp' => $m->sent_at?->timestamp ?? now()->timestamp,
                'is_read' => (bool) $m->is_read,
            ]),
        ]);
    }
}

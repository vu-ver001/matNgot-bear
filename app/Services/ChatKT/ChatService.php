<?php

namespace App\Services\ChatKT;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChatService
{
    /**
     * Lấy hoặc tạo Conversation duy nhất của khách hàng với Shop.
     */
    public function getOrCreateCustomerConversation(User $customer): Conversation
    {
        return Conversation::firstOrCreate(
            ['customer_id' => $customer->id],
            ['status' => 'OPEN']
        );
    }

    /**
     * Lấy Support Case của hội thoại (tái sử dụng / mở lại nếu đã đóng, không tạo dòng mới lặp lại nick).
     */
    public function getActiveOrCreateSupportCase(Conversation $conversation, ?int $orderId = null): SupportCase
    {
        /** @var SupportCase|null $existingCase */
        $existingCase = $conversation->supportCases()
            ->latest('id')
            ->first();

        if ($existingCase) {
            // Khi khách gửi tin nhắn mới: Nếu ca trước đó đã CLOSED, tự động mở lại về WAITING, không tạo dòng mới lặp lại nick
            if ($existingCase->isClosed()) {
                $existingCase->update([
                    'status' => SupportCase::STATUS_WAITING,
                    'closed_at' => null,
                    'assigned_staff_id' => null,
                    'last_activity_at' => now(),
                    'order_id' => $orderId ?: $existingCase->order_id,
                ]);
            } elseif ($orderId && ! $existingCase->order_id) {
                $existingCase->update(['order_id' => $orderId]);
            }
            return $existingCase;
        }

        return $this->createNewSupportCase($conversation, $orderId);
    }

    /**
     * Sinh mã case mới duy nhất dạng CASE-YYYYMMDD-XXX.
     */
    public function generateCaseCode(): string
    {
        $todayPrefix = 'CASE-' . now()->format('Ymd') . '-';
        $todayCount = SupportCase::where('case_code', 'like', $todayPrefix . '%')->count();
        $nextNumber = str_pad((string) ($todayCount + 1), 3, '0', STR_PAD_LEFT);
        $caseCode = $todayPrefix . $nextNumber;

        while (SupportCase::where('case_code', $caseCode)->exists()) {
            $todayCount++;
            $nextNumber = str_pad((string) ($todayCount + 1), 3, '0', STR_PAD_LEFT);
            $caseCode = $todayPrefix . $nextNumber;
        }

        return $caseCode;
    }

    /**
     * Tạo một Support Case mới cho hội thoại.
     */
    public function createNewSupportCase(Conversation $conversation, ?int $orderId = null): SupportCase
    {
        $caseCode = $this->generateCaseCode();

        return SupportCase::create([
            'case_code' => $caseCode,
            'conversation_id' => $conversation->id,
            'customer_id' => $conversation->customer_id,
            'assigned_staff_id' => null,
            'order_id' => $orderId,
            'status' => SupportCase::STATUS_WAITING,
            'priority' => 'Bình thường',
            'channel' => 'Website',
            'opened_at' => now(),
            'closed_at' => null,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Khách hàng gửi tin nhắn.
     *
     * @throws ValidationException
     */
    public function customerSendMessage(User $customer, string $content, ?int $orderId = null): Message
    {
        $cleanContent = trim($content);
        if ($cleanContent === '') {
            throw ValidationException::withMessages([
                'content' => 'Nội dung tin nhắn không được để trống.',
            ]);
        }

        if (mb_strlen($cleanContent) > 2000) {
            throw ValidationException::withMessages([
                'content' => 'Tin nhắn không được vượt quá 2000 ký tự.',
            ]);
        }

        // Tự động nhận diện mã đơn hàng nếu có trong nội dung (ví dụ #MNB...)
        if (! $orderId && preg_match('/#?([A-Za-z0-9\-_]{6,30})/', $cleanContent, $matches)) {
            $potentialCode = ltrim($matches[0], '#');
            $matchedOrder = Order::where('customer_id', $customer->id)
                ->where('order_code', $potentialCode)
                ->first();
            if ($matchedOrder) {
                $orderId = $matchedOrder->id;
            }
        }

        $conversation = $this->getOrCreateCustomerConversation($customer);
        $case = $this->getActiveOrCreateSupportCase($conversation, $orderId);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'support_case_id' => $case->id,
            'sender_id' => $customer->id,
            'content' => $cleanContent,
            'is_read' => false,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        $case->update(['last_activity_at' => now()]);
        $conversation->touch();

        return $message;
    }

    /**
     * Nhân viên / Admin gửi tin nhắn phản hồi cho khách hàng.
     *
     * @throws ValidationException
     */
    public function staffSendMessage(User $staffOrAdmin, SupportCase $case, string $content, ?int $orderId = null): Message
    {
        $cleanContent = trim($content);
        if ($cleanContent === '') {
            throw ValidationException::withMessages([
                'content' => 'Nội dung phản hồi không được để trống.',
            ]);
        }

        if (mb_strlen($cleanContent) > 2000) {
            throw ValidationException::withMessages([
                'content' => 'Tin nhắn không được vượt quá 2000 ký tự.',
            ]);
        }

        // Helper gán mã đơn liên quan vào case khi nhân viên bấm gửi đơn
        $linkOrderIfProvided = function (SupportCase $targetCase) use ($orderId, $cleanContent) {
            if ($orderId) {
                $matchedOrder = Order::where('id', $orderId)->where('customer_id', $targetCase->customer_id)->first();
                if ($matchedOrder) {
                    $targetCase->update(['order_id' => $matchedOrder->id]);
                }
            } elseif (str_contains($cleanContent, '📦 [ĐƠN HÀNG #')) {
                if (preg_match('/#([A-Z0-9\-]+)/', $cleanContent, $matches)) {
                    $matchedOrder = Order::where('order_code', $matches[1])->where('customer_id', $targetCase->customer_id)->first();
                    if ($matchedOrder) {
                        $targetCase->update(['order_id' => $matchedOrder->id]);
                    }
                }
            }
        };

        // Case CLOSED: Khi Staff/Admin gửi tin nhắn mới -> tự động MỞ LẠI (Reopen) về IN_PROGRESS
        if ($case->isClosed()) {
            return DB::transaction(function () use ($staffOrAdmin, $case, $cleanContent, $linkOrderIfProvided) {
                /** @var SupportCase|null $lockedCase */
                $lockedCase = SupportCase::where('id', $case->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedCase) {
                    throw ValidationException::withMessages([
                        'case' => 'Không tìm thấy cuộc hỗ trợ.',
                    ]);
                }

                $lockedCase->update([
                    'status' => SupportCase::STATUS_IN_PROGRESS,
                    'assigned_staff_id' => $staffOrAdmin->id,
                    'closed_at' => null,
                    'last_activity_at' => now(),
                ]);

                $linkOrderIfProvided($lockedCase);

                $message = Message::create([
                    'conversation_id' => $lockedCase->conversation_id,
                    'support_case_id' => $lockedCase->id,
                    'sender_id' => $staffOrAdmin->id,
                    'content' => $cleanContent,
                    'is_read' => false,
                    'read_at' => null,
                    'sent_at' => now(),
                ]);

                $this->markMessagesAsReadForStaff($lockedCase);
                $lockedCase->update(['last_activity_at' => now()]);
                $lockedCase->conversation->touch();

                return $message;
            });
        }

        if ($case->isWaiting()) {
            if ($staffOrAdmin->role !== User::ROLE_ADMIN) {
                throw ValidationException::withMessages([
                    'case' => 'Bạn cần bấm "Nhận xử lý" trước khi gửi tin nhắn hỗ trợ.',
                ]);
            }

            // ADMIN gửi tin vào WAITING case: Claim Case cho Admin + lưu Message trong cùng transaction
            return DB::transaction(function () use ($staffOrAdmin, $case, $cleanContent, $linkOrderIfProvided) {
                /** @var SupportCase|null $lockedCase */
                $lockedCase = SupportCase::where('id', $case->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedCase) {
                    throw ValidationException::withMessages([
                        'case' => 'Không tìm thấy cuộc hỗ trợ.',
                    ]);
                }

                if ($lockedCase->isClosed()) {
                    throw ValidationException::withMessages([
                        'case' => 'Cuộc hỗ trợ đã kết thúc, không thể gửi thêm tin nhắn.',
                    ]);
                }

                if ($lockedCase->isWaiting()) {
                    $lockedCase->update([
                        'status' => SupportCase::STATUS_IN_PROGRESS,
                        'assigned_staff_id' => $staffOrAdmin->id,
                        'last_activity_at' => now(),
                    ]);
                } elseif ((int) $lockedCase->assigned_staff_id !== (int) $staffOrAdmin->id) {
                    throw ValidationException::withMessages([
                        'case' => 'Case này đã được người khác tiếp nhận.',
                    ]);
                }

                $linkOrderIfProvided($lockedCase);

                $message = Message::create([
                    'conversation_id' => $lockedCase->conversation_id,
                    'support_case_id' => $lockedCase->id,
                    'sender_id' => $staffOrAdmin->id,
                    'content' => $cleanContent,
                    'is_read' => false,
                    'read_at' => null,
                    'sent_at' => now(),
                ]);

                $this->markMessagesAsReadForStaff($lockedCase);
                $lockedCase->update(['last_activity_at' => now()]);
                $lockedCase->conversation->touch();

                return $message;
            });
        }

        // Case IN_PROGRESS
        if ((int) $case->assigned_staff_id !== (int) $staffOrAdmin->id) {
            if ($staffOrAdmin->role === User::ROLE_ADMIN) {
                // Admin can thiệp / nhắn xen vào -> trở thành người xử lý case
                $case->update([
                    'assigned_staff_id' => $staffOrAdmin->id,
                    'last_activity_at' => now(),
                ]);
            } else {
                throw ValidationException::withMessages([
                    'case' => 'Cuộc hỗ trợ này đang do nhân viên khác phụ trách. Bạn chỉ có quyền xem, không thể gửi tin nhắn.',
                ]);
            }
        }

        $linkOrderIfProvided($case);

        $message = Message::create([
            'conversation_id' => $case->conversation_id,
            'support_case_id' => $case->id,
            'sender_id' => $staffOrAdmin->id,
            'content' => $cleanContent,
            'is_read' => false,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        // Đánh dấu đã đọc các tin nhắn trước đó của khách hàng
        $this->markMessagesAsReadForStaff($case);

        $case->update(['last_activity_at' => now()]);
        $case->conversation->touch();

        return $message;
    }

    /**
     * Nhân viên / Admin nhận xử lý case (Có khoá điều kiện chống 2 staff nhận cùng lúc).
     *
     * @return array{success: bool, message: string, case: SupportCase}
     */
    public function acceptCase(User $staffOrAdmin, SupportCase $case): array
    {
        if ($case->isClosed()) {
            return [
                'success' => false,
                'message' => 'Cuộc hỗ trợ này đã kết thúc, không thể nhận xử lý.',
                'case' => $case,
            ];
        }

        if ($case->isInProgress()) {
            if ((int) $case->assigned_staff_id === (int) $staffOrAdmin->id) {
                return [
                    'success' => true,
                    'message' => 'Bạn đang phụ trách cuộc hỗ trợ này.',
                    'case' => $case,
                ];
            }

            return [
                'success' => false,
                'message' => 'Case này đã được người khác tiếp nhận.',
                'case' => $case,
            ];
        }

        // Cập nhật nguyên tử (Atomic Conditional Update) chống Race Condition
        $affected = SupportCase::where('id', $case->id)
            ->where('status', SupportCase::STATUS_WAITING)
            ->whereNull('assigned_staff_id')
            ->update([
                'status' => SupportCase::STATUS_IN_PROGRESS,
                'assigned_staff_id' => $staffOrAdmin->id,
                'last_activity_at' => now(),
                'updated_at' => now(),
            ]);

        if ($affected === 0) {
            $case->refresh();
            if ((int) $case->assigned_staff_id === (int) $staffOrAdmin->id) {
                return [
                    'success' => true,
                    'message' => 'Bạn đã tiếp nhận cuộc hỗ trợ này.',
                    'case' => $case,
                ];
            }

            return [
                'success' => false,
                'message' => 'Case này đã được người khác tiếp nhận.',
                'case' => $case,
            ];
        }

        $case->refresh();

        return [
            'success' => true,
            'message' => 'Đã tiếp nhận xử lý cuộc hỗ trợ thành công.',
            'case' => $case,
        ];
    }

    /**
     * Bàn giao case về hàng chờ WAITING để nhân viên khác tiếp nhận.
     *
     * @return array{success: bool, message: string, status_code?: int, case: SupportCase}
     */
    public function handoverCase(User $staffOrAdmin, SupportCase $case): array
    {
        if ((int) $case->assigned_staff_id !== (int) $staffOrAdmin->id) {
            return [
                'success' => false,
                'message' => 'Bạn chỉ có thể bàn giao cuộc hỗ trợ do chính mình đang phụ trách.',
                'status_code' => 403,
                'case' => $case,
            ];
        }

        if (! $case->isInProgress()) {
            return [
                'success' => false,
                'message' => 'Chỉ có thể bàn giao cuộc hỗ trợ đang trong trạng thái Đang xử lý.',
                'status_code' => 422,
                'case' => $case,
            ];
        }

        $case->update([
            'status' => SupportCase::STATUS_WAITING,
            'assigned_staff_id' => null,
            'last_activity_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Đã bàn giao cuộc hỗ trợ về hàng chờ Chưa xử lý.',
            'case' => $case->fresh(),
        ];
    }

    /**
     * Admin tiếp quản cuộc hỗ trợ từ nhân viên khác.
     *
     * @return array{success: bool, message: string, status_code?: int, case: SupportCase}
     */
    public function takeoverCase(User $admin, SupportCase $case): array
    {
        if ($admin->role !== User::ROLE_ADMIN) {
            return [
                'success' => false,
                'message' => 'Chỉ Quản trị viên mới có quyền tiếp quản cuộc hỗ trợ.',
                'status_code' => 403,
                'case' => $case,
            ];
        }

        if ($case->isClosed()) {
            return [
                'success' => false,
                'message' => 'Cuộc hỗ trợ đã kết thúc, không thể tiếp quản.',
                'status_code' => 422,
                'case' => $case,
            ];
        }

        $case->update([
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'assigned_staff_id' => $admin->id,
            'last_activity_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Đã tiếp quản cuộc hỗ trợ thành công. Bạn hiện là người phụ trách.',
            'case' => $case->fresh(),
        ];
    }

    /**
     * Admin thu hồi cuộc hỗ trợ về hàng chờ WAITING.
     *
     * @return array{success: bool, message: string, status_code?: int, case: SupportCase}
     */
    public function revokeCase(User $admin, SupportCase $case): array
    {
        if ($admin->role !== User::ROLE_ADMIN) {
            return [
                'success' => false,
                'message' => 'Chỉ Quản trị viên mới có quyền thu hồi cuộc hỗ trợ.',
                'status_code' => 403,
                'case' => $case,
            ];
        }

        if (! $case->isInProgress()) {
            return [
                'success' => false,
                'message' => 'Chỉ có thể thu hồi cuộc hỗ trợ đang trong trạng thái Đang xử lý.',
                'status_code' => 422,
                'case' => $case,
            ];
        }

        $case->update([
            'status' => SupportCase::STATUS_WAITING,
            'assigned_staff_id' => null,
            'last_activity_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Đã thu hồi cuộc hỗ trợ về hàng chờ Chưa xử lý.',
            'case' => $case->fresh(),
        ];
    }

    /**
     * Admin chuyển cuộc hỗ trợ cho một Nhân viên cụ thể.
     *
     * @return array{success: bool, message: string, status_code?: int, case: SupportCase}
     */
    public function assignCase(User $admin, SupportCase $case, int $targetStaffId): array
    {
        if ($admin->role !== User::ROLE_ADMIN) {
            return [
                'success' => false,
                'message' => 'Chỉ Quản trị viên mới có quyền phân công cuộc hỗ trợ.',
                'status_code' => 403,
                'case' => $case,
            ];
        }

        if ($case->isClosed()) {
            return [
                'success' => false,
                'message' => 'Cuộc hỗ trợ đã kết thúc, không thể phân công nhân viên.',
                'status_code' => 422,
                'case' => $case,
            ];
        }

        $targetStaff = User::where('id', $targetStaffId)
            ->where('role', User::ROLE_STAFF)
            ->where('status', User::STATUS_ACTIVE)
            ->first();

        if (! $targetStaff) {
            return [
                'success' => false,
                'message' => 'Nhân viên được chọn không hợp lệ hoặc đang bị khóa.',
                'status_code' => 422,
                'case' => $case,
            ];
        }

        $case->update([
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'assigned_staff_id' => $targetStaff->id,
            'last_activity_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => "Đã chuyển cuộc hỗ trợ cho nhân viên {$targetStaff->full_name}.",
            'case' => $case->fresh(),
        ];
    }

    /**
     * Kết thúc hỗ trợ case (CLOSED).
     *
     * @return array{success: bool, message: string, status_code?: int, case: SupportCase}
     */
    public function closeCase(User $staffOrAdmin, SupportCase $case): array
    {
        if ($staffOrAdmin->role !== User::ROLE_ADMIN && (int) $case->assigned_staff_id !== (int) $staffOrAdmin->id) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền kết thúc cuộc hỗ trợ này.',
                'status_code' => 403,
                'case' => $case,
            ];
        }

        // Không cho phép Admin đóng ca đang do nhân viên khác phụ trách xử lý (cần Tiếp quản trước)
        if ($case->isInProgress() && $case->assigned_staff_id && (int) $case->assigned_staff_id !== (int) $staffOrAdmin->id) {
            return [
                'success' => false,
                'message' => 'Không thể kết thúc cuộc hỗ trợ khi nhân viên khác đang phụ trách xử lý.',
                'status_code' => 403,
                'case' => $case,
            ];
        }

        if ($case->isClosed()) {
            return [
                'success' => false,
                'message' => 'Cuộc hỗ trợ này đã được kết thúc trước đó.',
                'status_code' => 422,
                'case' => $case,
            ];
        }

        $case->update([
            'status' => SupportCase::STATUS_CLOSED,
            'closed_at' => now(),
            'last_activity_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Đã kết thúc cuộc hỗ trợ thành công.',
            'case' => $case->fresh(),
        ];
    }

    /**
     * Mở lại ca hỗ trợ đã kết thúc để tiếp tục trao đổi với khách hàng.
     *
     * @return array{success: bool, message: string, status_code?: int, case: SupportCase}
     */
    public function reopenCase(User $staffOrAdmin, SupportCase $case): array
    {
        if (! $case->isClosed()) {
            return [
                'success' => false,
                'message' => 'Chỉ có thể mở lại cuộc hỗ trợ đã kết thúc.',
                'status_code' => 422,
                'case' => $case,
            ];
        }

        $case->update([
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'assigned_staff_id' => $staffOrAdmin->id,
            'closed_at' => null,
            'last_activity_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Đã mở lại cuộc hỗ trợ thành công. Bạn đang phụ trách phiên này.',
            'case' => $case->fresh(),
        ];
    }

    /**
     * Lấy hoặc tạo mới SupportCase để shop chủ động liên hệ với khách hàng.
     */
    public function findOrCreateCaseForStaff(User $staffOrAdmin, int $customerId, ?int $orderId = null): SupportCase
    {
        $customer = User::findOrFail($customerId);

        $conversation = Conversation::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'staff_id' => $staffOrAdmin->id,
                'status' => 'OPEN',
                'last_message_at' => now(),
            ]
        );

        $existingCase = SupportCase::where('customer_id', $customer->id)
            ->latest('id')
            ->first();

        if ($existingCase) {
            // Không tự ý gán order_id vào case cho đến khi nhân viên bấm Gửi đơn này hoặc gửi tin nhắn
            return $existingCase;
        }

        return SupportCase::create([
            'conversation_id' => $conversation->id,
            'customer_id' => $customer->id,
            'order_id' => null, // Chỉ được gán khi nhân viên bấm gửi đơn
            'assigned_staff_id' => $staffOrAdmin->id,
            'case_code' => $this->generateCaseCode(),
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'priority' => 'Bình thường',
            'channel' => 'Website',
            'opened_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Đánh dấu đã đọc tất cả tin nhắn từ Staff/Admin khi Customer mở chat.
     */
    public function markMessagesAsReadForCustomer(Conversation $conversation): void
    {
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $conversation->customer_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Đánh dấu đã đọc tất cả tin nhắn từ Customer khi Staff/Admin mở case.
     */
    public function markMessagesAsReadForStaff(SupportCase $case): void
    {
        Message::where('conversation_id', $case->conversation_id)
            ->where('sender_id', $case->customer_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Lấy số lượng thực tế của từng tab phía Staff / Admin.
     * Mỗi khách hàng chỉ đếm 1 lần đại diện bởi phiên mới nhất của khách đó.
     * Chỉ tính những cuộc trò chuyện ĐÃ CÓ tin nhắn (không tính các ca nháp chưa gửi gì).
     * Đối với Nhân viên (Staff): Tab "Đang xử lý" chỉ đếm các cuộc trò chuyện do chính nhân viên đó phụ trách.
     *
     * @return array{all: int, waiting: int, in_progress: int, closed: int}
     */
    public function getStaffCounts(?string $search = null, ?User $user = null): array
    {
        // Gom theo khách hàng: Mỗi nick đại diện bởi 1 SupportCase mới nhất đã có tin nhắn
        $baseQuery = SupportCase::query()
            ->whereHas('messages')
            ->whereIn('id', SupportCase::whereHas('messages')->selectRaw('MAX(id)')->groupBy('customer_id'));

        if ($search && trim($search) !== '') {
            $search = trim($search);
            $baseQuery->where(function ($q) use ($search) {
                $q->where('case_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('messages', function ($mq) use ($search) {
                        $mq->where('content', 'like', "%{$search}%");
                    });
            });
        }

        $inProgressQuery = (clone $baseQuery)->where('status', SupportCase::STATUS_IN_PROGRESS);
        if ($user && $user->role !== User::ROLE_ADMIN) {
            $inProgressQuery->where('assigned_staff_id', $user->id);
        }

        $waitingCount = (clone $baseQuery)->where('status', SupportCase::STATUS_WAITING)->count();
        $inProgressCount = $inProgressQuery->count();
        $closedCount = (clone $baseQuery)->where('status', SupportCase::STATUS_CLOSED)->count();

        // Tab "Tất cả" luôn hiển thị và đếm toàn bộ cuộc trò chuyện trong hệ thống (mỗi nick 1 lần)
        $allCount = (clone $baseQuery)->count();

        return [
            'all' => $allCount,
            'waiting' => $waitingCount,
            'in_progress' => $inProgressCount,
            'closed' => $closedCount,
        ];
    }

    /**
     * Lấy danh sách các Support Cases theo tab status và từ khóa tìm kiếm.
     * Chỉ hiển thị các cuộc trò chuyện ĐÃ CÓ tin nhắn (không hiển thị ca nháp trống).
     * Đối với Nhân viên (Staff): Duy nhất tab "Đang xử lý" mới chỉ hiển thị các cuộc trò chuyện do chính nhân viên đó phụ trách.
     * Tab "Tất cả" vẫn hiển thị toàn bộ các cuộc trò chuyện.
     */
    public function getStaffCases(string $statusTab = 'all', ?string $search = null, int $perPage = 25, ?User $user = null): LengthAwarePaginator
    {
        // Gom theo khách hàng: Mỗi nick chỉ hiển thị 1 dòng duy nhất trên danh sách chat (chỉ tính case có tin nhắn)
        $query = SupportCase::query()
            ->whereHas('messages')
            ->whereIn('id', SupportCase::whereHas('messages')->selectRaw('MAX(id)')->groupBy('customer_id'))
            ->with([
                'customer',
                'assignedStaff',
                'order',
                'latestMessage',
                'conversation.lastMessage',
                'messages' => fn ($mq) => $mq->latest('id')->limit(1),
            ]);

        if (! in_array(strtolower($statusTab), ['all', 'tat-ca'])) {
            $statusEnum = match (strtolower($statusTab)) {
                'in_progress', 'dang-xu-ly' => SupportCase::STATUS_IN_PROGRESS,
                'closed', 'da-ket-thuc' => SupportCase::STATUS_CLOSED,
                default => SupportCase::STATUS_WAITING,
            };

            $query->where('status', $statusEnum);

            // Chỉ duy nhất tab "Đang xử lý" (in_progress) với role Staff mới lọc theo assigned_staff_id
            if ($statusEnum === SupportCase::STATUS_IN_PROGRESS && $user && $user->role !== User::ROLE_ADMIN) {
                $query->where('assigned_staff_id', $user->id);
            }
        }
        // Tab "Tất cả" (all): Hiển thị tất cả cuộc trò chuyện mà không lọc theo staff

        if ($search && trim($search) !== '') {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('case_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('messages', function ($mq) use ($search) {
                        $mq->where('content', 'like', "%{$search}%");
                    });
            });
        }

        return $query
            ->orderByRaw('COALESCE((SELECT MAX(sent_at) FROM messages WHERE messages.conversation_id = support_cases.conversation_id), support_cases.last_activity_at, support_cases.created_at) DESC')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Xử lý khi một nhân viên bị khóa (BLOCKED) hoặc xóa mềm:
     * Tự động trả các case IN_PROGRESS của nhân viên đó về WAITING để nhân viên khác tiếp quản.
     */
    public function handleStaffInactivated(User $staff): int
    {
        return SupportCase::where('assigned_staff_id', $staff->id)
            ->where('status', SupportCase::STATUS_IN_PROGRESS)
            ->update([
                'status' => SupportCase::STATUS_WAITING,
                'assigned_staff_id' => null,
                'last_activity_at' => now(),
            ]);
    }

    /**
     * Đếm số lượng tin nhắn chưa đọc của một Case từ phía Khách hàng gửi.
     */
    public function countUnreadMessagesForStaff(SupportCase $case): int
    {
        return Message::where('conversation_id', $case->conversation_id)
            ->where('sender_id', $case->customer_id)
            ->where('is_read', false)
            ->count();
    }
}

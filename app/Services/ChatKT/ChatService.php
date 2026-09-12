<?php

namespace App\Services\ChatKT;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
     * Xử lý lưu trữ hình ảnh chat.
     */
    /**
     * Upload xử lý một hình ảnh đính kèm trong tin nhắn chat.
     */
    protected function handleChatImageUpload(mixed $image): ?string
    {
        if ($image instanceof \Illuminate\Http\UploadedFile && $image->isValid()) {
            $path = $image->store('chat_images', 'public');
            return asset('storage/' . $path);
        }
        if (is_string($image) && ! empty($image)) {
            return $image;
        }
        return null;
    }

    /**
     * Upload xử lý nhiều hình ảnh đính kèm trong tin nhắn chat (hỗ trợ cả mảng và đơn lẻ).
     *
     * @return array<string>
     */
    protected function handleChatImagesUpload(mixed $images = null, mixed $image = null): array
    {
        $urls = [];

        if (is_array($images) && ! empty($images)) {
            foreach ($images as $img) {
                $url = $this->handleChatImageUpload($img);
                if ($url) {
                    $urls[] = $url;
                }
            }
        } elseif ($images) {
            $url = $this->handleChatImageUpload($images);
            if ($url) {
                $urls[] = $url;
            }
        } elseif ($image) {
            $singleUrl = $this->handleChatImageUpload($image);
            if ($singleUrl) {
                $urls[] = $singleUrl;
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * Khách hàng gửi tin nhắn.
     *
     * @throws ValidationException
     */
    public function customerSendMessage(User $customer, ?string $content = null, ?int $orderId = null, mixed $image = null, mixed $images = null): Message
    {
        $imageUrls = $this->handleChatImagesUpload($images, $image);
        $imageUrl = ! empty($imageUrls) ? $imageUrls[0] : null;
        $cleanContent = trim($content ?? '');

        if ($cleanContent === '' && empty($imageUrls)) {
            throw ValidationException::withMessages([
                'content' => 'Nội dung tin nhắn không được để trống hoặc phải đính kèm hình ảnh.',
            ]);
        }

        if ($cleanContent !== '' && mb_strlen($cleanContent) > 2000) {
            throw ValidationException::withMessages([
                'content' => 'Tin nhắn không được vượt quá 2000 ký tự.',
            ]);
        }

        // Tự động nhận diện mã đơn hàng nếu có trong nội dung (ví dụ #MNB...)
        if (! $orderId && $cleanContent !== '' && preg_match('/#?([A-Za-z0-9\-_]{6,30})/', $cleanContent, $matches)) {
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
        if ($orderId && (int) $case->order_id !== (int) $orderId) {
            $case->update(['order_id' => $orderId]);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'support_case_id' => $case->id,
            'sender_id' => $customer->id,
            'content' => $cleanContent !== '' ? $cleanContent : null,
            'image_url' => $imageUrl,
            'images' => ! empty($imageUrls) ? $imageUrls : null,
            'is_read' => false,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        $case->update(['last_activity_at' => now()]);
        $conversation->touch();

        // Kiểm tra câu trả lời tự động cho câu hỏi thường gặp (FAQ)
        if ($cleanContent !== '') {
            $autoReplyContent = $this->findFaqAnswer($cleanContent);
            if ($autoReplyContent) {
                $replyMessage = $this->sendFaqAutoReply($conversation, $case, $autoReplyContent, $message);
                $message->setRelation('autoReply', $replyMessage);
            }
        }

        return $message;
    }

    /**
     * Nhân viên / Admin gửi tin nhắn phản hồi cho khách hàng.
     *
     * @throws ValidationException
     */
    public function staffSendMessage(User $staffOrAdmin, SupportCase $case, ?string $content = null, ?int $orderId = null, mixed $image = null, mixed $images = null): Message
    {
        $imageUrls = $this->handleChatImagesUpload($images, $image);
        $imageUrl = ! empty($imageUrls) ? $imageUrls[0] : null;
        $cleanContent = trim($content ?? '');

        if ($cleanContent === '' && empty($imageUrls)) {
            throw ValidationException::withMessages([
                'content' => 'Nội dung phản hồi không được để trống hoặc phải đính kèm hình ảnh.',
            ]);
        }

        if ($cleanContent !== '' && mb_strlen($cleanContent) > 2000) {
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
            } elseif ($cleanContent !== '' && str_contains($cleanContent, '📦 [ĐƠN HÀNG #')) {
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
            return DB::transaction(function () use ($staffOrAdmin, $case, $cleanContent, $imageUrl, $imageUrls, $linkOrderIfProvided) {
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
                    'content' => $cleanContent !== '' ? $cleanContent : null,
                    'image_url' => $imageUrl,
                    'images' => ! empty($imageUrls) ? $imageUrls : null,
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
            return DB::transaction(function () use ($staffOrAdmin, $case, $cleanContent, $imageUrl, $imageUrls, $linkOrderIfProvided) {
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
                    'content' => $cleanContent !== '' ? $cleanContent : null,
                    'image_url' => $imageUrl,
                    'images' => ! empty($imageUrls) ? $imageUrls : null,
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
            'content' => $cleanContent !== '' ? $cleanContent : null,
            'image_url' => $imageUrl,
            'images' => ! empty($imageUrls) ? $imageUrls : null,
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
        // 1. Ca đã đóng/kết thúc thì nhân viên không còn việc cần xử lý
        if ($case->isClosed()) {
            return 0;
        }

        return Message::where('conversation_id', $case->conversation_id)
            ->where('sender_id', $case->customer_id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Lấy tin nhắn xem trước (preview) cho thẻ Case trên Sidebar của Nhân viên.
     * Nếu có tin nhắn khách hàng chưa được trả lời (chưa đọc), ưu tiên hiển thị tin nhắn chưa trả lời đó
     * để nhân viên nhìn thấy ngay nội dung cần xử lý thay vì tin bot tự động gửi.
     */
    public function getCasePreviewMessage(SupportCase $case, ?Message $fallbackLastMsg = null): ?Message
    {
        $unreadCustomerMsg = Message::where('conversation_id', $case->conversation_id)
            ->where('sender_id', $case->customer_id)
            ->where('is_read', false)
            ->latest('id')
            ->first();

        if ($unreadCustomerMsg) {
            return $unreadCustomerMsg;
        }

        return $fallbackLastMsg ?? $case->latestMessage ?? $case->messages?->first() ?? $case->conversation?->lastMessage;
    }

    /**
     * Đếm tổng số lượng tin nhắn chưa đọc của khách hàng (do nhân viên / quản trị viên gửi).
     */
    public function countUnreadMessagesForCustomer(User $customer): int
    {
        $conversation = Conversation::where('customer_id', $customer->id)->first();
        if (! $conversation) {
            return 0;
        }

        return Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $customer->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Kiểm tra trạng thái trực tuyến của Shop.
     * Shop chỉ hiển thị "Đang trực tuyến" khi thực sự có ít nhất 1 tài khoản Nhân viên (STAFF)
     * hoặc Quản trị viên (ADMIN) đang đăng nhập và có hoạt động gần đây.
     * Khi toàn bộ nhân viên/admin đăng xuất (chỉ còn khách hàng), Shop sẽ lập tức hiển thị "Ngoại tuyến".
     */
    public function isShopOnline(): bool
    {
        // 1. Kiểm tra trực tiếp bảng sessions: chỉ quét tài khoản STAFF và ADMIN (đang ACTIVE)
        if (Schema::hasTable('sessions')) {
            try {
                $hasActiveStaffSession = DB::table('sessions')
                    ->join('users', 'sessions.user_id', '=', 'users.id')
                    ->whereIn('users.role', [User::ROLE_STAFF, User::ROLE_ADMIN])
                    ->where('users.status', User::STATUS_ACTIVE)
                    ->where('sessions.last_activity', '>=', now()->subMinutes(10)->timestamp)
                    ->exists();

                if ($hasActiveStaffSession) {
                    return true;
                }

                // Nếu đang dùng database session và có bảng sessions nhưng không có staff nào: chắc chắn Offline!
                if (config('session.driver') === 'database') {
                    return false;
                }
            } catch (\Throwable $e) {
                // Không ngắt luồng nếu có lỗi truy vấn sessions
            }
        }

        // 2. Môi trường testing hoặc fallback (khi không dùng database session driver):
        if (app()->environment('testing')) {
            return User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])
                ->where('status', User::STATUS_ACTIVE)
                ->exists();
        }

        return false;
    }

    /**
     * Lấy thông tin nhãn trạng thái hiển thị trên Header khung chat của Khách hàng.
     */
    public function getCustomerChatHeaderStatus(?SupportCase $activeCase): array
    {
        $isOnline = $this->isShopOnline();

        // 1. Khi Shop ngoại tuyến (toàn bộ nhân viên và quản trị viên đã đăng xuất):
        // Luôn hiển thị trạng thái Ngoại tuyến để khách hàng biết hiện không có nhân viên trực
        if (! $isOnline) {
            return [
                'type' => 'offline',
                'label' => 'Ngoại tuyến',
                'class' => 'is-offline',
                'subtext' => 'Hiện chưa có nhân viên trực tuyến (để lại tin nhắn shop sẽ phản hồi sớm)',
            ];
        }

        // 2. Khi Shop đang trực tuyến:
        if ($activeCase && ! $activeCase->isClosed()) {
            if ($activeCase->status === SupportCase::STATUS_WAITING) {
                return [
                    'type' => 'waiting',
                    'label' => 'Chờ tiếp nhận',
                    'class' => 'is-waiting',
                    'subtext' => 'Thường phản hồi trong vài phút',
                ];
            }

            if ($activeCase->status === SupportCase::STATUS_IN_PROGRESS) {
                $staffName = $activeCase->assignedStaff?->full_name ?: 'Nhân viên';
                return [
                    'type' => 'in_progress',
                    'label' => 'Đang hỗ trợ (' . $staffName . ')',
                    'class' => 'is-in-progress',
                    'subtext' => 'Nhân viên đang trực tiếp trao đổi với bạn',
                ];
            }
        }

        // 3. Khi Shop trực tuyến và không có ca nào đang chờ/xử lý (hoặc ca trước đã đóng):
        return [
            'type' => 'online',
            'label' => 'Đang trực tuyến',
            'class' => 'is-online',
            'subtext' => 'Thường phản hồi trong vài phút',
        ];
    }

    /**
     * Danh sách câu hỏi thường gặp (FAQ) và câu trả lời tự động của Mật Ngọt Bear.
     *
     * @return array<array<string, mixed>>
     */
    public function getFaqData(): array
    {
        return [
            [
                'id' => 1,
                'category' => 'order',
                'category_name' => 'Đơn hàng & Vận chuyển',
                'icon' => 'fa-box',
                'question' => 'Làm sao để kiểm tra đơn hàng?',
                'aliases' => ['Kiểm tra đơn hàng', 'Tra cứu đơn hàng', 'Xem tình trạng đơn hàng'],
                'answer' => "📦 **Cách kiểm tra đơn hàng tại Mật Ngọt Bear:**\n"
                    . "1. Bạn có thể vào mục **'Đơn hàng của tôi'** trong menu tài khoản để theo dõi lộ trình và trạng thái đơn hàng theo thời gian thực.\n"
                    . "2. Nếu cần kiểm tra nhanh, bạn chỉ cần gửi **Mã đơn hàng** (ví dụ: `#MNB-123456`) vào khung chat này hoặc bấm nút **'Gửi đơn này'** ở phần gợi ý bên dưới.\n"
                    . "3. Mật Ngọt Bear luôn gửi email kèm mã vận đơn ngay khi đơn hàng được bàn giao cho đơn vị vận chuyển ạ! 💕",
            ],
            [
                'id' => 2,
                'category' => 'return',
                'category_name' => 'Đổi trả & Hoàn tiền',
                'icon' => 'fa-rotate-left',
                'question' => 'Chính sách đổi trả như thế nào?',
                'aliases' => ['Chính sách đổi trả', 'Đổi trả hàng', 'Quy định đổi trả'],
                'answer' => "🔄 **Chính sách đổi trả tại Mật Ngọt Bear:**\n"
                    . "• **Thời gian:** Hỗ trợ đổi trả trong vòng **07 ngày** kể từ ngày nhận hàng thành công.\n"
                    . "• **Điều kiện:** Sản phẩm còn nguyên tem mác, chưa qua sử dụng/giặt tẩy và có video quay khui hộp.\n"
                    . "• **Lỗi từ Shop (giao nhầm mẫu, lỗi chỉ, bẩn do đóng gói):** Shop hỗ trợ **đổi mới 100% miễn phí vận chuyển 2 chiều**.\n"
                    . "• Bạn có thể gửi ảnh hoặc video sản phẩm vào đây để nhân viên hỗ trợ xử lý ngay lập tức nhé!",
            ],
            [
                'id' => 3,
                'category' => 'order',
                'category_name' => 'Đơn hàng & Vận chuyển',
                'icon' => 'fa-truck-fast',
                'question' => 'Thời gian giao hàng bao lâu?',
                'aliases' => ['Thời gian giao hàng', 'Bao lâu thì nhận được hàng', 'Thời gian ship', 'Shop giao hàng trong bao lâu', 'Giao hàng bao lâu', 'Thời gian giao hàng là bao lâu'],
                'answer' => "🚚 **Thời gian giao hàng dự kiến:**\n"
                    . "• **Nội thành Hà Nội & TP.HCM:** Giao nhanh trong **1 - 2 ngày làm việc** (hỗ trợ ship hỏa tốc nếu bạn cần gấp).\n"
                    . "• **Các tỉnh thành khác:** Từ **2 - 4 ngày làm việc** tùy khu vực.\n"
                    . "• Các đơn đặt trước 15:00 sẽ được Mật Ngọt Bear đóng gói và gửi đi ngay trong ngày. Bạn có thể theo dõi tiến độ ở mục Đơn hàng nhé! 🧸",
            ],
            [
                'id' => 4,
                'category' => 'order',
                'category_name' => 'Đơn hàng & Vận chuyển',
                'icon' => 'fa-ban',
                'question' => 'Tôi muốn hủy đơn hàng thì làm thế nào?',
                'aliases' => ['Tôi muốn hủy đơn hàng', 'Hủy đơn hàng', 'Cách hủy đơn hàng'],
                'answer' => "❌ **Hướng dẫn hủy đơn hàng:**\n"
                    . "• **Khi đơn ở trạng thái 'Chờ xác nhận':** Bạn có thể vào chi tiết đơn hàng trong tài khoản và nhấn nút **'Hủy đơn hàng'** trực tiếp.\n"
                    . "• **Khi đơn đã 'Đang xử lý' hoặc 'Đang giao':** Bạn không thể tự hủy trên web. Vui lòng gửi Mã đơn hàng vào đây để nhân viên hỗ trợ liên hệ đơn vị vận chuyển chặn hàng và hoàn tiền nhanh nhất cho bạn ạ!",
            ],
            [
                'id' => 5,
                'category' => 'product',
                'category_name' => 'Sản phẩm & Bảo hành',
                'icon' => 'fa-shield-heart',
                'question' => 'Sản phẩm có bảo hành không?',
                'aliases' => ['Chính sách bảo hành', 'Bảo hành sản phẩm', 'Bảo hành gấu bông'],
                'answer' => "✨ **Chính sách bảo hành tại Mật Ngọt Bear:**\n"
                    . "• Toàn bộ gấu bông được **bảo hành đường may và chỉ khâu trọn đời**.\n"
                    . "• **Bảo hành độ đàn hồi của bông nhồi:** Cam kết 100% bông gòn trắng tinh khiết loại 1 đàn hồi cao, không xẹp lún, không pha tạp chất, an toàn tuyệt đối cho làn da nhạy cảm.\n"
                    . "• Nếu sản phẩm bị bung chỉ hoặc rơi phụ kiện (mắt, mũi, nơ), bạn có thể gửi về shop để được sửa chữa hoàn toàn miễn phí bất kỳ lúc nào ạ!",
            ],
            [
                'id' => 6,
                'category' => 'order',
                'category_name' => 'Đơn hàng & Vận chuyển',
                'icon' => 'fa-gift',
                'question' => 'Phí vận chuyển được tính như thế nào?',
                'aliases' => ['Phí vận chuyển', 'Phí ship', 'Giá ship bao nhiêu', 'Có được freeship không'],
                'answer' => "🎁 **Chính sách phí vận chuyển:**\n"
                    . "• **Miễn phí vận chuyển toàn quốc (Freeship)** cho đơn hàng từ **350.000đ** trở lên.\n"
                    . "• **Đơn hàng dưới 350.000đ:** Đồng giá 25.000đ nội thành và 30.000đ ngoại thành / liên tỉnh.\n"
                    . "• Bạn có thể áp dụng thêm mã Freeship hoặc Voucher giảm giá tại bước thanh toán nhé!",
            ],
            [
                'id' => 7,
                'category' => 'payment',
                'category_name' => 'Thanh toán & Khuyến mãi',
                'icon' => 'fa-credit-card',
                'question' => 'Shop hỗ trợ những phương thức thanh toán nào?',
                'aliases' => ['Phương thức thanh toán', 'Hình thức thanh toán', 'Thanh toán như thế nào'],
                'answer' => "💳 **Phương thức thanh toán hỗ trợ tại Mật Ngọt Bear:**\n"
                    . "1. **Thanh toán khi nhận hàng (COD):** Kiểm tra và thanh toán tiền mặt trực tiếp cho shipper khi nhận gấu.\n"
                    . "2. **Chuyển khoản ngân hàng (VietQR):** Quét mã QR thanh toán nhanh chóng, hệ thống xác nhận tự động chỉ trong vài giây.\n"
                    . "3. **Ví điện tử (MoMo, VNPAY, ZaloPay):** Tiện lợi, an toàn và thường xuyên có ưu đãi giảm giá.",
            ],
            [
                'id' => 8,
                'category' => 'product',
                'category_name' => 'Sản phẩm & Bảo hành',
                'icon' => 'fa-bath',
                'question' => 'Làm sao để giặt và bảo quản gấu bông đúng cách?',
                'aliases' => ['Cách giặt gấu bông', 'Vệ sinh gấu bông', 'Bảo quản gấu bông'],
                'answer' => "🧼 **Mẹo giặt & bảo quản gấu bông:**\n"
                    . "• **Gấu nhỏ (< 50cm):** Cho vào túi giặt, giặt máy chế độ giặt nhẹ (nước lạnh) và phơi nơi thoáng mát có nắng nhẹ.\n"
                    . "• **Gấu lớn (> 50cm):** Tháo khóa kéo phía sau lưng gấu, lấy bông gòn ra túi riêng, giặt sạch vỏ ngoài rồi phơi khô. Sau đó nhồi lại bông và vỗ đều cho bông tơi xốp như mới!\n"
                    . "• *Lưu ý:* Tránh dùng chất tẩy mạnh hoặc sấy nhiệt độ cao để giữ lông gấu luôn mềm mịn nha! 🐻",
            ],
            [
                'id' => 9,
                'category' => 'payment',
                'category_name' => 'Thanh toán & Khuyến mãi',
                'icon' => 'fa-eye',
                'question' => 'Có được kiểm tra hàng trước khi thanh toán không?',
                'aliases' => ['Có được kiểm tra hàng không', 'Đồng kiểm hàng', 'Xem hàng trước khi thanh toán'],
                'answer' => "👀 **Chính sách đồng kiểm hàng:**\n"
                    . "• Mật Ngọt Bear **HOÀN TOÀN HỖ TRỢ ĐỒNG KIỂM** khi nhận hàng ạ!\n"
                    . "• Bạn được quyền mở hộp kiểm tra đúng mẫu mã, màu sắc và kích thước trước khi thanh toán cho shipper.\n"
                    . "• Nếu sản phẩm không đúng như mô tả hoặc gặp lỗi, bạn có thể từ chối nhận hàng mà không tốn bất kỳ chi phí nào!",
            ],
            [
                'id' => 10,
                'category' => 'payment',
                'category_name' => 'Thanh toán & Khuyến mãi',
                'icon' => 'fa-ticket',
                'question' => 'Làm sao để áp dụng mã giảm giá / Voucher?',
                'aliases' => ['Cách dùng voucher', 'Áp dụng mã giảm giá', 'Nhập mã giảm giá ở đâu'],
                'answer' => "🎟️ **Cách áp dụng Voucher khuyến mãi:**\n"
                    . "1. Tại trang **Giỏ hàng** hoặc bước **Thanh toán**, bạn tìm khung **'Mã giảm giá / Voucher'**.\n"
                    . "2. Nhập mã voucher hoặc bấm **'Chọn Voucher'** từ danh sách voucher bạn đang có.\n"
                    . "3. Hệ thống sẽ tự động trừ số tiền giảm giá vào tổng đơn hàng trước khi bạn bấm Đặt hàng nhé!",
            ],
        ];
    }

    /**
     * Tìm câu trả lời tự động tương ứng cho câu hỏi của khách hàng.
     */
    public function findFaqAnswer(string $question): ?string
    {
        $clean = mb_strtolower(trim($question), 'UTF-8');
        $clean = preg_replace('/[?!.,;]+$/u', '', $clean);
        $clean = trim($clean);

        if ($clean === '') {
            return null;
        }

        $faqList = $this->getFaqData();
        foreach ($faqList as $faq) {
            $faqQ = mb_strtolower(trim($faq['question']), 'UTF-8');
            $faqQ = preg_replace('/[?!.,;]+$/u', '', $faqQ);
            $faqQ = trim($faqQ);

            if ($clean === $faqQ) {
                return $faq['answer'];
            }

            if (! empty($faq['aliases'])) {
                foreach ($faq['aliases'] as $alias) {
                    $aliasClean = mb_strtolower(trim($alias), 'UTF-8');
                    $aliasClean = preg_replace('/[?!.,;]+$/u', '', $aliasClean);
                    if ($clean === trim($aliasClean)) {
                        return $faq['answer'];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Tạo tin nhắn phản hồi tự động từ Shop (Mật Ngọt Bear Bot).
     */
    public function sendFaqAutoReply(Conversation $conversation, SupportCase $case, string $replyContent, ?Message $triggerMessage = null): Message
    {
        $botSender = null;
        if ($case->assigned_staff_id) {
            $botSender = $case->assignedStaff;
        }
        if (! $botSender) {
            $botSender = User::where('role', User::ROLE_STAFF)
                ->where('status', User::STATUS_ACTIVE)
                ->first()
                ?? User::where('role', User::ROLE_ADMIN)
                    ->where('status', User::STATUS_ACTIVE)
                    ->first();
        }
        if (! $botSender) {
            $botSender = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])->first();
        }

        $reply = Message::create([
            'conversation_id' => $conversation->id,
            'support_case_id' => $case->id,
            'sender_id' => $botSender?->id ?? 1,
            'content' => $replyContent,
            'image_url' => null,
            'images' => null,
            'is_read' => false,
            'read_at' => null,
            'sent_at' => now(),
        ]);

        // Đánh dấu tin nhắn câu hỏi FAQ của khách hàng vừa gửi là đã đọc (vì hệ thống đã tự động giải đáp)
        if ($triggerMessage) {
            $triggerMessage->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            $triggerMessage->is_read = true;
        }

        // Đảm bảo tất cả các tin nhắn câu hỏi FAQ của khách hàng cũng được đánh dấu đã đọc
        $unmarkedCustomerMsgs = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $conversation->customer_id)
            ->where('is_read', false)
            ->get();
        foreach ($unmarkedCustomerMsgs as $uMsg) {
            if ($uMsg->content && $this->findFaqAnswer($uMsg->content) !== null) {
                $uMsg->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            }
        }

        // Kiểm tra xem khách hàng có tin nhắn nào trước đó chưa được nhân viên trả lời hay không
        $hasUnanswered = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $conversation->customer_id)
            ->where('is_read', false)
            ->exists();

        if (! $hasUnanswered) {
            // Toàn bộ tin nhắn đã được giải đáp tự động (không còn tin nhắn chờ)
            // Tự động đóng ca nếu ca đang ở WAITING để không nằm trong hàng chờ "Chưa xử lý"
            if ($case->status === SupportCase::STATUS_WAITING) {
                $case->update([
                    'status' => SupportCase::STATUS_CLOSED,
                    'closed_at' => now(),
                    'last_activity_at' => now(),
                ]);
            } else {
                $case->update(['last_activity_at' => now()]);
            }
        } else {
            // Nếu trước đó còn tin nhắn khách chưa được nhân viên trả lời (ví dụ gửi mã đơn, câu hỏi tư vấn...):
            // Tuyệt đối KHÔNG đóng ca, và GIỮ NGUYÊN trạng thái và người phụ trách hiện tại của ca:
            // - Nếu ca đang ở WAITING ("Chưa xử lý"): Giữ nguyên WAITING và assigned_staff_id = null (không tự ý gán nhân viên).
            // - Nếu ca đang ở IN_PROGRESS ("Đang xử lý"): Giữ nguyên IN_PROGRESS và nhân viên đang phụ trách.
            $updateData = ['last_activity_at' => now()];

            if ($case->isClosed()) {
                $updateData['status'] = $case->assigned_staff_id ? SupportCase::STATUS_IN_PROGRESS : SupportCase::STATUS_WAITING;
                $updateData['closed_at'] = null;
            }

            $case->update($updateData);
        }

        $conversation->touch();

        return $reply;
    }

    /**
     * Kiểm tra xem tin nhắn cuối cùng của hội thoại có phải là tin nhắn trả lời tự động (FAQ) hay không.
     */
    public function isLastMessageFaqAutoReply(Conversation $conversation): bool
    {
        $lastMsg = $conversation->relationLoaded('lastMessage') && $conversation->lastMessage
            ? $conversation->lastMessage
            : $conversation->messages()->latest('id')->first();

        if (! $lastMsg || ! $lastMsg->content) {
            return false;
        }

        $faqAnswers = array_column($this->getFaqData(), 'answer');
        return in_array($lastMsg->content, $faqAnswers, true);
    }
}

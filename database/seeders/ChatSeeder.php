<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->where('email', 'customer@matngotbear.test')->firstOrFail();
        $linh = User::query()->where('email', 'linh@matngotbear.test')->firstOrFail();
        $staff = User::query()->where('email', 'staff@matngotbear.test')->firstOrFail();

        $supportedConversation = Conversation::query()->firstOrCreate(
            ['customer_id' => $customer->id, 'staff_id' => $staff->id],
            ['status' => 'OPEN'],
        );

        $this->seedMessage(
            $supportedConversation,
            $customer,
            'Shop ơi, gấu Teddy Mật Ong còn hàng không ạ?',
            now()->subMinutes(20),
            true,
        );
        $this->seedMessage(
            $supportedConversation,
            $staff,
            'Chào bạn, mẫu Teddy Mật Ong hiện vẫn còn hàng nhé!',
            now()->subMinutes(17),
            true,
        );
        $this->seedMessage(
            $supportedConversation,
            $customer,
            'Shop có hỗ trợ gói quà và viết thiệp không?',
            now()->subMinutes(12),
            true,
        );
        $this->seedMessage(
            $supportedConversation,
            $staff,
            'Dạ có, bạn ghi nội dung thiệp ở phần ghi chú đơn hàng giúp mình nha.',
            now()->subMinutes(8),
            false,
        );

        $waitingConversation = Conversation::query()->firstOrCreate(
            ['customer_id' => $linh->id, 'staff_id' => null],
            ['status' => 'OPEN'],
        );

        $this->seedMessage(
            $waitingConversation,
            $linh,
            'Mình muốn kiểm tra tình trạng đơn MNB-DEMO-002.',
            now()->subMinutes(5),
            false,
        );
    }

    private function seedMessage(
        Conversation $conversation,
        User $sender,
        string $content,
        CarbonInterface $sentAt,
        bool $isRead,
    ): void {
        Message::query()->updateOrCreate(
            [
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'content' => $content,
            ],
            [
                'is_read' => $isRead,
                'sent_at' => $sentAt,
            ],
        );
    }
}

<?php

namespace Tests\Feature\ChatKT;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\SupportCase;
use App\Models\User;
use App\Services\ChatKT\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportChatTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ], $attributes));
    }

    private function createStaff(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_STAFF,
            'status' => User::STATUS_ACTIVE,
        ], $attributes));
    }

    private function createAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ], $attributes));
    }

    public function test_guest_cannot_access_customer_chat(): void
    {
        $response = $this->get(route('customer.messages.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_access_chat_page(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('customer.messages.index'));
        $response->assertOk();
        $response->assertSee('Mật Ngọt Bear Support');
    }

    public function test_customer_view_shows_assigned_staff_in_header_badge(): void
    {
        $customer = $this->createCustomer();
        $staff = $this->createStaff(['full_name' => 'Nguyễn Bí Mật Staff 999']);
        $chatService = app(ChatService::class);

        $msg1 = $chatService->customerSendMessage($customer, 'Hỏi về thời gian mở cửa');
        $case = $msg1->supportCase;

        // Khi đang WAITING -> Header hiển thị "Chờ tiếp nhận"
        $waitingResponse = $this->actingAs($customer)->get(route('customer.messages.index'));
        $waitingResponse->assertOk();
        $waitingResponse->assertSee('Chờ tiếp nhận');
        $waitingResponse->assertSee('is-waiting');

        // Nhân viên tiếp nhận ca -> Header hiển thị "Đang hỗ trợ (Tên nhân viên)"
        $chatService->acceptCase($staff, $case);
        $case->refresh();

        $chatService->staffSendMessage($staff, $case, 'Dạ Mật Ngọt Bear mở cửa từ 8h00 đến 22h00 ạ!');

        $response = $this->actingAs($customer)->get(route('customer.messages.index'));
        $response->assertOk();
        $response->assertSee('Mật Ngọt Bear Support');
        $response->assertSee('Dạ Mật Ngọt Bear mở cửa từ 8h00 đến 22h00 ạ!');
        $response->assertSee('Đang hỗ trợ (Nguyễn Bí Mật Staff 999)');
        $response->assertSee('is-in-progress');
    }

    public function test_customer_sends_first_message_creates_conversation_waiting_case_and_message(): void
    {
        $customer = $this->createCustomer(['full_name' => 'Nguyễn Văn A']);

        $response = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => 'Xin chào shop Mật Ngọt Bear, tư vấn giúp em sản phẩm gấu bông với ạ!',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
            ]);

        // Kiểm tra Conversation được tạo
        $this->assertDatabaseHas('conversations', [
            'customer_id' => $customer->id,
            'status' => 'OPEN',
        ]);

        // Kiểm tra SupportCase được tạo với trạng thái WAITING
        $this->assertDatabaseHas('support_cases', [
            'customer_id' => $customer->id,
            'status' => SupportCase::STATUS_WAITING,
            'assigned_staff_id' => null,
        ]);

        $supportCase = SupportCase::where('customer_id', $customer->id)->first();
        $this->assertNotNull($supportCase);
        $this->assertStringStartsWith('CASE-', $supportCase->case_code);

        // Kiểm tra Message được lưu
        $this->assertDatabaseHas('messages', [
            'sender_id' => $customer->id,
            'support_case_id' => $supportCase->id,
            'content' => 'Xin chào shop Mật Ngọt Bear, tư vấn giúp em sản phẩm gấu bông với ạ!',
        ]);
    }

    public function test_customer_sends_subsequent_message_reuses_active_case(): void
    {
        $customer = $this->createCustomer();

        // Tin nhắn 1
        $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => 'Tin nhắn thứ nhất',
        ]);

        $case1 = SupportCase::where('customer_id', $customer->id)->first();

        // Tin nhắn 2 khi case vẫn đang mở
        $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => 'Tin nhắn thứ hai',
        ]);

        $this->assertEquals(1, SupportCase::where('customer_id', $customer->id)->count());
        $this->assertEquals(2, Message::where('support_case_id', $case1->id)->count());
    }

    public function test_customer_message_after_case_closed_reopens_existing_case_to_waiting(): void
    {
        $customer = $this->createCustomer();
        $staff = $this->createStaff();
        $chatService = app(ChatService::class);

        // Khách gửi tin nhắn 1
        $msg1 = $chatService->customerSendMessage($customer, 'Khách hỏi đơn hàng');
        $case1 = $msg1->supportCase;

        // Nhân viên tiếp nhận và đóng case
        $chatService->acceptCase($staff, $case1);
        $case1->refresh();
        $chatService->closeCase($staff, $case1);
        $case1->refresh();

        $this->assertEquals(SupportCase::STATUS_CLOSED, $case1->status);

        // Khách gửi tin nhắn tiếp theo sau khi case đã đóng -> Tự động mở lại chính ca đó về WAITING, không sinh dòng mới
        $msg2 = $chatService->customerSendMessage($customer, 'Em muốn hỏi thêm vấn đề khác');
        $case2 = $msg2->supportCase;

        $this->assertEquals($case1->id, $case2->id);
        $this->assertEquals(SupportCase::STATUS_WAITING, $case2->status);
        $this->assertNull($case2->assigned_staff_id);
        $this->assertNull($case2->closed_at);
        $this->assertEquals(1, SupportCase::where('customer_id', $customer->id)->count());
    }

    public function test_closed_case_shows_shop_welcome_greeting_when_customer_opens_chat(): void
    {
        $customer = $this->createCustomer();
        $staff = $this->createStaff();
        $chatService = app(ChatService::class);

        // Khách gửi tin nhắn và nhân viên hỗ trợ rồi đóng ca
        $msg = $chatService->customerSendMessage($customer, 'Xin hỏi về sản phẩm gấu bông');
        $case = $msg->supportCase;
        $chatService->acceptCase($staff, $case);
        $case->refresh();
        $chatService->closeCase($staff, $case);
        $case->refresh();

        $this->assertEquals(SupportCase::STATUS_CLOSED, $case->status);

        // Khi khách hàng vào lại trang chat (kể cả từ menu, trực tiếp hay nhắn tin với shop)
        $response = $this->actingAs($customer)->get(route('customer.messages.index'));
        $response->assertStatus(200);
        $response->assertSee('Xin chào bạn! 👋', false);
        $response->assertSee('Mật Ngọt Bear rất vui được hỗ trợ bạn. Bạn cần tư vấn về sản phẩm, đơn hàng hay vấn đề nào khác ạ?', false);
        $response->assertSee('data-greeting-prompt="1"', false);

        // Polling cũng báo should_show_greeting là true
        $pollResponse = $this->actingAs($customer)->getJson(route('customer.messages.poll'));
        $pollResponse->assertStatus(200);
        $pollResponse->assertJson([
            'success' => true,
            'should_show_greeting' => true,
        ]);

        // Khi khách gửi tin nhắn mới -> Ca trở thành WAITING (đang hoạt động) -> should_show_greeting chuyển về false
        $chatService->customerSendMessage($customer, 'Em có câu hỏi mới');
        $pollResponseActive = $this->actingAs($customer)->getJson(route('customer.messages.poll'));
        $pollResponseActive->assertStatus(200);
        $pollResponseActive->assertJson([
            'success' => true,
            'should_show_greeting' => false,
        ]);
    }

    public function test_customer_cannot_poll_or_view_other_customer_messages(): void
    {
        $customerA = $this->createCustomer(['full_name' => 'Khách Hàng A']);
        $customerB = $this->createCustomer(['full_name' => 'Khách Hàng B']);
        $chatService = app(ChatService::class);

        $chatService->customerSendMessage($customerA, 'Tin nhắn bảo mật của khách A');

        // Khách B poll tin nhắn
        $response = $this->actingAs($customerB)->getJson(route('customer.messages.poll'));
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }

    public function test_customer_polling_marks_staff_messages_as_read(): void
    {
        $customer = $this->createCustomer();
        $staff = $this->createStaff();
        $chatService = app(ChatService::class);

        $msg1 = $chatService->customerSendMessage($customer, 'Xin chào');
        $case = $msg1->supportCase;
        $chatService->acceptCase($staff, $case);
        $case->refresh();

        $staffMsg = $chatService->staffSendMessage($staff, $case, 'Chào bạn, Mật Ngọt Bear có thể giúp gì cho bạn?');
        $this->assertNull($staffMsg->read_at);

        // Khách poll
        $this->actingAs($customer)->getJson(route('customer.messages.poll'));

        $staffMsg->refresh();
        $this->assertNotNull($staffMsg->read_at);
    }

    public function test_guest_and_customer_cannot_access_staff_support(): void
    {
        $customer = $this->createCustomer();

        // Guest bị chuyển hướng
        $this->get(route('staff.support.index'))->assertRedirect(route('login'));

        // Customer bị từ chối 403
        $this->actingAs($customer)->get(route('staff.support.index'))->assertForbidden();
    }

    public function test_staff_can_view_support_dashboard_with_case_tabs(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer(['full_name' => 'Thảo Nguyên']);
        $chatService = app(ChatService::class);

        $chatService->customerSendMessage($customer, 'Cần tư vấn');

        $response = $this->actingAs($staff)->get(route('staff.support.index', ['tab' => 'waiting']));
        $response->assertOk();
        $response->assertSee('Tất cả');
        $response->assertSee('Chưa xử lý');
        $response->assertSee('Đang xử lý');
        $response->assertSee('Cần tư vấn');
        $response->assertSee('staff-support-case-avatar');
        $response->assertSee('T');

        // Kiểm tra tab "all" (Tất cả)
        $allResponse = $this->actingAs($staff)->get(route('staff.support.index', ['tab' => 'all']));
        $allResponse->assertOk();
        $allResponse->assertSee('Cần tư vấn');
    }

    public function test_staff_support_renders_redesigned_empty_state_when_no_case_selected(): void
    {
        $staff = $this->createStaff(['full_name' => 'Nguyễn Thị Hoa']);

        $response = $this->actingAs($staff)->get(route('staff.support.index'));
        $response->assertOk();
        $response->assertSee('is-empty-selection', false);
        $response->assertSee('staff-support-empty-state', false);
        $response->assertSee('staff-empty-mascot-img', false);
        $response->assertSee('Cổng Hỗ Trợ Khách Hàng', false);
        $response->assertSee('Xin chào, Nguyễn Thị Hoa! 👋', false);
        $response->assertSee('Vui lòng chọn một cuộc hỗ trợ từ danh sách bên trái để xem nội dung.', false);
        $response->assertSee('Chưa xử lý', false);
        $response->assertSee('Đang xử lý', false);
        $response->assertSee('Đã hoàn thành', false);
        $response->assertSee('Hướng dẫn thao tác nhanh cho nhân viên', false);
    }

    public function test_staff_can_view_specific_case(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer(['full_name' => 'Trần Thị B']);
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Hỏi về thời gian giao hàng');
        $case = $msg->supportCase;

        // Truy cập qua JSON API
        $jsonResponse = $this->actingAs($staff)->getJson(route('staff.support.show', $case));
        $jsonResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Truy cập qua Web request chuyển hướng vào tab và case tương ứng
        $webResponse = $this->actingAs($staff)->get(route('staff.support.show', $case));
        $webResponse->assertRedirect(route('staff.support.index', [
            'tab' => 'waiting',
            'case_id' => $case->id,
        ]));

        // Kiểm tra trang giao diện chat hiển thị đúng khối thông tin Trạng thái & Người xử lý dưới tên khách hàng
        $pageResponse = $this->actingAs($staff)->get(route('staff.support.index', [
            'tab' => 'waiting',
            'case_id' => $case->id,
        ]));
        $pageResponse->assertOk();
        $pageResponse->assertSee('Trạng thái:');
        $pageResponse->assertSee('Người xử lý:');
        $pageResponse->assertSee('staff-support-chat-status-meta');
        $pageResponse->assertDontSee('staff-support-chat-customer-contacts');
    }

    public function test_staff_atomic_accept_transitions_case_to_in_progress(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Chào shop');
        $case = $msg->supportCase;

        $response = $this->actingAs($staff)->postJson(route('staff.support.accept', $case));
        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_IN_PROGRESS, $case->status);
        $this->assertEquals($staff->id, $case->assigned_staff_id);
    }

    public function test_staff_accept_concurrency_returns_409_conflict(): void
    {
        $staff1 = $this->createStaff(['email' => 'staff1@test.com']);
        $staff2 = $this->createStaff(['email' => 'staff2@test.com']);
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Chào shop');
        $case = $msg->supportCase;

        // Staff 1 tiếp nhận trước
        $this->actingAs($staff1)->postJson(route('staff.support.accept', $case))
            ->assertOk();

        // Staff 2 tiếp nhận sau trên cùng một case -> Bị 409 Conflict
        $response = $this->actingAs($staff2)->postJson(route('staff.support.accept', $case));
        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Case này đã được người khác tiếp nhận.',
            ]);
    }

    public function test_unassigned_staff_cannot_reply_to_case_assigned_to_another_staff(): void
    {
        $staff1 = $this->createStaff(['email' => 'staff1@test.com']);
        $staff2 = $this->createStaff(['email' => 'staff2@test.com']);
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Chào shop');
        $case = $msg->supportCase;

        // Giao cho Staff 1
        $chatService->acceptCase($staff1, $case);
        $case->refresh();

        // Staff 2 cố gắng gửi tin nhắn -> 422 Unprocessable Entity do validate phụ trách
        $response = $this->actingAs($staff2)->postJson(route('staff.support.messages.send', $case), [
            'content' => 'Staff 2 chen ngang',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['case']);

        // Staff 2 không thể mở cuộc trò chuyện đang do nhân viên khác xử lý (bị khóa, không có form chat)
        $viewResponse = $this->actingAs($staff2)->get(route('staff.support.index', ['case_id' => $case->id]));
        $viewResponse->assertOk();
        $viewResponse->assertSee('is-locked-other');
        $viewResponse->assertSee('Đang xử lý');
        $viewResponse->assertDontSee('data-staff-chat-submit');
    }

    public function test_assigned_staff_can_reply_and_mark_customer_messages_as_read(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $customerMsg = $chatService->customerSendMessage($customer, 'Cho em hỏi mẫu này còn không ạ?');
        $case = $customerMsg->supportCase;

        $chatService->acceptCase($staff, $case);
        $case->refresh();

        $this->assertNull($customerMsg->read_at);

        $response = $this->actingAs($staff)->postJson(route('staff.support.messages.send', $case), [
            'content' => 'Dạ bên em vẫn còn sẵn hàng bạn nhé!',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('messages', [
            'support_case_id' => $case->id,
            'sender_id' => $staff->id,
            'content' => 'Dạ bên em vẫn còn sẵn hàng bạn nhé!',
        ]);

        $customerMsg->refresh();
        $this->assertNotNull($customerMsg->read_at);
    }

    public function test_staff_can_handover_case_back_to_waiting(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Cần giải quyết khiếu nại');
        $case = $msg->supportCase;

        $chatService->acceptCase($staff, $case);
        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_IN_PROGRESS, $case->status);

        // Bàn giao lại
        $response = $this->actingAs($staff)->postJson(route('staff.support.handover', $case));
        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_WAITING, $case->status);
        $this->assertNull($case->assigned_staff_id);
    }

    public function test_staff_can_close_case_and_can_reopen_by_sending_message(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Xong việc rồi cảm ơn shop');
        $case = $msg->supportCase;

        $chatService->acceptCase($staff, $case);
        $case->refresh();

        // Đóng case
        $response = $this->actingAs($staff)->postJson(route('staff.support.close', $case));
        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_CLOSED, $case->status);
        $this->assertNotNull($case->closed_at);

        // Shop (Staff) có thể chủ động nhắn trước cho khách hàng ở ca đã đóng -> tự động Reopen ca về IN_PROGRESS
        $sendResponse = $this->actingAs($staff)->postJson(route('staff.support.messages.send', $case), [
            'content' => 'Shop chào bạn, bên mình gửi thêm hướng dẫn nhé!',
        ]);
        $sendResponse->assertCreated()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_IN_PROGRESS, $case->status);
        $this->assertEquals($staff->id, $case->assigned_staff_id);
        $this->assertNull($case->closed_at);
    }

    public function test_staff_can_manually_reopen_closed_case(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Cần giải đáp');
        $case = $msg->supportCase;
        $chatService->acceptCase($staff, $case);
        $chatService->closeCase($staff, $case);

        $case->refresh();
        $this->assertTrue($case->isClosed());

        $response = $this->actingAs($staff)->postJson(route('staff.support.reopen', $case));
        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertTrue($case->isInProgress());
        $this->assertEquals($staff->id, $case->assigned_staff_id);
    }

    public function test_staff_cannot_send_message_to_waiting_case_without_accepting(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Khách gửi tin');
        $case = $msg->supportCase;

        // Staff gửi tin vào WAITING case mà chưa nhận -> bị chặn 422
        $response = $this->actingAs($staff)->postJson(route('staff.support.messages.send', $case), [
            'content' => 'Staff nhắn vào case chưa nhận',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['case']);
    }

    public function test_admin_sending_message_to_waiting_case_claims_and_sends_atomically(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Khách gửi tin');
        $case = $msg->supportCase;

        $this->assertTrue($case->isWaiting());
        $this->assertNull($case->assigned_staff_id);

        // Admin gửi tin vào WAITING case -> Tự động claim và gửi tin thành công
        $response = $this->actingAs($admin)->postJson(route('admin.support.messages.send', $case), [
            'content' => 'Admin hỗ trợ bạn ngay lập tức!',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertTrue($case->isInProgress());
        $this->assertEquals($admin->id, $case->assigned_staff_id);

        $this->assertDatabaseHas('messages', [
            'support_case_id' => $case->id,
            'sender_id' => $admin->id,
            'content' => 'Admin hỗ trợ bạn ngay lập tức!',
        ]);
    }

    public function test_admin_can_chat_directly_and_takeover_when_staff_is_handling(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Khách gửi tin');
        $case = $msg->supportCase;

        // Giao cho staff
        $chatService->acceptCase($staff, $case);
        $case->refresh();
        $this->assertEquals($staff->id, $case->assigned_staff_id);

        // Admin truy cập trang admin support
        $this->actingAs($admin)->get(route('admin.support.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.support.show', $case))->assertRedirect(route('admin.support.index', [
            'tab' => 'in_progress',
            'case_id' => $case->id,
        ]));

        // Admin có thể gửi tin trực tiếp (nhắn xen vào) -> Tự động chuyển người phụ trách thành Admin
        $response = $this->actingAs($admin)->postJson(route('admin.support.messages.send', $case), [
            'content' => 'Admin can thiệp hỗ trợ',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertEquals($admin->id, $case->assigned_staff_id);
        $this->assertTrue((bool) $msg->fresh()->is_read);

        $this->assertDatabaseHas('messages', [
            'support_case_id' => $case->id,
            'sender_id' => $admin->id,
            'content' => 'Admin can thiệp hỗ trợ',
        ]);
    }

    public function test_admin_can_revoke_case_back_to_waiting(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Cần giải quyết');
        $case = $msg->supportCase;

        $chatService->acceptCase($staff, $case);
        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_IN_PROGRESS, $case->status);

        // Admin thu hồi
        $response = $this->actingAs($admin)->postJson(route('admin.support.revoke', $case));
        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_WAITING, $case->status);
        $this->assertNull($case->assigned_staff_id);
    }

    public function test_admin_can_assign_case_to_another_staff(): void
    {
        $admin = $this->createAdmin();
        $staffA = $this->createStaff(['email' => 'staffA@test.com', 'full_name' => 'Staff A']);
        $staffB = $this->createStaff(['email' => 'staffB@test.com', 'full_name' => 'Staff B']);
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Chuyển ca');
        $case = $msg->supportCase;

        $chatService->acceptCase($staffA, $case);
        $case->refresh();
        $this->assertEquals($staffA->id, $case->assigned_staff_id);

        // Admin chuyển sang Staff B
        $response = $this->actingAs($admin)->postJson(route('admin.support.assign', $case), [
            'staff_id' => $staffB->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_IN_PROGRESS, $case->status);
        $this->assertEquals($staffB->id, $case->assigned_staff_id);
    }

    public function test_inactive_staff_automatically_releases_assigned_cases(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Tin nhắn trước khi nhân viên nghỉ');
        $case = $msg->supportCase;

        $chatService->acceptCase($staff, $case);
        $case->refresh();
        $this->assertEquals($staff->id, $case->assigned_staff_id);
        $this->assertEquals(SupportCase::STATUS_IN_PROGRESS, $case->status);

        // Nhân viên bị khóa / ngừng hoạt động
        $staff->update(['status' => User::STATUS_BLOCKED]);
        $releasedCount = $chatService->handleStaffInactivated($staff);

        $this->assertEquals(1, $releasedCount);
        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_WAITING, $case->status);
        $this->assertNull($case->assigned_staff_id);
    }

    public function test_staff_in_progress_tab_only_shows_cases_assigned_to_that_staff(): void
    {
        $staffA = $this->createStaff(['full_name' => 'Staff Nguyễn Văn A']);
        $staffB = $this->createStaff(['full_name' => 'Staff Trần Văn B']);
        $admin = $this->createAdmin();

        $customer1 = $this->createCustomer(['full_name' => 'Khách hàng 1']);
        $customer2 = $this->createCustomer(['full_name' => 'Khách hàng 2']);

        $chatService = app(ChatService::class);

        $msg1 = $chatService->customerSendMessage($customer1, 'Tin nhắn cho Staff A');
        $case1 = $msg1->supportCase;
        $chatService->acceptCase($staffA, $case1);

        $msg2 = $chatService->customerSendMessage($customer2, 'Tin nhắn cho Staff B');
        $case2 = $msg2->supportCase;
        $chatService->acceptCase($staffB, $case2);

        // Staff A truy cập tab in_progress: Thấy Case 1, KHÔNG thấy Case 2, counts['in_progress'] = 1
        $responseA = $this->actingAs($staffA)->get(route('staff.support.index', ['tab' => 'in_progress']));
        $responseA->assertOk();
        $responseA->assertSee('Khách hàng 1');
        $responseA->assertDontSee('Khách hàng 2');
        $responseA->assertViewHas('counts', fn ($counts) => $counts['in_progress'] === 1);

        // Staff B truy cập tab in_progress: Thấy Case 2, KHÔNG thấy Case 1, counts['in_progress'] = 1
        $responseB = $this->actingAs($staffB)->get(route('staff.support.index', ['tab' => 'in_progress']));
        $responseB->assertOk();
        $responseB->assertSee('Khách hàng 2');
        $responseB->assertDontSee('Khách hàng 1');
        $responseB->assertViewHas('counts', fn ($counts) => $counts['in_progress'] === 1);

        // Staff C chưa nhận case nào truy cập tab in_progress: counts['in_progress'] = 0
        $staffC = $this->createStaff(['full_name' => 'Staff Lê Văn C']);
        $responseC = $this->actingAs($staffC)->get(route('staff.support.index', ['tab' => 'in_progress']));
        $responseC->assertOk();
        $responseC->assertDontSee('Khách hàng 1');
        $responseC->assertDontSee('Khách hàng 2');
        $responseC->assertViewHas('counts', fn ($counts) => $counts['in_progress'] === 0);

        // Admin truy cập tab in_progress: Thấy cả 2 cases, counts['in_progress'] = 2
        $responseAdmin = $this->actingAs($admin)->get(route('admin.support.index', ['tab' => 'in_progress']));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Khách hàng 1');
        $responseAdmin->assertSee('Khách hàng 2');
        $responseAdmin->assertViewHas('counts', fn ($counts) => $counts['in_progress'] === 2);

        // Tab "all" (Tất cả): Staff vẫn thấy tất cả cuộc trò chuyện (cả Case 1 và Case 2), counts['all'] = 2
        $responseAllStaff = $this->actingAs($staffA)->get(route('staff.support.index', ['tab' => 'all']));
        $responseAllStaff->assertOk();
        $responseAllStaff->assertSee('Khách hàng 1');
        $responseAllStaff->assertSee('Khách hàng 2');
        $responseAllStaff->assertViewHas('counts', fn ($counts) => $counts['all'] === 2);
    }

    public function test_admin_cannot_close_case_when_staff_is_handling(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff(['full_name' => 'Nhân viên Mai']);
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Cần tư vấn nhanh');
        $case = $msg->supportCase;
        $chatService->acceptCase($staff, $case);

        // Admin cố gắng gọi API close khi Staff đang xử lý -> 403 Forbidden
        $response = $this->actingAs($admin)->postJson(route('admin.support.close', $case));
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Không thể kết thúc cuộc hỗ trợ khi nhân viên khác đang phụ trách xử lý.',
            ]);

        $case->refresh();
        $this->assertTrue($case->isInProgress());

        // Admin thực hiện Tiếp quản (Takeover) -> Trở thành người xử lý
        $takeoverRes = $this->actingAs($admin)->postJson(route('admin.support.takeover', $case));
        $takeoverRes->assertOk();

        // Sau khi đã tiếp quản, Admin mới có quyền kết thúc hỗ trợ
        $closeRes = $this->actingAs($admin)->postJson(route('admin.support.close', $case));
        $closeRes->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $case->refresh();
        $this->assertTrue($case->isClosed());
    }

    public function test_customer_reply_to_in_progress_case_remains_unread_until_staff_explicitly_opens_it(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer(['full_name' => 'Lê Văn Khách']);
        $chatService = app(ChatService::class);

        // 1. Khách gửi tin nhắn lần đầu
        $msg1 = $chatService->customerSendMessage($customer, 'Chào shop');
        $case = $msg1->supportCase;

        // 2. Nhân viên tiếp nhận và trả lời (Case chuyển sang Đang xử lý)
        $chatService->acceptCase($staff, $case);
        $chatService->staffSendMessage($staff, $case, 'Dạ shop nghe ạ!');

        $msg1->refresh();
        $this->assertNotNull($msg1->read_at);

        // 3. Khách phản hồi lần 2
        $msg2 = $chatService->customerSendMessage($customer, 'Cho mình hỏi thêm áo size L còn không?');
        $this->assertFalse((bool) $msg2->fresh()->is_read);

        // 4. Nhân viên vào danh sách ca Đang xử lý nhưng CHƯA ấn mở case (không có case_id)
        $listResponse = $this->actingAs($staff)->get(route('staff.support.index', ['tab' => 'in_progress']));
        $listResponse->assertOk();

        // Tin nhắn phản hồi lần 2 vẫn phải ở trạng thái CHƯA ĐỌC
        $this->assertFalse((bool) $msg2->fresh()->is_read);
        $this->assertEquals(1, $chatService->countUnreadMessagesForStaff($case));
        $listResponse->assertSee('is-unread');
        $listResponse->assertSee('staff-support-unread-dot');

        // 5. Nhân viên ấn vào đọc (truy cập với case_id)
        $openResponse = $this->actingAs($staff)->get(route('staff.support.index', [
            'tab' => 'in_progress',
            'case_id' => $case->id,
        ]));
        $openResponse->assertOk();

        // Bây giờ mới được đánh dấu đã đọc
        $this->assertTrue((bool) $msg2->fresh()->is_read);
        $this->assertEquals(0, $chatService->countUnreadMessagesForStaff($case));
    }

    public function test_other_staff_cannot_click_or_access_case_handled_by_another_staff(): void
    {
        $staffA = $this->createStaff(['full_name' => 'Nhân viên A']);
        $staffB = $this->createStaff(['full_name' => 'Nhân viên B']);
        $admin = $this->createAdmin();
        $customer = $this->createCustomer(['full_name' => 'Khách VIP']);
        $chatService = app(ChatService::class);

        // Khách gửi tin nhắn và Staff A nhận xử lý
        $msg = $chatService->customerSendMessage($customer, 'Cần tư vấn đơn hàng riêng');
        $case = $msg->supportCase;
        $chatService->acceptCase($staffA, $case);

        // 1. Staff B xem tab Tất cả: Case hiển thị class is-locked-other, không phải thẻ <a> click được, không có pill badge trong card
        $responseB = $this->actingAs($staffB)->get(route('staff.support.index', ['tab' => 'all']));
        $responseB->assertOk();
        $responseB->assertSee('is-locked-other');
        $responseB->assertSee('data-locked="true"', false);
        $responseB->assertSee('data-locked-by="Nhân viên A"', false);
        $responseB->assertSee('data-tooltip="🔒 Đang do Nhân viên A xử lý. Bạn không thể truy cập."', false);
        $responseB->assertDontSee('staff-case-staff-pill');
        $responseB->assertSee('is-unread');
        $responseB->assertSee('staff-support-unread-dot');
        $responseB->assertSee('staff-support-case-unread');

        // 2. Staff B cố truy cập trực tiếp qua show JSON -> 403 Forbidden
        $jsonRes = $this->actingAs($staffB)->getJson(route('staff.support.show', $case));
        $jsonRes->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Cuộc hỗ trợ này đang do nhân viên khác phụ trách. Bạn không có quyền truy cập.',
            ]);

        // 3. Staff B cố tình truyền case_id trên URL -> Không load selectedCase
        $indexRes = $this->actingAs($staffB)->get(route('staff.support.index', ['case_id' => $case->id]));
        $indexRes->assertOk();
        $indexRes->assertSee('Vui lòng chọn một cuộc hỗ trợ từ danh sách bên trái để xem nội dung.');
        $indexRes->assertDontSee('data-staff-chat-submit');

        // 4. Staff A (người phụ trách) xem: Bình thường, mở được và thấy form chat
        $responseA = $this->actingAs($staffA)->get(route('staff.support.index', ['case_id' => $case->id]));
        $responseA->assertOk();
        $responseA->assertDontSee('is-locked-other');
        $responseA->assertSee('data-staff-chat-submit');

        // 5. Admin xem: Thấy tooltip hiển thị tên nhân viên đang xử lý khi di chuột vào, không có pill badge trong card, mở được và thấy nút Tiếp quản
        $responseAdmin = $this->actingAs($admin)->get(route('admin.support.index', ['case_id' => $case->id]));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('data-tooltip="🔒 Đang do Nhân viên A xử lý"', false);
        $responseAdmin->assertDontSee('staff-case-staff-pill');
        $responseAdmin->assertSee('data-btn-takeover');
        $responseAdmin->assertSee('Tiếp quản');
    }

    public function test_viewing_case_without_being_handler_keeps_unread_until_admin_sends_message(): void
    {
        $staffA = $this->createStaff(['full_name' => 'Nhân viên A']);
        $admin = $this->createAdmin(['full_name' => 'Quản trị viên']);
        $customer = $this->createCustomer(['full_name' => 'Khách hàng 123']);
        $chatService = app(ChatService::class);

        // Khách gửi tin nhắn và Staff A nhận xử lý rồi trả lời lần 1
        $msg = $chatService->customerSendMessage($customer, 'Đơn hàng của tôi giao tới đâu rồi?');
        $case = $msg->supportCase;
        $chatService->acceptCase($staffA, $case);
        $chatService->staffSendMessage($staffA, $case, 'Dạ em kiểm tra cho mình ngay ạ!');
        $this->assertTrue((bool) $msg->fresh()->is_read);

        // Khách gửi tiếp câu hỏi thứ 2 -> tin nhắn chưa đọc
        $msg2 = $chatService->customerSendMessage($customer, 'Giao trong chiều nay kịp không?');
        $this->assertFalse((bool) $msg2->fresh()->is_read);
        $this->assertEquals(1, $chatService->countUnreadMessagesForStaff($case));

        // 1. Admin bấm vào xem case (mở case_id)
        $adminViewResponse = $this->actingAs($admin)->get(route('admin.support.index', [
            'tab' => 'in_progress',
            'case_id' => $case->id,
        ]));
        $adminViewResponse->assertOk();

        // Tin nhắn VẪN PHẢI GIỮ NGUYÊN LÀ CHƯA ĐỌC vì Admin không phải người phụ trách
        $this->assertFalse((bool) $msg2->fresh()->is_read);
        $this->assertEquals(1, $chatService->countUnreadMessagesForStaff($case));
        $adminViewResponse->assertSee('is-unread');
        $adminViewResponse->assertSee('staff-support-unread-dot');
        $adminViewResponse->assertSee('staff-support-case-unread');

        // 2. Admin nhắn xen vào để xử lý case
        $sendResponse = $this->actingAs($admin)->postJson(route('admin.support.messages.send', $case), [
            'content' => 'Chào bạn, mình là Admin sẽ hỗ trợ kiểm tra đơn ngay nhé!',
        ]);
        $sendResponse->assertCreated();

        // Sau khi Admin nhắn xen vào: Admin trở thành người phụ trách
        $case->refresh();
        $this->assertEquals($admin->id, $case->assigned_staff_id);

        // Tin nhắn khách gửi đã được đánh dấu ĐÃ ĐỌC (mất in đậm)
        $this->assertTrue((bool) $msg2->fresh()->is_read);
        $this->assertEquals(0, $chatService->countUnreadMessagesForStaff($case));

        // Admin tải lại trang: Không còn class is-unread hay badge đỏ
        $afterResponse = $this->actingAs($admin)->get(route('admin.support.index', [
            'tab' => 'in_progress',
            'case_id' => $case->id,
        ]));
        $afterResponse->assertOk();
        $afterResponse->assertDontSee('staff-support-unread-dot');
    }

    public function test_case_item_time_is_calculated_from_latest_message_sent_at(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer();
        $chatService = app(ChatService::class);

        // Khách gửi tin nhắn lúc 15 phút trước
        $msg = $chatService->customerSendMessage($customer, 'Tin nhắn gửi 15 phút trước');
        $msg->update(['sent_at' => now()->subMinutes(15)]);

        $case = $msg->supportCase;
        // Giả sử case vừa có hoạt động (last_activity_at) cách đây 20 giây (ví dụ: gán nhân viên, nhận case...)
        $case->update([
            'last_activity_at' => now()->subSeconds(20),
            'status' => SupportCase::STATUS_WAITING,
        ]);

        $response = $this->actingAs($staff)->get(route('staff.support.index'));
        $response->assertOk();

        // Thời gian hiển thị trên thẻ cuộc trò chuyện phải tính theo tin nhắn cuối (15 minutes), KHÔNG PHẢI 20 seconds
        $response->assertSee('15 minutes');
        $response->assertDontSee('20 seconds');
    }

    public function test_cases_are_sorted_by_latest_message_time_descending(): void
    {
        $staff = $this->createStaff();
        $customerOld = $this->createCustomer(['full_name' => 'Kim Tuyến']);
        $customerNew = $this->createCustomer(['full_name' => 'Test User']);
        $chatService = app(ChatService::class);

        // 1. Kim Tuyến gửi tin nhắn cách đây 1 ngày
        $msgOld = $chatService->customerSendMessage($customerOld, 'Tin nhắn hôm qua');
        $msgOld->update(['sent_at' => now()->subDay()]);
        $caseOld = $msgOld->supportCase;

        // Kim Tuyến được mở xem/chấp nhận cách đây 10 giây (last_activity_at = now() - 10s)
        $caseOld->update(['last_activity_at' => now()->subSeconds(10)]);

        // 2. Test User gửi tin nhắn cách đây 17 phút (tin nhắn mới hơn Kim Tuyến)
        $msgNew = $chatService->customerSendMessage($customerNew, 'Tin nhắn 17 phút trước');
        $msgNew->update(['sent_at' => now()->subMinutes(17)]);
        $caseNew = $msgNew->supportCase;
        $caseNew->update(['last_activity_at' => now()->subMinutes(17)]);

        // Lấy danh sách cases cho staff
        $cases = $chatService->getStaffCases('all', null, 25, $staff);
        $items = $cases->items();

        // Test User (17 phút trước) PHẢI nhảy lên đầu danh sách trước Kim Tuyến (1 ngày trước)
        $this->assertEquals($caseNew->id, $items[0]->id);
        $this->assertEquals($caseOld->id, $items[1]->id);

        // 3. Khi Kim Tuyến gửi thêm tin nhắn mới vào lúc này
        $msgLatest = $chatService->customerSendMessage($customerOld, 'Tin nhắn vừa mới gửi!');
        $caseOld->refresh();

        // Bây giờ Kim Tuyến PHẢI nhảy lên đầu danh sách
        $updatedCases = $chatService->getStaffCases('all', null, 25, $staff);
        $updatedItems = $updatedCases->items();
        $this->assertEquals($caseOld->id, $updatedItems[0]->id);
        $this->assertEquals($caseNew->id, $updatedItems[1]->id);
    }

    public function test_staff_order_suggestion_renders_and_can_be_sent_as_chat_card(): void
    {
        $staff = $this->createStaff(['full_name' => 'Nhân viên Mai']);
        $customer = $this->createCustomer(['full_name' => 'Khách Hàng Đặt Đơn']);

        $category = Category::create(['name' => 'Gấu bông quà tặng', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu bông Teddy Nơ Hồng 50cm',
            'description' => 'Gấu bông dễ thương',
            'price' => 250000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);

        // Tạo đơn hàng cho khách
        $order = Order::create([
            'order_code' => 'ORD-12345',
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách Hàng Đặt Đơn',
            'recipient_phone' => '0987654321',
            'recipient_address' => '123 Đường ABC, Quận 1, TP.HCM',
            'subtotal' => 250000,
            'total_amount' => 250000,
            'order_status' => 'PENDING',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Gấu bông Teddy Nơ Hồng 50cm',
            'product_price' => 250000,
            'quantity' => 1,
            'line_total' => 250000,
        ]);

        // 1. Staff click "Nhắn tin cho khách" từ trang đơn hàng -> điều hướng sang staff.support.index với customer_id & order_id
        $response = $this->actingAs($staff)->get(route('staff.support.index', [
            'customer_id' => $customer->id,
            'order_id' => $order->id,
        ]));

        $response->assertOk();
        // Kiểm tra hiển thị khung gợi ý Shopee
        $response->assertSee('staff-support-order-suggestion');
        $response->assertSee('Gợi ý: Gửi thông tin đơn hàng này cho khách');
        $response->assertSee('ORD-12345');
        $response->assertSee('Gấu bông Teddy Nơ Hồng 50cm');
        $response->assertSee('Gửi đơn này');
        $response->assertSee('data-send-suggested-order');
        $response->assertSee('data-dismiss-suggested-order');

        // Lấy case vừa được tạo / liên kết
        $case = SupportCase::where('customer_id', $customer->id)->first();
        $this->assertNotNull($case);
        // Khi mới nhấn vào khách hàng nhưng CHƯA gửi đơn hay tin nhắn:
        // 1. Chưa có mã đơn liên quan (order_id là null)
        // 2. Cuộc trò chuyện không được hiển thị trong danh sách cuộc trò chuyện bên trái (không thấy "Chưa có tin nhắn")
        $this->assertNull($case->order_id);
        $response->assertDontSee('Chưa có tin nhắn');

        // 2. Staff bấm "Gửi đơn này" -> gửi tin nhắn định dạng Order Card
        $formattedMessage = "📦 [ĐƠN HÀNG #{$order->order_code}]\n• Sản phẩm: Gấu bông Teddy Nơ Hồng 50cm\n• Tổng tiền: " . number_format($order->total_amount, 0, ',', '.') . "đ\n• Trạng thái: Chờ xác nhận\n• Mã đơn hàng: #{$order->order_code}";

        $sendResponse = $this->actingAs($staff)->postJson(route('staff.support.messages.send', $case), [
            'content' => $formattedMessage,
            'order_id' => $order->id,
        ]);

        $sendResponse->assertCreated();
        $this->assertDatabaseHas('messages', [
            'support_case_id' => $case->id,
            'sender_id' => $staff->id,
            'content' => $formattedMessage,
        ]);

        // Sau khi bấm gửi: Mã đơn liên quan mới được nhảy / liên kết
        $case->refresh();
        $this->assertEquals($order->id, $case->order_id);

        // 3. Khách hàng mở chat xem -> thẻ đơn hàng được hiển thị dưới dạng card Shopee đẹp mắt
        $customerResponse = $this->actingAs($customer)->get(route('customer.messages.index'));
        $customerResponse->assertOk();
        $customerResponse->assertSee('chat-order-card');
        $customerResponse->assertSee('#' . $order->order_code);
        $customerResponse->assertDontSee('Đơn hàng #' . $order->order_code);
        $customerResponse->assertSee('PENDING');
        $customerResponse->assertSee('250.000');
    }

    public function test_abandoned_draft_case_without_messages_disappears_when_navigating_away(): void
    {
        $staff = $this->createStaff();
        $customer = $this->createCustomer(['full_name' => 'Khách Hàng Mới Chưa Chat']);

        // 1. Staff mở khung chat với khách hàng mới nhưng KHÔNG gửi tin nhắn nào
        $response = $this->actingAs($staff)->get(route('staff.support.index', [
            'customer_id' => $customer->id,
        ]));
        $response->assertOk();

        // Danh sách cuộc trò chuyện bên trái không được hiện ca trống này
        $response->assertDontSee('Chưa có tin nhắn');

        // 2. Staff thoát ra / chuyển sang trang danh sách hỗ trợ chung mà chưa gửi gì
        $indexResponse = $this->actingAs($staff)->get(route('staff.support.index'));
        $indexResponse->assertOk();

        // Ca nháp trống không có tin nhắn nào phải tự động biến mất hoàn toàn
        $this->assertEquals(0, SupportCase::where('customer_id', $customer->id)->count());
    }

    public function test_all_customer_orders_are_rendered_in_support_info_panel(): void
    {
        $staff = $this->createStaff(['full_name' => 'Nhân viên Mai']);
        $customer = $this->createCustomer(['full_name' => 'Khách Hàng Thân Thiết']);

        // Tạo 2 đơn hàng cho khách hàng này
        $order1 = Order::create([
            'order_code' => 'ORD-11111',
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách Hàng Thân Thiết',
            'recipient_phone' => '0987654321',
            'recipient_address' => '123 Đường ABC, TP.HCM',
            'subtotal' => 200000,
            'total_amount' => 200000,
            'order_status' => 'PENDING',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ]);

        $order2 = Order::create([
            'order_code' => 'ORD-22222',
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách Hàng Thân Thiết',
            'recipient_phone' => '0987654321',
            'recipient_address' => '123 Đường ABC, TP.HCM',
            'subtotal' => 450000,
            'total_amount' => 450000,
            'order_status' => 'COMPLETED',
            'payment_method' => 'VNPAY',
            'payment_status' => 'PAID',
        ]);

        $chatService = app(ChatService::class);
        $msg = $chatService->customerSendMessage($customer, 'Tư vấn giúp em với shop');
        $case = $msg->supportCase;
        $case->update(['order_id' => $order1->id]);
        $chatService->acceptCase($staff, $case);

        // Staff mở xem ca hỗ trợ
        $response = $this->actingAs($staff)->get(route('staff.support.index', ['case_id' => $case->id]));
        $response->assertOk();

        // Kiểm tra hiển thị thông tin đơn hàng
        $response->assertSee('Thông tin đơn hàng');
        $response->assertSee('2 đơn');
        $response->assertSee('ORD-11111');
        $response->assertSee('ORD-22222');
        $response->assertSee('Đơn liên quan');
        $response->assertSee('Chờ xác nhận');
        $response->assertSee('Hoàn thành');
        $response->assertSee('200.000');
        $response->assertSee('450.000');
    }

    public function test_opening_new_customer_without_sending_does_not_show_in_list_and_related_order_links_only_on_send(): void
    {
        $admin = $this->createAdmin();
        $otherCustomer = $this->createCustomer(['full_name' => 'Khách Hàng Cũ Đã Chat']);
        $newCustomer = $this->createCustomer(['full_name' => 'Trần Thị Bình']);

        // Khách hàng cũ đã có tin nhắn từ trước
        $chatService = app(ChatService::class);
        $oldMsg = $chatService->customerSendMessage($otherCustomer, 'Em cần hỏi mẫu này');
        $oldCase = $oldMsg->supportCase;

        // Tạo đơn hàng cho khách hàng mới Trần Thị Bình
        $order = Order::create([
            'order_code' => 'ORD-TB-999',
            'customer_id' => $newCustomer->id,
            'recipient_name' => 'Trần Thị Bình',
            'recipient_phone' => '0982222222',
            'recipient_address' => 'TP. Hồ Chí Minh',
            'subtotal' => 199000,
            'total_amount' => 199000,
            'order_status' => 'COMPLETED',
            'payment_method' => 'VNPAY',
            'payment_status' => 'PAID',
        ]);

        // 1. Admin bấm vào "Nhắn tin cho khách" từ đơn hàng của Trần Thị Bình
        $response = $this->actingAs($admin)->get(route('admin.support.index', [
            'customer_id' => $newCustomer->id,
            'order_id' => $order->id,
        ]));

        $response->assertOk();

        // A. Trong danh sách cuộc trò chuyện bên trái:
        // - Phải thấy khách hàng cũ đã chat
        // - KHÔNG được thấy 'Chưa có tin nhắn'
        $response->assertSee('Khách Hàng Cũ Đã Chat');
        $response->assertDontSee('Chưa có tin nhắn');

        // B. Trong panel Thông tin hỗ trợ (Card 2):
        // - Mã đơn liên quan PHẢI là 'Không có' (chưa được nhảy mã đơn liên quan)
        $response->assertSee('Không có');

        // C. Khung gợi ý Shopee phải hiển thị sẵn sàng
        $response->assertSee('Gợi ý: Gửi thông tin đơn hàng này cho khách');
        $response->assertSee('Gửi đơn này');

        // D. Trong cơ sở dữ liệu: Case được tạo nhưng order_id là NULL
        $case = SupportCase::where('customer_id', $newCustomer->id)->first();
        $this->assertNotNull($case);
        $this->assertNull($case->order_id);

        // 2. Admin bấm "Gửi đơn này"
        $formattedMsg = "📦 [ĐƠN HÀNG #{$order->order_code}]\n• Sản phẩm: Gấu bông Mật Ngọt\n• Tổng tiền: 199.000 đ\n• Trạng thái: Hoàn thành\n• Mã đơn hàng: #{$order->order_code}";
        $sendResponse = $this->actingAs($admin)->postJson(route('admin.support.messages.send', $case), [
            'content' => $formattedMsg,
            'order_id' => $order->id,
        ]);

        $sendResponse->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => 'ORD-TB-999',
                    'is_first_message' => true,
                ],
            ]);

        // E. Sau khi bấm gửi: Mã đơn liên quan đã nhảy thành công vào case
        $case->refresh();
        $this->assertEquals($order->id, $case->order_id);

        // F. Mở lại trang xem phiên chat sau khi đã gửi:
        $afterResponse = $this->actingAs($admin)->get(route('admin.support.index', [
            'case_id' => $case->id,
        ]));
        $afterResponse->assertOk();

        // Giờ đây Trần Thị Bình ĐÃ xuất hiện trên danh sách cuộc trò chuyện bên trái cùng mã đơn
        $afterResponse->assertSee('Trần Thị Bình');
        $afterResponse->assertSee('#ORD-TB-999');
        $afterResponse->assertSee('Đơn liên quan');
    }

    public function test_customer_order_list_scroll_behavior_at_five_orders(): void
    {
        $staff = $this->createStaff(['full_name' => 'Nhân viên Scroll Test']);
        $customer = $this->createCustomer(['full_name' => 'Khách Hàng Nhiều Đơn']);

        // Tạo 4 đơn hàng đầu tiên
        for ($i = 1; $i <= 4; $i++) {
            Order::create([
                'order_code' => "ORD-TEST-0{$i}",
                'customer_id' => $customer->id,
                'recipient_name' => 'Khách Hàng Nhiều Đơn',
                'recipient_phone' => '0987654321',
                'recipient_address' => '123 Đường ABC, TP.HCM',
                'subtotal' => 100000 * $i,
                'total_amount' => 100000 * $i,
                'order_status' => 'PENDING',
                'payment_method' => 'COD',
                'payment_status' => 'UNPAID',
            ]);
        }

        $chatService = app(ChatService::class);
        $msg = $chatService->customerSendMessage($customer, 'Chào shop kiểm tra đơn giúp mình');
        $case = $msg->supportCase;
        $chatService->acceptCase($staff, $case);

        // 1. Khi có 4 đơn hàng: KHÔNG CÓ class 'has-scroll'
        $response4 = $this->actingAs($staff)->get(route('staff.support.index', [
            'case_id' => $case->id,
        ]));
        $response4->assertOk();
        $response4->assertSee('4 đơn');
        $response4->assertSee('staff-support-order-list ');
        $response4->assertDontSee('staff-support-order-list has-scroll');

        // Tạo thêm đơn hàng thứ 5
        Order::create([
            'order_code' => 'ORD-TEST-05',
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách Hàng Nhiều Đơn',
            'recipient_phone' => '0987654321',
            'recipient_address' => '123 Đường ABC, TP.HCM',
            'subtotal' => 500000,
            'total_amount' => 500000,
            'order_status' => 'COMPLETED',
            'payment_method' => 'VNPAY',
            'payment_status' => 'PAID',
        ]);

        // 2. Khi có từ 5 đơn hàng trở lên: BẮT BUỘC CÓ class 'has-scroll'
        $response5 = $this->actingAs($staff)->get(route('staff.support.index', [
            'case_id' => $case->id,
        ]));
        $response5->assertOk();
        $response5->assertSee('5 đơn');
        $response5->assertSee('staff-support-order-list has-scroll');
        $response5->assertSee('#ORD-TEST-01');
        $response5->assertSee('#ORD-TEST-05');
    }

    public function test_customer_can_send_image_in_chat(): void
    {
        Storage::fake('public');

        $customer = $this->createCustomer(['full_name' => 'Khách Gửi Ảnh']);
        $image = UploadedFile::fake()->image('customer_photo.jpg', 400, 400);

        $response = $this->actingAs($customer)->post(route('customer.messages.send'), [
            'content' => 'Shop ơi xem giúp em ảnh này',
            'image' => $image,
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_self' => true,
                    'content' => 'Shop ơi xem giúp em ảnh này',
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertNotNull($responseData['image_url']);
        $this->assertStringContainsString('chat_images/', $responseData['image_url']);

        $message = Message::latest('id')->first();
        $this->assertNotNull($message->image_url);
        $this->assertEquals('Shop ơi xem giúp em ảnh này', $message->content);
    }

    public function test_staff_and_admin_can_send_image_in_chat(): void
    {
        Storage::fake('public');

        $staff = $this->createStaff(['full_name' => 'Nhân Viên Hỗ Trợ']);
        $customer = $this->createCustomer(['full_name' => 'Khách Nhận Ảnh']);

        $chatService = app(ChatService::class);
        $custMsg = $chatService->customerSendMessage($customer, 'Tư vấn giúp em mẫu gấu bông');
        $case = $custMsg->supportCase;
        $chatService->acceptCase($staff, $case);

        $image = UploadedFile::fake()->image('staff_product.png', 600, 600);

        // Gửi tin nhắn có ảnh và không cần text
        $response = $this->actingAs($staff)->post(route('staff.support.messages.send', $case), [
            'image' => $image,
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_customer' => false,
                    'content' => null,
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertNotNull($responseData['image_url']);
        $this->assertStringContainsString('chat_images/', $responseData['image_url']);

        $message = Message::latest('id')->first();
        $this->assertNotNull($message->image_url);
        $this->assertNull($message->content);

        // Xem trang staff index để kiểm tra render HTML ảnh
        $viewResponse = $this->actingAs($staff)->get(route('staff.support.index', [
            'case_id' => $case->id,
        ]));
        $viewResponse->assertOk();
        $viewResponse->assertSee('chat-msg-image-wrap');
        $viewResponse->assertSee($message->image_url);
    }

    public function test_image_upload_validation_rejects_invalid_file_and_large_size(): void
    {
        Storage::fake('public');

        $customer = $this->createCustomer();

        // 1. Tệp không phải là hình ảnh (pdf)
        $fakePdf = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
        $responsePdf = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'image' => $fakePdf,
        ]);
        $responsePdf->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);

        // 2. Hình ảnh vượt quá 5MB (6000KB)
        $largeImage = UploadedFile::fake()->create('large.png', 6000, 'image/png');
        $responseLarge = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'image' => $largeImage,
        ]);
        $responseLarge->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    public function test_all_three_roles_have_image_button_and_no_emoji_or_paperclip_buttons(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $customer = $this->createCustomer();

        $chatService = app(ChatService::class);
        $msg = $chatService->customerSendMessage($customer, 'Xin chào shop');
        $case = $msg->supportCase;
        $chatService->acceptCase($staff, $case);

        // 1. Phía Customer
        $customerView = $this->actingAs($customer)->get(route('customer.messages.index'));
        $customerView->assertOk();
        $customerView->assertSee('data-chat-btn-image', false);
        $customerView->assertSee('data-chat-file-input', false);
        $customerView->assertDontSee('fa-paperclip');
        $customerView->assertDontSee('fa-face-smile');
        $customerView->assertDontSee('Biểu tượng cảm xúc');

        // 2. Phía Staff
        $staffView = $this->actingAs($staff)->get(route('staff.support.index', ['case_id' => $case->id]));
        $staffView->assertOk();
        $staffView->assertSee('data-chat-btn-image', false);
        $staffView->assertSee('data-chat-file-input', false);
        $staffView->assertDontSee('fa-paperclip');
        $staffView->assertDontSee('fa-face-smile');
        $staffView->assertDontSee('Emoji');

        // 3. Phía Admin
        $adminView = $this->actingAs($admin)->get(route('admin.support.index', ['case_id' => $case->id]));
        $adminView->assertOk();
        $adminView->assertSee('data-chat-btn-image', false);
        $adminView->assertSee('data-chat-file-input', false);
        $adminView->assertDontSee('fa-paperclip');
        $adminView->assertDontSee('fa-face-smile');
        $adminView->assertDontSee('Emoji');
    }

    public function test_customer_can_send_multiple_images(): void
    {
        Storage::fake('public');
        $customer = $this->createCustomer();

        $images = [
            UploadedFile::fake()->image('image1.png', 400, 400),
            UploadedFile::fake()->image('image2.jpg', 400, 400),
            UploadedFile::fake()->image('image3.webp', 400, 400),
        ];

        $response = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'images' => $images,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(3, 'data.images');

        $message = Message::latest('id')->first();
        $this->assertNotNull($message);
        $this->assertCount(3, $message->image_urls);
        $this->assertNotEmpty($message->image_url);
        $this->assertEquals($message->image_urls[0], $message->image_url);
    }

    public function test_staff_can_send_multiple_images(): void
    {
        Storage::fake('public');
        $staff = $this->createStaff();
        $customer = $this->createCustomer();

        $chatService = app(ChatService::class);
        $initMsg = $chatService->customerSendMessage($customer, 'Cần tư vấn');
        $case = $initMsg->supportCase;
        $chatService->acceptCase($staff, $case);

        $images = [
            UploadedFile::fake()->image('staff_img1.png', 300, 300),
            UploadedFile::fake()->image('staff_img2.png', 300, 300),
        ];

        $response = $this->actingAs($staff)->postJson(route('staff.support.messages.send', $case), [
            'images' => $images,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'data.images');

        $message = Message::latest('id')->first();
        $this->assertNotNull($message);
        $this->assertCount(2, $message->image_urls);
    }

    public function test_customer_uploading_images_with_legacy_single_image_field_does_not_duplicate(): void
    {
        Storage::fake('public');
        $customer = $this->createCustomer();

        $img1 = UploadedFile::fake()->image('img1.png', 400, 400);
        $img2 = UploadedFile::fake()->image('img2.png', 400, 400);
        $img3 = UploadedFile::fake()->image('img3.png', 400, 400);

        // Sending both images[] and image (legacy single image matching img1)
        $response = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'images' => [$img1, $img2, $img3],
            'image' => $img1,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(3, 'data.images');

        $message = Message::latest('id')->first();
        $this->assertNotNull($message);
        $this->assertCount(3, $message->image_urls);
        // Ensure all URLs are unique and there is no 4th duplicate
        $this->assertCount(3, array_unique($message->image_urls));
    }

    public function test_cannot_send_more_than_10_images(): void
    {
        Storage::fake('public');
        $customer = $this->createCustomer();

        $images = [];
        for ($i = 0; $i < 11; $i++) {
            $images[] = UploadedFile::fake()->image("image_{$i}.png", 100, 100);
        }

        $response = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'images' => $images,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['images']);
    }

    public function test_customer_chat_with_order_id_displays_order_suggestion_card(): void
    {
        $customer = $this->createCustomer(['full_name' => 'Khách Chat Đơn']);
        $category = Category::create(['name' => 'Gấu Bông', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu Bông Test Gợi Ý',
            'description' => 'Mô tả test',
            'price' => 199000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);

        $order = Order::create([
            'order_code' => 'MNB-TESTSUGG',
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách Chat Đơn',
            'recipient_phone' => '0912345678',
            'recipient_address' => 'Địa chỉ test',
            'subtotal' => 199000,
            'total_amount' => 199000,
            'order_status' => 'PENDING',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Gấu Bông Test Gợi Ý',
            'product_price' => 199000,
            'quantity' => 1,
            'line_total' => 199000,
        ]);

        $response = $this->actingAs($customer)->get(route('customer.messages.index', ['order_id' => $order->id]));
        $response->assertOk();
        $response->assertSee('data-order-suggestion', false);
        $response->assertSee('#MNB-TESTSUGG');
        $response->assertSee('Gửi đơn này');
        $response->assertSee('Gấu Bông Test Gợi Ý');
    }

    public function test_customer_can_send_suggested_order_and_links_case_to_order(): void
    {
        $customer = $this->createCustomer(['full_name' => 'Khách Gửi Đơn']);
        $category = Category::create(['name' => 'Gấu Bông', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu Bông Thỏ Hồng',
            'description' => 'Mô tả test',
            'price' => 299000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);

        $order = Order::create([
            'order_code' => 'MNB-SENDORD',
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách Gửi Đơn',
            'recipient_phone' => '0912345678',
            'recipient_address' => 'Địa chỉ test',
            'subtotal' => 299000,
            'total_amount' => 299000,
            'order_status' => 'PENDING',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Gấu Bông Thỏ Hồng',
            'product_price' => 299000,
            'quantity' => 1,
            'line_total' => 299000,
        ]);

        $content = "📦 [ĐƠN HÀNG #MNB-SENDORD]\n• Sản phẩm: {$product->name}\n• Tổng tiền: " . number_format($order->total_amount, 0, ',', '.') . " đ\n• Trạng thái: {$order->order_status}\n• Mã đơn hàng: #MNB-SENDORD";

        $response = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => $content,
            'order_id' => $order->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $customer->id,
        ]);

        $message = Message::latest('id')->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('MNB-SENDORD', $message->content);
        $this->assertEquals($order->id, $message->supportCase->order_id);
    }

    public function test_order_views_contain_chat_with_shop_link_with_order_id(): void
    {
        $customer = $this->createCustomer(['full_name' => 'Khách Nút Chat']);
        $category = Category::create(['name' => 'Gấu Bông', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gấu Bông Cỡ Lớn',
            'description' => 'Mô tả test',
            'price' => 350000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);

        $order = Order::create([
            'order_code' => 'MNB-LINKTEST',
            'customer_id' => $customer->id,
            'recipient_name' => 'Khách Nút Chat',
            'recipient_phone' => '0912345678',
            'recipient_address' => 'Địa chỉ test',
            'subtotal' => 350000,
            'total_amount' => 350000,
            'order_status' => 'PENDING',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Gấu Bông Cỡ Lớn',
            'product_price' => 350000,
            'quantity' => 1,
            'line_total' => 350000,
        ]);

        $expectedUrl = route('customer.messages.index', ['order_id' => $order->id]);

        // Kiểm tra trong danh sách đơn hàng
        $indexResponse = $this->actingAs($customer)->get(route('customer.orders.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee($expectedUrl, false);

        // Kiểm tra trong chi tiết đơn hàng
        $showResponse = $this->actingAs($customer)->get(route('customer.orders.show', $order));
        $showResponse->assertOk();
        $showResponse->assertSee($expectedUrl, false);
    }

    public function test_chat_input_bar_is_unified_with_photo_button_outside_border_for_customer_and_staff(): void
    {
        $customer = $this->createCustomer(['full_name' => 'Khách Test Input']);
        $staff = $this->createStaff(['full_name' => 'Staff Test Input']);
        $chatService = app(ChatService::class);

        $msg = $chatService->customerSendMessage($customer, 'Xin chào shop');
        $case = $msg->supportCase;
        $chatService->acceptCase($staff, $case);

        // 1. Kiểm tra giao diện phía Khách hàng: nút ảnh bên ngoài border, nút gửi có icon paper-plane
        $custResponse = $this->actingAs($customer)->get(route('customer.messages.index'));
        $custResponse->assertOk();
        $custResponse->assertSee('data-chat-btn-image', false);
        $custResponse->assertSee('customer-chat-input', false);
        $custResponse->assertSee('data-chat-submit', false);
        $custResponse->assertSee('fa-paper-plane', false);
        $custResponse->assertDontSee('customer-chat-input-wrap', false);

        // 2. Kiểm tra giao diện phía Nhân viên / Admin: hoàn toàn đồng bộ
        $staffResponse = $this->actingAs($staff)->get(route('staff.support.index', [
            'tab' => 'in_progress',
            'case_id' => $case->id,
        ]));
        $staffResponse->assertOk();
        $staffResponse->assertSee('data-chat-btn-image', false);
        $staffResponse->assertSee('staff-support-chat-input', false);
        $staffResponse->assertSee('data-staff-chat-submit', false);
        $staffResponse->assertSee('fa-paper-plane', false);
        $staffResponse->assertDontSee('staff-support-chat-input-wrap', false);
    }

    public function test_consecutive_messages_messenger_grouping_renders_correctly(): void
    {
        $customer = $this->createCustomer();
        $staff = $this->createStaff();
        $chatService = app(ChatService::class);

        // Khách hàng gửi 3 tin nhắn liên tiếp trong vòng 1-2 phút
        $msg1 = $chatService->customerSendMessage($customer, 'Tin 1 của khách');
        $msg1->update(['sent_at' => now()->subMinutes(3)]);

        $msg2 = $chatService->customerSendMessage($customer, 'Tin 2 của khách');
        $msg2->update(['sent_at' => now()->subMinutes(2)]);

        $msg3 = $chatService->customerSendMessage($customer, 'Tin 3 của khách');
        $msg3->update(['sent_at' => now()->subMinute()]);

        $case = $msg1->supportCase;
        $chatService->acceptCase($staff, $case);

        // Nhân viên trả lời 2 tin nhắn liên tiếp
        $staffMsg1 = $chatService->staffSendMessage($staff, $case, 'Shop chào bạn câu 1');
        $staffMsg1->update(['sent_at' => now()->subSeconds(30)]);

        $staffMsg2 = $chatService->staffSendMessage($staff, $case, 'Shop chào bạn câu 2');
        $staffMsg2->update(['sent_at' => now()]);

        // 1. Kiểm tra view Customer
        $custResponse = $this->actingAs($customer)->get(route('customer.messages.index'));
        $custResponse->assertOk();
        $custResponse->assertSee('pos-first', false);
        $custResponse->assertSee('pos-middle', false);
        $custResponse->assertSee('pos-last', false);
        // Với 2 tin của shop, tin đầu phải có avatar spacer để căn thẳng hàng
        $custResponse->assertSee('customer-chat-msg-row__avatar is-spacer', false);

        // 2. Kiểm tra view Staff
        $staffResponse = $this->actingAs($staff)->get(route('staff.support.index', [
            'tab' => 'in_progress',
            'case_id' => $case->id,
        ]));
        $staffResponse->assertOk();
        $staffResponse->assertSee('pos-first', false);
        $staffResponse->assertSee('pos-middle', false);
        $staffResponse->assertSee('pos-last', false);
        // Với tin nhắn của khách, tin trước có spacer avatar
        $staffResponse->assertSee('staff-chat-msg-avatar is-spacer', false);
    }

    public function test_message_delivery_status_shows_as_small_zalo_text_below_last_bubble(): void
    {
        $chatService = app(ChatService::class);
        $customer = $this->createCustomer();
        $staff = $this->createStaff();

        $msg1 = $chatService->customerSendMessage($customer, 'Xin chào shop Mật Ngọt Bear');
        $msg1->update(['sent_at' => now()->subMinutes(2), 'is_read' => false]);

        $case = $msg1->supportCase;

        // 1. Khách xem chat khi chưa đọc -> Hiện chữ "Đã gửi" dưới bubble, không còn icon SVG
        $custResponse = $this->actingAs($customer)->get(route('customer.messages.index'));
        $custResponse->assertOk();
        $custResponse->assertSee('chat-msg-status', false);
        $custResponse->assertSee('Đã gửi', false);
        $custResponse->assertDontSee('customer-chat-check-icon', false);

        // 2. Khi tin nhắn được đọc (is_read = true) -> Hiện chữ "Đã xem"
        $msg1->update(['is_read' => true]);
        $custResponse2 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $custResponse2->assertOk();
        $custResponse2->assertSee('chat-msg-status', false);
        $custResponse2->assertSee('Đã xem', false);

        // 3. Nhân viên tiếp nhận và trả lời
        $chatService->acceptCase($staff, $case);
        $staffMsg = $chatService->staffSendMessage($staff, $case, 'Shop xin chào bạn ạ!');
        $staffMsg->update(['sent_at' => now(), 'is_read' => false]);

        $staffResponse = $this->actingAs($staff)->get(route('staff.support.index', [
            'tab' => 'in_progress',
            'case_id' => $case->id,
        ]));
        $staffResponse->assertOk();
        $staffResponse->assertSee('staff-chat-status', false);
        $staffResponse->assertSee('Đã gửi', false);

        // 4. Sau khi đối phương trả lời, tin trước đó KHÔNG còn là tin cuối cùng của đoạn chat -> Không hiện trạng thái ở giữa đoạn chat
        $custResponse3 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $custResponse3->assertOk();
        $custResponse3->assertDontSee('chat-msg-status', false);
    }

    public function test_customer_account_sidebar_shows_unread_message_count_badge(): void
    {
        $customer = $this->createCustomer(['email' => 'khach_badge@test.com']);
        $staff = $this->createStaff(['email' => 'nhanvien_badge@test.com']);
        $chatService = app(ChatService::class);

        // Ban đầu chưa có tin nhắn -> Không có badge
        $this->assertEquals(0, $chatService->countUnreadMessagesForCustomer($customer));
        $res1 = $this->actingAs($customer)->get(route('customer.orders.index'));
        $res1->assertOk();
        $res1->assertDontSee('customer-account-badge', false);

        // Khách gửi tin nhắn và nhân viên trả lời 2 tin chưa đọc
        $msg = $chatService->customerSendMessage($customer, 'Xin chào shop');
        $case = $msg->supportCase;
        $chatService->acceptCase($staff, $case);

        $staffMsg1 = $chatService->staffSendMessage($staff, $case, 'Dạ em chào anh/chị ạ');
        $staffMsg2 = $chatService->staffSendMessage($staff, $case, 'Shop có thể hỗ trợ gì cho anh/chị ạ?');
        $staffMsg1->update(['is_read' => false]);
        $staffMsg2->update(['is_read' => false]);

        // Đếm số tin chưa đọc = 2
        $this->assertEquals(2, $chatService->countUnreadMessagesForCustomer($customer));

        // Khách hàng vào trang đơn hàng -> Menu hiện badge với số 2
        $res2 = $this->actingAs($customer)->get(route('customer.orders.index'));
        $res2->assertOk();
        $res2->assertSee('customer-account-badge', false);
        $res2->assertSee('2 tin nhắn chưa đọc', false);

        // Khách hàng mở vào trang chat -> Tin nhắn tự động được đánh dấu đã đọc
        $res3 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $res3->assertOk();
        $this->assertEquals(0, $chatService->countUnreadMessagesForCustomer($customer));

        // Quay lại trang đơn hàng -> Badge biến mất
        $res4 = $this->actingAs($customer)->get(route('customer.orders.index'));
        $res4->assertOk();
        $res4->assertDontSee('customer-account-badge', false);
    }

    public function test_customer_chat_header_status_badge_lifecycle(): void
    {
        $customer = $this->createCustomer(['email' => 'customer_header_status@test.com']);
        $staff = $this->createStaff(['full_name' => 'Nguyễn Thị Thu Trang']);
        $chatService = app(ChatService::class);

        // 1. Khi chưa có ca nào -> Hiển thị trạng thái Online của Shop (Đang trực tuyến)
        $res1 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $res1->assertOk();
        $res1->assertSee('customer-chat-header__badge-online', false);
        $res1->assertSee('is-online', false);
        $res1->assertSee('Đang trực tuyến', false);

        // 2. Khách hàng gửi tin nhắn -> Ca tạo ra ở trạng thái WAITING -> Hiển thị "Chờ tiếp nhận" (vàng cam)
        $sendRes = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => 'Xin chào, em muốn hỏi thông tin ạ',
        ]);
        $sendRes->assertCreated();
        $sendRes->assertJsonPath('header_status.type', 'waiting');
        $sendRes->assertJsonPath('header_status.label', 'Chờ tiếp nhận');
        $sendRes->assertJsonPath('header_status.class', 'is-waiting');

        $res2 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $res2->assertOk();
        $res2->assertSee('is-waiting', false);
        $res2->assertSee('Chờ tiếp nhận', false);

        // 3. Nhân viên tiếp nhận ca -> IN_PROGRESS -> Hiển thị "Đang hỗ trợ (Nguyễn Thị Thu Trang)" (xanh lá)
        $case = \App\Models\SupportCase::where('customer_id', $customer->id)->latest('id')->first();
        $chatService->acceptCase($staff, $case);

        $res3 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $res3->assertOk();
        $res3->assertSee('is-in-progress', false);
        $res3->assertSee('Đang hỗ trợ (Nguyễn Thị Thu Trang)', false);

        // Kiểm tra polling cũng trả về header_status cập nhật thời gian thực
        $pollRes = $this->actingAs($customer)->getJson(route('customer.messages.poll'));
        $pollRes->assertOk();
        $pollRes->assertJsonPath('header_status.type', 'in_progress');
        $pollRes->assertJsonPath('header_status.label', 'Đang hỗ trợ (Nguyễn Thị Thu Trang)');
        $pollRes->assertJsonPath('header_status.class', 'is-in-progress');

        // 4. Nhân viên đóng ca hỗ trợ -> Trở về trạng thái trực tuyến của Shop
        $chatService->closeCase($staff, $case);
        $res4 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $res4->assertOk();
        $res4->assertSee('is-online', false);
        $res4->assertSee('Đang trực tuyến', false);
    }

    public function test_shop_switches_to_offline_when_all_staff_and_admin_logged_out(): void
    {
        config(['session.driver' => 'database']);
        $chatService = app(ChatService::class);

        $customer = $this->createCustomer(['email' => 'customer_offline_check@test.com']);
        $staff = $this->createStaff(['full_name' => 'Nhân Viên Hỗ Trợ 1']);

        // Trường hợp 1: Chỉ có khách hàng có session trong bảng sessions
        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'cust_session_123',
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        // Kiểm tra: Vì chỉ có khách hàng, không có nhân viên/admin -> Phải là Ngoại tuyến (false)
        $this->assertFalse($chatService->isShopOnline());

        $res1 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $res1->assertOk();
        $res1->assertSee('Ngoại tuyến');
        $res1->assertSee('is-offline');

        // Trường hợp 2: Nhân viên đăng nhập vào hệ thống (tạo session trong DB)
        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'staff_session_456',
            'user_id' => $staff->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        // Kiểm tra: Đã có nhân viên online -> Trực tuyến (true)
        $this->assertTrue($chatService->isShopOnline());

        $res2 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $res2->assertOk();
        $res2->assertSee('Đang trực tuyến');
        $res2->assertSee('is-online');

        // Trường hợp 3: Nhân viên đăng xuất (xóa session khỏi DB)
        \Illuminate\Support\Facades\DB::table('sessions')->where('id', 'staff_session_456')->delete();

        // Kiểm tra: Nhân viên vừa đăng xuất hết -> Lập tức hiển thị Ngoại tuyến (false)
        $this->assertFalse($chatService->isShopOnline());

        $res3 = $this->actingAs($customer)->get(route('customer.messages.index'));
        $res3->assertOk();
        $res3->assertSee('Ngoại tuyến');
        $res3->assertSee('is-offline');
    }

    public function test_customer_chat_page_displays_faq_sidebar_with_expand_toggle(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('customer.messages.index'));
        $response->assertOk();

        // Kiểm tra Card FAQ bên thanh Sidebar
        $response->assertSee('Câu hỏi thường gặp');
        $response->assertSee('Xem tất cả câu hỏi');
        $response->assertSee('data-faq-card', false);
        $response->assertSee('data-faq-toggle-btn', false);
        $response->assertSee('data-faq-extra', false);
        $response->assertSee('data-faq-list-wrap', false);

        // Đảm bảo không còn modal popup
        $response->assertDontSee('customerFaqModal');
    }

    public function test_customer_sending_faq_question_triggers_automatic_reply(): void
    {
        $customer = $this->createCustomer();
        $this->createStaff(); // Đảm bảo có nhân viên/admin để bot lấy sender_id

        $question = 'Làm sao để kiểm tra đơn hàng?';

        $response = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => $question,
        ]);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'data' => [
                'content' => $question,
                'is_self' => true,
            ],
        ]);

        // Kiểm tra phản hồi tự động trong JSON
        $data = $response->json();
        $this->assertNotNull($data['reply']);
        $this->assertFalse($data['reply']['is_self']);
        $this->assertStringContainsString('Cách kiểm tra đơn hàng tại Mật Ngọt Bear', $data['reply']['content']);

        // Kiểm tra cả 2 tin nhắn (của khách + của bot) đã được lưu vào database
        $this->assertDatabaseCount('messages', 2);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $customer->id,
            'content' => $question,
        ]);
        $this->assertDatabaseHas('messages', [
            'content' => $data['reply']['content'],
        ]);
    }

    public function test_customer_sending_faq_question_alias_triggers_automatic_reply(): void
    {
        $customer = $this->createCustomer();
        $this->createAdmin();

        $aliasQuestion = 'Shop giao hàng trong bao lâu?';

        $response = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => $aliasQuestion,
        ]);

        $response->assertCreated();
        $data = $response->json();
        $this->assertNotNull($data['reply']);
        $this->assertStringContainsString('Thời gian giao hàng dự kiến', $data['reply']['content']);
    }

    public function test_faq_auto_reply_does_not_place_case_in_waiting_queue(): void
    {
        $customer = $this->createCustomer();
        $staff = $this->createStaff();
        $chatService = app(ChatService::class);

        // 1. Khách hàng gửi câu hỏi thường gặp (FAQ)
        $question = 'Làm sao để kiểm tra đơn hàng?';
        $response = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => $question,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('header_status.type', 'online');
        $response->assertJsonPath('header_status.label', 'Đang trực tuyến');

        // Ca hỗ trợ phải ở trạng thái CLOSED (Đã giải quyết tự động), KHÔNG nằm trong hàng chờ WAITING
        $case = SupportCase::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($case);
        $this->assertEquals(SupportCase::STATUS_CLOSED, $case->status);
        $this->assertNotEquals(SupportCase::STATUS_WAITING, $case->status);

        // Kiểm tra danh sách phía Nhân viên: Tab "Chưa xử lý" (waiting) phải bằng 0 và không chứa ca này
        $staffCounts = $chatService->getStaffCounts(null, $staff);
        $this->assertEquals(0, $staffCounts['waiting']);
        $this->assertEquals(1, $staffCounts['closed']);

        $waitingCases = $chatService->getStaffCases('waiting', null, 25, $staff);
        $this->assertCount(0, $waitingCases);

        // Polling của khách hàng không bị hiển thị lời chào lặp lại
        $pollRes = $this->actingAs($customer)->getJson(route('customer.messages.poll'));
        $pollRes->assertOk();
        $pollRes->assertJsonPath('should_show_greeting', false);
        $pollRes->assertJsonPath('header_status.type', 'online');
    }

    public function test_non_faq_message_after_faq_places_case_in_waiting_queue(): void
    {
        $customer = $this->createCustomer();
        $staff = $this->createStaff();
        $chatService = app(ChatService::class);

        // 1. Gửi câu hỏi FAQ trước -> ca đóng, không chờ xử lý
        $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => 'Chính sách đổi trả như thế nào?',
        ]);
        $this->assertEquals(0, $chatService->getStaffCounts(null, $staff)['waiting']);

        // 2. Khách hàng gửi tiếp một câu hỏi tùy biến cần nhân viên hỗ trợ
        $customQuestion = 'Shop có gói quà giấu tên người gửi không ạ?';
        $res = $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => $customQuestion,
        ]);

        $res->assertCreated();
        $res->assertJsonPath('header_status.type', 'waiting');
        $res->assertJsonPath('header_status.label', 'Chờ tiếp nhận');

        // Ca hỗ trợ lúc này đã tự động mở lại về trạng thái WAITING
        $case = SupportCase::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertEquals(SupportCase::STATUS_WAITING, $case->status);

        // Phía Nhân viên: Tab "Chưa xử lý" xuất hiện ca này
        $staffCounts = $chatService->getStaffCounts(null, $staff);
        $this->assertEquals(1, $staffCounts['waiting']);

        $waitingCases = $chatService->getStaffCases('waiting', null, 25, $staff);
        $this->assertCount(1, $waitingCases);
        $this->assertEquals($case->id, $waitingCases->first()->id);
    }

    public function test_faq_auto_reply_does_not_show_unread_indicators_for_staff(): void
    {
        $customer = $this->createCustomer(['full_name' => 'Nguyễn Thị Hoa']);
        $staff = $this->createStaff();
        $chatService = app(ChatService::class);

        // 1. Khách hàng gửi câu hỏi thường gặp (FAQ) được bot trả lời
        $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => 'Làm sao để kiểm tra đơn hàng?',
        ]);

        $case = SupportCase::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($case);

        // Đếm tin chưa đọc phía nhân viên phải bằng 0
        $this->assertEquals(0, $chatService->countUnreadMessagesForStaff($case));

        // Nhân viên xem danh sách hội thoại ở Tab Tất cả: Không có class is-unread, không có chấm xanh, không có badge đỏ số lượng
        $response = $this->actingAs($staff)->get(route('staff.support.index', ['tab' => 'all']));
        $response->assertOk();
        $response->assertSee('Nguyễn Thị Hoa');
        $response->assertDontSee('staff-support-unread-dot');
        $response->assertDontSee('staff-support-case-unread');

        // 2. Khách hàng hỏi thêm một câu hỏi FAQ khác
        $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => 'Chính sách đổi trả như thế nào?',
        ]);

        // Unread cho nhân viên vẫn phải là 0
        $this->assertEquals(0, $chatService->countUnreadMessagesForStaff($case->fresh()));

        $response2 = $this->actingAs($staff)->get(route('staff.support.index', ['tab' => 'all']));
        $response2->assertOk();
        $response2->assertDontSee('staff-support-unread-dot');
        $response2->assertDontSee('staff-support-case-unread');

        // 3. Khách hàng gửi câu hỏi thường không phải FAQ -> Cần nhân viên
        $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => 'Shop ơi tư vấn size gấu 1m2 với ạ',
        ]);

        $case->refresh();
        $this->assertEquals(SupportCase::STATUS_WAITING, $case->status);
        $this->assertEquals(1, $chatService->countUnreadMessagesForStaff($case));

        $response3 = $this->actingAs($staff)->get(route('staff.support.index', ['tab' => 'all']));
        $response3->assertOk();
        $response3->assertSee('staff-support-unread-dot');
        $response3->assertSee('staff-support-case-unread');
    }

    public function test_unanswered_message_prioritized_and_keeps_case_in_progress_when_faq_auto_replied(): void
    {
        $customer = $this->createCustomer(['full_name' => 'Trần Văn Hùng']);
        $staff = $this->createStaff(['full_name' => 'Nhân viên Minh Anh']);
        $chatService = app(ChatService::class);

        // 1. Khách gửi tin nhắn riêng chưa được trả lời (ví dụ mã đơn hoặc câu hỏi tùy biến)
        $unansweredText = 'Cần tư vấn đặt gấu bông theo yêu cầu riêng';
        $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => $unansweredText,
        ]);

        // 2. Khách gửi tiếp câu hỏi thường gặp FAQ (được bot tự động trả lời)
        $faqQuestion = 'Thời gian giao hàng bao lâu?';
        $this->actingAs($customer)->postJson(route('customer.messages.send'), [
            'content' => $faqQuestion,
        ]);

        $case = SupportCase::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($case);

        // Ca chưa được nhân viên nào nhận nên PHẢI giữ nguyên trạng thái WAITING ("Chưa xử lý"),
        // tuyệt đối không bị đóng (CLOSED) và không tự ý gán nhân viên (assigned_staff_id = null)
        $this->assertEquals(SupportCase::STATUS_WAITING, $case->status);
        $this->assertNull($case->assigned_staff_id);

        // Ca PHẢI hiển thị đúng 1 tin nhắn chưa đọc
        $this->assertEquals(1, $chatService->countUnreadMessagesForStaff($case));

        // Kiểm tra hàm lấy preview: ƯU TIÊN hiển thị tin nhắn chưa trả lời của khách, KHÔNG hiển thị tin bot auto-reply
        $previewMsg = $chatService->getCasePreviewMessage($case);
        $this->assertNotNull($previewMsg);
        $this->assertEquals($unansweredText, $previewMsg->content);

        // Phía Nhân viên xem Tab "Chưa xử lý" (waiting): Vẫn thấy ca này, không bị mất
        $response = $this->actingAs($staff)->get(route('staff.support.index', ['tab' => 'waiting']));
        $response->assertOk();
        $response->assertSee('Trần Văn Hùng');
        $response->assertSee($unansweredText); // Ưu tiên hiện lên để nhân viên đọc
        $response->assertSee('staff-support-unread-dot');
        $response->assertSee('staff-support-case-unread');

        // Tab "Đang xử lý" (in_progress): Chưa có vì nhân viên chưa ấn nhận ca
        $responseInProgress = $this->actingAs($staff)->get(route('staff.support.index', ['tab' => 'in_progress']));
        $responseInProgress->assertOk();
        $responseInProgress->assertDontSee('Trần Văn Hùng');

        // Khi Nhân viên ấn tiếp nhận ca: Ca mới chính thức chuyển sang IN_PROGRESS
        $chatService->acceptCase($staff, $case);
        $this->assertEquals(SupportCase::STATUS_IN_PROGRESS, $case->fresh()->status);
        $this->assertEquals($staff->id, $case->fresh()->assigned_staff_id);
    }
}




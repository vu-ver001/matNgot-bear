<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class CancelUnpaidOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động hủy các đơn hàng thanh toán online chưa hoàn tất sau 24 giờ';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService): int
    {
        $this->info('Đang quét các đơn hàng thanh toán online quá hạn 24 giờ...');

        $cancelledCount = $orderService->cancelExpiredUnpaidOrders();

        if ($cancelledCount > 0) {
            $this->info("Đã tự động hủy thành công {$cancelledCount} đơn hàng quá hạn 24 giờ và hoàn trả kho/voucher.");
        } else {
            $this->comment('Không có đơn hàng nào quá hạn cần hủy.');
        }

        return self::SUCCESS;
    }
}

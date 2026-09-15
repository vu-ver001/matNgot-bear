<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class ExpireCancelRequestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-cancel-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động từ chối các yêu cầu hủy đơn hàng đã quá hạn xử lý 24 giờ của nhân viên';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService): int
    {
        $this->info('Đang quét các yêu cầu hủy đơn hàng quá hạn 24 giờ...');

        $expiredCount = $orderService->autoRejectExpiredCancelRequests();

        if ($expiredCount > 0) {
            $this->info("Đã tự động từ chối thành công {$expiredCount} yêu cầu hủy đơn quá hạn 24 giờ.");
        } else {
            $this->comment('Không có yêu cầu hủy đơn nào quá hạn cần xử lý.');
        }

        return self::SUCCESS;
    }
}

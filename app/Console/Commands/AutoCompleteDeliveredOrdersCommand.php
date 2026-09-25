<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class AutoCompleteDeliveredOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-complete-delivered {--days=7 : Số ngày chờ khách xác nhận trước khi tự động hoàn tất}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động chuyển trạng thái Đã nhận hàng cho Khách hàng sau 7 ngày kể từ khi nhân viên giao hàng';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService): int
    {
        $days = (int) $this->option('days') ?: 7;
        $this->info("Đang quét các đơn hàng đã giao quá {$days} ngày chưa được khách hàng xác nhận...");

        $count = $orderService->autoCompleteDeliveredOrders($days);

        if ($count > 0) {
            $this->info("Đã tự động xác nhận Đã nhận hàng cho {$count} đơn hàng thành công.");
        } else {
            $this->comment('Không có đơn hàng nào cần tự động xác nhận.');
        }

        return self::SUCCESS;
    }
}

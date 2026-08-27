<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function revenue(Request $request)
    {
        $filters = $this->validateDateRange($request);

        $from = $filters['from'];
        $to = $filters['to'];

        $baseQuery = fn () => Order::where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->whereBetween('created_at', [$from, $to]);

        $totalRevenue = $baseQuery()->sum('total_amount');
        $totalOrders = $baseQuery()->count();
        $totalDiscount = $baseQuery()->sum('discount_amount');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $dailyRevenue = $baseQuery()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as order_count, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        $orders = $baseQuery()
            ->with('customer')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.revenue', compact(
            'from',
            'to',
            'totalRevenue',
            'totalOrders',
            'totalDiscount',
            'avgOrderValue',
            'dailyRevenue',
            'orders',
        ));
    }

    public function revenueExport(Request $request)
    {
        $filters = $this->validateDateRange($request);

        $orders = Order::where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->whereBetween('created_at', [$filters['from'], $filters['to']])
            ->with('customer')
            ->orderBy('created_at')
            ->get();

        $rows = collect([
            ['Ngày', 'Mã đơn', 'Khách hàng', 'SĐT', 'Tạm tính', 'Giảm giá', 'Phí ship', 'Tổng tiền', 'Phương thức thanh toán'],
        ]);

        $rows = $rows->concat($orders->map(fn (Order $order) => [
            $order->created_at->format('d/m/Y'),
            $order->order_code,
            $order->customer?->full_name ?? $order->recipient_name,
            $order->recipient_phone,
            $order->subtotal,
            $order->discount_amount,
            $order->shipping_fee,
            $order->total_amount,
            $order->payment_method,
        ]));

        $filename = 'doanh-thu-'.$filters['from']->format('Ymd').'-'.$filters['to']->format('Ymd').'.csv';

        return Response::streamDownload(function () use ($rows) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validateDateRange(Request $request): array
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ], [
            'to_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
        ]);

        $from = isset($validated['from_date'])
            ? Carbon::parse($validated['from_date'])->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = isset($validated['to_date'])
            ? Carbon::parse($validated['to_date'])->endOfDay()
            : Carbon::now()->endOfDay();

        return ['from' => $from, 'to' => $to];
    }
}
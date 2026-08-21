<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1E293B] leading-tight">Báo cáo doanh thu</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-[#64748B] mb-1">Từ ngày</label>
                            <input type="date" name="from_date" value="{{ $from->format('Y-m-d') }}"
                                   class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#64748B] mb-1">Đến ngày</label>
                            <input type="date" name="to_date" value="{{ $to->format('Y-m-d') }}"
                                   class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B]">Lọc</button>
                            <a href="{{ route('admin.reports.revenue') }}"
                               class="px-4 py-2 text-sm font-medium text-[#8B5A2B] bg-amber-50 rounded-full hover:bg-amber-100">Xóa lọc</a>
                        </div>
                        <div class="lg:col-span-2 flex justify-end">
                            <a href="{{ route('admin.reports.revenue.export', request()->only(['from_date', 'to_date'])) }}"
                               class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-full hover:bg-green-700">Xuất CSV</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Doanh thu (COMPLETED + PAID)</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($totalRevenue, 0, ',', '.') }} đ</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Số đơn hoàn thành</p>
                    <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ $totalOrders }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Giá trị trung bình / đơn</p>
                    <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ number_format($avgOrderValue, 0, ',', '.') }} đ</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Tổng giảm giá</p>
                    <p class="mt-2 text-2xl font-bold text-rose-500">-{{ number_format($totalDiscount, 0, ',', '.') }} đ</p>
                </div>
            </div>

            <div class="mt-6 bg-white rounded-2xl border border-amber-100 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Doanh thu theo ngày</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-amber-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Ngày</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Số đơn</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($dailyRevenue as $day)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-[#1E293B]">{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-[#64748B] text-right">{{ $day->order_count }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-amber-600 text-right">{{ number_format($day->total, 0, ',', '.') }} đ</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-10 text-center text-sm text-[#64748B]">Không có doanh thu trong khoảng thời gian này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white rounded-2xl border border-amber-100 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Danh sách đơn hàng</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-amber-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Mã đơn</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Khách hàng</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Tổng tiền</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Phương thức</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Ngày</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-[#1E293B]">{{ $order->order_code }}</td>
                                        <td class="px-4 py-3 text-sm text-[#64748B]">{{ $order->customer?->full_name ?? $order->recipient_name }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-[#1E293B] text-right">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                        <td class="px-4 py-3 text-sm text-[#64748B]">{{ $order->payment_method }}</td>
                                        <td class="px-4 py-3 text-sm text-[#64748B]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center text-sm text-[#64748B]">Không có đơn hàng nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
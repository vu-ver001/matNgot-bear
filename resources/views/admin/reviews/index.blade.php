<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Quản lý đánh giá</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái hiển thị</label>
                            <select name="is_hidden" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Tất cả</option>
                                <option value="0" @selected(request('is_hidden') === '0')>Đang hiển thị</option>
                                <option value="1" @selected(request('is_hidden') === '1')>Đã ẩn</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số sao</label>
                            <select name="rating" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Tất cả</option>
                                @foreach ([5, 4, 3, 2, 1] as $rating)
                                    <option value="{{ $rating }}" @selected(request('rating') == $rating)>{{ $rating }} sao</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800">Lọc</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sao</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn hàng</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($reviews as $review)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $review->product?->name ?? '—' }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ $review->user?->full_name ?? '—' }}</td>
                                        <td class="px-4 py-4 text-sm text-yellow-500">
                                            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-700 max-w-xs">{{ $review->comment ?? '—' }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-500">{{ $review->order?->order_code ?? '—' }}</td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $review->is_hidden ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $review->is_hidden ? 'Đã ẩn' : 'Đang hiển thị' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium rounded-md {{ $review->is_hidden ? 'text-green-700 bg-green-50 border border-green-200 hover:bg-green-100' : 'text-red-700 bg-red-50 border border-red-200 hover:bg-red-100' }}">
                                                    {{ $review->is_hidden ? 'Hiện lại' : 'Ẩn đánh giá' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Không có đánh giá nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $reviews->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
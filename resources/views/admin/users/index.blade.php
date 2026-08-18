<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Quản lý người dùng</h2>
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
                    <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Tên / Email / SĐT"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò</label>
                            <select name="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Tất cả</option>
                                @foreach (['CUSTOMER' => 'Khách hàng', 'STAFF' => 'Nhân viên', 'ADMIN' => 'Quản trị viên'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Tất cả</option>
                                @foreach (['ACTIVE' => 'Hoạt động', 'BLOCKED' => 'Bị khóa'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Họ tên</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SĐT</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đăng ký</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="px-4 py-4 text-sm text-gray-500">{{ $user->id }}</td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $user->full_name }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ $user->email }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-500">{{ $user->phone ?? '—' }}</td>
                                        <td class="px-4 py-4"><x-role-badge :role="$user->role" /></td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $user->status === 'ACTIVE' ? 'Hoạt động' : 'Bị khóa' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end items-center gap-2">
                                                <form method="POST" action="{{ route('admin.users.updateRole', $user) }}" class="flex items-center gap-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="role" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                                                            @disabled($user->id === auth()->id())>
                                                        @foreach (['CUSTOMER' => 'Customer', 'STAFF' => 'Staff', 'ADMIN' => 'Admin'] as $value => $label)
                                                            <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Đổi</button>
                                                </form>
                                                @if ($user->id !== auth()->id())
                                                    <form method="POST" action="{{ route('admin.users.updateStatus', $user) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ $user->status === 'ACTIVE' ? 'BLOCKED' : 'ACTIVE' }}">
                                                        <button type="submit"
                                                                class="px-2 py-1 text-xs font-medium rounded-md {{ $user->status === 'ACTIVE' ? 'text-red-700 bg-red-50 border border-red-200 hover:bg-red-100' : 'text-green-700 bg-green-50 border border-green-200 hover:bg-green-100' }}">
                                                            {{ $user->status === 'ACTIVE' ? 'Khóa' : 'Mở khóa' }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">Không có người dùng nào phù hợp.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
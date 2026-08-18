<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1E293B] leading-tight">Quản lý người dùng</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                <div class="p-6">
                    <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#64748B] mb-1">Tìm kiếm</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Tên / Email / SĐT"
                                   class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#64748B] mb-1">Vai trò</label>
                            <select name="role" class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                                <option value="">Tất cả</option>
                                @foreach (['CUSTOMER' => 'Khách hàng', 'STAFF' => 'Nhân viên', 'ADMIN' => 'Quản trị viên'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#64748B] mb-1">Trạng thái</label>
                            <select name="status" class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                                <option value="">Tất cả</option>
                                @foreach (['ACTIVE' => 'Hoạt động', 'BLOCKED' => 'Bị khóa'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B]">Lọc</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-amber-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Họ tên</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">SĐT</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Vai trò</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Ngày đăng ký</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="px-4 py-4 text-sm text-[#64748B]">{{ $user->id }}</td>
                                        <td class="px-4 py-4 text-sm font-medium text-[#1E293B]">{{ $user->full_name }}</td>
                                        <td class="px-4 py-4 text-sm text-[#64748B]">{{ $user->email }}</td>
                                        <td class="px-4 py-4 text-sm text-[#64748B]">{{ $user->phone ?? '—' }}</td>
                                        <td class="px-4 py-4"><x-role-badge :role="$user->role" /></td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-rose-100 text-rose-800' }}">
                                                {{ $user->status === 'ACTIVE' ? 'Hoạt động' : 'Bị khóa' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-[#64748B]">{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end items-center gap-2">
                                                <form method="POST" action="{{ route('admin.users.updateRole', $user) }}" class="flex items-center gap-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="role" class="rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-xs"
                                                            @disabled($user->id === auth()->id())>
                                                        @foreach (['CUSTOMER' => 'Customer', 'STAFF' => 'Staff', 'ADMIN' => 'Admin'] as $value => $label)
                                                            <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="px-2 py-1 text-xs font-medium text-[#8B5A2B] bg-amber-100 rounded-full hover:bg-amber-200">Đổi</button>
                                                </form>
                                                @if ($user->id !== auth()->id())
                                                    <form method="POST" action="{{ route('admin.users.updateStatus', $user) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ $user->status === 'ACTIVE' ? 'BLOCKED' : 'ACTIVE' }}">
                                                        <button type="submit"
                                                                class="px-2 py-1 text-xs font-medium rounded-full {{ $user->status === 'ACTIVE' ? 'text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100' : 'text-green-700 bg-green-50 border border-green-200 hover:bg-green-100' }}">
                                                            {{ $user->status === 'ACTIVE' ? 'Khóa' : 'Mở khóa' }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-10 text-center text-sm text-[#64748B]">Không có người dùng nào phù hợp.</td>
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
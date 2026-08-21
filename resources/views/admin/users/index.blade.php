@extends('layouts.admin-dashboard')
@section('page-title', 'Quản lý người dùng')
@section('content')
@php
    $editingUser = old('user_id') ? \App\Models\User::find(old('user_id')) : null;
    $modalOpen = $errors->any() || old('email') !== null;
    $initialForm = [
        'id' => $editingUser->id ?? null,
        'full_name' => old('full_name', $editingUser->full_name ?? ''),
        'email' => old('email', $editingUser->email ?? ''),
        'phone' => old('phone', $editingUser->phone ?? ''),
        'address' => old('address', $editingUser->address ?? ''),
        'role' => old('role', $editingUser->role ?? 'STAFF'),
        'password' => '',
    ];
@endphp

<div class="py-12"
     x-data="{
         modal: @js($modalOpen),
         mode: @js($editingUser ? 'edit' : 'create'),
         editUrl: @js($editingUser ? route('admin.users.update', $editingUser) : ''),
         form: @js($initialForm),
         openCreate() {
             this.mode = 'create';
             this.editUrl = '';
             this.form = { id: null, full_name: '', email: '', phone: '', address: '', role: 'STAFF', password: '' };
             this.modal = true;
         },
         openEdit(user) {
             this.mode = 'edit';
             this.editUrl = user.edit_url;
             this.form = { id: user.id, full_name: user.full_name, email: user.email, phone: user.phone ?? '', address: user.address ?? '', role: user.role, password: '' };
             this.modal = true;
         },
         close() {
             this.modal = false;
         },
     }">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
            <div class="p-6">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-[#1E293B]">Danh sách người dùng</h3>
                    <button type="button" @click="openCreate()"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B]">
                        + Thêm người dùng
                    </button>
                </div>

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
                                            <button type="button" @click="openEdit(@js([
                                                'id' => $user->id,
                                                'full_name' => $user->full_name,
                                                'email' => $user->email,
                                                'phone' => $user->phone,
                                                'address' => $user->address,
                                                'role' => $user->role,
                                                'edit_url' => route('admin.users.update', $user),
                                            ]))"
                                                    class="px-2 py-1 text-xs font-medium text-[#8B5A2B] bg-amber-100 rounded-full hover:bg-amber-200">Sửa</button>
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
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                      onsubmit="return confirm('Xác nhận xóa người dùng {{ $user->full_name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="px-2 py-1 text-xs font-medium text-rose-700 bg-rose-50 border border-rose-200 rounded-full hover:bg-rose-100">Xóa</button>
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

        <div x-show="modal" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/50" @click="close()"></div>
            <div class="relative bg-white rounded-2xl border border-amber-100 shadow-xl w-full max-w-xl my-auto">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-[#1E293B]" x-text="mode === 'create' ? 'Thêm người dùng' : 'Sửa người dùng'"></h3>
                            <button type="button" @click="close()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" :action="mode === 'create' ? '{{ route('admin.users.store') }}' : editUrl">
                            @csrf
                            <input type="hidden" name="user_id" :value="form.id">
                            <input type="hidden" name="_method" value="PUT" x-show="mode === 'edit'" style="display: none;">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-[#64748B] mb-1">Họ tên <span class="text-rose-600">*</span></label>
                                    <input type="text" name="full_name" x-model="form.full_name"
                                           class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                                    @error('full_name')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#64748B] mb-1">Email <span class="text-rose-600">*</span></label>
                                    <input type="email" name="email" x-model="form.email"
                                           class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                                    @error('email')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#64748B] mb-1">Số điện thoại</label>
                                    <input type="text" name="phone" x-model="form.phone"
                                           class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#64748B] mb-1">Địa chỉ</label>
                                    <input type="text" name="address" x-model="form.address"
                                           class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#64748B] mb-1">Vai trò <span class="text-rose-600">*</span></label>
                                    <select name="role" x-model="form.role"
                                            class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm"
                                            :disabled="mode === 'edit' && form.id === {{ auth()->id() }}">
                                        @foreach (['CUSTOMER' => 'Khách hàng', 'STAFF' => 'Nhân viên', 'ADMIN' => 'Quản trị viên'] as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-[#64748B] mt-1" x-show="mode === 'edit' && form.id === {{ auth()->id() }}">
                                        Không thể đổi vai trò tài khoản đang đăng nhập.
                                    </p>
                                    @error('role')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#64748B] mb-1">
                                        Mật khẩu
                                        <span class="text-rose-600" x-show="mode === 'create'">*</span>
                                        <span class="text-[#64748B] font-normal" x-show="mode === 'edit'">(để trống giữ nguyên)</span>
                                    </label>
                                    <input type="password" name="password" x-model="form.password"
                                           class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                                    @error('password')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button" @click="close()"
                                        class="px-4 py-2 text-sm font-medium text-[#64748B] bg-gray-100 rounded-full hover:bg-gray-200">Hủy</button>
                                <button type="submit"
                                        class="px-6 py-2 text-sm font-medium text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B]">Lưu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
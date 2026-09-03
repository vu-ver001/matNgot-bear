@extends('layouts.admin-dashboard')
@section('page-title', 'Quản Lý Người Dùng')
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

<div x-data="{
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

    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-green-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 1. Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Tổng người dùng</div>
                <div class="stat-value">{{ $stats['total'] ?? $users->total() }}</div>
                <div class="stat-subtext text-[#8E8076]">Tất cả tài khoản</div>
            </div>
            <div class="stat-icon brown"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Khách hàng</div>
                <div class="stat-value text-blue-700">{{ $stats['customer'] ?? 0 }}</div>
                <div class="stat-subtext text-blue-600">Khách mua hàng</div>
            </div>
            <div class="stat-icon blue"><i class="fa-solid fa-user"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Nhân viên</div>
                <div class="stat-value text-purple-700">{{ $stats['staff'] ?? 0 }}</div>
                <div class="stat-subtext text-purple-600">Vận hành & CSKH</div>
            </div>
            <div class="stat-icon purple"><i class="fa-solid fa-user-shield"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Quản trị viên</div>
                <div class="stat-value text-amber-700">{{ $stats['admin'] ?? 0 }}</div>
                <div class="stat-subtext text-amber-600">Toàn quyền hệ thống</div>
            </div>
            <div class="stat-icon amber"><i class="fa-solid fa-crown"></i></div>
        </div>
    </div>

    <!-- 2. Main Panel -->
    <div class="panel-card">
        <div class="panel-header">
            <div>
                <div class="panel-title">
                    <i class="fa-solid fa-user-gear"></i>
                    Danh Sách Người Dùng
                </div>
                <div class="panel-subtitle">Quản lý phân quyền, thông tin cá nhân và tài khoản truy cập</div>
            </div>
            <button type="button" @click="openCreate()" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Thêm Người Dùng
            </button>
        </div>

        <!-- Role Filter Pills -->
        @php
            $roleTabs = [
                '' => ['label' => 'Tất cả', 'count' => $stats['total'] ?? null],
                'CUSTOMER' => ['label' => 'Khách hàng', 'count' => $stats['customer'] ?? null],
                'STAFF' => ['label' => 'Nhân viên', 'count' => $stats['staff'] ?? null],
                'ADMIN' => ['label' => 'Quản trị viên', 'count' => $stats['admin'] ?? null],
            ];
        @endphp
        <div class="nav-pills">
            @foreach ($roleTabs as $rKey => $rTab)
                <a href="{{ route('admin.users.index', array_merge(request()->except('role', 'page'), $rKey ? ['role' => $rKey] : [])) }}"
                   class="nav-pill {{ request('role') === $rKey ? 'active' : '' }}">
                    <span>{{ $rTab['label'] }}</span>
                    @if (isset($rTab['count']))
                        <span class="nav-pill-count">{{ $rTab['count'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <!-- Toolbar Filters -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="toolbar-grid">
            @if (request('role'))
                <input type="hidden" name="role" value="{{ request('role') }}">
            @endif

            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Tìm theo tên, email, số điện thoại..."
                       class="input-control">
            </div>

            <div style="min-width: 170px;">
                <select name="status" class="select-control">
                    <option value="">-- Trạng thái: Tất cả --</option>
                    @foreach (['ACTIVE' => 'Hoạt động', 'BLOCKED' => 'Bị khóa'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter text-xs"></i> Lọc
                </button>
                @if (request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline" title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-left text-xs"></i> Đặt lại
                    </a>
                @endif
            </div>
        </form>

        <!-- Users Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Người Dùng</th>
                        <th>Số Điện Thoại</th>
                        <th>Địa Chỉ</th>
                        <th>Vai Trò</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th class="text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="text-xs text-[#8E8076] font-mono">#{{ $user->id }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar-circle">
                                        {{ mb_strtoupper(mb_substr($user->full_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-[#4E342E]">{{ $user->full_name }}</div>
                                        <div class="text-xs text-[#8E8076]">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-[#795548] font-medium">{{ $user->phone ?? '—' }}</td>
                            <td class="text-xs text-[#795548] max-w-[200px] truncate" title="{{ $user->address }}">{{ $user->address ?? '—' }}</td>
                            <td><x-role-badge :role="$user->role" /></td>
                            <td>
                                @if ($user->status === 'ACTIVE')
                                    <span class="badge-pastel green">
                                        <i class="fa-solid fa-circle text-[6px]"></i> Hoạt động
                                    </span>
                                @else
                                    <span class="badge-pastel red">
                                        <i class="fa-solid fa-lock text-[9px]"></i> Bị khóa
                                    </span>
                                @endif
                            </td>
                            <td class="text-xs text-[#795548]">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="text-right">
                                <div class="flex justify-end items-center gap-1.5">
                                    <button type="button" @click="openEdit(@js([
                                        'id' => $user->id,
                                        'full_name' => $user->full_name,
                                        'email' => $user->email,
                                        'phone' => $user->phone,
                                        'address' => $user->address,
                                        'role' => $user->role,
                                        'edit_url' => route('admin.users.update', $user),
                                    ]))" class="btn-icon edit" title="Sửa thông tin">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.updateStatus', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $user->status === 'ACTIVE' ? 'BLOCKED' : 'ACTIVE' }}">
                                            <button type="submit" class="btn-icon {{ $user->status === 'ACTIVE' ? 'delete' : 'view' }}"
                                                    title="{{ $user->status === 'ACTIVE' ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                                <i class="fa-solid {{ $user->status === 'ACTIVE' ? 'fa-lock' : 'fa-lock-open' }} text-xs"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('Xác nhận xóa tài khoản: {{ $user->full_name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon delete" title="Xóa tài khoản">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-[#8E8076]">
                                <i class="fa-solid fa-users-slash text-3xl text-amber-300 mb-2 block"></i>
                                Không tìm thấy tài khoản người dùng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="mt-4">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- 3. Modal Thêm / Sửa Người Dùng -->
    <div x-show="modal" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-[#4E342E]/40 backdrop-blur-xs" @click="close()"></div>
        <div class="relative bg-white rounded-3xl border border-[#EADFCF] shadow-[0_20px_50px_rgba(109,76,65,0.25)] w-full max-w-xl my-auto z-10 overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-[#EADFCF] bg-[#FAF6F0] flex items-center justify-between">
                <div class="font-extrabold text-lg text-[#4E342E] flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-amber-600"></i>
                    <span x-text="mode === 'create' ? 'Thêm Người Dùng Mới' : 'Cập Nhật Người Dùng'"></span>
                </div>
                <button type="button" @click="close()" class="btn-icon btn-sm" aria-label="Đóng">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6">
                <form method="POST" :action="mode === 'create' ? '{{ route('admin.users.store') }}' : editUrl">
                    @csrf
                    <input type="hidden" name="user_id" :value="form.id">
                    <input type="hidden" name="_method" value="PUT" x-show="mode === 'edit'" style="display: none;">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#795548] uppercase mb-1">Họ tên <span class="text-rose-600">*</span></label>
                            <input type="text" name="full_name" x-model="form.full_name" placeholder="Nguyễn Văn A" class="input-control">
                            @error('full_name')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#795548] uppercase mb-1">Email <span class="text-rose-600">*</span></label>
                            <input type="email" name="email" x-model="form.email" placeholder="example@email.com" class="input-control">
                            @error('email')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#795548] uppercase mb-1">Số điện thoại</label>
                            <input type="text" name="phone" x-model="form.phone" placeholder="0912345678" class="input-control">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#795548] uppercase mb-1">Địa chỉ</label>
                            <input type="text" name="address" x-model="form.address" placeholder="Hà Nội..." class="input-control">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#795548] uppercase mb-1">Vai trò <span class="text-rose-600">*</span></label>
                            <select name="role" x-model="form.role" class="select-control"
                                    :disabled="mode === 'edit' && form.id === {{ auth()->id() }}">
                                @foreach (['CUSTOMER' => 'Khách hàng', 'STAFF' => 'Nhân viên', 'ADMIN' => 'Quản trị viên'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-[#8E8076] mt-1" x-show="mode === 'edit' && form.id === {{ auth()->id() }}">
                                Không thể đổi vai trò của tài khoản đang đăng nhập.
                            </p>
                            @error('role')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#795548] uppercase mb-1">
                                Mật khẩu
                                <span class="text-rose-600" x-show="mode === 'create'">*</span>
                                <span class="text-[#8E8076] font-normal" x-show="mode === 'edit'">(để trống giữ nguyên)</span>
                            </label>
                            <input type="password" name="password" x-model="form.password" placeholder="••••••••" class="input-control">
                            @error('password')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-[#EADFCF] flex justify-end gap-2.5">
                        <button type="button" @click="close()" class="btn btn-outline">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Lưu Thông Tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request)
    {
        $users = $this->userService->list($request->only('role', 'status', 'search'));

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:CUSTOMER,STAFF',
        ]);

        $this->userService->create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Tạo người dùng thành công.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:CUSTOMER,STAFF,ADMIN',
        ]);

        $this->userService->update($user, $validated);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật người dùng thành công.');
    }

    public function destroy(User $user)
    {
        $error = $this->userService->delete($user);

        return $error ?? redirect()->route('admin.users.index')->with('success', 'Xóa người dùng thành công.');
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => 'required|in:ACTIVE,BLOCKED',
        ]);

        $error = $this->userService->toggleStatus($user);

        return $error ?? redirect()->back()->with('success', 'Cập nhật trạng thái người dùng thành công.');
    }
}

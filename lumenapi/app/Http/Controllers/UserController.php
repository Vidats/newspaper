<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Chèn Facade Hash để mã hóa mật khẩu

class UserController extends Controller
{
    // Lấy danh sách Users
    public function index()
    {
        // Bạn có thể dùng User::all() hoặc paginate() để phân trang nếu dữ liệu lớn
        $users = User::all();
        return response()->json($users, 200);
    }

    // Xem thông tin 1 User
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        return response()->json($user, 200);
    }

    // Tạo mới User
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // email phải là duy nhất
            'password' => 'required|string|min:6', // Mật khẩu tối thiểu 6 ký tự
            'role' => 'sometimes|string'
        ]);

        // Tạo user mới và mã hóa mật khẩu
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')), // BẮT BUỘC MÃ HÓA
            'role' => $request->input('role', 'user'), // Nếu không truyền role, mặc định là 'user'
        ]);

        return response()->json([
            'message' => 'Tạo người dùng thành công',
            'data' => $user // Thuộc tính password sẽ tự động bị ẩn do bạn đã set $hidden trong model
        ], 201);
    }

    // Cập nhật thông tin User
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        // Validate dữ liệu
        $this->validate($request, [
            'name' => 'sometimes|required|string|max:255',
            // Kiểm tra email duy nhất nhưng BỎ QUA email của chính user đang được update
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
            'role' => 'sometimes|string'
        ]);

        // Lấy dữ liệu gửi lên
        $dataToUpdate = $request->only(['name', 'email', 'role']);

        // Nếu người dùng có gửi password mới lên thì mới tiến hành mã hóa và cập nhật
        if ($request->has('password')) {
            $dataToUpdate['password'] = Hash::make($request->input('password'));
        }

        $user->update($dataToUpdate);

        return response()->json([
            'message' => 'Cập nhật thành công',
            'data' => $user
        ], 200);
    }

    // Xóa User
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Xóa người dùng thành công'], 200);
    }
}

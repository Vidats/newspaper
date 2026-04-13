<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // API Đăng ký
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user' // Mặc định ai đăng ký cũng là user thường
        ]);

        return response()->json(['message' => 'Đăng ký thành công'], 201);
    }

    // API Đăng nhập
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        // Auth::attempt sẽ kiểm tra email/pass. Nếu đúng, nó trả về 1 chuỗi Token.
        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Email hoặc mật khẩu không chính xác'], 401);
        }

        return response()->json([
            'token' => $token,
            'user' => auth()->user()
        ], 200);
    }

    // API Lấy thông tin user đang đăng nhập (phải có Token mới gọi được)
    public function me()
    {
        return response()->json(auth()->user());
    }

    // API Đăng xuất (Vô hiệu hóa Token)
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Đã đăng xuất thành công']);
    }
}

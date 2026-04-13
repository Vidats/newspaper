<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdmin
{
    public function handle($request, Closure $next)
    {
        // 1. Lấy thông tin user từ Token (do auth middleware đã giải mã trước đó)
        $user = $request->user();

        // 2. Kiểm tra xem user có tồn tại và cột role có phải là 'admin' không
        if ($user && $user->role === 'admin') {
            // Đủ thẩm quyền -> Cho phép đi tiếp vào Controller
            return $next($request);
        }

        // Không đủ thẩm quyền -> Đuổi về với lỗi 403 Forbidden
        return response()->json([
            'status' => 'error',
            'message' => 'Truy cập bị từ chối! Bạn không phải là Quản trị viên.'
        ], 403);
    }
}

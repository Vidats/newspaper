<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Xác thực người dùng thông qua mã JWT Token
        $this->app['auth']->viaRequest('api', function ($request) {
            try {
                // Kiểm tra xem header Authorization có Token hợp lệ không
                if ($user = \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate()) {
                    return $user;
                }
            } catch (\Exception $e) {
                // Nếu Token lỗi hoặc không có, trả về null (không gây ra lỗi 401 ngay lập tức)
                return null;
            }
            return null;
        });
    }
}

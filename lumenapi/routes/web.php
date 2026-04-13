<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Nhóm các route API chính
$router->group(['prefix' => 'api'], function () use ($router) {

    /* =========================================================
       1. KHU VỰC PUBLIC (Ai cũng có thể truy cập, không cần Token)
       ========================================================= */
    // Xác thực
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    // Menu (Chỉ được xem)
    $router->get('menus', 'MenuController@index');
    $router->get('menus/{id}', 'MenuController@show');
    $router->get('menus/{id}/posts', 'MenuController@getPost');

    // Media (Chỉ được xem thông tin file)
    $router->get('media', 'MediaController@index');
    $router->get('media/{id}', 'MediaController@show');

    // Bài viết (Chỉ được xem)
    $router->get('posts', 'PostController@index');
    $router->get('posts/{id}', 'PostController@show');
    $router->get('posts/menu/{id}', 'PostController@getByMenu');

    /* =========================================================
       2. KHU VỰC CHỈ CẦN ĐĂNG NHẬP (Bảo vệ bởi middleware 'auth')
       ========================================================= */
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('me', 'AuthController@me');
        $router->post('logout', 'AuthController@logout');
    });

    /* =========================================================
       3. KHU VỰC CẤM ĐỊA (Bắt buộc Đăng nhập + Phải là Admin)
       ========================================================= */
    $router->group(['middleware' => ['auth', 'admin']], function () use ($router) {

        // Quản lý Users (Admin mới được xem danh sách và thao tác)
        $router->get('users', 'UserController@index');
        $router->get('users/{id}', 'UserController@show');
        $router->post('users', 'UserController@store');
        $router->put('users/{id}', 'UserController@update');
        $router->delete('users/{id}', 'UserController@destroy');

        // Quản lý Menu (Thêm, Sửa, Xóa)
        $router->post('menus', 'MenuController@store');
        $router->put('menus/{id}', 'MenuController@update');
        $router->delete('menus/{id}', 'MenuController@destroy');

        // Quản lý Media (Thêm, Sửa, Xóa)
        $router->post('media', 'MediaController@store');
        $router->put('media/{id}', 'MediaController@update');
        $router->delete('media/{id}', 'MediaController@destroy');

        // Quản lý Posts (Thêm, Sửa, Xóa)
        $router->post('posts', 'PostController@store');
        $router->put('posts/{id}', 'PostController@update');
        $router->delete('posts/{id}', 'PostController@destroy');
    });

});

/*
|--------------------------------------------------------------------------
| Route Mở Khóa Hình Ảnh (Bạn đã làm ở bài Media)
|--------------------------------------------------------------------------
*/
$router->get('storage/{folder}/{filename}', function ($folder, $filename) {
    $path = storage_path('app/public/' . $folder . '/' . $filename);

    if (!file_exists($path)) {
        return response()->json(['message' => 'Không tìm thấy file'], 404);
    }

    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return response($file, 200)->header('Content-Type', $type);
});

<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // Lấy danh sách Menu
    public function index()
    {
        $menus = Menu::all();
        return response()->json($menus, 200);
    }

    // Lấy thông tin một Menu cụ thể
    public function show($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Menu không tồn tại'], 404);
        }

        return response()->json($menu, 200);
    }

    // Tạo mới Menu
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $menu = Menu::create($request->all());

        return response()->json($menu, 201);
    }

    // Cập nhật Menu
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Menu không tồn tại'], 404);
        }

        $this->validate($request, [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $menu->update($request->all());

        return response()->json($menu, 200);
    }

    // Xóa Menu
    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Menu không tồn tại'], 404);
        }

        $menu->delete();

        return response()->json(['message' => 'Xóa menu thành công'], 200);
    }


}

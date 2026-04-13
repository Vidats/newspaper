<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Import class Str để hỗ trợ tạo slug

class PostController extends Controller
{
    // Lấy danh sách bài viết (kèm theo thông tin User, Menu, Media)
    public function index()
    {
        // Sử dụng with() để lấy dữ liệu từ các bảng liên quan, tránh lỗi N+1 Query
        $posts = Post::with(['user', 'menu', 'media'])->latest()->get();

        return response()->json($posts, 200);
    }

    // Xem thông tin 1 bài viết chi tiết (Hỗ trợ tìm qua ID hoặc Slug)
    public function show($id)
    {
        // 1. Thử tìm theo Slug trước (vì đa phần Frontend giờ gửi lên Slug)
        $post = Post::with(['user', 'menu', 'media'])->where('slug', $id)->first();

        // 2. Nếu không thấy bài viết theo Slug, thử tìm theo ID (nếu giá trị gửi lên là số)
        if (!$post && is_numeric($id)) {
            $post = Post::with(['user', 'menu', 'media'])->find($id);
        }

        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        return response()->json($post, 200);
    }
    // Tạo mới bài viết
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string',
            'slug' => 'nullable|string|unique:posts', // Nếu tự nhập slug thì không được trùng
            // Đảm bảo các ID truyền vào phải tồn tại trong các bảng tương ứng
            'menu_id' => 'required|integer|exists:menus,id',
            'media_id' => 'nullable|integer|exists:media,id',
        ]);
        $data = $request->all();
        // Lấy ID của người dùng đang đăng nhập
        $data['user_id'] = $request->user()->id;

        // Nếu client không gửi lên slug, ta sẽ tự động tạo slug từ title
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']) . '-' . time(); // Thêm time() để đảm bảo tính duy nhất
        }

        $post = Post::create($data);

        return response()->json([
            'message' => 'Tạo bài viết thành công',
            // Load luôn dữ liệu các bảng liên quan để trả về ngay sau khi tạo
            'data' => $post->load(['user', 'menu', 'media'])
        ], 201);
    }

    // Cập nhật bài viết
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        $this->validate($request, [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'summary' => 'nullable|string',
            'slug' => 'sometimes|required|string|unique:posts,slug,' . $id, // Trừ slug của chính bài này ra
            'menu_id' => 'sometimes|required|integer|exists:menus,id',
            'media_id' => 'nullable|integer|exists:media,id',
        ]);

        $data = $request->all();

        // Nếu title bị thay đổi và client không gửi slug mới, có thể bạn muốn update lại slug
        if ($request->has('title') && empty($data['slug'])) {
             $data['slug'] = Str::slug($data['title']) . '-' . time();
        }

        $post->update($data);

        return response()->json([
            'message' => 'Cập nhật thành công',
            'data' => $post->load(['user', 'menu', 'media'])
        ], 200);
    }

    // Xóa bài viết
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Xóa bài viết thành công'], 200);
    }

    public function getByMenu($id){
        $posts=Post::where('menu_id', $id)->with(['user','menu','media'])->latest()->get();
return response()->json($posts);
    }
}

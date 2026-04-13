<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Media::latest()->get());

    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $this->validate($request,[
            'file'=>'required|file|max:10240',
            'name'=>'required|string|max:255',
        ]);
        $file = $request->file('file');
        $path = $file->store('media','public');

        $media =Media::create([
            'name'=>$validated['name']?? $file->getClientOriginalName(), // Nếu không có tên riêng, dùng tên gốc của file
            'path'=>$path,
            'type'=>$file->getClientMimeType(),// Lấy loại MIME của file
            'size'=>$file->getSize(), // Lấy kích thước của file
            ]);

            return response()->json([
                'status'=>'success',
                'message'=>'File da duoc tai len thanh cong',
                'data'=>$media
            ],201);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function show(Media $media)
    {
        return response()->json($media);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $media = Media::findOrFail($id);

        $validated = $this->validate($request,[
            'file'=>'nullable|file|max:10240',
            'name'=>'required|string|max:255',
        ]);
        if($request->hasFile('file')){
            // Xóa file cũ nếu có
            if(!empty($media->path) && Storage::disk('public')->exists($media->path)){
                Storage::disk('public')->delete($media->path);
            }
            // Tải file mới lên
            $file = $request->file('file');
            $path = $file->store('media','public');

            // Cập nhật thông tin media
            $media->path = $path;
            $media->type = $file->getClientMimeType();
            $media->size = $file->getSize();
            $media->name = $validated['name'] ?? $file->getClientOriginalName();
        }
         else {
            // Chỉ cập nhật tên nếu không có file mới
            $media->name = $validated['name'] ?? $media->name;
        }


        $media->save();


        return response()->json([
            'status'=>'success',
            'message'=>'Media đã được cập nhật thành công',
            'data'=>$media
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)

    {
        $media =Media::findOrFail($id);
        // Xóa file khỏi storage nếu tồn tại
        if(!empty($media->path)&& storage::disk('public')->exists($media->path)){
            storage::disk('public')->delete($media->path);
        }
        $media->delete();
        return response()->json([
            'status'=>'success',
            'message'=>'Media đã được xóa thành công',
        ]);
    }
}

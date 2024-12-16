<?php

namespace App\Http\Controllers\Upload;

use App\Services\FirebaseService;
use App\Http\Response\ApiResponse;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    private $upload;
    public function __construct(FirebaseService $firebaseService)
    {
        $this->upload = $firebaseService;
    }
    //user
    public function cv()
    {
        $validate = $this->validator(request()->file(), ['cv' => 'required|file|mimes:pdf|max:10240']);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $cv_file = $validate['cv'];
        $cv = $this->upload->uploadFile($cv_file, 'cv/taskee/' . auth()->guard()->id(), auth()->guard()->user()->username . '.pdf');
        $link = $this->upload->sign($cv);
        return ApiResponse::OK([
            'path' => $cv,
            'link' => $link
        ], 'successfully uploaded');
    }
    public function image()
    {
        $validate = $this->validator(request()->file(), ['image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240']);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $file = $validate['image'];
        $image = $this->upload->uploadFile($file, 'images/' . auth()->guard()->user()->role . "/" . auth()->guard()->id());
        $link = $this->upload->sign($image);
        return ApiResponse::OK([
            'path' => $image,
            'link' => $link
        ], 'successfully uploaded');
    }
    //challenge
    public function imageChallenge()
    {
        $validate = $this->validator(request()->file(), ['image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240']);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $file = $validate['image'];
        $image = $this->upload->uploadFile($file, 'challenges/' . auth()->guard()->id());
        $link = $this->upload->sign($image);
        return ApiResponse::OK([
            'path' => $image,
            'link' => $link
        ], 'successfully uploaded');
    }
    public function sourceChallenge()
    {
        $validate = $this->validator(request()->file(), ['source' => 'required|file|mimes:zip|max:10240']);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $source_file = $validate['source'];
        $source = $this->upload->uploadFile($source_file, 'challenges/' . auth()->guard()->id(), time() . '-source.zip');
        return ApiResponse::OK(['path' => $source], 'Successfully uploaded');
    }
    public function figmaChallenge()
    {
        $validate = $this->validator(request()->file(), ['figma' => 'required|file|mimes:zip|max:10240']);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $figma_file = $validate['figma'];
        $figma = $this->upload->uploadFile($figma_file, 'challenges/' . auth()->guard()->id(), time() . '-figma.zip');
        return ApiResponse::OK(['path' => $figma], 'Successfully uploaded');
    }
    //task
    public function imageTask()
    {
        $validate = $this->validator(request()->file(), ['image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240']);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $file = $validate['image'];
        $image = $this->upload->uploadFile($file, 'tasks/' . auth()->guard()->id());
        $link = $this->upload->sign($image);
        return ApiResponse::OK([
            'path' => $image,
            'link' => $link
        ], 'successfully uploaded');
    }
    public function sourceTask()
    {
        $validate = $this->validator(request()->file(), ['source' => 'required|file|mimes:zip|max:10240']);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $source_file = $validate['source'];
        $source = $this->upload->uploadFile($source_file, 'tasks/' . auth()->guard()->id(), time() . '-source.zip');
        return ApiResponse::OK(['path' => $source], 'Successfully uploaded');
    }
    public function figmaTask()
    {
        $validate = $this->validator(request()->file(), ['figma' => 'required|file|mimes:zip|max:10240']);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $figma_file = $validate['figma'];
        $figma = $this->upload->uploadFile($figma_file, 'tasks/' . auth()->guard()->id(), time() . '-figma.zip');
        return ApiResponse::OK(['path' => $figma], 'Successfully uploaded');
    }
    public function remove()
    {
        $validate = $this->validator(request()->post(), ['path' => 'required|array']);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $paths = $validate['path'];
        foreach ($paths as $path) {
            $this->upload->delete($path);
        }
        return ApiResponse::OK();
    }
}

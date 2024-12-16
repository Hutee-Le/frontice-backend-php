<?php

namespace App\Http\Controllers;

use App\Http\Response\ApiResponse;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // Tạo mới Service
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:services,name',
            'type' => 'required|in:monthly,3-monthly,6-monthly,yearly',
            'price' => 'required|integer|min:0',
        ]);

        $service = Service::create($data);

        return response()->json($service, 201);
    }

    // Lấy danh sách Service
    public function index()
    {
        $data['services'][] = Service::all();
        return ApiResponse::OK($data);
    }

    // Lấy chi tiết một Service
    public function show($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service);
    }

    // Cập nhật Service
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $data = $request->validate([
            'price' => 'integer|min:0',
        ]);

        $service->update($data);

        return ApiResponse::OK($data);
    }

    // Xóa Service
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service deleted']);
    }
}

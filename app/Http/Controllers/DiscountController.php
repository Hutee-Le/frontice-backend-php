<?php

namespace App\Http\Controllers;

use App\Http\Response\ApiResponse;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscountController extends Controller
{
    // Tạo mới Discount
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:discounts',
            'usage_limit' => 'required|integer|min:0',
            'value' => 'required|integer|min:0|max:50',
            'expired' => 'required|date',
        ]);

        $data['id'] = Str::uuid();
        $discount = Discount::create($data);

        return ApiResponse::OK($discount, 'created successfully');
    }

    // Lấy danh sách Discount
    public function index()
    {
        $data['discount'] = [];
        $discounts = Discount::latest()->paginate(request()->query('per_page') ?? 10);
        foreach ($discounts as $discount) {
            $data['discount'][] = $discount;
        }
        $data['total'] = $discounts->total();
        $data['currentPage'] = $discounts->currentPage();
        $data['lastPage'] = $discounts->lastPage();
        $data['perPage'] = $discounts->perPage();
        return ApiResponse::OK($data);
    }

    // Lấy chi tiết một Discount
    public function show($id)
    {
        $discount = Discount::findOrFail($id);
        return ApiResponse::OK($discount);
    }

    // Cập nhật Discount
    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        $data = $request->validate([
            'code' => 'nullable|string|max:50|unique:discounts,code,' . $id,
            'usage_limit' => 'nullable|integer',
            'value' => 'nullable|integer|min:0|max:50',
            'expired' => 'nullable|date',
        ]);

        $discount->update(array_filter($data));

        return ApiResponse::OK($discount);
    }

    // Xóa Discount
    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();

        return ApiResponse::OK(['message' => 'Discount deleted']);
    }
    public function isUsable()
    {
        $discount = Discount::where('code', request()->query('code'))->first();
        if (!$discount) {
            return ApiResponse::NOT_FOUND('Discount not found');
        }
        return ApiResponse::OK(['result' => $discount->usable()]);
    }
}

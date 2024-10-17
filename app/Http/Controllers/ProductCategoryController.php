<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Http\Requests\Product\CreateProductCategoryRequest;
use App\Http\Requests\Product\UpdateProductCategoryRequest;
use App\Services\ProductCategoryService;

class ProductCategoryController extends Controller
{
    public function index()
    {
        if (!$limit = request()->limit) {
            $limit = 10;
        }
        if (!$search = request()->search) {
            $search = null;
        }
        [$proceed, $message, $data] = (new ProductCategoryService())->listProductCategory($limit, $search);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function create(CreateProductCategoryRequest $request)
    {
        [$proceed, $message, $data] = (new ProductCategoryService())->createProductCategory($request->all());
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function update(UpdateProductCategoryRequest $request, $id)
    {
        [$proceed, $message, $data] = (new ProductCategoryService())->updateProductCategory($request->all(), $id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function show($id)
    {
        [$proceed, $message, $data] = (new ProductCategoryService())->getDetailProductCategory($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function delete($id)
    {
        [$proceed, $message, $data] = (new ProductCategoryService())->deleteProductCategory($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}

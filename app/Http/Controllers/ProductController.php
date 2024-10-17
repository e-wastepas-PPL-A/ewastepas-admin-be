<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\UpdateStatusProductRequest;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function index()
    {
        if (!$limit = request()->limit) {
            $limit = 10;
        }
        if (!$search = request()->search) {
            $search = null;
        }
        if (!$filter = request()->filter) {
            $filter = null;
        }

        [$proceed, $message, $data] = (new ProductService())->listProductWithPagination($limit, $search, $filter);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function create(CreateProductRequest $request)
    {
        [$proceed, $message, $data] = (new ProductService())->createProduct($request->all());

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function update(UpdateProductRequest $request, $id)
    {
        [$proceed, $message, $data] = (new ProductService())->updateProduct($request->all(), $id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function updateStatus(UpdateStatusProductRequest $request, $id)
    {
        [$proceed, $message, $data] = (new ProductService())->updateStatusProduct($request->all(), $id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function show($id)
    {
        [$proceed, $message, $data] = (new ProductService())->detailProduk($id);

        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
    public function delete($id)
    {
        [$proceed, $message, $data] = (new ProductService())->deleteProduct($id);
        if (!$proceed) {
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }
}

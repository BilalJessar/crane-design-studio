<?php

namespace App\Http\Controllers;

use App\Http\Requests\CraneDesignRequest;
use App\Http\Resources\CraneDesignResource;
use App\Models\CraneDesign;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CraneDesignController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CraneDesignResource::collection(
            CraneDesign::latest('updated_at')->orderByDesc('id')->get()
        );
    }

    public function store(CraneDesignRequest $request): CraneDesignResource
    {
        return new CraneDesignResource(CraneDesign::create($request->validated()));
    }

    public function show(CraneDesign $design): CraneDesignResource
    {
        return new CraneDesignResource($design);
    }

    public function update(CraneDesignRequest $request, CraneDesign $design): CraneDesignResource
    {
        $design->update($request->validated());

        return new CraneDesignResource($design);
    }

    public function destroy(CraneDesign $design): Response
    {
        $design->delete();

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Permalink;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * قائمة الخدمات المفعّلة.
     */
    public function index(Request $request): JsonResponse
    {
        $services = Service::query()
            ->with('permalinks')
            ->where('is_active', true)
            ->search($request->string('search')->toString())
            ->latest('id')
            ->paginate(
                min($request->integer('per_page', 12), 50)
            )
            ->withQueryString();

        return ServiceResource::collection($services)
            ->response();
    }

    /**
     * تفاصيل خدمة واحدة بواسطة الرابط الدائم (عربي أو إنجليزي).
     */
    public function show(string $slug): JsonResponse
    {
        $permalink = Permalink::query()
            ->with('linkable')
            ->where('slug', $slug)
            ->whereHasMorph(
                'linkable',
                [Service::class],
                fn ($query) => $query->where('is_active', true)
            )
            ->first();

        abort_unless($permalink, 404);

        $service = $permalink->linkable;

        $service->loadMissing('permalinks');

        return (new ServiceResource($service))
            ->response();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkController extends Controller
{
    /**
     * عرض جميع الأعمال في الواجهة العامة.
     */
    public function index(Request $request): View
    {
        $works = Work::query()
            ->with([
                'service:id,title_ar,title_en',
                'technologies:id,name,icon',
            ])
            ->where('is_active', true)
            ->when(
                $request->filled('search'),
                function ($query) use ($request): void {
                    $search = trim(
                        $request->string('search')->toString()
                    );

                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere(
                                    'short_description',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'client_name',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where(
                    'type',
                    $request->string('type')->toString()
                )
            )
            ->when(
                $request->filled('service'),
                fn ($query) => $query->where(
                    'service_id',
                    $request->integer('service')
                )
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $services = Service::query()
            ->active()
            ->ordered()
            ->get([
                'id',
                'title_ar',
                'title_en',
            ]);

        $types = $this->workTypes();

        return view(
            'works.index',
            compact(
                'works',
                'services',
                'types'
            )
        );
    }

    /**
     * عرض تفاصيل عمل واحد.
     */
    public function show(Work $work): View
    {
        abort_unless($work->is_active, 404);

        $work->load([
            'service:id,title_ar,title_en',
            'technologies:id,name,icon',
            'images',
        ]);

        $relatedWorks = Work::query()
            ->where('is_active', true)
            ->whereKeyNot($work->id)
            ->when(
                $work->service_id,
                fn ($query) => $query->where(
                    'service_id',
                    $work->service_id
                )
            )
            ->orderByDesc('is_featured')
            ->latest('id')
            ->limit(3)
            ->get();

        $types = $this->workTypes();

        return view(
            'works.show',
            compact(
                'work',
                'relatedWorks',
                'types'
            )
        );
    }

    /**
     * أنواع الأعمال المتاحة.
     */
    private function workTypes(): array
    {
        return [
            'website' => 'موقع إلكتروني',
            'application' => 'تطبيق',
            'design' => 'تصميم',
            'advertisement' => 'إعلان',
            'video' => 'فيديو ومونتاج',
            'photography' => 'تصوير',
            'event' => 'فعالية أو مشاركة',
            'personal' => 'عمل شخصي',
            'other' => 'أعمال أخرى',
        ];
    }
}

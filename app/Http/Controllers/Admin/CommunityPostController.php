<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunityPostController extends Controller
{
    /**
     * أنواع منشورات المجتمع المتاحة.
     */
    private const TYPES = [
        'post',
        'discussion',
        'event',
        'announcement',
    ];

    /**
     * عرض قائمة منشورات المجتمع.
     */
    public function index(Request $request): View
    {
        $search = trim(
            $request->string('search')->toString()
        );

        $posts = CommunityPost::query()
            ->with('category')

            // البحث في العنوان والوصف والمحتوى
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere(
                            'short_description',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'content',
                            'like',
                            "%{$search}%"
                        );
                });
            })

            // الفلترة حسب التصنيف
            ->when(
                $request->filled('category'),
                fn ($query) => $query->where(
                    'community_category_id',
                    $request->integer('category')
                )
            )

            // الفلترة حسب نوع المنشور
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where(
                    'type',
                    $request->string('type')->toString()
                )
            )

            // الفلترة حسب حالة المنشور
            ->when(
                $request->filled('status'),
                function ($query) use ($request) {
                    match ($request->string('status')->toString()) {
                        'active' => $query->where('is_active', true),
                        'inactive' => $query->where('is_active', false),
                        'featured' => $query->where('is_featured', true),
                        'scheduled' => $query
                            ->where('is_active', true)
                            ->whereNotNull('published_at')
                            ->where('published_at', '>', now()),
                        default => null,
                    };
                }
            )

            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = CommunityCategory::query()
            ->orderBy('name')
            ->get();

        $types = $this->types();

        return view(
            'admin.community.index',
            compact(
                'posts',
                'categories',
                'types'
            )
        );
    }

    /**
     * عرض نموذج إنشاء منشور جديد.
     */
    public function create(): View
    {
        $categories = CommunityCategory::query()
            ->orderBy('name')
            ->get();

        $types = $this->types();

        return view(
            'admin.community.create',
            compact(
                'categories',
                'types'
            )
        );
    }

    /**
     * حفظ منشور جديد.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCommunityPost($request);

        $data = $this->prepareCommunityPostData(
            request: $request,
            validated: $validated
        );

        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store('community', 'public');
        }

        CommunityPost::create($data);

        return redirect()
            ->route('admin.community.index')
            ->with(
                'success',
                'تم إنشاء منشور المجتمع بنجاح.'
            );
    }

    /**
     * تحويل صفحة العرض إلى صفحة التعديل.
     */
    public function show(
        CommunityPost $community
    ): RedirectResponse {
        return redirect()->route(
            'admin.community.edit',
            $community
        );
    }

    /**
     * عرض نموذج تعديل المنشور.
     */
    public function edit(
        CommunityPost $community
    ): View {
        $community->load('category');

        $categories = CommunityCategory::query()
            ->orderBy('name')
            ->get();

        $types = $this->types();

        return view(
            'admin.community.edit',
            [
                'communityPost' => $community,
                'categories' => $categories,
                'types' => $types,
            ]
        );
    }

    /**
     * تحديث بيانات المنشور.
     */
    public function update(
        Request $request,
        CommunityPost $community
    ): RedirectResponse {
        $validated = $this->validateCommunityPost(
            request: $request,
            communityPost: $community
        );

        $data = $this->prepareCommunityPostData(
            request: $request,
            validated: $validated,
            communityPost: $community
        );

        /*
         * حذف الصورة الحالية عند اختيار المستخدم ذلك،
         * ما لم يتم رفع صورة جديدة.
         */
        if (
            $request->boolean('remove_image')
            && ! $request->hasFile('image')
        ) {
            $this->deleteImage($community->image);

            $data['image'] = null;
        }

        /*
         * استبدال الصورة القديمة بصورة جديدة.
         */
        if ($request->hasFile('image')) {
            $oldImage = $community->image;

            $data['image'] = $request
                ->file('image')
                ->store('community', 'public');

            $this->deleteImage($oldImage);
        }

        $community->update($data);

        return redirect()
            ->route('admin.community.index')
            ->with(
                'success',
                'تم تحديث منشور المجتمع بنجاح.'
            );
    }

    /**
     * حذف منشور المجتمع.
     */
    public function destroy(
        CommunityPost $community
    ): RedirectResponse {
        $image = $community->image;

        $community->delete();

        $this->deleteImage($image);

        return redirect()
            ->route('admin.community.index')
            ->with(
                'success',
                'تم حذف منشور المجتمع بنجاح.'
            );
    }

    /**
     * التحقق من بيانات المنشور.
     */
    private function validateCommunityPost(
        Request $request,
        ?CommunityPost $communityPost = null
    ): array {
        return $request->validate(
            [
                'community_category_id' => [
                    'nullable',
                    'integer',
                    'exists:community_categories,id',
                ],

                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique(
                        'community_posts',
                        'slug'
                    )->ignore($communityPost?->id),
                ],

                'short_description' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'content' => [
                    'required',
                    'string',
                    'min:10',
                ],

                'type' => [
                    'required',
                    Rule::in(self::TYPES),
                ],

                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:4096',
                ],

                'published_at' => [
                    'nullable',
                    'date',
                ],

                'event_start_at' => [
                    'nullable',
                    'date',
                    'required_if:type,event',
                ],

                'event_end_at' => [
                    'nullable',
                    'date',
                    'after_or_equal:event_start_at',
                ],

                'location' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'registration_url' => [
                    'nullable',
                    'url',
                    'max:2048',
                ],

                'is_featured' => [
                    'nullable',
                    'boolean',
                ],

                'is_active' => [
                    'nullable',
                    'boolean',
                ],

                'remove_image' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'community_category_id.exists' => 'التصنيف المحدد غير موجود.',

                'title.required' => 'عنوان المنشور مطلوب.',

                'title.max' => 'عنوان المنشور يجب ألا يتجاوز 255 حرفًا.',

                'slug.unique' => 'الرابط المختصر مستخدم في منشور آخر.',

                'slug.max' => 'الرابط المختصر يجب ألا يتجاوز 255 حرفًا.',

                'short_description.max' => 'الوصف المختصر يجب ألا يتجاوز 500 حرف.',

                'content.required' => 'محتوى المنشور مطلوب.',

                'content.min' => 'محتوى المنشور يجب ألا يقل عن 10 أحرف.',

                'type.required' => 'نوع المنشور مطلوب.',

                'type.in' => 'نوع المنشور المحدد غير صحيح.',

                'image.image' => 'الملف المحدد يجب أن يكون صورة.',

                'image.mimes' => 'صيغة الصورة يجب أن تكون JPG أو JPEG أو PNG أو WEBP.',

                'image.max' => 'حجم الصورة يجب ألا يتجاوز 4 ميجابايت.',

                'published_at.date' => 'تاريخ النشر غير صحيح.',

                'event_start_at.required_if' => 'تاريخ بداية الفعالية مطلوب عند اختيار نوع فعالية.',

                'event_start_at.date' => 'تاريخ بداية الفعالية غير صحيح.',

                'event_end_at.date' => 'تاريخ نهاية الفعالية غير صحيح.',

                'event_end_at.after_or_equal' => 'تاريخ نهاية الفعالية يجب أن يكون بعد تاريخ البداية أو مساويًا له.',

                'location.max' => 'اسم الموقع يجب ألا يتجاوز 255 حرفًا.',

                'registration_url.url' => 'رابط التسجيل غير صحيح.',

                'registration_url.max' => 'رابط التسجيل طويل جدًا.',
            ]
        );
    }

    /**
     * تجهيز البيانات قبل الحفظ أو التحديث.
     */
    private function prepareCommunityPostData(
        Request $request,
        array $validated,
        ?CommunityPost $communityPost = null
    ): array {
        $title = trim($validated['title']);

        $data = [
            'community_category_id' => $validated['community_category_id'] ?? null,

            'title' => $title,

            'slug' => $this->generateUniqueSlug(
                title: $title,
                requestedSlug: $validated['slug'] ?? null,
                ignoreId: $communityPost?->id
            ),

            'short_description' => filled(
                $validated['short_description'] ?? null
            )
                ? trim($validated['short_description'])
                : Str::limit(
                    strip_tags($validated['content']),
                    500,
                    ''
                ),

            'content' => $validated['content'],

            'type' => $validated['type'],

            'published_at' => $validated['published_at'] ?? null,

            'is_featured' => $request->boolean('is_featured'),

            'is_active' => $request->boolean('is_active'),
        ];

        /*
         * بيانات الفعالية تحفظ فقط عندما يكون النوع event.
         */
        if ($validated['type'] === 'event') {
            $data['event_start_at'] =
                $validated['event_start_at'] ?? null;

            $data['event_end_at'] =
                $validated['event_end_at'] ?? null;

            $data['location'] = filled(
                $validated['location'] ?? null
            )
                ? trim($validated['location'])
                : null;

            $data['registration_url'] = filled(
                $validated['registration_url'] ?? null
            )
                ? trim($validated['registration_url'])
                : null;
        } else {
            $data['event_start_at'] = null;
            $data['event_end_at'] = null;
            $data['location'] = null;
            $data['registration_url'] = null;
        }

        return $data;
    }

    /**
     * توليد رابط مختصر فريد.
     */
    private function generateUniqueSlug(
        string $title,
        ?string $requestedSlug = null,
        ?int $ignoreId = null
    ): string {
        $value = filled($requestedSlug)
            ? $requestedSlug
            : $title;

        $baseSlug = $this->formatSlug($value);

        if ($baseSlug === '') {
            $baseSlug = 'community-'.Str::lower(
                Str::random(8)
            );
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            CommunityPost::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where(
                        'id',
                        '!=',
                        $ignoreId
                    )
                )
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * تنسيق الرابط مع دعم اللغة العربية والإنجليزية.
     */
    private function formatSlug(string $value): string
    {
        $value = trim(mb_strtolower($value));

        $value = preg_replace(
            '/[^\p{Arabic}\p{L}\p{N}\s_-]+/u',
            '',
            $value
        );

        $value = preg_replace(
            '/[\s_-]+/u',
            '-',
            (string) $value
        );

        return trim(
            (string) $value,
            '-'
        );
    }

    /**
     * حذف صورة المنشور من storage/app/public.
     */
    private function deleteImage(?string $image): void
    {
        if (blank($image)) {
            return;
        }

        if (Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);
        }
    }

    /**
     * أسماء أنواع منشورات المجتمع.
     */
    private function types(): array
    {
        return [
            'post' => 'منشور',
            'discussion' => 'نقاش',
            'event' => 'فعالية',
            'announcement' => 'إعلان',
        ];
    }
}

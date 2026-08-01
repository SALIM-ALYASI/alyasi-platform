<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    /**
     * عرض روابط التواصل الاجتماعي.
     */
    public function index(): View
    {
        $socialLinks = SocialLink::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.social-links.index', compact('socialLinks'));
    }

    /**
     * صفحة إضافة رابط جديد.
     */
    public function create(): View
    {
        return view('admin.social-links.create');
    }

    /**
     * حفظ الرابط الجديد.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        SocialLink::create($data);

        return redirect()
            ->route('admin.social-links.index')
            ->with('success', 'تمت إضافة رابط التواصل الاجتماعي بنجاح.');
    }

    /**
     * صفحة تعديل الرابط.
     */
    public function edit(SocialLink $socialLink): View
    {
        return view(
            'admin.social-links.edit',
            compact('socialLink')
        );
    }

    /**
     * تحديث الرابط.
     */
    public function update(
        Request $request,
        SocialLink $socialLink
    ): RedirectResponse {
        $data = $this->validatedData($request);

        $socialLink->update($data);

        return redirect()
            ->route('admin.social-links.index')
            ->with('success', 'تم تحديث رابط التواصل الاجتماعي بنجاح.');
    }

    /**
     * حذف الرابط.
     */
    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $socialLink->delete();

        return redirect()
            ->route('admin.social-links.index')
            ->with('success', 'تم حذف رابط التواصل الاجتماعي بنجاح.');
    }

    /**
     * التحقق من البيانات وتجهيز الحقول المنطقية.
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'platform' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:500'],
            'username' => ['nullable', 'string', 'max:150'],
            'icon' => ['required', 'string', 'max:255'],
            'color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'open_new_tab' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم المنصة مطلوب.',
            'platform.required' => 'معرّف المنصة مطلوب.',
            'url.required' => 'رابط الحساب مطلوب.',
            'url.url' => 'رابط الحساب غير صحيح.',
            'icon.required' => 'كلاس الأيقونة مطلوب.',
            'color.regex' => 'يجب أن يكون اللون مثل: #E1306C',
            'sort_order.required' => 'ترتيب الظهور مطلوب.',
            'sort_order.integer' => 'ترتيب الظهور يجب أن يكون رقمًا.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['open_new_tab'] = $request->boolean('open_new_tab');

        return $data;
    }
}

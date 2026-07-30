<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * عرض الأسئلة الشائعة.
     */
    public function index(): View
    {
        $faqs = Faq::query()
            ->ordered()
            ->paginate(20);

        return view('admin.faqs.index', compact('faqs'));
    }

    /**
     * صفحة إضافة سؤال جديد.
     */
    public function create(): View
    {
        return view('admin.faqs.create');
    }

    /**
     * حفظ سؤال جديد.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateFaq($request);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        Faq::query()->create($validated);

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'تمت إضافة السؤال بنجاح.');
    }

    /**
     * صفحة تعديل سؤال.
     */
    public function edit(Faq $faq): View
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    /**
     * تحديث سؤال.
     */
    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $this->validateFaq($request);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $faq->update($validated);

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'تم تحديث السؤال بنجاح.');
    }

    /**
     * حذف سؤال.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'تم حذف السؤال بنجاح.');
    }

    /**
     * تغيير حالة السؤال.
     */
    public function toggleStatus(Faq $faq): RedirectResponse
    {
        $faq->update([
            'is_active' => ! $faq->is_active,
        ]);

        $message = $faq->is_active
            ? 'تم تفعيل السؤال.'
            : 'تم إخفاء السؤال.';

        return back()->with('success', $message);
    }

    /**
     * التحقق من بيانات السؤال.
     */
    private function validateFaq(Request $request): array
    {
        return $request->validate([
            'question_ar' => ['required', 'string', 'max:255'],
            'question_en' => ['nullable', 'string', 'max:255'],
            'answer_ar' => ['required', 'string', 'max:2000'],
            'answer_en' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'question_ar.required' => 'السؤال بالعربية مطلوب.',
            'answer_ar.required' => 'الإجابة بالعربية مطلوبة.',
        ]);
    }
}

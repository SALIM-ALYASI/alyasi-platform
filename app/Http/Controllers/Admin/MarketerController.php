<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketer;
use App\Models\MarketerCustomer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketerController extends Controller
{
    /**
     * عرض قائمة المسوّقين.
     */
    public function index(): View
    {
        $marketers = Marketer::query()
            ->withCount('customers')
            ->latest()
            ->paginate(20);

        return view('admin.marketers.index', compact('marketers'));
    }

    /**
     * صفحة إضافة مسوّق جديد.
     */
    public function create(): View
    {
        return view('admin.marketers.create');
    }

    /**
     * حفظ مسوّق جديد وتوليد كوده.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMarketer($request);

        $marketer = Marketer::query()->create([
            ...$validated,
            'code' => Marketer::generateUniqueCode(),
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.marketers.edit', $marketer)
            ->with('success', "تمت إضافة المسوّق بنجاح. الكود الخاص به: {$marketer->code}");
    }

    /**
     * صفحة تعديل مسوّق وإدارة عملائه.
     */
    public function edit(Marketer $marketer): View
    {
        $marketer->load(['customers' => fn ($query) => $query->latest()]);

        return view('admin.marketers.edit', compact('marketer'));
    }

    /**
     * تحديث بيانات مسوّق.
     */
    public function update(Request $request, Marketer $marketer): RedirectResponse
    {
        $validated = $this->validateMarketer($request);

        $marketer->update($validated);

        return redirect()
            ->route('admin.marketers.edit', $marketer)
            ->with('success', 'تم تحديث بيانات المسوّق بنجاح.');
    }

    /**
     * حذف مسوّق وكل عملائه المسجّلين.
     */
    public function destroy(Marketer $marketer): RedirectResponse
    {
        $marketer->delete();

        return redirect()
            ->route('admin.marketers.index')
            ->with('success', 'تم حذف المسوّق بنجاح.');
    }

    /**
     * تفعيل/إيقاف مسوّق.
     */
    public function toggleStatus(Marketer $marketer): RedirectResponse
    {
        $marketer->update([
            'is_active' => ! $marketer->is_active,
        ]);

        $message = $marketer->is_active
            ? 'تم تفعيل المسوّق.'
            : 'تم إيقاف المسوّق.';

        return back()->with('success', $message);
    }

    /**
     * تسجيل عميل جديد جابه المسوّق، مع احتساب نسبة عمولته الحالية.
     */
    public function storeCustomer(Request $request, Marketer $marketer): RedirectResponse
    {
        $validated = $request->validate(
            [
                'customer_name' => ['required', 'string', 'max:255'],
                'customer_phone' => ['required', 'string', 'max:30'],
                'deal_amount' => ['nullable', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ],
            [
                'customer_name.required' => 'يرجى إدخال اسم العميل.',
                'customer_phone.required' => 'يرجى إدخال رقم جوال العميل.',
            ]
        );

        $newCount = $marketer->customers()->count() + 1;
        $rate = $marketer->commissionRateForCustomerCount($newCount);

        MarketerCustomer::query()->create([
            ...$validated,
            'marketer_id' => $marketer->id,
            'commission_rate' => $rate,
            'commission_amount' => isset($validated['deal_amount'])
                ? round($validated['deal_amount'] * $rate / 100, 2)
                : null,
        ]);

        return redirect()
            ->route('admin.marketers.edit', $marketer)
            ->with('success', 'تمت إضافة العميل بنجاح.');
    }

    /**
     * حذف عميل من سجل المسوّق.
     */
    public function destroyCustomer(Marketer $marketer, MarketerCustomer $customer): RedirectResponse
    {
        abort_unless($customer->marketer_id === $marketer->id, 404);

        $customer->delete();

        return redirect()
            ->route('admin.marketers.edit', $marketer)
            ->with('success', 'تم حذف العميل من سجل المسوّق.');
    }

    /**
     * التحقق من بيانات المسوّق.
     */
    private function validateMarketer(Request $request): array
    {
        return $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:30'],
            ],
            [
                'name.required' => 'يرجى إدخال اسم المسوّق.',
                'phone.required' => 'يرجى إدخال رقم جوال المسوّق.',
            ]
        );
    }
}

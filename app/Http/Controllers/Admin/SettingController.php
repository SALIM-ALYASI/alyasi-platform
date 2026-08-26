<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * عرض صفحة الإعدادات.
     */
    public function index(): View
    {
        $maintenanceMode = Setting::get('maintenance_mode', '0') === '1';
        $contactEmail = Setting::get('contact_email');
        $contactPhone = Setting::get('contact_phone');
        $showCommunityEvents = Setting::get('show_community_events', '1') === '1';
        $showArticles = Setting::get('show_articles', '1') === '1';

        return view('admin.settings.index', compact(
            'maintenanceMode',
            'contactEmail',
            'contactPhone',
            'showCommunityEvents',
            'showArticles'
        ));
    }

    /**
     * تحديث بريد وهاتف التواصل الظاهرين في صفحة "تواصل معنا".
     */
    public function updateContactInfo(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'contact_email' => ['nullable', 'email', 'max:255'],
                'contact_phone' => ['nullable', 'string', 'max:30'],
            ],
            [
                'contact_email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            ]
        );

        Setting::set('contact_email', $validated['contact_email'] ?? null);
        Setting::set('contact_phone', $validated['contact_phone'] ?? null);

        return back()->with('success', 'تم تحديث بيانات التواصل بنجاح.');
    }

    /**
     * تفعيل/إيقاف وضع الصيانة.
     */
    public function toggleMaintenance(): RedirectResponse
    {
        $current = Setting::get('maintenance_mode', '0') === '1';

        Setting::set('maintenance_mode', $current ? '0' : '1');

        $message = $current
            ? 'تم إيقاف وضع الصيانة، الموقع متاح للزوار الآن.'
            : 'تم تفعيل وضع الصيانة، الصفحات العامة مغلقة الآن أمام الزوار.';

        return back()->with('success', $message);
    }

    /**
     * إظهار/إخفاء قسم "فعاليات المجتمع" من الموقع العام (الهيدر والفوتر
     * والصفحة الرئيسية)، بدون حذف أي بيانات أو تعطيل لوحة التحكم الخاصة به.
     */
    public function toggleCommunityEvents(): RedirectResponse
    {
        $current = Setting::get('show_community_events', '1') === '1';

        Setting::set('show_community_events', $current ? '0' : '1');

        $message = $current
            ? 'تم إخفاء قسم فعاليات المجتمع من الموقع العام.'
            : 'تم إظهار قسم فعاليات المجتمع بالموقع العام مجددًا.';

        return back()->with('success', $message);
    }

    /**
     * إظهار/إخفاء قسم "مقالاتي" من الموقع العام، بدون حذف أي بيانات أو
     * تعطيل لوحة التحكم الخاصة به.
     */
    public function toggleArticles(): RedirectResponse
    {
        $current = Setting::get('show_articles', '1') === '1';

        Setting::set('show_articles', $current ? '0' : '1');

        $message = $current
            ? 'تم إخفاء قسم مقالاتي من الموقع العام.'
            : 'تم إظهار قسم مقالاتي بالموقع العام مجددًا.';

        return back()->with('success', $message);
    }

    /**
     * إعادة توليد public/sitemap.xml يدويًا — الأمر نفسه يعمل تلقائيًا
     * عند إضافة/تعديل مقال أو خدمة أو خبر أو عمل، لكن هذا الزر مفيد
     * لتحديث الصفحات الثابتة فورًا، أو بعد أي مشكلة تحتاج إعادة توليد يدوية.
     */
    public function regenerateSitemap(): RedirectResponse
    {
        $exitCode = Artisan::call('sitemap:generate');

        if ($exitCode !== 0) {
            return back()->with(
                'error',
                'فشل توليد sitemap.xml: '.trim(Artisan::output())
            );
        }

        return back()->with('success', 'تم إعادة توليد sitemap.xml بنجاح — '.trim(Artisan::output()));
    }

    /**
     * تحديث الاسم والبريد الإلكتروني لحساب المدير الحالي.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'display_name_ar' => ['nullable', 'string', 'max:255'],
                'display_name_en' => ['nullable', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('admins', 'email')->ignore($admin->id),
                ],
            ],
            [
                'name.required' => 'يرجى إدخال الاسم.',
                'email.required' => 'يرجى إدخال البريد الإلكتروني.',
                'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
                'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',
            ]
        );

        $admin->update($validated);

        return back()->with('success', 'تم تحديث بيانات الحساب بنجاح.');
    }

    /**
     * تحديث كلمة مرور حساب المدير الحالي.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'current_password' => ['required', 'current_password:admin'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ],
            [
                'current_password.required' => 'يرجى إدخال كلمة المرور الحالية.',
                'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.',
                'password.required' => 'يرجى إدخال كلمة المرور الجديدة.',
                'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            ]
        );

        Auth::guard('admin')->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }
}

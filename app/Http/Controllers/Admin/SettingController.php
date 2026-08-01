<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('admin.settings.index', compact('maintenanceMode'));
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
     * تحديث الاسم والبريد الإلكتروني لحساب المدير الحالي.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
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

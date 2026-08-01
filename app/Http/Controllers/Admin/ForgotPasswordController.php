<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * عرض نموذج طلب رابط إعادة تعيين كلمة المرور.
     */
    public function showLinkRequestForm(): View
    {
        return view('admin.auth.forgot-password');
    }

    /**
     * إرسال رابط إعادة التعيين إلى البريد الإلكتروني (إن وُجد الحساب).
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'email']],
            ['email.required' => 'يرجى إدخال البريد الإلكتروني.']
        );

        Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        // رسالة عامة دائمًا، بغض النظر عن وجود الحساب من عدمه، حتى لا
        // يُستخدَم هذا النموذج لمعرفة البريد الإلكتروني المسجَّل كمدير.
        return back()->with(
            'success',
            'إذا كان البريد الإلكتروني مسجّلاً كحساب مدير، فسيصلك رابط إعادة تعيين كلمة المرور خلال دقائق.'
        );
    }

    /**
     * عرض نموذج إعادة تعيين كلمة المرور.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * تنفيذ إعادة تعيين كلمة المرور.
     */
    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', 'min:8'],
            ],
            [
                'email.required' => 'يرجى إدخال البريد الإلكتروني.',
                'password.required' => 'يرجى إدخال كلمة المرور الجديدة.',
                'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
                'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            ]
        );

        $status = Password::broker('admins')->reset(
            $validated,
            function ($admin, $password) {
                $admin->forceFill([
                    'password' => $password,
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $this->translateStatus($status)]);
        }

        return redirect()
            ->route('admin.login')
            ->with('success', 'تم تغيير كلمة المرور بنجاح، يمكنك تسجيل الدخول الآن.');
    }

    private function translateStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'رابط إعادة التعيين غير صالح أو منتهي الصلاحية.',
            Password::INVALID_USER => 'لا يوجد حساب مدير بهذا البريد الإلكتروني.',
            default => 'تعذّر إعادة تعيين كلمة المرور، حاول مرة أخرى.',
        };
    }
}

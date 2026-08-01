<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * لا توجد بيانات دخول افتراضية مثبّتة في الكود: البريد وكلمة المرور
     * يُقرآن من متغيرات البيئة (ADMIN_EMAIL / ADMIN_PASSWORD)، وإن لم
     * تُضبط تُولَّد كلمة مرور عشوائية قوية وتُطبع مرة واحدة على الطرفية.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (blank($email)) {
            $this->command?->error(
                'ADMIN_EMAIL غير مضبوط في .env — تم تخطي إنشاء حساب المدير.'
            );

            return;
        }

        $generatedPassword = null;

        if (blank($password)) {
            $generatedPassword = Str::password(16);
            $password = $generatedPassword;
        }

        Admin::updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => env('ADMIN_NAME', 'مدير ALYASI'),
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );

        if ($generatedPassword !== null) {
            $this->command?->warn(
                "تم إنشاء المدير ({$email}) بكلمة مرور عشوائية: {$generatedPassword}"
            );

            $this->command?->warn(
                'احفظها الآن — لن تُعرض مرة أخرى. يمكنك تغييرها من لوحة التحكم لاحقًا.'
            );
        }
    }
}

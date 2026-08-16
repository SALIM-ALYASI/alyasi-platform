<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * تتحقق من أن الرابط المختصر (slug) الذي يكتبه المدير يدويًا نص نظيف
 * فعلاً — لا رابط URL كامل ملصوق بالغلط، ولا رموز غريبة — بدل السماح
 * بأي نص وتمريره لِـ Str::slug() التي "تبتلع" أي مدخل وتحوّله لشيء
 * شبه-صالح الشكل حتى لو كان في الأصل رابطًا كاملًا.
 */
class CleanSlug implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;

        if (Str::contains($value, ['://', 'www.'])) {
            $fail('الرابط المختصر لا يجب أن يحتوي على رابط كامل أو عنوان موقع.');

            return;
        }

        if (preg_match('/[.\/?#@\s]/', $value)) {
            $fail('الرابط المختصر يجب أن يحتوي فقط على حروف إنجليزية صغيرة، أرقام، وشرطات (-)، بدون مسافات أو رموز.');

            return;
        }

        if (! preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $value)) {
            $fail('صيغة الرابط المختصر غير صحيحة — يجب أن يبدأ وينتهي بحرف أو رقم، ويستخدم الشرطة (-) للفصل فقط.');
        }
    }
}

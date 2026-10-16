<?php

namespace Tests\Feature;

use Tests\TestCase;

class TranslationParityTest extends TestCase
{
    /**
     * مفاتيح عربية بدون مقابل إنجليزي تطلع للزائر كنص خام (services.show.why_choose).
     * المستثنى: صفحات ثنائية اللغة تطلب لغة محددة صراحة، وصفحات معطلة.
     */
    private const ALLOWED_MISSING = [
        'data_deletion.php' => ['meta_title', 'meta_description', 'title'],
    ];

    private const DISABLED_FILES = ['product_launches.php'];

    public function test_every_arabic_translation_key_has_an_english_twin(): void
    {
        $missing = [];

        foreach (glob(lang_path('ar/*.php')) as $file) {
            $name = basename($file);

            if (in_array($name, self::DISABLED_FILES, true)) {
                continue;
            }

            $english = lang_path('en/'.$name);
            $this->assertFileExists($english, "lang/en/{$name} مفقود");

            $keys = array_diff_key($this->flatten(require $file), $this->flatten(require $english));
            $keys = array_diff(array_keys($keys), self::ALLOWED_MISSING[$name] ?? []);

            foreach ($keys as $key) {
                $missing[] = basename($name, '.php').'.'.$key;
            }
        }

        $this->assertSame([], $missing, 'مفاتيح بدون ترجمة إنجليزية');
    }

    private function flatten(array $items, string $prefix = ''): array
    {
        $flat = [];

        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $flat += $this->flatten($value, $prefix.$key.'.');
            } else {
                $flat[$prefix.$key] = true;
            }
        }

        return $flat;
    }
}

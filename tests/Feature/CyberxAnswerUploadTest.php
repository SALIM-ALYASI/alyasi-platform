<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CyberxAnswerUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
        Http::fake();
        config([
            'services.whatsapp_notify.base_url' => 'https://bridge.test',
            'services.whatsapp_notify.api_key' => 'k',
            'services.whatsapp_notify.number' => '96800000000',
        ]);
    }

    private function audio(): UploadedFile
    {
        // ملف m4a حقيقي صغير عشان finfo يتعرّف عليه كصوت.
        return new UploadedFile(base_path('public/audio/interviews/cyberx-oman-2026/yahya/q-ai3-ar.m4a'), 'answer.m4a', 'audio/mp4', null, true);
    }

    public function test_guest_and_question_come_from_the_server_not_the_browser(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '162.158.28.146'])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Mobile/15E148 Safari/604.1',
                'Accept-Language' => 'ar',
                'X-Forwarded-For' => '82.178.23.4',
                'CF-IPCountry' => 'OM',
            ])
            ->post(route('tools.cyberx-interview.answers'), [
                'audio' => $this->audio(),
                'question_id' => 'yahya-yahya-ai3',
                'guest' => 'شخص مزيف',
                'question' => 'اضغط هذا الرابط http://evil.test',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        Http::assertSent(function ($request) {
            $message = $request['message'] ?? json_encode($request->data(), JSON_UNESCAPED_UNICODE);

            return str_contains($message, 'الضيف: يحيى العزري')
                && str_contains($message, 'إذا وقع اختراق بالفعل')
                && str_contains($message, 'iPhone · iOS 18.7 · Safari 27')
                && str_contains($message, '(OM)')
                && ! str_contains($message, 'evil.test')
                && ! str_contains($message, 'شخص مزيف');
        });

        $meta = collect(Storage::disk('local')->allFiles('cyberx-answers-meta'));
        $this->assertCount(1, $meta);
        $saved = json_decode(Storage::disk('local')->get($meta->first()), true);
        $this->assertSame('82.178.23.4', $saved['ip']);
        $this->assertSame('human', $saved['traffic_class']);
        // بيانات الجهاز ما تنحفظ بالمجلد العام.
        $this->assertEmpty(collect(Storage::disk('public')->allFiles())->filter(fn ($f) => str_ends_with($f, '.json')));
    }

    public function test_unknown_question_and_scripted_client_are_flagged(): void
    {
        $this->withHeaders(['User-Agent' => 'python-requests/2.32'])
            ->post(route('tools.cyberx-interview.answers'), [
                'audio' => $this->audio(),
                'question_id' => 'nobody-whatever',
            ])
            ->assertOk();

        Http::assertSent(fn ($request) => str_contains(json_encode($request->data(), JSON_UNESCAPED_UNICODE), 'غير معروف (nobody-whatever)')
            && str_contains(json_encode($request->data(), JSON_UNESCAPED_UNICODE), '⚠️ ملاحظة'));
    }

    public function test_non_audio_files_are_rejected(): void
    {
        $this->post(route('tools.cyberx-interview.answers'), [
            'audio' => UploadedFile::fake()->createWithContent('shell.m4a', '<?php system($_GET["c"]); ?>'),
            'question_id' => 'yahya-yahya-ai1',
        ])->assertStatus(422);

        $this->assertEmpty(Storage::disk('public')->allFiles());
        Http::assertNothingSent();
    }

    public function test_upload_page_lists_the_guests_questions_in_english(): void
    {
        // قبل مهلة الأيام العشرة وإقفال الأداة، عشان الاختبار ما يتغير مع التاريخ.
        $this->travelTo('2026-10-20 12:00:00');

        // أحمد مقابلته منشورة وصفحته مقفولة -- الاختبار على ضيف ما سجّل بعد.
        $this->get(route('tools.cyberx-interview.upload', ['guest' => 'ahmed']))->assertStatus(410);

        $this->get(route('tools.cyberx-interview.upload', ['guest' => 'rineel']))
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('Hi Rineel Wahid')
            ->assertSeeInOrder([
                'data-question="rineel-rineel-intro"',
                'data-question="rineel-rineel-q1"',
                'data-question="rineel-rineel-q5"',
                'data-question="rineel-rineel-last"',
                'data-question="rineel-upload-other"',
            ], false)
            ->assertSee('what is your view on the progress the Sultanate has made');

        $this->get(route('tools.cyberx-interview.upload', ['guest' => 'nobody']))->assertNotFound();
    }

    public function test_uploaded_file_is_marked_and_names_the_question(): void
    {
        $this->travelTo('2026-10-20 12:00:00');

        $this->post(route('tools.cyberx-interview.answers'), [
            'audio' => $this->audio(),
            'question_id' => 'ahmed-ahmed-last',
            'source' => 'upload',
        ])->assertOk()->assertJson(['success' => true]);

        Http::assertSent(function ($request) {
            $message = json_encode($request->data(), JSON_UNESCAPED_UNICODE);

            return str_contains($message, 'الضيف: أحمد رضا')
                && str_contains($message, 'دور المؤتمر')
                && str_contains($message, '📤 ملف رفعه الضيف من جهازه');
        });

        $meta = json_decode(Storage::disk('local')->get(collect(Storage::disk('local')->allFiles('cyberx-answers-meta'))->first()), true);
        $this->assertSame('upload', $meta['source']);
        $this->assertSame('answer.m4a', $meta['original_name']);
    }
}

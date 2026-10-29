<?php

namespace Tests\Feature;

use App\Http\Controllers\InterviewStudioController as Studio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InterviewStudioTest extends TestCase
{
    use RefreshDatabase;

    private const EVENT = 'cysec-oman-2026';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        Http::fake();
        Config::set('services.whatsapp_notify', ['base_url' => 'https://wa.test', 'api_key' => 'k', 'number' => '96800000000']);
        $this->travelTo('2026-11-01 10:00:00');
    }

    private function url(string $who): string
    {
        return route('tools.interview-studio', ['event' => self::EVENT, 'token' => Studio::token(self::EVENT, $who)]);
    }

    private function audio(): UploadedFile
    {
        // رأس ملف m4a حقيقي (ftyp) عشان finfo يعرفه صوت.
        return UploadedFile::fake()->createWithContent('a.m4a', "\x00\x00\x00\x20ftypM4A \x00\x00\x00\x00M4A mp42isom".str_repeat("\x00", 64));
    }

    public function test_each_guest_link_shows_only_that_guest(): void
    {
        $this->get($this->url('talal'))->assertOk()
            ->assertSee('د. طلال السعدي')
            ->assertSee('noindex', false)
            ->assertSee('حوكمة الذكاء الاصطناعي')
            ->assertDontSee('عبدالله البرواني');

        $this->get($this->url('host'))->assertOk()->assertSee('وضع المضيف');
        $this->get(route('tools.interview-studio', ['event' => self::EVENT, 'token' => 'abcdef0123456789']))->assertNotFound();
        $this->get(route('tools.interview-studio', ['event' => 'nope', 'token' => Studio::token('nope', 'talal')]))->assertNotFound();
    }

    public function test_answer_is_saved_once_per_take_and_guest_cannot_answer_for_others(): void
    {
        $post = fn (string $who, string $q, string $take) => $this->post(
            route('tools.interview-studio.answers', ['event' => self::EVENT, 'token' => Studio::token(self::EVENT, $who)]),
            ['audio' => $this->audio(), 'question_id' => $q, 'take_id' => $take],
        );

        $post('talal', 'talal-q2', 'take-aaaa-1111')->assertOk()->assertJson(['success' => true]);
        // نفس التسجيل مرة ثانية (ضغطتين أو إعادة محاولة): ما يتكرر الحفظ ولا الواتساب.
        $post('talal', 'talal-q2', 'take-aaaa-1111')->assertOk()->assertJson(['duplicate' => true]);

        $this->assertCount(1, collect(Storage::disk('public')->allFiles())->filter(fn ($f) => str_contains($f, 'talal-q2')));
        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => str_contains(json_encode($r->data(), JSON_UNESCAPED_UNICODE), 'د. طلال السعدي')
            && str_contains(json_encode($r->data(), JSON_UNESCAPED_UNICODE), 'التحول الرقمي في المؤسسات الحكومية'));

        // ضيف ثاني ما يرسل على سؤال طلال، وطلال ما عنده سؤال إضافي.
        $post('barwani', 'talal-q3', 'take-bbbb-2222')->assertStatus(422);
        $post('talal', 'talal-extra', 'take-cccc-3333')->assertStatus(422);
        $post('barwani', 'barwani-extra', 'take-dddd-4444')->assertOk();
        // المضيف يرسل لأي ضيف.
        $post('host', 'maain-q1', 'take-eeee-5555')->assertOk();
    }

    public function test_non_audio_upload_is_rejected(): void
    {
        $this->post(route('tools.interview-studio.answers', ['event' => self::EVENT, 'token' => Studio::token(self::EVENT, 'majid')]), [
            'audio' => UploadedFile::fake()->createWithContent('x.m4a', '<?php echo 1; ?>'),
            'question_id' => 'majid-q1',
            'take_id' => 'take-ffff-6666',
        ])->assertStatus(422);

        $this->assertEmpty(Storage::disk('public')->allFiles());
        Http::assertNothingSent();
    }
}

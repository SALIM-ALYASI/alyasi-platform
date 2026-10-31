<?php

namespace Tests\Feature;

use App\Http\Controllers\VoiceNotesController;
use App\Models\Article;
use App\Models\VoiceDictionaryEntry;
use App\Models\VoiceNote;
use App\Support\Voice\LetterCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VoiceNotesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Config::set('services.conference_journalist.agent_token', 'agent-secret');
    }

    private function base(): string
    {
        return '/tools/voice/'.VoiceNotesController::token();
    }

    private function audio(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('n.m4a', "\x00\x00\x00\x20ftypM4A \x00\x00\x00\x00M4A mp42isom".str_repeat("\x00", 64));
    }

    private function agent(): array
    {
        return ['Authorization' => 'Bearer agent-secret', 'Accept' => 'application/json'];
    }

    public function test_page_needs_the_secret_link(): void
    {
        $this->get($this->base())->assertOk()->assertSee('noindex', false)->assertSee('صوتي');
        $this->get('/tools/voice/'.str_repeat('a', 24))->assertNotFound();
        $this->get('/tools/voice/short')->assertNotFound();
    }

    public function test_upload_is_saved_once_per_take_and_rejects_non_audio(): void
    {
        $this->post($this->base().'/notes', ['audio' => $this->audio(), 'take_id' => 'take-1111-aaaa', 'language' => 'ar'])
            ->assertOk()->assertJsonPath('note.status', 'queued');
        $this->post($this->base().'/notes', ['audio' => $this->audio(), 'take_id' => 'take-1111-aaaa'])
            ->assertOk()->assertJsonPath('duplicate', true);
        $this->assertSame(1, VoiceNote::count());

        $this->post($this->base().'/notes', ['audio' => UploadedFile::fake()->createWithContent('x.m4a', '<?php echo 1;'), 'take_id' => 'take-2222-bbbb'])
            ->assertStatus(422);
        $this->assertSame(1, VoiceNote::count());
    }

    public function test_mac_gets_dictionary_and_corrections_learn_from_edits(): void
    {
        VoiceDictionaryEntry::remember('term', 'ar', '', 'الأمن السيبراني');
        $this->post($this->base().'/notes', ['audio' => $this->audio(), 'take_id' => 'take-3333-cccc', 'language' => 'ar'])->assertOk();

        $this->getJson('/api/cj/agent/voice/next')->assertUnauthorized();
        $job = $this->getJson('/api/cj/agent/voice/next', $this->agent())->assertOk()->json('note');
        $this->assertSame('take-3333-cccc', $job['id']);
        $this->assertStringContainsString('الأمن السيبراني', $job['prompt']);
        $this->getJson('/api/cj/agent/voice/next', $this->agent())->assertJsonPath('note', null); // محجوزة
        $this->get('/api/cj/agent/voice/take-3333-cccc/audio', $this->agent())->assertOk();

        $this->postJson('/api/cj/agent/voice/take-3333-cccc/complete', ['transcript' => 'منصة الياسر للأمن', 'duration_seconds' => 90], $this->agent())->assertOk();

        $this->postJson($this->base().'/notes/take-3333-cccc/text', [
            'text' => 'منصة الياسي للأمن',
            'learn' => [['wrong' => 'الياسر', 'right' => 'الياسي']],
        ])->assertOk()->assertJsonPath('learned', 1)->assertJsonPath('entries.0.right', 'الياسي');

        $note = VoiceNote::first();
        $this->assertNotNull($note->approved_at);
        $this->assertSame(1.5, VoiceNote::trainingMinutes());
        $this->getJson($this->base().'/notes')->assertJsonPath('training.minutes', 1.5);
        $this->assertSame([['الياسر', 'الياسي']], VoiceDictionaryEntry::correctionsFor('ar'));
        $this->getJson('/api/cj/agent/voice/dataset', $this->agent())->assertJsonPath('items.0.text', 'منصة الياسي للأمن');
    }

    public function test_owner_can_delete_a_note_and_its_audio(): void
    {
        $this->post($this->base().'/notes', ['audio' => $this->audio(), 'take_id' => 'take-5555-eeee'])->assertOk();
        $path = VoiceNote::first()->original_path;
        Storage::disk('local')->assertExists($path);

        $this->deleteJson('/tools/voice/'.str_repeat('b', 24).'/notes/take-5555-eeee')->assertNotFound();
        $this->deleteJson($this->base().'/notes/take-5555-eeee')->assertOk();
        $this->assertSame(0, VoiceNote::count());
        Storage::disk('local')->assertMissing($path);
    }

    public function test_read_and_record_saves_a_lesson_and_feeds_the_prompt(): void
    {
        Article::query()->create([
            'title_ar' => 'مقال',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'content_ar' => '<p>هذه جملة عربية طويلة بما يكفي لتكون تمرين قراءة مفيد. قصيرة.</p><p>Visit https://example.com now please for more words here.</p>',
        ]);
        $sentences = $this->getJson($this->base().'/script')->assertOk()->json('sentences');
        $this->assertSame(['هذه جملة عربية طويلة بما يكفي لتكون تمرين قراءة مفيد.'], $sentences);

        $this->post($this->base().'/notes', ['audio' => $this->audio(), 'take_id' => 'take-6666-ffff', 'script' => $sentences[0]])->assertOk();
        $note = VoiceNote::first();
        $this->assertNotNull($note->approved_at);
        $this->assertSame($sentences[0], $note->edited_text);

        $prompt = $this->getJson('/api/cj/agent/voice/next', $this->agent())->json('note.prompt');
        $this->assertStringEndsWith($sentences[0], $prompt);
    }

    public function test_script_puts_sentences_with_missing_letters_first_and_hides_read_ones(): void
    {
        Article::query()->create([
            'title_ar' => 'مقال',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'content_ar' => '<p>المنصة تنشر الأخبار كل يوم من مسقط إلى العالم.</p><p>ظل الثعلب الغاضب يذوب في ظهيرة الصيف الحارة.</p>',
        ]);
        VoiceNote::create(['uuid' => 'take-7777-aaaa', 'status' => 'done', 'language' => 'ar', 'original_path' => 'x',
            'edited_text' => 'المنصة تنشر الأخبار كل يوم من مسقط إلى العالم.', 'approved_at' => now()]);

        $out = $this->getJson($this->base().'/script')->assertOk()->json();

        // الجملة المقروءة انشالت، والباقية فيها ظ وث وغ اللي ما انسجّلت.
        $this->assertSame(['ظل الثعلب الغاضب يذوب في ظهيرة الصيف الحارة.'], $out['sentences']);
        $letters = collect($out['letters'])->pluck('count', 'letter');
        $this->assertCount(29, $letters);
        $this->assertSame(0, $letters['ظ']);
        $this->assertSame(3, $letters['ن']); // المنصة، تنشر، من
        $this->assertSame(LetterCoverage::TARGET, $out['target']);
    }

    public function test_letter_ranking_prefers_rare_letters_and_merges_letter_forms(): void
    {
        $counts = LetterCoverage::count('أإآة');
        $this->assertSame(3, $counts['ء']);
        $this->assertSame(1, $counts['ت']);

        $have = array_fill_keys(LetterCoverage::LETTERS, LetterCoverage::TARGET);
        $have['ظ'] = 0;
        $ranked = LetterCoverage::rank(['كتب الولد الدرس', 'ظهر الظل'], $have);
        $this->assertSame(['ظهر الظل', 'كتب الولد الدرس'], $ranked);
    }

    public function test_failed_note_retries_then_stops(): void
    {
        $this->post($this->base().'/notes', ['audio' => $this->audio(), 'take_id' => 'take-4444-dddd'])->assertOk();
        foreach (range(1, 3) as $i) {
            $this->getJson('/api/cj/agent/voice/next', $this->agent())->assertJsonPath('note.id', 'take-4444-dddd');
            $this->postJson('/api/cj/agent/voice/take-4444-dddd/fail', ['error' => 'boom'], $this->agent())->assertOk();
        }
        $this->assertSame(VoiceNote::FAILED, VoiceNote::first()->status);
        $this->postJson($this->base().'/notes/take-4444-dddd/retry')->assertOk()->assertJsonPath('note.status', 'queued');
    }
}

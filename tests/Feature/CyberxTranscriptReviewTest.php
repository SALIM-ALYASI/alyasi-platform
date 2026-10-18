<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CyberxTranscriptReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_transcript_opens_only_with_a_valid_signed_link(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('cyberx-transcripts/guest-name.html', '<h1>Interview with Guest</h1>');

        $url = URL::temporarySignedRoute('tools.cyberx-interview.review', now()->addDays(30), ['name' => 'guest-name'], absolute: false);

        $this->get($url)
            ->assertOk()
            ->assertSee('Interview with Guest', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->get('/tools/cyberx-interview/review/guest-name')->assertForbidden();
        $this->get($url.'x')->assertForbidden();

        $missing = URL::temporarySignedRoute('tools.cyberx-interview.review', now()->addDay(), ['name' => 'nobody'], absolute: false);
        $this->get($missing)->assertNotFound();
    }
}

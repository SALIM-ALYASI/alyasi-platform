<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CyberxInterviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_hub_lists_only_published_guests_in_both_languages(): void
    {
        $this->get('/events/cyberx-oman-2026/interviews')
            ->assertOk()
            ->assertSee('د. هيثم الحجري')
            ->assertSee(url('/events/cyberx-oman-2026/interviews/haitham-al-hajri'), false)
            ->assertSee('يحيى العزري')
            ->assertSee('علي اللواتي')
            ->assertSee('خالد العمراني')
            ->assertDontSee('قريبًا');

        $this->get('/en/events/cyberx-oman-2026/interviews')
            ->assertOk()
            ->assertSee('Dr. Haitham Al Hajri')
            ->assertSee('Yahya Al-Azri')
            // ترجمة خالد مخفية (معاينة فقط) -- ما يطلع بصفحة اللقاءات الإنجليزية.
            ->assertDontSee('Khalid Al Amrani')
            ->assertDontSee('In Arabic')
            ->assertSee('href="'.url('/en/events/cyberx-oman-2026/interviews/haitham-al-hajri').'"', false)
            ->assertDontSee('Coming soon');
    }

    public function test_interview_page_shows_questions_timed_answers_and_audio(): void
    {
        $this->get('/events/cyberx-oman-2026/interviews/haitham-al-hajri')
            ->assertOk()
            ->assertSee('كيف يوازن البنك بين تسهيل الخدمات الرقمية للعملاء وحمايتها؟')
            ->assertSee('data-t="3.28"', false)
            ->assertSee('haitham\/a1.m4a', false)
            ->assertSee('href="'.url('/en/events/cyberx-oman-2026/interviews/haitham-al-hajri').'" data-lang-choice="en"', false)
            ->assertSee('haitham-al-hajri-og.jpg', false);
    }

    public function test_english_page_shows_the_guests_own_written_answers(): void
    {
        $this->get('/en/events/cyberx-oman-2026/interviews/haitham-al-hajri')
            ->assertOk()
            ->assertSee('Thank you, Salem, for this important question.')
            ->assertSee('AI-generated voice, with Dr. Haitham’s consent')
            ->assertSee('Original recording in Arabic')
            ->assertSee('haitham\/en-a1.m4a', false)
            ->assertSee('haitham-al-hajri-og-en.jpg', false)
            ->assertSee('haitham\/a1.m4a', false);
    }

    public function test_coverage_page_renders_with_audio_report_cues(): void
    {
        $this->get('/events/cyberx-oman-2026/coverage')
            ->assertOk()
            ->assertSee('coverageListenPlay', false)
            ->assertSee('data-cue="0"', false)
            ->assertSee('coverageDock', false);

        $this->get('/en/events/cyberx-oman-2026/coverage')->assertOk();
    }

    public function test_unpublished_or_unknown_guest_is_not_found(): void
    {
        $this->get('/events/cyberx-oman-2026/interviews/nobody')->assertNotFound();

        // حتى رابط معاينة موقّع ما يفتح ضيف غير موجود.
        $url = URL::signedRoute('event_coverage.cyberx_oman_2026.interview', ['guest' => 'nobody'], absolute: false);
        $this->get($url)->assertNotFound();
    }

    public function test_ali_arabic_page_reads_the_translation_in_his_consented_ai_voice(): void
    {
        $this->withHeader('Accept-Language', 'ar')
            ->get('/events/cyberx-oman-2026/interviews/ali-al-lawati')
            ->assertOk()
            ->assertSee('صوت مولّد بالذكاء الاصطناعي بموافقة الأستاذ علي اللواتي')
            ->assertSee('التسجيل الأصلي بالإنجليزية')
            ->assertSee('أكبر تحدٍّ هو الوعي')
            ->assertSee('ali\\/ar-a2.m4a', false)
            ->assertSeeInOrder(['تعريف الضيف · سالم الحجري', 'سؤال 1 · سالم الحجري', 'سؤال 4 · سالم الحجري']);

        $this->get('/en/events/cyberx-oman-2026/interviews/ali-al-lawati')
            ->assertOk()
            ->assertSee('The biggest challenge facing the spread of digital payments in Oman is basically awareness.')
            ->assertDontSee('AI-generated voice');
    }

    public function test_yahya_english_page_uses_his_consented_ai_voice(): void
    {
        $this->get('/en/events/cyberx-oman-2026/interviews/yahya-al-azri')
            ->assertOk()
            ->assertSee('AI-generated voice, with Mr. Yahya Al Azri’s consent')
            ->assertSee('Unified National Digital Identity')
            ->assertSee('yahya\\/en-a2.m4a', false)
            ->assertSee('yahya-al-azri-og-en-v2.jpg', false)
            ->assertSee('Listen to my closing');
    }

    public function test_khalid_english_translation_is_hidden_but_previewable(): void
    {
        // الرابط العام يرجع للعربي.
        $this->get('/en/events/cyberx-oman-2026/interviews/khalid-al-amrani')
            ->assertRedirect(route('event_coverage.cyberx_oman_2026.interview', ['guest' => 'khalid-al-amrani']));

        // رابط المعاينة يفتح الترجمة النصية مع تسجيله العربي (بدون صوت مولّد).
        $url = URL::signedRoute('event_coverage.cyberx_oman_2026.interview.en', ['guest' => 'khalid-al-amrani'], absolute: false);

        $this->get($url)
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('Before you click any link, take two seconds and think')
            ->assertSee('Original recording in Arabic')
            ->assertDontSee('AI-generated voice');
    }

    public function test_yahya_interview_is_published_in_arabic_with_intro_card_and_closing(): void
    {
        $this->withHeader('Accept-Language', 'ar')
            ->get('/events/cyberx-oman-2026/interviews/yahya-al-azri')
            ->assertOk()
            ->assertSee('شرطة عُمان السلطانية')
            // التعريف أول بطاقة بدون رقم، والأسئلة بعده من 1 إلى 4.
            ->assertSeeInOrder(['تعريف الضيف · سالم الحجري', 'سؤال 1 · سالم الحجري', 'سؤال 4 · سالم الحجري'])
            ->assertDontSee('سؤال 5 ·')
            // خاتمة سالم بعد آخر إجابة، بصوته.
            ->assertSeeInOrder(['سؤال 4 · سالم الحجري', 'id="interviewOutro"', 'أنا كنت هناك لهذا السبب تحديدًا', 'استمع للخاتمة بصوتي'], false)
            ->assertSee('yahya\\/outro-ar.m4a', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactRetentionTest extends TestCase
{
    use RefreshDatabase;

    private function message(string $email, int $monthsAgo, bool $retained = false): ContactMessage
    {
        $message = ContactMessage::query()->create([
            'name' => 'Test',
            'email' => $email,
            'message' => 'رسالة تجريبية',
            'status' => 'read',
            'is_retained' => $retained,
        ]);
        $message->forceFill(['created_at' => now()->subMonths($monthsAgo)->subDay()])->save();

        return $message;
    }

    public function test_messages_older_than_twelve_months_since_last_contact_are_deleted(): void
    {
        $old = $this->message('old@example.com', 13);
        $returning = $this->message('back@example.com', 14);
        $this->message('back@example.com', 2); // تواصل حديث: يبقي رسائله القديمة
        $contract = $this->message('client@example.com', 20, retained: true);
        $recent = $this->message('new@example.com', 3);

        $this->assertSame(1, ContactMessage::pruneExpired());

        $this->assertModelMissing($old);
        $this->assertModelExists($returning);
        $this->assertModelExists($contract);
        $this->assertModelExists($recent);
    }
}

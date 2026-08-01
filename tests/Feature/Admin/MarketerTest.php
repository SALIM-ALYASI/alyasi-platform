<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Marketer;
use App\Models\MarketerCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_marketers(): void
    {
        $this->get(route('admin.marketers.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_marketer_with_generated_code(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.marketers.store'), [
                'name' => 'Ahmed',
                'phone' => '99999999',
            ])
            ->assertRedirect();

        $marketer = Marketer::query()->firstOrFail();

        $this->assertSame('Ahmed', $marketer->name);
        $this->assertMatchesRegularExpression('/^\d{9}$/', $marketer->code);
    }

    public function test_generated_codes_are_unique(): void
    {
        $codes = collect(range(1, 20))->map(fn () => Marketer::generateUniqueCode());

        $this->assertSame($codes->count(), $codes->unique()->count());
    }

    public function test_commission_rate_starts_at_base_rate(): void
    {
        $marketer = Marketer::factory()->create([
            'base_commission_rate' => 5,
            'tier_increment_rate' => 1,
            'customers_per_tier' => 10,
        ]);

        $this->assertSame(5.0, $marketer->currentCommissionRate());
    }

    public function test_commission_rate_increases_every_ten_customers(): void
    {
        $marketer = Marketer::factory()->create([
            'base_commission_rate' => 5,
            'tier_increment_rate' => 1,
            'customers_per_tier' => 10,
        ]);

        MarketerCustomer::factory()->count(10)->create(['marketer_id' => $marketer->id]);
        $this->assertSame(6.0, $marketer->fresh()->currentCommissionRate());

        MarketerCustomer::factory()->count(10)->create(['marketer_id' => $marketer->id]);
        $this->assertSame(7.0, $marketer->fresh()->currentCommissionRate());
    }

    public function test_adding_customer_snapshots_commission_and_computes_amount(): void
    {
        $admin = Admin::factory()->create();
        $marketer = Marketer::factory()->create([
            'base_commission_rate' => 5,
            'tier_increment_rate' => 1,
            'customers_per_tier' => 10,
        ]);

        MarketerCustomer::factory()->count(9)->create(['marketer_id' => $marketer->id]);

        // This is the marketer's 10th customer, so the bonus tier applies (6%).
        $this->actingAs($admin, 'admin')
            ->post(route('admin.marketers.customers.store', $marketer), [
                'customer_name' => 'Sara',
                'customer_phone' => '88888888',
                'deal_amount' => 100,
            ])
            ->assertRedirect();

        $customer = MarketerCustomer::query()->where('customer_name', 'Sara')->firstOrFail();

        $this->assertSame('6.00', $customer->commission_rate);
        $this->assertSame('6.00', $customer->commission_amount);
    }

    public function test_deleting_a_customer_from_a_different_marketer_is_rejected(): void
    {
        $admin = Admin::factory()->create();
        $marketerA = Marketer::factory()->create();
        $marketerB = Marketer::factory()->create();
        $customer = MarketerCustomer::factory()->create(['marketer_id' => $marketerB->id]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.marketers.customers.destroy', [$marketerA, $customer]))
            ->assertNotFound();

        $this->assertDatabaseHas('marketer_customers', ['id' => $customer->id]);
    }
}

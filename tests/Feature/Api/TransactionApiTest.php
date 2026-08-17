<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/transactions')->assertUnauthorized();
        $this->postJson('/api/v1/transactions', [])->assertUnauthorized();
        $this->getJson('/api/v1/categories')->assertUnauthorized();
    }

    public function test_user_can_record_a_daily_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Food & Drinks',
            'type' => 'expense',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/transactions', [
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'description' => 'Lunch',
            'payee' => 'Warteg',
            'transaction_date' => '2026-08-17',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'expense')
            ->assertJsonPath('data.amount', 50000)
            ->assertJsonPath('data.category', 'Food & Drinks');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 50000,
            'transaction_date' => '2026-08-17',
        ]);
    }

    public function test_category_must_belong_to_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $category = Category::create([
            'user_id' => $other->id,
            'name' => 'Someone else\'s category',
            'type' => 'expense',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 1000,
            'transaction_date' => '2026-08-17',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    }

    public function test_user_can_list_their_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Transportation',
            'type' => 'expense',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 25000,
            'transaction_date' => '2026-08-17',
        ])->assertCreated();

        $this->getJson('/api/v1/transactions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.amount', 25000)
            ->assertJsonPath('data.0.category', 'Transportation');
    }

    public function test_user_can_record_a_transaction_by_category_name(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/transactions', [
            'category_name' => 'Coffee',
            'type' => 'expense',
            'amount' => 20000,
            'transaction_date' => '2026-08-17',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.category', 'Coffee')
            ->assertJsonPath('category_created', true);

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Coffee',
            'type' => 'expense',
        ]);
    }

    public function test_category_name_reuses_existing_category(): void
    {
        $user = User::factory()->create();
        Category::create([
            'user_id' => $user->id,
            'name' => 'Coffee',
            'type' => 'expense',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'category_name' => 'Coffee',
            'type' => 'expense',
            'amount' => 15000,
            'transaction_date' => '2026-08-17',
        ])->assertCreated()
            ->assertJsonPath('category_created', false)
            ->assertJsonPath('data.category', 'Coffee');

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_category_id_and_category_name_cannot_both_be_provided(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Food & Drinks',
            'type' => 'expense',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'category_id' => $category->id,
            'category_name' => 'Food & Drinks',
            'type' => 'expense',
            'amount' => 1000,
            'transaction_date' => '2026-08-17',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('category_name');
    }

    public function test_receipt_scan_can_post_with_minimal_fields(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/transactions', [
            'amount' => 45000,
            'merchant' => 'Alfamart',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'expense')
            ->assertJsonPath('data.payee', 'Alfamart')
            ->assertJsonPath('data.category', 'Uncategorized')
            ->assertJsonPath('data.transaction_date', now()->format('Y-m-d'))
            ->assertJsonPath('category_created', true);

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Uncategorized',
            'type' => 'expense',
        ]);
    }

    public function test_bulk_endpoint_records_valid_items_and_reports_invalid_ones(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/transactions/bulk', [
            'transactions' => [
                ['amount' => 20000, 'merchant' => 'Warteg', 'category_name' => 'Food & Drinks'],
                ['amount' => 80000, 'merchant' => 'SPBU', 'category_name' => 'Fuel'],
                ['amount' => -5, 'merchant' => 'Bad'],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(2, 'created')
            ->assertJsonCount(1, 'errors')
            ->assertJsonPath('created.0.payee', 'Warteg')
            ->assertJsonPath('errors.0.index', 2);

        $this->assertDatabaseCount('transactions', 2);
    }
}

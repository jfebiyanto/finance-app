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
}

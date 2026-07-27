<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $defaultCategories = [
            ['name' => 'Food & Drinks', 'type' => 'expense', 'icon' => '🍕', 'color' => '#ef4444'],
            ['name' => 'Transportation', 'type' => 'expense', 'icon' => '🚗', 'color' => '#f97316'],
            ['name' => 'Shopping', 'type' => 'expense', 'icon' => '🛍️', 'color' => '#eab308'],
            ['name' => 'Entertainment', 'type' => 'expense', 'icon' => '🎬', 'color' => '#a855f7'],
            ['name' => 'Utilities', 'type' => 'expense', 'icon' => '💡', 'color' => '#06b6d4'],
            ['name' => 'Rent', 'type' => 'expense', 'icon' => '🏠', 'color' => '#3b82f6'],
            ['name' => 'Healthcare', 'type' => 'expense', 'icon' => '🏥', 'color' => '#ec4899'],
            ['name' => 'Education', 'type' => 'expense', 'icon' => '📚', 'color' => '#8b5cf6'],
            ['name' => 'Salary', 'type' => 'income', 'icon' => '💰', 'color' => '#22c55e'],
            ['name' => 'Freelance', 'type' => 'income', 'icon' => '💻', 'color' => '#10b981'],
            ['name' => 'Business', 'type' => 'income', 'icon' => '🏪', 'color' => '#14b8a6'],
            ['name' => 'Gifts', 'type' => 'income', 'icon' => '🎁', 'color' => '#f43f5e'],
            ['name' => 'Loan', 'type' => 'debt', 'icon' => '🏦', 'color' => '#7c3aed'],
            ['name' => 'Credit Card', 'type' => 'debt', 'icon' => '💳', 'color' => '#6366f1'],
            ['name' => 'Stocks', 'type' => 'investment', 'icon' => '📈', 'color' => '#2563eb'],
            ['name' => 'Crypto', 'type' => 'investment', 'icon' => '🪙', 'color' => '#f59e0b'],
            ['name' => 'Gold', 'type' => 'investment', 'icon' => '🥇', 'color' => '#d97706'],
        ];

        foreach ($users as $user) {
            foreach ($defaultCategories as $category) {
                Category::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $category['name'],
                        'type' => $category['type'],
                    ],
                    $category + ['user_id' => $user->id]
                );
            }
        }
    }
}

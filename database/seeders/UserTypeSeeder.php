<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create or update an account manager user
        $accountManager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Account Manager',
                'password' => Hash::make('password'),
                'user_type' => 'account_manager',
                'is_active' => true,
            ]
        );

        // Create or update an approver user
        $approver = User::firstOrCreate(
            ['email' => 'approver@example.com'],
            [
                'name' => 'Bill Approver',
                'password' => Hash::make('password'),
                'user_type' => 'approver',
                'is_active' => true,
            ]
        );

        // Update the first existing user (if any) to be an account manager
        $firstUser = User::whereNotIn('email', ['manager@example.com', 'approver@example.com'])->first();
        if ($firstUser) {
            $firstUser->update([
                'user_type' => 'account_manager',
                'is_active' => true,
            ]);
        }

        $this->command->info('User types seeded successfully!');
        $this->command->info('Account Manager: manager@example.com (password: password)');
        $this->command->info('Approver: approver@example.com (password: password)');
    }
}

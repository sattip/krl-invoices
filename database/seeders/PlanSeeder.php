<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small businesses',
                'price' => 29.00,
                'invoice_limit' => 50,
                'features' => [
                    'AI-powered extraction',
                    'REST API access',
                    'Email support',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing businesses',
                'price' => 79.00,
                'invoice_limit' => 200,
                'features' => [
                    'AI-powered extraction',
                    'REST API access',
                    'Priority support',
                    'Faster processing',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large organizations',
                'price' => 199.00,
                'invoice_limit' => 1000,
                'features' => [
                    'AI-powered extraction',
                    'REST API access',
                    'Dedicated account manager',
                    'Priority support',
                    'Custom integrations',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}

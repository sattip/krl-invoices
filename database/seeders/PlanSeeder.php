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
                'description' => 'Για μικρές επιχειρήσεις',
                'price' => 29.00,
                'invoice_limit' => 50,
                'features' => ['Εξαγωγή δεδομένων με τεχνητή νοημοσύνη', 'Πρόσβαση στο REST API', 'Υποστήριξη μέσω email'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Για επιχειρήσεις σε ανάπτυξη',
                'price' => 79.00,
                'invoice_limit' => 200,
                'features' => ['Εξαγωγή δεδομένων με τεχνητή νοημοσύνη', 'Πρόσβαση στο REST API', 'Υποστήριξη μέσω email'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Για μεγαλύτερο όγκο',
                'price' => 199.00,
                'invoice_limit' => 1000,
                'features' => ['Εξαγωγή δεδομένων με τεχνητή νοημοσύνη', 'Πρόσβαση στο REST API', 'Υποστήριξη μέσω email'],
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

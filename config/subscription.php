<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the subscription and billing system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Plans
    |--------------------------------------------------------------------------
    |
    | Default plan configuration used by the PlanSeeder.
    |
    */
    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'invoice_limit' => 50,
            'price' => 29,
        ],
        'professional' => [
            'name' => 'Professional',
            'invoice_limit' => 200,
            'price' => 79,
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'invoice_limit' => 1000,
            'price' => 199,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Number of days after a payment fails before access is blocked.
    |
    */
    'grace_period_days' => 3,

    /*
    |--------------------------------------------------------------------------
    | Usage Warning Threshold
    |--------------------------------------------------------------------------
    |
    | Percentage at which to show usage warnings to users.
    |
    */
    'warning_threshold' => 80,

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The currency used for subscriptions.
    |
    */
    'currency' => 'eur',
];

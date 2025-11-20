<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;

class AssignUserToCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-company
                            {email : The email of the user}
                            {--company= : The company ID or slug}
                            {--create-company= : Create a new company with this name}
                            {--role=owner : The role (owner, admin, member)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a user to a company or create a new company for them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $role = $this->option('role');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Get or create company
        $company = null;

        if ($this->option('create-company')) {
            $companyName = $this->option('create-company');
            $company = Company::create([
                'name' => $companyName,
                'email' => $email,
            ]);
            $this->info("Created new company: {$companyName}");
        } elseif ($this->option('company')) {
            $companyIdentifier = $this->option('company');
            $company = Company::where('id', $companyIdentifier)
                ->orWhere('slug', $companyIdentifier)
                ->first();

            if (!$company) {
                $this->error("Company '{$companyIdentifier}' not found.");
                return 1;
            }
        } else {
            // List existing companies and let user choose
            $companies = Company::all();

            if ($companies->isEmpty()) {
                $this->error("No companies exist. Use --create-company to create one.");
                return 1;
            }

            $this->info("Available companies:");
            $companies->each(function ($c) {
                $this->line("  [{$c->id}] {$c->name} ({$c->slug})");
            });

            $companyId = $this->ask('Enter company ID');
            $company = Company::find($companyId);

            if (!$company) {
                $this->error("Company not found.");
                return 1;
            }
        }

        // Validate role
        if (!in_array($role, ['owner', 'admin', 'member'])) {
            $this->error("Invalid role. Must be: owner, admin, or member.");
            return 1;
        }

        // Assign user to company
        $user->update([
            'company_id' => $company->id,
            'role' => $role,
        ]);

        $this->info("User '{$user->name}' assigned to '{$company->name}' as {$role}.");
        return 0;
    }
}

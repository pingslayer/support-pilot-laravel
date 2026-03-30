<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Jobs\ProcessIncomingMessageJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SimulateSupportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simulate-support {--body= : The customer message body}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate an incoming customer support email to trigger the AI Reasoning Loop.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🚀 Starting AI Support Agent Simulation...");

        // 1. Ensure a Tenant and Knowledge Base exists
        $tenant = Tenant::first() ?: Tenant::create([
            'name' => 'Test SaaS Corp',
            'api_key' => 'test_api_key_' . Str::random(8),
        ]);

        if ($tenant->knowledgeBaseItems()->count() === 0) {
            $this->comment("Seeding sample Knowledge Base Item...");
            $item = $tenant->knowledgeBaseItems()->create([
                'title' => 'Standard Return Policy',
                'content' => 'Our return policy allows customers to return items within 30 days of purchase. Items must be in original condition with tags attached. Refunds are processed within 5-7 business days.',
            ]);

            $this->comment("Syncing Knowledge Base to Local PostgreSQL (pgvector)...");
            (new \App\Services\Support\KnowledgeBaseService())->sync($item);
            $tenant->refresh();
        }

        $this->line("Tenant: <info>{$tenant->name}</info> (ID: {$tenant->id})");
        $this->line("Knowledge Sync: <comment>Local (Postgres)</comment>");

        // 2. Prepare Mock Data
        $fromEmail = 'customer@example.com';
        $fromName = 'John Doe';
        $subject = 'Inquiry';
        $body = $this->option('body') ?: 'Hi, I want to know what your return policy is. How many days do I have?';

        $this->line("Customer: <info>{$fromName} ({$fromEmail})</info>");
        $this->line("Message: <comment>{$body}</comment>");

        // 3. Dispatch the Job
        $this->comment("Dispatching ProcessIncomingMessageJob...");

        ProcessIncomingMessageJob::dispatch(
            $tenant->id,
            $fromEmail,
            $fromName,
            $subject,
            $body
        );

        $this->info("✅ Webhook simulation complete!");
        $this->line("");
        $this->warn("IMPORTANT: Ensure your queue worker is running: 'php artisan queue:work'");
        $this->warn("Monitor your logs to see the AI reasoning: 'tail -f storage/logs/laravel.log'");
    }
}

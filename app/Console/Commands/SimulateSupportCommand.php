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

        // 1. Ensure a Tenant exists
        $tenant = Tenant::first() ?: Tenant::create([
            'name' => 'Test SaaS Corp',
            'api_key' => 'test_api_key_' . Str::random(8),
        ]);

        $this->line("Tenant: <info>{$tenant->name}</info> (ID: {$tenant->id})");

        // 2. Prepare Mock Data
        $fromEmail = 'customer@example.com';
        $fromName = 'John Doe';
        $subject = 'Order Inquiry';
        $body = $this->option('body') ?: 'Hi, I would like to know the status of my order #ORD-12345. Can you help?';

        $this->line("Customer: <info>{$fromName} ({$fromEmail})</info>");
        $this->line("Message: <comment>{$body}</comment>");

        // 3. Dispatch the Job synchronously for easier testing if configured, 
        // but ewe'll use dispatch() to follow the real production flow.
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

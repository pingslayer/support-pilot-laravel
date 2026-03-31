<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Jobs\ProcessIncomingMessageJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SimulateSupportCommandForTenent1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simulate-support-1 {--body= : The customer message body}';

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

        // Prepare Mock Data
        $fromEmail = 'customer@example.com';
        $fromName = 'John Doe';
        $subject = 'Inquiry';
        $body = $this->option('body') ?: 'Hi, I want to know what your return policy is. How many days do I have?';

        $this->line("Customer: <info>{$fromName} ({$fromEmail})</info>");
        $this->line("Message: <comment>{$body}</comment>");

        // 3. Dispatch the Job
        $this->comment("Dispatching ProcessIncomingMessageJob...");

        ProcessIncomingMessageJob::dispatch(
            1,
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

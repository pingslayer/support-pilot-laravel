<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Message;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIncomingMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $tenantId,
        public string $fromEmail,
        public string $fromName,
        public string $subject,
        public string $body
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Resolve Customer
        $customer = Customer::withoutGlobalScopes()->firstOrCreate(
            [
                'tenant_id' => $this->tenantId,
                'email' => $this->fromEmail
            ],
            ['name' => $this->fromName]
        );

        // Basic Threading: Find an existing open ticket by the same customer and subject
        $ticket = Ticket::withoutGlobalScopes()
            ->where('tenant_id', $this->tenantId)
            ->where('customer_id', $customer->id)
            ->where('subject', $this->subject)
            ->where('status', 'open')
            ->first();

        // If no open ticket exists, create one
        if (!$ticket) {
            $ticket = Ticket::create([
                'tenant_id' => $this->tenantId,
                'customer_id' => $customer->id,
                'subject' => $this->subject,
                'status' => 'open'
            ]);
        }

        // Add the message to the ticket
        Message::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'customer',
            'role' => 'user',
            'content' => $this->body
        ]);

        // Classify Intent (Phase 1 AI Integration)
        $intentService = new \App\Services\Ai\IntentService();
        $intent = $intentService->classify($this->body);

        Log::info("Ticket #{$ticket->id} (Tenant #{$this->tenantId}) classified as: {$intent}");
    }
}

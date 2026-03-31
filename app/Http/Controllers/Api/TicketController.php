<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the tenant's tickets.
     */
    public function index()
    {
        // TenantScope automatically filters this list
        return response()->json(
            Ticket::with('customer')->latest()->get()
        );
    }

    /**
     * Display the specified ticket with its message history.
     */
    public function show(string $id)
    {
        // TenantScope ensures a user cannot see a ticket from another tenant
        $ticket = Ticket::with(['customer', 'messages'])->findOrFail($id);

        return response()->json($ticket);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the tenant's customers.
     */
    public function index()
    {
        // TenantScope automatically filters this list
        return response()->json(
            Customer::withCount('tickets')->latest()->get()
        );
    }
}

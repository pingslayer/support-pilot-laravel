<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'api_key',
        'external_api_url',
        'external_api_key',
        'vector_store_id',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function knowledgeBaseItems(): HasMany
    {
        return $this->hasMany(KnowledgeBaseItem::class);
    }
}

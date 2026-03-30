<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseChunk extends Model
{
    /** @use HasFactory<\Database\Factories\KnowledgeBaseChunkFactory> */
    use HasFactory;

    protected $fillable = [
        'knowledge_base_item_id',
        'content',
        'embedding',
    ];

    /**
     * Get the item that owns this chunk.
     */
    public function knowledgeBaseItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseItem::class);
    }
}

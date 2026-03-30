<?php

namespace App\Services\Support;

use App\Models\KnowledgeBaseItem;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Stores;

class KnowledgeBaseService
{
    /**
     * Sync a specific knowledge base item to the local database.
     */
    public function sync(KnowledgeBaseItem $item): void
    {
        // 1. Clear existing chunks for a fresh sync
        $item->chunks()->delete();

        // 2. Split content into overlapping chunks (Sliding Window)
        $chunks = $this->chunkText($item->content);

        // 3. Generate Embeddings for all chunks in one batch
        $response = \Laravel\Ai\Embeddings::for($chunks)->generate();
        $vectors = $response->embeddings;

        // 4. Store Chunks and Embeddings locally
        foreach ($chunks as $index => $content) {
            $embedding = $vectors[$index];
            
            // We use a raw SQL insert for the vector column
            $chunk = $item->chunks()->create(['content' => $content]);
            
            // Convert numerical array to PostgreSQL vector format "[1.2, 3.4, ...]"
            $vectorString = '[' . implode(',', $embedding) . ']';
            
            \Illuminate\Support\Facades\DB::statement(
                'UPDATE knowledge_base_chunks SET embedding = ?::vector WHERE id = ?',
                [$vectorString, $chunk->id]
            );
        }

        $item->update(['last_synced_at' => now()]);

        Log::info("Synced KnowledgeBaseItem #{$item->id} locally with " . count($chunks) . " chunks.");
    }

    /**
     * Splits text into smaller chunks for improved semantic search accuracy.
     */
    private function chunkText(string $text, int $size = 800, int $overlap = 150): array
    {
        $chunks = [];
        $start = 0;
        $totalLength = mb_strlen($text);

        if ($totalLength <= $size) {
            return [$text];
        }

        while ($start < $totalLength) {
            $chunks[] = mb_substr($text, $start, $size);
            $start += ($size - $overlap);
        }

        return $chunks;
    }

    /**
     * Local RAG doesn't require managed vector stores.
     */
    public function ensureVectorStore(Tenant $tenant): string
    {
        return 'local';
    }

    /**
     * Delete local chunks.
     */
    public function delete(KnowledgeBaseItem $item): void
    {
        $item->chunks()->delete();
        Log::info("Deleted local KnowledgeBaseItem chunks.");
    }
}

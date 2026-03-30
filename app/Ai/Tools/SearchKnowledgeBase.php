<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchKnowledgeBase implements Tool
{
    /**
     * Create a new tool instance.
     */
    public function __construct(protected int $tenantId) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the company knowledge base for policies, FAQs, and procedures relating to the customer query.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('The search query to look up in the knowledge base.')->required(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = $request->input('query');

        // 1. Generate an embedding for the search query
        $response = Embeddings::for($query)->generate();
        $queryVector = '[' . implode(',', $response->embeddings[0]) . ']';

        // 2. Perform Nearest Neighbor Search using pgvector's <=> (cosine distance)
        // We scope by tenant_id to ensure multi-tenancy.
        $results = DB::table('knowledge_base_chunks')
            ->join('knowledge_base_items', 'knowledge_base_chunks.knowledge_base_item_id', '=', 'knowledge_base_items.id')
            ->where('knowledge_base_items.tenant_id', $this->tenantId)
            ->select('knowledge_base_chunks.content')
            ->orderByRaw('embedding <=> ?::vector', [$queryVector])
            ->limit(3)
            ->get();

        if ($results->isEmpty()) {
            return "No relevant information found in the knowledge base for '{$query}'.";
        }

        // 3. Return the combined text pieces to the AI
        return $results->map(fn ($r) => "Snippet: " . $r->content)->implode("\n\n---\n\n");
    }
}

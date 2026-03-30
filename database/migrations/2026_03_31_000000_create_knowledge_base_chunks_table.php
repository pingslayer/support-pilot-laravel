<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This table stores the actual chunks of text and their vector embeddings
        Schema::create('knowledge_base_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_base_item_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            // We use raw SQL for the vector column as Blueprint doesn't support it natively yet
            $table->timestamps();
        });

        // Add the vector column (1536 dimensions for OpenAI text-embedding-3-small)
        DB::statement('ALTER TABLE knowledge_base_chunks ADD COLUMN embedding vector(1536)');
        
        // Add a vector index for fast similarity search
        DB::statement('CREATE INDEX ON knowledge_base_chunks USING hnsw (embedding vector_cosine_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_chunks');
    }
};

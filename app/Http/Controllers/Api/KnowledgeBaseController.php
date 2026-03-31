<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBaseItem;
use App\Services\Support\KnowledgeBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KnowledgeBaseController extends Controller
{
    /**
     * Display a listing of the tenant's knowledge base items.
     */
    public function index()
    {
        // TenantScope automatically filters this list
        return response()->json(
            KnowledgeBaseItem::select('id', 'title', 'last_synced_at', 'created_at', 'updated_at')
                ->latest()
                ->get()
        );
    }

    /**
     * Store a newly created item and sync it to the pgvector knowledge base.
     */
    public function store(Request $request, KnowledgeBaseService $kbService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $item = DB::transaction(function () use ($request, $validated, $kbService) {
            $item = KnowledgeBaseItem::create([
                'tenant_id' => $request->user()->tenant_id,
                'title' => $validated['title'],
                'content' => $validated['content'],
            ]);

            // Synchronously chunk text and generate embeddings
            $kbService->sync($item);

            return $item;
        });

        return response()->json($item, 201);
    }

    /**
     * Display the specified knowledge base item.
     */
    public function show(string $id)
    {
        // TenantScope ensures checking ownership
        $item = KnowledgeBaseItem::findOrFail($id);

        return response()->json($item);
    }

    /**
     * Update the specified knowledge base item and re-sync embeddings.
     */
    public function update(Request $request, string $id, KnowledgeBaseService $kbService)
    {
        $item = KnowledgeBaseItem::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $requiresSync = $item->content !== $validated['content'];

        DB::transaction(function () use ($item, $validated, $kbService, $requiresSync) {
            $item->update($validated);

            if ($requiresSync) {
                $kbService->sync($item);
            }
        });

        return response()->json($item);
    }

    /**
     * Remove the specified knowledge base item and its vector chunks.
     */
    public function destroy(string $id, KnowledgeBaseService $kbService)
    {
        $item = KnowledgeBaseItem::findOrFail($id);

        DB::transaction(function () use ($item, $kbService) {
            // Service will delete associated chunks
            $kbService->delete($item);
            $item->delete();
        });

        return response()->json(null, 204);
    }
}

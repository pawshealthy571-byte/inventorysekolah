<?php

namespace App\Http\Controllers;

use App\Services\ItemAssistantService;
use App\Services\ItemCreationService;
use App\Services\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemAssistantController extends Controller
{
    /**
     * Parse a natural-language command and respond using Gemini.
     */
    public function store(
        Request $request,
        \App\Services\GeminiService $geminiService,
        \App\Services\ItemAssistantService $itemAssistantService,
        \App\Services\ItemCreationService $itemCreationService,
        \App\Services\StockMovementService $stockMovementService,
    ): JsonResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $message = $validated['message'];

        // If it looks like a command to add an item (has keywords), try parsing it
        $isItemCommand = preg_match('/\b(tambah|simpan|buat|input|masukkan|add|save|create)\b.*\b(barang|item|produk)\b/iu', $message);
        
        if ($isItemCommand) {
            $draft = $itemAssistantService->buildDraft($message);

            // If we have enough info to create an item
            if ($draft['missing'] === []) {
                try {
                    $itemData = $itemCreationService->validate($draft['attributes']);
                    $item = $itemCreationService->create($itemData, $stockMovementService);

                    return response()->json([
                        'ok' => true,
                        'message' => "Barang {$item->name} berhasil ditambahkan ke database.",
                        'summary' => $draft['summary'],
                        'warnings' => $draft['warnings'],
                        'item_id' => $item->id,
                        'redirect_url' => route('barang.show', $item),
                    ]);
                } catch (ValidationException $exception) {
                    // Fallback to general AI if validation fails
                }
            }
        }

        // General AI response for anything else
        $aiResponse = $geminiService->generateResponse($message);

        return response()->json([
            'ok' => true,
            'message' => $aiResponse,
            'summary' => [],
            'warnings' => [],
        ]);
    }
}

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
     * Parse a natural-language item command and store it.
     */
    public function store(
        Request $request,
        ItemAssistantService $itemAssistantService,
        ItemCreationService $itemCreationService,
        StockMovementService $stockMovementService,
    ): JsonResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $draft = $itemAssistantService->buildDraft($validated['message']);

        if ($draft['missing'] !== []) {
            return response()->json([
                'ok' => false,
                'message' => 'Data belum bisa disimpan. Informasi yang masih kurang: ' . implode(', ', $draft['missing']) . '.',
                'summary' => $draft['summary'],
                'warnings' => $draft['warnings'],
                'missing' => $draft['missing'],
            ], 422);
        }

        try {
            $itemData = $itemCreationService->validate($draft['attributes']);
            $item = $itemCreationService->create($itemData, $stockMovementService);
        } catch (ValidationException $exception) {
            return response()->json([
                'ok' => false,
                'message' => 'Data hasil parsing belum valid untuk disimpan.',
                'summary' => $draft['summary'],
                'warnings' => $draft['warnings'],
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => "Barang {$item->name} berhasil ditambahkan ke database.",
            'summary' => $draft['summary'],
            'warnings' => $draft['warnings'],
            'item_id' => $item->id,
            'redirect_url' => route('barang.show', $item),
        ]);
    }
}

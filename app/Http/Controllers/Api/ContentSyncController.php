<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ContentSyncService;

class ContentSyncController extends Controller
{
    private ContentSyncService $syncService;

    public function __construct(ContentSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Manually trigger content synchronization
     */
    public function sync()
    {
        $result = $this->syncService->syncAllProviders();

        // Store sync status for future reference
        $this->syncService->storeLastSyncStatus($result);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? "Successfully synced {$result['total_synced']} items"
                : 'Sync completed with errors',
            'data' => $result
        ], $result['success'] ? 200 : 207);
    }

    /**
     * Get last sync status
     */
    public function status()
    {
        $status = $this->syncService->getLastSyncStatus();

        return response()->json([
            'success' => true,
            'data' => $status ?? [
                'message' => 'No sync has been performed yet'
            ]
        ]);
    }
}

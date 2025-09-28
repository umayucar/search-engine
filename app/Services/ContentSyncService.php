<?php

namespace App\Services;

use App\Services\Providers\JsonProvider;
use App\Services\Providers\XmlProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContentSyncService
{
    private array $providers = [];

    public function __construct()
    {
        // Initialize providers with URLs
        $this->providers = [
            new JsonProvider(
                'https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider1',
                'JSON_Provider'
            ),
            new XmlProvider(
                'https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider2', 
                'XML_Provider'
            )
        ];
    }

    /**
     * Sync data from all providers
     */
    public function syncAllProviders(): array
    {
        $results = [];
        $totalSynced = 0;
        $errors = [];

        foreach ($this->providers as $provider) {
            $result = $provider->syncData();
            $results[] = $result;

            if ($result['success']) {
                $totalSynced += $result['synced_count'];
            } else {
                $errors[] = $result['error'];
            }
        }

        // Clear search cache after sync
        Cache::flush();

        Log::info("Content sync completed. Total synced: {$totalSynced}");

        return [
            'total_synced' => $totalSynced,
            'provider_results' => $results,
            'errors' => $errors,
            'success' => empty($errors)
        ];
    }

    /**
     * Get sync status from cache
     */
    public function getLastSyncStatus(): ?array
    {
        return Cache::get('last_sync_status');
    }

    /**
     * Store sync status in cache
     */
    public function storeLastSyncStatus(array $status): void
    {
        Cache::put('last_sync_status', array_merge($status, [
            'synced_at' => now()->toISOString()
        ]), now()->addHour());
    }
}

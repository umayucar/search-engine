<?php

namespace App\Services\Providers;

use App\Models\Content;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class AbstractProvider
{
    protected string $url;
    protected string $providerName;

    public function __construct(string $url, string $providerName)
    {
        $this->url = $url;
        $this->providerName = $providerName;
    }

    /**
     * Fetch and sync data from provider
     */
    public function syncData(): array
    {
        try {
            $response = Http::timeout(30)->get($this->url);
            
            if (!$response->successful()) {
                throw new \Exception("HTTP request failed with status: " . $response->status());
            }

            $rawData = $response->body();
            $parsedData = $this->parseData($rawData);
            $mappedData = $this->mapToStandardFormat($parsedData);
            
            $syncedCount = 0;
            foreach ($mappedData as $item) {
                $content = Content::updateOrCreate(
                    [
                        'provider_id' => $item['provider_id'],
                        'provider_name' => $item['provider_name']
                    ],
                    $item
                );
                
                // Calculate and update score
                $content->calculateScore();
                $syncedCount++;
            }

            Log::info("Synced {$syncedCount} items from {$this->providerName}");
            
            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'provider' => $this->providerName
            ];

        } catch (\Exception $e) {
            Log::error("Error syncing data from {$this->providerName}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->providerName
            ];
        }
    }

    /**
     * Parse raw data from provider
     */
    abstract protected function parseData(string $rawData): array;

    /**
     * Map provider data to standard format
     */
    abstract protected function mapToStandardFormat(array $data): array;
}

<?php

namespace App\Services\Providers;

use Carbon\Carbon;

class JsonProvider extends AbstractProvider
{
    /**
     * Parse JSON data
     */
    protected function parseData(string $rawData): array
    {
        $data = json_decode($rawData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON data: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Map JSON provider data to standard format
     */
    protected function mapToStandardFormat(array $data): array
    {
        $mappedItems = [];

        if (!isset($data['contents']) || !is_array($data['contents'])) {
            throw new \Exception('Invalid JSON structure: missing contents array');
        }

        foreach ($data['contents'] as $item) {
            $mappedItems[] = [
                'provider_id' => $item['id'],
                'provider_name' => $this->providerName,
                'title' => $item['title'],
                'type' => $this->normalizeContentType($item['type']),
                'tags' => $item['tags'] ?? [],
                'views' => $item['metrics']['views'] ?? null,
                'likes' => $item['metrics']['likes'] ?? null,
                'duration' => $item['metrics']['duration'] ?? null,
                'reading_time' => null,
                'reactions' => null,
                'comments' => null,
                'published_at' => $this->parseDateTime($item['published_at']),
            ];
        }

        return $mappedItems;
    }

    /**
     * Normalize content type to match our enum
     */
    private function normalizeContentType(string $type): string
    {
        return match (strtolower($type)) {
            'video' => 'video',
            'article', 'text' => 'article',
            default => 'article'
        };
    }

    /**
     * Parse ISO datetime string
     */
    private function parseDateTime(string $datetime): Carbon
    {
        return Carbon::parse($datetime);
    }
}

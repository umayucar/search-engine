<?php

namespace App\Services\Providers;

use Carbon\Carbon;

class XmlProvider extends AbstractProvider
{
    /**
     * Parse XML data
     */
    protected function parseData(string $rawData): array
    {
        // Disable XML external entity loading for security
        libxml_disable_entity_loader(true);
        
        $xml = simplexml_load_string($rawData, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        if ($xml === false) {
            throw new \Exception('Invalid XML data');
        }

        // Convert SimpleXML to array
        return json_decode(json_encode($xml), true);
    }

    /**
     * Map XML provider data to standard format
     */
    protected function mapToStandardFormat(array $data): array
    {
        $mappedItems = [];
        
        if (!isset($data['items']['item'])) {
            throw new \Exception('Invalid XML structure: missing items/item');
        }

        $items = $data['items']['item'];
        
        // Handle case where there's only one item (not an array)
        if (!isset($items[0])) {
            $items = [$items];
        }

        foreach ($items as $item) {
            $mappedItems[] = [
                'provider_id' => $item['id'],
                'provider_name' => $this->providerName,
                'title' => $item['headline'],
                'type' => $this->normalizeContentType($item['type']),
                'tags' => $this->extractTags($item['categories']),
                'views' => $item['stats']['views'] ?? null,
                'likes' => $item['stats']['likes'] ?? null,
                'duration' => $item['stats']['duration'] ?? null,
                'reading_time' => $item['stats']['reading_time'] ?? null,
                'reactions' => $item['stats']['reactions'] ?? null,
                'comments' => $item['stats']['comments'] ?? null,
                'published_at' => $this->parseDateTime($item['publication_date']),
            ];
        }

        return $mappedItems;
    }

    /**
     * Extract tags from categories structure
     */
    private function extractTags(array $categories): array
    {
        if (!isset($categories['category'])) {
            return [];
        }

        $categoryData = $categories['category'];
        
        // Handle single category vs multiple categories
        if (is_string($categoryData)) {
            return [$categoryData];
        }
        
        if (is_array($categoryData)) {
            return $categoryData;
        }

        return [];
    }

    /**
     * Normalize content type to match our enum
     */
    private function normalizeContentType(string $type): string
    {
        return match(strtolower($type)) {
            'video' => 'video',
            'article', 'text' => 'article',
            default => 'article'
        };
    }

    /**
     * Parse date string
     */
    private function parseDateTime(string $datetime): Carbon
    {
        return Carbon::parse($datetime);
    }
}

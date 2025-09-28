<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\Content;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    private const CACHE_MINUTES = 10;
    private const STATS_CACHE_MINUTES = 30;

    /**
     * Search for content with filters and pagination
     */
    public function search(SearchRequest $request)
    {
        $validatedData = $request->validated();
        $params = array_merge([
            'sort' => 'relevance',
            'order' => 'desc',
            'per_page' => 10,
            'page' => 1,
        ], $validatedData);

        $cacheKey = 'search:' . md5(http_build_query($params));

        $results = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_MINUTES), function () use ($params) {
            $builder = Content::query();

            if ($params['query'] ?? null) {
                $builder->search($params['query']);
            }

            if ($params['type'] ?? null) {
                $builder->ofType($params['type']);
            }

            switch ($params['sort']) {
                case 'relevance':
                    $builder->orderByRelevance($params['order']);
                    break;
                case 'date':
                    $builder->orderBy('published_at', $params['order']);
                    break;
                case 'popularity':
                    $builder->orderBy('score', $params['order']);
                    break;
                default:
                    $builder->orderByRelevance($params['order']);
            }

            return $builder->paginate($params['per_page']);
        });

        return response()->json([
            'success' => true,
            'data' => collect($results->items())->map(function ($item) {
                $item->score = (float) $item->score;
                return $item;
            }),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
            ],
            'filters' => $params,
        ]);
    }

    /**
     * Get content statistics
     */
    public function stats()
    {
        $stats = Cache::remember('content_stats', now()->addMinutes(self::STATS_CACHE_MINUTES), function () {
            return Content::getContentStats();
        });

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}

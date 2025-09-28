<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\Content;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    /**
     * Search for content with filters and pagination
     */
    public function search(SearchRequest $request)
    {
        $query = $request->input('query');
        $type = $request->input('type');
        $sort = $request->input('sort', 'relevance');
        $order = $request->input('order', 'desc');
        $perPage = $request->input('per_page', 10);

        // Create cache key based on search parameters
        $cacheKey = 'search:' . md5(serialize([
            'query' => $query,
            'type' => $type,
            'sort' => $sort,
            'order' => $order,
            'page' => $request->input('page', 1),
            'per_page' => $perPage
        ]));

        // Try to get results from cache
        $results = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query, $type, $sort, $order, $perPage) {
            $builder = Content::query();

            if ($query) {
                $builder->search($query);
            }

            if ($type) {
                $builder->ofType($type);
            }

            switch ($sort) {
                case 'relevance':
                    $builder->orderByRelevance($order);
                    break;
                case 'date':
                    $builder->orderBy('published_at', $order);
                    break;
                case 'popularity':
                    $builder->orderBy('score', $order);
                    break;
                default:
                    $builder->orderByRelevance($order);
            }

            return $builder->paginate($perPage);
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
            'filters' => [
                'query' => $query,
                'type' => $type,
                'sort' => $sort,
                'order' => $order
            ]
        ]);
    }

    /**
     * Get content statistics
     */
    public function stats()
    {
        $stats = Cache::remember('content_stats', now()->addMinutes(30), function () {
            return [
                'total_contents' => Content::count(),
                'total_videos' => Content::where('type', 'video')->count(),
                'total_articles' => Content::where('type', 'article')->count(),
                'avg_score' => (float) Content::avg('score'),
                'last_updated' => Content::latest('updated_at')->first()?->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}

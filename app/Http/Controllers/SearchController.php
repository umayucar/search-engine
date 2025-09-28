<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Content;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SearchController extends Controller
{
    private const CACHE_MINUTES = 30;

    public function index(SearchRequest $request)
    {
        $query = $request->input('query');
        $type = $request->input('type');
        $sort = $request->input('sort', 'relevance');
        $order = $request->input('order', 'desc');
        $perPage = $request->input('per_page', 10);

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

        $results = $builder->paginate($perPage);

        // Get content statistics
        $stats = Cache::remember('content_stats', now()->addMinutes(self::CACHE_MINUTES), function () {
            return Content::getContentStats();
        });

        return Inertia::render('Search', [
            'contents' => collect($results->items())->map(function ($item) {
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
            ],
            'stats' => $stats
        ]);
    }
}

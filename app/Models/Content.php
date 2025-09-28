<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Content extends Model
{
    protected $fillable = [
        'provider_id',
        'provider_name',
        'title',
        'type',
        'tags',
        'views',
        'likes',
        'duration',
        'reading_time',
        'reactions',
        'comments',
        'published_at',
        'score'
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'score' => 'decimal:2'
    ];

    /**
     * Calculate and update the content score based on the given formula
     */
    public function calculateScore(): float
    {
        $baseScore = $this->getBaseScore();
        $contentTypeCoefficient = $this->getContentTypeCoefficient();
        $freshnessScore = $this->getFreshnessScore();
        $interactionScore = $this->getInteractionScore();

        $finalScore = ($baseScore * $contentTypeCoefficient) + $freshnessScore + $interactionScore;

        $this->update(['score' => $finalScore]);

        return $finalScore;
    }

    /**
     * Get base score based on content type
     */
    private function getBaseScore(): float
    {
        if ($this->type === 'video') {
            return ($this->views / 1000) + ($this->likes / 100);
        } else { // article
            return $this->reading_time + ($this->reactions / 50);
        }
    }

    /**
     * Get content type coefficient
     */
    private function getContentTypeCoefficient(): float
    {
        return $this->type === 'video' ? 1.5 : 1.0;
    }

    /**
     * Get freshness score based on publication date
     */
    private function getFreshnessScore(): int
    {
        $now = Carbon::now();
        $publishedAt = Carbon::parse($this->published_at);
        $daysDiff = $now->diffInDays($publishedAt);

        if ($daysDiff <= 7) {
            return 5;
        } elseif ($daysDiff <= 30) {
            return 3;
        } elseif ($daysDiff <= 90) {
            return 1;
        }

        return 0;
    }

    /**
     * Get interaction score
     */
    private function getInteractionScore(): float
    {
        if ($this->type === 'video' && $this->views > 0) {
            return ($this->likes / $this->views) * 10;
        } elseif ($this->type === 'article' && $this->reading_time > 0) {
            return ($this->reactions / $this->reading_time) * 5;
        }

        return 0;
    }

    /**
     * Scope for searching content
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'LIKE', "%{$keyword}%")
                ->orWhereJsonContains('tags', $keyword)
                ->orWhere('tags', 'LIKE', "%{$keyword}%");
        });
    }

    /**
     * Scope for filtering by content type
     */
    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('type', $type);
    }

    /**
     * Scope for ordering by score and relevance
     */
    public function scopeOrderByRelevance(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('score', $direction)
            ->orderBy('published_at', 'desc');
    }

    /**
     * Get content stats 
     */
    public function getContentStats(): array
    {
        return [
            'total_contents' => self::count(),
            'total_videos' => self::where('type', 'video')->count(),
            'total_articles' => self::where('type', 'article')->count(),
            'avg_score' => (float) self::avg('score'),
            'last_updated' => self::latest('updated_at')->first()?->updated_at,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
class Book extends Model
{
    use HasFactory;

    public function reviews(): HasMany{
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title): Builder{
        return $query->where(
            "title",
            "like",
            '%'. $title.'%'
        );
    }

    // public function scopePopular(Builder $query): Builder{
    //     return $query->withCount('reviews');
    // }

    public function scopePopular(Builder $query, string $from = null, string $to = null){
        $query->withCount([
            'reviews' => function (Builder $q) use ($from, $to){
                return $this->dateRangeFilter($q, $from, $to);
            }
            
        ]);

    }

    public function scopeHighestRated(Builder $query, string $from = null, string $to = null): Builder{
        return $query->withAvg(
            [
                'reviews' => function (Builder $q) use ($from, $to){
                                return $this->dateRangeFilter($q, $from, $to);
                            }
            ],
                'rating'
            );
    }

    public function scopeMinReviews(Builder $query, int $reviews){
        return $query->having('reviews_count', '>=', $reviews);
    }

    //private functions
    private function dateRangeFilter(Builder $query, string $from = null, string $to = null): Builder{
        if($from && is_null($to)) {
            return $query->where('created_at', '>=', $from);
        }
        else if(is_null($from) && $to){
            return $query->where('created_at', '<=', $to);
        }
        else if ($from && $to){
            return $query->whereBetween('created_at', [$from, $to]);
        }
        return $query;
    }
}

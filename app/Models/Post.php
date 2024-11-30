<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'slug',
        'image',
        'user_id',
        'category_id',
        'status'
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    protected static function booted()
    {
        static::creating(function (PostCategory $category) {
            $category->status = StatusEnum::ACTIVE->value;
            $category->slug = Str::slug($category->name);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class);
    }
}

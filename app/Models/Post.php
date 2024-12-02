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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'slug',
        'image',
        'user_id',
        'category_id',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => StatusEnum::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The "booted" method of the Post model.
     *
     * This method is called after the model is booted and registers model events
     * for creating, deleting, and restoring posts. During creation, it automatically
     * generates a slug from the post title and sets the status to active. When a post
     * is deleted, it sets the status to inactive and saves the change. When a post is
     * restored, it reverts the status back to active and saves the update.
     *
     * @return void
     */
    protected static function booted()
    {
        parent::boot();

        static::creating(function (Post $post) {
            $post->slug = Str::slug($post->title);
            $post->status = StatusEnum::ACTIVE->value;
        });

        static::deleting(function (Post $post) {
            $post->status = StatusEnum::INACTIVE->value;
            $post->save();
        });

        static::restoring(function (Post $post) {
            $post->status = StatusEnum::ACTIVE->value;
            $post->save();
        });
    }

/**
 * Get the user that owns the post.
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The post category relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class);
    }
}

<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => StatusEnum::class,
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        parent::boot();

        static::creating(function (PostCategory $category) {
            $category->slug = Str::slug($category->name);
            $category->status = StatusEnum::ACTIVE->value;
        });

        static::deleting(function (PostCategory $category) {
            $category->status = StatusEnum::INACTIVE->value;
            $category->save();
        });

        static::restoring(function (PostCategory$category) {
            $category->status = StatusEnum::ACTIVE->value;
            $category->save();
        });
    }
}

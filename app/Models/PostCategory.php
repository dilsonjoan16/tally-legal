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

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    protected static function booted()
    {
        static::creating(function (PostCategory $category) {
            $category->slug = Str::slug($category->name);
            $category->status = StatusEnum::ACTIVE->value;
        });
    }
}

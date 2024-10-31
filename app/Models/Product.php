<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
            'uuid',
            'category_id',
            'supplier_id',
            'name',
            'slug',
            'image',
            'price',
            'quantity',
            'description'
    ];


    public static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}

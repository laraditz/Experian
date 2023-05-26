<?php

namespace Laraditz\Experian\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExperianRequest extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'action', 'request', 'response', 'error_response'];

    protected $casts = [
        'request' => 'json',
        'response' => 'json',
    ];

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = $model->id ?? (string) Str::orderedUuid();
        });
    }
}

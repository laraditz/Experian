<?php

namespace Laraditz\Experian\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExperianRecord extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'ref_no', 'ccris_search', 'ccris_entity', 'ccris_report'];

    protected $casts = [
        'ccris_search' => 'json',
        'ccris_entity' => 'json',
        'ccris_report' => 'json',
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
            $model->ref_no = $model->ref_no ?? self::generateRefNo();
        });
    }

    private static function generateRefNo()
    {
        $ref_no = self::randomAlphanumeric();

        while (self::getModel()->where('ref_no', $ref_no)->count()) {
            $ref_no = self::randomAlphanumeric();
        }

        return $ref_no;
    }

    private static function randomAlphanumeric(int $length = 8)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        return substr(str_shuffle($characters), 0, $length);
    }
}

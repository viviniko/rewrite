<?php

namespace Viviniko\Rewrite;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

trait RewriteTrait
{
    /**
     * Boot the Rewrite trait for a model.
     *
     * @return void
     */
    public static function bootRewriteTrait()
    {
        static::saving(function ($model) {
            $key = $model->getRewriteKeyName();
            $model->$key = trim($model->$key, " \t\n\r \v/");
            if (!empty($model->$key)) {
                $model->$key = '/' . $model->$key;
                Validator::make(['request_path' => $model->$key], [
                    'request_path' => 'max:255|unique:' . Config::get('rewrite.entities_table') . ',request_path' . ($model->rewrite ? (',' . $model->rewrite->id) : '')
                ])->validate();
            }
        });

        static::saved(function ($model) {
            $key = $model->getRewriteKeyName();
            $model->$key = trim($model->$key, " \t\n\r \v/");
            if (!empty($model->$key)) {
                $model->$key = '/' . $model->$key;
                $model->rewrite()->updateOrCreate([
                    'entity_type' => $model->getMorphClass(),
                    'entity_id' => $model->id,
                ], [
                    'request_path' => $model->$key,
                ]);
            }
        });

        static::deleted(function ($model) {
            try {
                $model->rewrite()->delete();
            } catch (QueryException $e) {
                // ignored
            }
        });
    }

    public function rewrite()
    {
        return $this->morphOne(Config::get('rewrite.entity'), 'entity');
    }

    public function getRewriteKeyName()
    {
        return 'slug';
    }

    public function getUrlAttribute($url)
    {
        if (!$url) {
            $rewriteKey = $this->getRewriteKeyName();
            return url($this->$rewriteKey);
        }

        return $url;
    }
}
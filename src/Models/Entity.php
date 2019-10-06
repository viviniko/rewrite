<?php

namespace Viviniko\Rewrite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Entity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_path', 'entity_type', 'entity_id'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('rewrite.entities_table');
    }
}
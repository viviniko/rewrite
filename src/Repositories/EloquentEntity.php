<?php

namespace Viviniko\Rewrite\Repositories;

use Illuminate\Support\Facades\Config;
use Viviniko\Repository\SimpleRepository;

class EloquentEntity extends SimpleRepository implements EntityRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('rewrite.entities_table'));
    }
}

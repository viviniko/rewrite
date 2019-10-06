<?php

namespace Viviniko\Rewrite\Services;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\RouteBinding;
use Illuminate\Support\Facades\Route;
use Viviniko\Rewrite\Repositories\EntityRepository;

class RewriteServiceImpl implements RewriteService
{
    /**
     * @var \Viviniko\Rewrite\Repositories\EntityRepository
     */
    protected $entities;

    /**
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $binders = [];

    /**
     * Rewrite constructor.
     * @param \Viviniko\Rewrite\Repositories\EntityRepository
     * @param \Illuminate\Container\Container  $container
     */
    public function __construct(EntityRepository $entities, Container $container = null)
    {
        $this->entities = $entities;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveEntity($entityType, $entityId)
    {
        if (isset($this->binders[$entityType])) {
            return call_user_func($this->binders[$entityType], $entityId);
        }

        $morphMap = Relation::morphMap();
        if (isset($morphMap[$entityType])) {
            $entityType = $morphMap[$entityType];
        }
        if (class_exists($entityType)) {
            $entity = $this->container->make($entityType);
            if ($entity instanceof Model) {
                return $entity->find($entityId);
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function bind($entityType, $binder)
    {
        $this->binders[$entityType] = RouteBinding::forCallback(
            $this->container, $binder
        );
    }

    /**
     * {@inheritdoc}
     */
    public function action($action, $methods = 'any', $entityTypes = null)
    {
        $this->getRequestPathsByEntityType($entityTypes)->each(function ($requestPath) use ($action, $methods) {
            if (is_string($methods))
                Route::$methods($requestPath, $action);
            else
                Route::match($methods, $requestPath, $action);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findByRequestPath($requestPath)
    {
        return $this->entities->findBy('request_path', $requestPath);
    }

    protected function getRequestPathsByEntityType($entityType)
    {
        static $requestPaths;

        if (!$requestPaths) {
            $requestPaths = $this->entities->all()
                ->filter(function ($item) {
                    $requestPath = trim(trim($item->request_path), '/');
                    return !empty($requestPath);
                })
                ->groupBy('entity_type')
                ->map(function ($items) {
                    return $items->pluck('request_path');
                });
        }

        $result = collect([]);

        if (!$entityType) {
            $result = $requestPaths->values();
        } else {
            $entityType = (array) $entityType;
            while ($type = array_pop($entityType)) {
                $result = $result->merge($requestPaths->get($type));
            }
        }

        return $result;
    }
}
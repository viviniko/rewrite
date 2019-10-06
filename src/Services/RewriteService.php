<?php

namespace Viviniko\Rewrite\Services;

interface RewriteService
{
    /**
     * @param $action
     * @param mixed $methods
     * @param null $entityTypes
     */
    public function action($action, $methods = 'any', $entityTypes = null);

    /**
     * @param $requestPath
     * @return mixed
     */
    public function findByRequestPath($requestPath);

    /**
     * @param $entityType
     * @param $entityId
     * @return mixed
     */
    public function resolveEntity($entityType, $entityId);

    /**
     * @param $entityType
     * @param $binder
     */
    public function bind($entityType, $binder);
}
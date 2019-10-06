<?php

namespace Viviniko\Rewrite;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Viviniko\Rewrite\Facades\Rewrite as RewriteFacade;

class Rewrite
{
    protected static $rewriteMap = [];

    /**
     * Rewrite.
     *
     * @param $entityType
     * @param $targetRoute
     * @return mixed
     */
    public static function rewrite($entityType, $targetRoute = null)
    {
        $entityType = is_array($entityType) ? $entityType : [$entityType => $targetRoute];
        static::$rewriteMap = array_merge(static::$rewriteMap, $entityType);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $rewriteRequest = clone $request;
        $requestUri = $rewriteRequest->getRequestUri();
        $pathInfo = preg_replace('@/+@', '/', $rewriteRequest->getPathInfo());

        $result = $this->getEntityByRequestPath($pathInfo);
        if ($result && isset(static::$rewriteMap[$result->entity_type])) {
            $request->server->remove('UNENCODED_URL');
            $request->server->remove('IIS_WasUrlRewritten');
            $request->server->remove('REQUEST_URI');
            $request->server->remove('ORIG_PATH_INFO');
            $rewritePath = '/' . trim(preg_replace('/\{\w+\}/', $result->entity_id, static::$rewriteMap[$result->entity_type]), '/');
            $query = str_replace($pathInfo, '', $requestUri);
            $request->server->set('REQUEST_URI', $rewritePath . $query);
            RewriteFacade::request($rewriteRequest);
        }

        return $next($request);
    }

    protected function getEntityByRequestPath($requestPath)
    {
        return Cache::remember("rewrite/getEntityByRequestPath?request_path={$requestPath}", Config::get('cache.ttl'), function () use ($requestPath) {
            return DB::table(Config::get('rewrite.entities_table'))->where('request_path', $requestPath)->first(['entity_type', 'entity_id']);
        });
    }
}
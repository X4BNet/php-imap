<?php
/*
* File:     Paginator.php
* Category: Support
* Author:   M. Goldenbaum
* Created:  16.03.18 03:13
* Updated:  -
*
* Description:
*  Paginator class replacing Illuminate\Pagination\Paginator
*/

namespace Webklex\PHPIMAP\Support;

/**
 * Class Paginator
 *
 * @package Webklex\PHPIMAP\Support
 */
class Paginator {

    /**
     * The current page resolver callback.
     *
     * @var \Closure|null
     */
    protected static ?\Closure $currentPageResolver = null;

    /**
     * The current path resolver callback.
     *
     * @var \Closure|null
     */
    protected static ?\Closure $currentPathResolver = null;

    /**
     * Resolve the current page from the request.
     *
     * @param string $pageName
     * @param int $default
     * @return int
     */
    public static function resolveCurrentPage(string $pageName = 'page', int $default = 1): int {
        if (static::$currentPageResolver !== null) {
            return call_user_func(static::$currentPageResolver, $pageName);
        }

        // Try to get page from query string
        $page = $_GET[$pageName] ?? $default;

        if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
            return (int) $page;
        }

        return $default;
    }

    /**
     * Set the current page resolver callback.
     *
     * @param \Closure $resolver
     * @return void
     */
    public static function currentPageResolver(\Closure $resolver): void {
        static::$currentPageResolver = $resolver;
    }

    /**
     * Resolve the current path.
     *
     * @param string $default
     * @return string
     */
    public static function resolveCurrentPath(string $default = '/'): string {
        if (static::$currentPathResolver !== null) {
            return call_user_func(static::$currentPathResolver);
        }

        return $_SERVER['REQUEST_URI'] ?? $default;
    }

    /**
     * Set the current path resolver callback.
     *
     * @param \Closure $resolver
     * @return void
     */
    public static function currentPathResolver(\Closure $resolver): void {
        static::$currentPathResolver = $resolver;
    }
}

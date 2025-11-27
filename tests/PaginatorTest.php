<?php
/*
* File: PaginatorTest.php
* Category: -
* Author: M.Goldenbaum
* Created: 27.11.24 00:00
* Updated: -
*
* Description:
*  Tests for the Paginator class
*/

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webklex\PHPIMAP\Support\Paginator;

class PaginatorTest extends TestCase {

    /**
     * Store original values to restore after tests
     */
    private static mixed $originalPageResolver = null;
    private static mixed $originalPathResolver = null;
    private static bool $initialized = false;

    /**
     * @return void
     */
    protected function setUp(): void {
        // Reset to null on setup - which means use default logic
        if (!self::$initialized) {
            self::$initialized = true;
        }
    }

    /**
     * Test resolve current page with default value
     *
     * @return void
     */
    public function testResolveCurrentPageDefault(): void {
        // Ensure we're using default resolver
        Paginator::currentPageResolver(function($pageName) {
            $page = $_GET[$pageName] ?? 1;
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            return 1;
        });
        
        // No $_GET set, should return default
        unset($_GET['page']);
        self::assertSame(1, Paginator::resolveCurrentPage());
    }

    /**
     * Test resolve current page from $_GET
     *
     * @return void
     */
    public function testResolveCurrentPageFromGet(): void {
        Paginator::currentPageResolver(function($pageName) {
            $page = $_GET[$pageName] ?? 1;
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            return 1;
        });
        
        $_GET['page'] = 5;
        self::assertSame(5, Paginator::resolveCurrentPage());
        
        $_GET['page'] = '10';
        self::assertSame(10, Paginator::resolveCurrentPage());
        
        // Clean up
        unset($_GET['page']);
    }

    /**
     * Test resolve current page with custom page name
     *
     * @return void
     */
    public function testResolveCurrentPageCustomName(): void {
        Paginator::currentPageResolver(function($pageName) {
            $page = $_GET[$pageName] ?? 1;
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            return 1;
        });
        
        $_GET['custom_page'] = 7;
        self::assertSame(7, Paginator::resolveCurrentPage('custom_page'));
        
        // Clean up
        unset($_GET['custom_page']);
    }

    /**
     * Test resolve current page with invalid value
     *
     * @return void
     */
    public function testResolveCurrentPageInvalid(): void {
        Paginator::currentPageResolver(function($pageName) {
            $page = $_GET[$pageName] ?? 1;
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
            return 1;
        });
        
        $_GET['page'] = 'invalid';
        self::assertSame(1, Paginator::resolveCurrentPage());
        
        $_GET['page'] = 0;
        self::assertSame(1, Paginator::resolveCurrentPage());
        
        $_GET['page'] = -5;
        self::assertSame(1, Paginator::resolveCurrentPage());
        
        // Clean up
        unset($_GET['page']);
    }

    /**
     * Test custom page resolver
     *
     * @return void
     */
    public function testCustomPageResolver(): void {
        Paginator::currentPageResolver(fn($pageName) => $pageName === 'page' ? 42 : 1);
        
        self::assertSame(42, Paginator::resolveCurrentPage());
        self::assertSame(1, Paginator::resolveCurrentPage('other'));
    }

    /**
     * Test resolve current path
     *
     * @return void
     */
    public function testResolveCurrentPath(): void {
        // Reset to default resolver
        Paginator::currentPathResolver(function() {
            return $_SERVER['REQUEST_URI'] ?? '/';
        });
        
        $_SERVER['REQUEST_URI'] = '/test/path';
        self::assertSame('/test/path', Paginator::resolveCurrentPath());
        
        // Clean up
        unset($_SERVER['REQUEST_URI']);
        
        // Should return default when REQUEST_URI is not set
        self::assertSame('/', Paginator::resolveCurrentPath());
    }

    /**
     * Test custom path resolver
     *
     * @return void
     */
    public function testCustomPathResolver(): void {
        Paginator::currentPathResolver(fn() => '/custom/path');
        
        self::assertSame('/custom/path', Paginator::resolveCurrentPath());
    }
}


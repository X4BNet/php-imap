<?php
/*
* File: LengthAwarePaginatorTest.php
* Category: -
* Author: M.Goldenbaum
* Created: 27.11.24 00:00
* Updated: -
*
* Description:
*  Tests for the LengthAwarePaginator class
*/

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webklex\PHPIMAP\Support\LengthAwarePaginator;
use Webklex\PHPIMAP\Support\Paginator;

class LengthAwarePaginatorTest extends TestCase {

    /**
     * @return void
     */
    protected function setUp(): void {
        // Set up a custom path resolver
        Paginator::currentPathResolver(fn() => '/items');
    }

    /**
     * Test basic paginator creation
     *
     * @return void
     */
    public function testBasicCreation(): void {
        $items = ['a', 'b', 'c', 'd', 'e'];
        $paginator = new LengthAwarePaginator($items, 50, 5, 1);
        
        self::assertSame(50, $paginator->total());
        self::assertSame(5, $paginator->perPage());
        self::assertSame(1, $paginator->currentPage());
        self::assertSame(10, $paginator->lastPage());
        self::assertSame(5, $paginator->count());
    }

    /**
     * Test paginator with options
     *
     * @return void
     */
    public function testWithOptions(): void {
        $paginator = new LengthAwarePaginator([], 100, 10, 1, [
            'path' => '/custom',
            'pageName' => 'p',
        ]);
        
        self::assertSame('/custom', $paginator->path());
        self::assertSame('p', $paginator->getPageName());
    }

    /**
     * Test URL generation
     *
     * @return void
     */
    public function testUrlGeneration(): void {
        $paginator = new LengthAwarePaginator([], 100, 10, 3, [
            'path' => '/items',
        ]);
        
        self::assertSame('/items?page=1', $paginator->url(1));
        self::assertSame('/items?page=5', $paginator->url(5));
        
        // Edge case: page 0 or negative should become 1
        self::assertSame('/items?page=1', $paginator->url(0));
        self::assertSame('/items?page=1', $paginator->url(-5));
    }

    /**
     * Test first and last page URLs
     *
     * @return void
     */
    public function testFirstAndLastPageUrls(): void {
        $paginator = new LengthAwarePaginator([], 100, 10, 5, [
            'path' => '/items',
        ]);
        
        self::assertSame('/items?page=1', $paginator->firstPageUrl());
        self::assertSame('/items?page=10', $paginator->lastPageUrl());
    }

    /**
     * Test previous and next page URLs
     *
     * @return void
     */
    public function testPreviousAndNextPageUrls(): void {
        // Middle page
        $paginator = new LengthAwarePaginator([], 100, 10, 5, [
            'path' => '/items',
        ]);
        
        self::assertSame('/items?page=4', $paginator->previousPageUrl());
        self::assertSame('/items?page=6', $paginator->nextPageUrl());
        
        // First page
        $firstPage = new LengthAwarePaginator([], 100, 10, 1, [
            'path' => '/items',
        ]);
        
        self::assertNull($firstPage->previousPageUrl());
        self::assertSame('/items?page=2', $firstPage->nextPageUrl());
        
        // Last page
        $lastPage = new LengthAwarePaginator([], 100, 10, 10, [
            'path' => '/items',
        ]);
        
        self::assertSame('/items?page=9', $lastPage->previousPageUrl());
        self::assertNull($lastPage->nextPageUrl());
    }

    /**
     * Test hasMorePages method
     *
     * @return void
     */
    public function testHasMorePages(): void {
        $paginator = new LengthAwarePaginator([], 100, 10, 5);
        self::assertTrue($paginator->hasMorePages());
        
        $lastPage = new LengthAwarePaginator([], 100, 10, 10);
        self::assertFalse($lastPage->hasMorePages());
    }

    /**
     * Test hasPages method
     *
     * @return void
     */
    public function testHasPages(): void {
        $multiPage = new LengthAwarePaginator([], 100, 10, 1);
        self::assertTrue($multiPage->hasPages());
        
        $singlePage = new LengthAwarePaginator([], 5, 10, 1);
        self::assertFalse($singlePage->hasPages());
    }

    /**
     * Test onFirstPage and onLastPage methods
     *
     * @return void
     */
    public function testOnFirstAndLastPage(): void {
        $first = new LengthAwarePaginator([], 100, 10, 1);
        self::assertTrue($first->onFirstPage());
        self::assertFalse($first->onLastPage());
        
        $last = new LengthAwarePaginator([], 100, 10, 10);
        self::assertFalse($last->onFirstPage());
        self::assertTrue($last->onLastPage());
        
        $middle = new LengthAwarePaginator([], 100, 10, 5);
        self::assertFalse($middle->onFirstPage());
        self::assertFalse($middle->onLastPage());
    }

    /**
     * Test firstItem and lastItem methods
     *
     * @return void
     */
    public function testFirstAndLastItem(): void {
        $items = ['a', 'b', 'c', 'd', 'e'];
        $paginator = new LengthAwarePaginator($items, 50, 5, 2);
        
        self::assertSame(6, $paginator->firstItem());
        self::assertSame(10, $paginator->lastItem());
        
        // Empty paginator
        $empty = new LengthAwarePaginator([], 0, 10, 1);
        self::assertSame(0, $empty->firstItem());
        self::assertSame(0, $empty->lastItem());
    }

    /**
     * Test items method
     *
     * @return void
     */
    public function testItems(): void {
        $items = ['a', 'b', 'c'];
        $paginator = new LengthAwarePaginator($items, 30, 3, 1);
        
        self::assertSame($items, $paginator->items());
    }

    /**
     * Test isEmpty and isNotEmpty methods
     *
     * @return void
     */
    public function testIsEmptyAndIsNotEmpty(): void {
        $empty = new LengthAwarePaginator([], 0, 10, 1);
        self::assertTrue($empty->isEmpty());
        self::assertFalse($empty->isNotEmpty());
        
        $notEmpty = new LengthAwarePaginator(['a'], 10, 10, 1);
        self::assertFalse($notEmpty->isEmpty());
        self::assertTrue($notEmpty->isNotEmpty());
    }

    /**
     * Test array access
     *
     * @return void
     */
    public function testArrayAccess(): void {
        $items = ['a', 'b', 'c'];
        $paginator = new LengthAwarePaginator($items, 30, 3, 1);
        
        self::assertSame('a', $paginator[0]);
        self::assertTrue(isset($paginator[0]));
        self::assertFalse(isset($paginator[10]));
        
        $paginator[3] = 'd';
        self::assertSame('d', $paginator[3]);
        
        unset($paginator[3]);
        self::assertFalse(isset($paginator[3]));
    }

    /**
     * Test iteration
     *
     * @return void
     */
    public function testIteration(): void {
        $items = ['a', 'b', 'c'];
        $paginator = new LengthAwarePaginator($items, 30, 3, 1);
        
        $result = [];
        foreach ($paginator as $key => $value) {
            $result[$key] = $value;
        }
        
        self::assertSame([0 => 'a', 1 => 'b', 2 => 'c'], $result);
    }

    /**
     * Test toArray method
     *
     * @return void
     */
    public function testToArray(): void {
        $items = ['a', 'b', 'c'];
        $paginator = new LengthAwarePaginator($items, 30, 3, 2, [
            'path' => '/items',
        ]);
        
        $array = $paginator->toArray();
        
        self::assertSame(2, $array['current_page']);
        self::assertSame($items, $array['data']);
        self::assertSame('/items?page=1', $array['first_page_url']);
        self::assertSame(4, $array['from']);
        self::assertSame(10, $array['last_page']);
        self::assertSame('/items?page=10', $array['last_page_url']);
        self::assertSame('/items?page=3', $array['next_page_url']);
        self::assertSame('/items', $array['path']);
        self::assertSame(3, $array['per_page']);
        self::assertSame('/items?page=1', $array['prev_page_url']);
        self::assertSame(6, $array['to']);
        self::assertSame(30, $array['total']);
    }

    /**
     * Test JSON serialization
     *
     * @return void
     */
    public function testJsonSerialization(): void {
        $items = ['a', 'b'];
        $paginator = new LengthAwarePaginator($items, 10, 2, 1, [
            'path' => '/test',
        ]);
        
        $json = $paginator->toJson();
        $decoded = json_decode($json, true);
        
        self::assertSame($items, $decoded['data']);
        self::assertSame(10, $decoded['total']);
    }

    /**
     * Test appends method
     *
     * @return void
     */
    public function testAppends(): void {
        $paginator = new LengthAwarePaginator([], 100, 10, 1, [
            'path' => '/items',
        ]);
        
        $paginator->appends('filter', 'active');
        self::assertSame('/items?filter=active&page=2', $paginator->url(2));
        
        $paginator->appends(['sort' => 'name', 'order' => 'asc']);
        self::assertSame('/items?filter=active&sort=name&order=asc&page=3', $paginator->url(3));
    }

    /**
     * Test fragment method
     *
     * @return void
     */
    public function testFragment(): void {
        $paginator = new LengthAwarePaginator([], 100, 10, 1, [
            'path' => '/items',
        ]);
        
        $paginator->fragment('section');
        self::assertSame('/items?page=2#section', $paginator->url(2));
    }

    /**
     * Test setPath and setPageName methods
     *
     * @return void
     */
    public function testSetPathAndPageName(): void {
        $paginator = new LengthAwarePaginator([], 100, 10, 1);
        
        $paginator->setPath('/new-path');
        self::assertSame('/new-path', $paginator->path());
        
        $paginator->setPageName('p');
        self::assertSame('p', $paginator->getPageName());
        self::assertSame('/new-path?p=2', $paginator->url(2));
    }

    /**
     * Test with Collection as items
     *
     * @return void
     */
    public function testWithCollection(): void {
        $collection = new \Webklex\PHPIMAP\Support\Collection(['a', 'b', 'c']);
        $paginator = new LengthAwarePaginator($collection, 30, 3, 1);
        
        self::assertSame(['a', 'b', 'c'], $paginator->items());
    }
}

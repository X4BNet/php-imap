<?php
/*
* File: CollectionTest.php
* Category: -
* Author: M.Goldenbaum
* Created: 27.11.24 00:00
* Updated: -
*
* Description:
*  Tests for the Collection class
*/

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webklex\PHPIMAP\Support\Collection;

class CollectionTest extends TestCase {

    /**
     * Test basic collection creation
     *
     * @return void
     */
    public function testBasicCreation(): void {
        $collection = new Collection([1, 2, 3]);
        self::assertSame([1, 2, 3], $collection->all());
        self::assertSame(3, $collection->count());
    }

    /**
     * Test static make method
     *
     * @return void
     */
    public function testMake(): void {
        $collection = Collection::make([1, 2, 3]);
        self::assertInstanceOf(Collection::class, $collection);
        self::assertSame([1, 2, 3], $collection->all());
    }

    /**
     * Test push method
     *
     * @return void
     */
    public function testPush(): void {
        $collection = new Collection([1, 2]);
        $collection->push(3);
        self::assertSame([1, 2, 3], $collection->all());
        
        $collection->push(4, 5);
        self::assertSame([1, 2, 3, 4, 5], $collection->all());
    }

    /**
     * Test put method
     *
     * @return void
     */
    public function testPut(): void {
        $collection = new Collection();
        $collection->put('key', 'value');
        self::assertSame('value', $collection->get('key'));
    }

    /**
     * Test get method
     *
     * @return void
     */
    public function testGet(): void {
        $collection = new Collection(['name' => 'John', 'age' => 30]);
        self::assertSame('John', $collection->get('name'));
        self::assertSame(30, $collection->get('age'));
        self::assertNull($collection->get('missing'));
        self::assertSame('default', $collection->get('missing', 'default'));
    }

    /**
     * Test has method
     *
     * @return void
     */
    public function testHas(): void {
        $collection = new Collection(['name' => 'John']);
        self::assertTrue($collection->has('name'));
        self::assertFalse($collection->has('age'));
    }

    /**
     * Test first and last methods
     *
     * @return void
     */
    public function testFirstAndLast(): void {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        self::assertSame(1, $collection->first());
        self::assertSame(5, $collection->last());
        
        // With callback
        self::assertSame(3, $collection->first(fn($item) => $item > 2));
        
        // Empty collection
        $empty = new Collection();
        self::assertNull($empty->first());
        self::assertNull($empty->last());
        self::assertSame('default', $empty->first(null, 'default'));
    }

    /**
     * Test map method
     *
     * @return void
     */
    public function testMap(): void {
        $collection = new Collection([1, 2, 3]);
        $mapped = $collection->map(fn($item) => $item * 2);
        
        self::assertSame([2, 4, 6], $mapped->all());
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter(): void {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $filtered = $collection->filter(fn($item) => $item > 2);
        
        self::assertSame([2 => 3, 3 => 4, 4 => 5], $filtered->all());
    }

    /**
     * Test each method
     *
     * @return void
     */
    public function testEach(): void {
        $collection = new Collection([1, 2, 3]);
        $sum = 0;
        $collection->each(function($item) use (&$sum) {
            $sum += $item;
        });
        
        self::assertSame(6, $sum);
    }

    /**
     * Test isEmpty and isNotEmpty methods
     *
     * @return void
     */
    public function testIsEmptyAndIsNotEmpty(): void {
        $empty = new Collection();
        $notEmpty = new Collection([1]);
        
        self::assertTrue($empty->isEmpty());
        self::assertFalse($empty->isNotEmpty());
        
        self::assertFalse($notEmpty->isEmpty());
        self::assertTrue($notEmpty->isNotEmpty());
    }

    /**
     * Test keys and values methods
     *
     * @return void
     */
    public function testKeysAndValues(): void {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        
        self::assertSame(['a', 'b', 'c'], $collection->keys()->all());
        self::assertSame([1, 2, 3], $collection->values()->all());
    }

    /**
     * Test merge method
     *
     * @return void
     */
    public function testMerge(): void {
        $collection = new Collection([1, 2]);
        $merged = $collection->merge([3, 4]);
        
        self::assertSame([1, 2, 3, 4], $merged->all());
    }

    /**
     * Test reverse method
     *
     * @return void
     */
    public function testReverse(): void {
        $collection = new Collection([1, 2, 3]);
        $reversed = $collection->reverse();
        
        self::assertSame([2 => 3, 1 => 2, 0 => 1], $reversed->all());
    }

    /**
     * Test forPage method
     *
     * @return void
     */
    public function testForPage(): void {
        $collection = new Collection([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        
        $page1 = $collection->forPage(1, 3);
        self::assertSame([0 => 1, 1 => 2, 2 => 3], $page1->all());
        
        $page2 = $collection->forPage(2, 3);
        self::assertSame([3 => 4, 4 => 5, 5 => 6], $page2->all());
        
        $page4 = $collection->forPage(4, 3);
        self::assertSame([9 => 10], $page4->all());
    }

    /**
     * Test array access
     *
     * @return void
     */
    public function testArrayAccess(): void {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        
        self::assertSame(1, $collection['a']);
        self::assertTrue(isset($collection['a']));
        self::assertFalse(isset($collection['c']));
        
        $collection['c'] = 3;
        self::assertSame(3, $collection['c']);
        
        unset($collection['c']);
        self::assertFalse(isset($collection['c']));
    }

    /**
     * Test iteration
     *
     * @return void
     */
    public function testIteration(): void {
        $collection = new Collection([1, 2, 3]);
        $items = [];
        
        foreach ($collection as $key => $value) {
            $items[$key] = $value;
        }
        
        self::assertSame([0 => 1, 1 => 2, 2 => 3], $items);
    }

    /**
     * Test toArray method
     *
     * @return void
     */
    public function testToArray(): void {
        $collection = new Collection([1, 2, 3]);
        self::assertSame([1, 2, 3], $collection->toArray());
        
        // Nested collection
        $nested = new Collection([
            'items' => new Collection([1, 2, 3])
        ]);
        self::assertSame(['items' => [1, 2, 3]], $nested->toArray());
    }

    /**
     * Test reduce method
     *
     * @return void
     */
    public function testReduce(): void {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        $sum = $collection->reduce(fn($carry, $item) => $carry + $item, 0);
        self::assertSame(15, $sum);
    }

    /**
     * Test contains method
     *
     * @return void
     */
    public function testContains(): void {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        self::assertTrue($collection->contains(3));
        self::assertFalse($collection->contains(6));
        
        // With callback
        self::assertTrue($collection->contains(fn($item) => $item > 4));
        self::assertFalse($collection->contains(fn($item) => $item > 10));
    }

    /**
     * Test contains method with comparison operators
     *
     * @return void
     */
    public function testContainsWithOperators(): void {
        $collection = new Collection([
            (object)['name' => 'John', 'age' => 30],
            (object)['name' => 'Jane', 'age' => 25],
            (object)['name' => 'Bob', 'age' => 35],
        ]);
        
        // Test with two arguments (key, value) - equality comparison
        self::assertTrue($collection->contains('age', 30));
        self::assertFalse($collection->contains('age', 40));
        
        // Test with three arguments (key, operator, value)
        self::assertTrue($collection->contains('age', '>', 30));
        self::assertFalse($collection->contains('age', '>', 40));
        
        self::assertTrue($collection->contains('age', '<', 30));
        self::assertFalse($collection->contains('age', '<', 25));
        
        self::assertTrue($collection->contains('age', '>=', 35));
        self::assertTrue($collection->contains('age', '<=', 25));
        
        self::assertTrue($collection->contains('age', '!=', 40));
        // contains returns true if ANY item matches, so '!= 30' is true because Jane (25) and Bob (35) don't have age 30
        self::assertTrue($collection->contains('age', '!=', 30));
        
        self::assertTrue($collection->contains('name', '===', 'John'));
        // '!== John' is true because Jane and Bob exist
        self::assertTrue($collection->contains('name', '!==', 'John'));
    }

    /**
     * Test chunk method
     *
     * @return void
     */
    public function testChunk(): void {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $chunks = $collection->chunk(2);
        
        self::assertCount(3, $chunks);
        self::assertSame([1, 2], $chunks->get(0)->all());
        self::assertSame([2 => 3, 3 => 4], $chunks->get(1)->all());
        self::assertSame([4 => 5], $chunks->get(2)->all());
    }

    /**
     * Test take method
     *
     * @return void
     */
    public function testTake(): void {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        $first3 = $collection->take(3);
        self::assertSame([1, 2, 3], $first3->all());
        
        $last2 = $collection->take(-2);
        self::assertSame([4, 5], $last2->all());
    }

    /**
     * Test unique method
     *
     * @return void
     */
    public function testUnique(): void {
        $collection = new Collection([1, 2, 2, 3, 3, 3]);
        $unique = $collection->unique();
        
        self::assertSame([1, 2, 3], array_values($unique->all()));
    }

    /**
     * Test pop method
     *
     * @return void
     */
    public function testPop(): void {
        $collection = new Collection([1, 2, 3]);
        $popped = $collection->pop();
        
        self::assertSame(3, $popped);
        self::assertSame([1, 2], $collection->all());
    }

    /**
     * Test where method
     *
     * @return void
     */
    public function testWhere(): void {
        $collection = new Collection([
            (object)['name' => 'John', 'age' => 30],
            (object)['name' => 'Jane', 'age' => 25],
            (object)['name' => 'Bob', 'age' => 30],
        ]);
        
        // Test with two arguments (key, value)
        $filtered = $collection->where('age', 30);
        self::assertCount(2, $filtered);
        
        // Test with three arguments (key, operator, value)
        $filtered = $collection->where('age', '>', 25);
        self::assertCount(2, $filtered);
        
        $filtered = $collection->where('age', '<', 30);
        self::assertCount(1, $filtered);
        
        $filtered = $collection->where('name', '===', 'John');
        self::assertCount(1, $filtered);
    }

    /**
     * Test forPage method with null perPage
     *
     * @return void
     */
    public function testForPageWithNull(): void {
        $collection = new Collection([1, 2, 3, 4, 5]);
        
        // When perPage is null, should return full collection
        $result = $collection->forPage(1, null);
        self::assertSame([1, 2, 3, 4, 5], $result->all());
    }

    /**
     * Test offsetExists with null values
     *
     * @return void
     */
    public function testOffsetExistsWithNullValues(): void {
        $collection = new Collection();
        $collection->put('foo', null);
        
        // Key exists even though value is null
        self::assertTrue(isset($collection['foo']));
        self::assertTrue($collection->has('foo'));
        self::assertNull($collection->get('foo'));
        
        // Key doesn't exist
        self::assertFalse(isset($collection['bar']));
    }
}

<?php
/*
* File: StrTest.php
* Category: -
* Author: M.Goldenbaum
* Created: 27.11.24 00:00
* Updated: -
*
* Description:
*  Tests for the Str helper class
*/

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webklex\PHPIMAP\Support\Str;

class StrTest extends TestCase {

    /**
     * Test snake_case conversion
     *
     * @return void
     */
    public function testSnake(): void {
        // Basic camelCase to snake_case
        self::assertSame('foo_bar', Str::snake('FooBar'));
        self::assertSame('foo_bar', Str::snake('fooBar'));
        self::assertSame('foo_bar_baz', Str::snake('FooBarBaz'));
        
        // Already snake_case
        self::assertSame('foo_bar', Str::snake('foo_bar'));
        
        // Single word
        self::assertSame('foo', Str::snake('Foo'));
        self::assertSame('foo', Str::snake('foo'));
        
        // Custom delimiter
        self::assertSame('foo-bar', Str::snake('FooBar', '-'));
        self::assertSame('foo.bar', Str::snake('FooBar', '.'));
    }

    /**
     * Test camelCase conversion
     *
     * @return void
     */
    public function testCamel(): void {
        // Basic snake_case to camelCase
        self::assertSame('fooBar', Str::camel('foo_bar'));
        self::assertSame('fooBarBaz', Str::camel('foo_bar_baz'));
        
        // Hyphenated
        self::assertSame('fooBar', Str::camel('foo-bar'));
        
        // Already camelCase
        self::assertSame('fooBar', Str::camel('fooBar'));
        
        // Single word
        self::assertSame('foo', Str::camel('foo'));
        self::assertSame('foo', Str::camel('Foo'));
    }

    /**
     * Test studly/PascalCase conversion
     *
     * @return void
     */
    public function testStudly(): void {
        // Basic snake_case to StudlyCase
        self::assertSame('FooBar', Str::studly('foo_bar'));
        self::assertSame('FooBarBaz', Str::studly('foo_bar_baz'));
        
        // Hyphenated
        self::assertSame('FooBar', Str::studly('foo-bar'));
        
        // Single word
        self::assertSame('Foo', Str::studly('foo'));
    }
}

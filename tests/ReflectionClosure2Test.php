<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

use Closure;
use Opis\Closure\ReflectionClosure;

// Fake
use Foo\{Bar, Baz as Qux};
// Dirty CS
define(Bar::class, Bar::class);
use function Foo\f1;
use function Bar\{b1, b2 as b3};

test('resolve arguments', function () {
    $f1 = function (Bar $p){};
    $e1 = 'function (\Foo\Bar $p){}';

    $f2 = function (Bar\Test $p){};
    $e2 = 'function (\Foo\Bar\Test $p){}';

    $f3 = function (Qux $p){};
    $e3 = 'function (\Foo\Baz $p){}';

    $f4 = function (Qux\Test $p){};
    $e4 = 'function (\Foo\Baz\Test $p){}';

    $f5 = function (array $p, string $x){};
    $e5 = 'function (array $p, string $x){}';

    $f6 = function ($a = self::VALUE){};
    $e6 = 'function ($a = self::VALUE){}';

    $f7 = function ($a = parent::VALUE){};
    $e7 = 'function ($a = parent::VALUE){}';

    $f8 = function ($a = [self::VALUE, parent::VALUE]){};
    $e8 = 'function ($a = [self::VALUE, parent::VALUE]){}';


    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
    test()->assertEquals($e4, c($f4));
    test()->assertEquals($e5, c($f5));
    test()->assertEquals($e6, c($f6));
    test()->assertEquals($e7, c($f7));
    test()->assertEquals($e8, c($f8));
});

test('resolve return type', function () {
    $f1 = function (): Bar{};
    $e1 = 'function (): \Foo\Bar{}';

    $f2 = function (): Bar\Test{};
    $e2 = 'function (): \Foo\Bar\Test{}';

    $f3 = function (): Qux{};
    $e3 = 'function (): \Foo\Baz{}';

    $f4 = function (): Qux\Test{};
    $e4 = 'function (): \Foo\Baz\Test{}';

    $f5 = function (): \Foo{};
    $e5 = 'function (): \Foo{}';

    $f6 = function (): Foo{};
    $e6 = 'function (): \\' . __NAMESPACE__. '\Foo{}';

    $f7 = function (): array{};
    $e7 = 'function (): array{}';

    $f8 = function (): string{};
    $e8 = 'function (): string{}';

    $f9 = function (){ return Relative\CONST_X + 1;};
    $e9 = 'function (){ return \\' . __NAMESPACE__. '\Relative\CONST_X + 1;}';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
    test()->assertEquals($e4, c($f4));
    test()->assertEquals($e5, c($f5));
    test()->assertEquals($e6, c($f6));
    test()->assertEquals($e7, c($f7));
    test()->assertEquals($e8, c($f8));
    test()->assertEquals($e9, c($f9));
});

test('closure inside closure', function () {
    $f1 = function() { return function ($a): A { return $a; }; };
    $e1 = 'function() { return function ($a): \Opis\Closure\Test\A { return $a; }; }';


    $f2 = function() { return function (A $a): A { return $a; }; };
    $e2 = 'function() { return function (\Opis\Closure\Test\A $a): \Opis\Closure\Test\A { return $a; }; }';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
});

test('anonymous inside closure', function () {
    $f1 = function() { return new class extends A {}; };
    $e1 = 'function() { return new class extends \Opis\Closure\Test\A {}; }';

    $f2 = function() { return new class extends A implements B {}; };
    $e2 = 'function() { return new class extends \Opis\Closure\Test\A implements \Opis\Closure\Test\B {}; }';

    $f3 = function() { return new class { function x(A $a): B {} }; };
    $e3 = 'function() { return new class { function x(\Opis\Closure\Test\A $a): \Opis\Closure\Test\B {} }; }';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
});

test('closure resolve traits names in anonymous classes', function () {
    $f1 = function () { new class { use Bar; }; };
    $e1 = 'function () { new class { use \Foo\Bar; }; }';

    $f2 = function () { new class { use Bar\Test; }; };
    $e2 = 'function () { new class { use \Foo\Bar\Test; }; }';

    $f3 = function () { new class { use Qux; }; };
    $e3 = 'function () { new class { use \Foo\Baz; }; }';

    $f4 = function () { new class { use Qux\Test; }; };
    $e4 = 'function () { new class { use \Foo\Baz\Test; }; }';

    $f5 = function () { new class { use \Foo; }; };
    $e5 = 'function () { new class { use \Foo; }; }';

    $f6 = function () { new class { use Foo; }; };
    $e6 = 'function () { new class { use \\' . __NAMESPACE__ . '\Foo; }; }';

    $f7 = function () { new class { use Bar; }; function a(Qux $q): Bar { f1(); $a = new class extends Bar {}; } };
    $e7 = 'function () { new class { use \Foo\Bar; }; function a(\Foo\Baz $q): \Foo\Bar '
        . '{ \Foo\f1(); $a = new class extends \Foo\Bar {}; } }';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
    test()->assertEquals($e4, c($f4));
    test()->assertEquals($e5, c($f5));
    test()->assertEquals($e6, c($f6));
    test()->assertEquals($e7, c($f7));
});

test('keyword as static method', function () {
    $f1 = function() { Bar::new(); };
    $e1 = 'function() { \Foo\Bar::new(); }';
    $f2 = function() { Bar::__FILE__(); };
    $e2 = 'function() { \Foo\Bar::__FILE__(); }';
    $f3 = function() { Bar::__CLASS__(); };
    $e3 = 'function() { \Foo\Bar::__CLASS__(); }';
    $f4 = function() { Bar::__DIR__(); };
    $e4 = 'function() { \Foo\Bar::__DIR__(); }';
    $f5 = function() { Bar::__FUNCTION__(); };
    $e5 = 'function() { \Foo\Bar::__FUNCTION__(); }';
    $f6 = function() { Bar::__METHOD__(); };
    $e6 = 'function() { \Foo\Bar::__METHOD__(); }';
    $f7 = function() { Bar::function(); };
    $e7 = 'function() { \Foo\Bar::function(); }';
    $f8 = function() { Bar::instanceof(); };
    $e8 = 'function() { \Foo\Bar::instanceof(); }';
    $f9 = function() { Bar::__LINE__(); };
    $e9 = 'function() { \Foo\Bar::__LINE__(); }';
    $f10 = function() { Bar::__NAMESPACE__(); };
    $e10 = 'function() { \Foo\Bar::__NAMESPACE__(); }';
    $f11 = function() { Bar::__TRAIT__(); };
    $e11 = 'function() { \Foo\Bar::__TRAIT__(); }';
    $f12 = function() { Bar::use(); };
    $e12 = 'function() { \Foo\Bar::use(); }';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
    test()->assertEquals($e4, c($f4));
    test()->assertEquals($e5, c($f5));
    test()->assertEquals($e6, c($f6));
    test()->assertEquals($e7, c($f7));
    test()->assertEquals($e8, c($f8));
    test()->assertEquals($e9, c($f9));
    test()->assertEquals($e10, c($f10));
    test()->assertEquals($e11, c($f11));
    test()->assertEquals($e12, c($f12));
});

test('this inside anonymous class', function () {
    $f1 = function() {
        return new class {
            function a(){
                $self = $this;
            }
        };
    };

    $f2 = function () {
        return new class {
            function a(){
                $self = $this;
                return new class {
                    function a(){
                        $self = $this;
                    }
                };
            }
        };
    };

    $f3 = function () {
        $self = $this;
        return new class {
            function a(){
                $self = $this;
            }
        };
    };

    $f4 = function () {
        return new class {
            function a(){
                $self = $this;
            }
        };
        $self = $this;
    };

    test()->assertFalse((new ReflectionClosure($f1))->isBindingRequired());
    test()->assertFalse((new ReflectionClosure($f2))->isBindingRequired());
    test()->assertTrue((new ReflectionClosure($f3))->isBindingRequired());
    test()->assertTrue((new ReflectionClosure($f4))->isBindingRequired());
});

test('is scope required', function () {
    $f1 = function () { static::test();};
    $f2 = function ($x = self::CONST_X) {};
    $f3 = function ($x = parent::CONST_X) {};
    $f4 = function () { static $i = 1;};
    $f5 = function () {
        return function() {
            static $i = 0;
        };
    };
    $f6 = function () {
        return function() {
            static::test();
        };
    };
    $f7 = $f5();
    $f8 = $f6();
    $f9 = function () { new static(); };
    $f10 = function () { new self(); };
    $f11 = function () {
        $a = static function ($retries) {
            return 750 * $retries;
        };
    };

    test()->assertTrue(r($f1)->isScopeRequired());
    test()->assertTrue(r($f2)->isScopeRequired());
    test()->assertTrue(r($f3)->isScopeRequired());
    test()->assertFalse(r($f4)->isScopeRequired());
    test()->assertFalse(r($f5)->isScopeRequired());
    test()->assertFalse(r($f6)->isScopeRequired());
    test()->assertFalse(r($f7)->isScopeRequired());
    test()->assertTrue(r($f8)->isScopeRequired());
    test()->assertTrue(r($f9)->isScopeRequired(), 'new static()');
    test()->assertTrue(r($f10)->isScopeRequired());
    test()->assertFalse(r($f11)->isScopeRequired());
});

// Helpers
function c(Closure $closure)
{
    $r = new ReflectionClosure($closure);
    return $r->getCode();
}

function r(Closure $closure) {
    return new ReflectionClosure($closure);
}

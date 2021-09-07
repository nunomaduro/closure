<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

use Opis\Closure\ReflectionClosure;
use Foo\{
    Bar as Baz,
    Baz\Qux
};
use Opis\Closure\SerializableClosure;

test('is short closure', function () {
    $f1 = fn() => 1;
    $f2 = static fn() => 1;
    $f3 = function () { fn() => 1; };

    test()->assertTrue(r($f1)->isShortClosure());
    test()->assertTrue(r($f2)->isShortClosure());
    test()->assertFalse(r($f3)->isShortClosure());
});

test('basic short closure', function () {
    $f1 = fn() => "hello";
    $e1 = 'fn() => "hello"';

    $f2 = fn&() => "hello";
    $e2 = 'fn&() => "hello"';

    $f3 = fn($a) => "hello";
    $e3 = 'fn($a) => "hello"';

    $f4 = fn(&$a) => "hello";
    $e4 = 'fn(&$a) => "hello"';

    $f5 = fn(&$a) : string => "hello";
    $e5 = 'fn(&$a) : string => "hello"';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
    test()->assertEquals($e4, c($f4));
    test()->assertEquals($e5, c($f5));
});

test('resolve types', function () {
    $f1 = fn(Baz $a) => "hello";
    $e1 = 'fn(\Foo\Bar $a) => "hello"';

    $f2 = fn(Baz $a) : Qux => "hello";
    $e2 = 'fn(\Foo\Bar $a) : \Foo\Baz\Qux => "hello"';

    $f3 = fn(Baz $a) : int => (function (Qux $x) {})();
    $e3 = 'fn(\Foo\Bar $a) : int => (function (\Foo\Baz\Qux $x) {})()';

    $f4 = fn() => new Qux();
    $e4 = 'fn() => new \Foo\Baz\Qux()';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
    test()->assertEquals($e4, c($f4));
});

test('class keywords instantiation', function () {
    test()->assertEquals(
        'function () { return new self(); }',
        c(function () { return new self(); })
    );

    test()->assertEquals(
        'function () { return new static(); }',
        c(function () { return new static(); })
    );

    test()->assertEquals(
        'function () { return new parent(); }',
        c(function () { return new parent(); })
    );
});

test('function inside expressions and arrays', function () {
    $f1 = (fn () => 1);
    $e1 = 'fn () => 1';

    $f2 = [fn () => 1];
    $e2 = 'fn () => 1';

    $f3 = [fn () => 1, 0];
    $e3 = 'fn () => 1';

    $f4 = fn () => ($a === true) && (!empty([0,1,]));
    $e4 = 'fn () => ($a === true) && (!empty([0,1,]))';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2[0]));
    test()->assertEquals($e3, c($f3[0]));
    test()->assertEquals($e4, c($f4));
});

test('serialize', function () {
    $f1 = fn() => 'hello';
    $c1 = s($f1);

    $f2 = fn($a, $b) => $a + $b;
    $c2 = s($f2);

    $a = 4;
    $f3 = fn(int $b, int $c = 5) : int => ($a + $b) * $c;
    $c3 = s($f3);

    test()->assertEquals('hello', $c1());
    test()->assertEquals(7, $c2(4, 3));
    test()->assertEquals(40, $c3(4));
    test()->assertEquals(48, $c3(4, 6));
});

test('typed properties', function () {
    $user = new User();
    $s = s(function () use ($user) {
        return true;
    });
    test()->assertTrue($s());

    $user = new User();
    $product = new Product();
    $product->name = "PC";
    $user->setProduct($product);

    $u = s(function () use ($user) {
        return $user->getProduct()->name;
    });

    test()->assertEquals('PC', $u());
});

// Helpers
function c(Closure $closure)
{
    $r = new ReflectionClosure($closure);
    return $r->getCode();
}

function r(Closure $closure)
{
    return new ReflectionClosure($closure);
}

function s(Closure $closure)
{
    return unserialize(serialize(new SerializableClosure($closure)))->getClosure();
}

function getProduct(): Product
{
    return test()->product;
}

function setProduct(Product $product): void
{
    test()->product = $product;
}

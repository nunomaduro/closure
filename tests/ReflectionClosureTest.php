<?php
/* ===========================================================================
 * Copyright (c) 2018-2019 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

use Opis\Closure\ReflectionClosure;

// Fake
use Foo\Bar;
use Foo\Baz as Qux;

test('new instance', function () {
    $f = function (){ $c = '\A'; new $c;};
    $e = 'function (){ $c = \'\A\'; new $c;}';
    test()->assertEquals($e, c($f));
});

test('new instance2', function () {
    $f = function (){ new A; };
    $e = 'function (){ new \Opis\Closure\Test\A; }';
    test()->assertEquals($e, c($f));

    $f = function (){ new A\B; };
    $e = 'function (){ new \Opis\Closure\Test\A\B; }';
    test()->assertEquals($e, c($f));

    $f = function (){ new \A; };
    $e = 'function (){ new \A; }';
    test()->assertEquals($e, c($f));

    $f = function (){ new A(new B, [new C]); };
    $e = 'function (){ new \Opis\Closure\Test\A(new \Opis\Closure\Test\B, [new \Opis\Closure\Test\C]); }';
    test()->assertEquals($e, c($f));

    $f = function (){ new self; new static; new parent; };
    $e = 'function (){ new self; new static; new parent; }';
    test()->assertEquals($e, c($f));
});

test('instance of', function () {
    $f = function (){ $c = null; $b = '\X\y'; v($c instanceof $b);};
    $e = 'function (){ $c = null; $b = \'\X\y\'; v($c instanceof $b);}';
    test()->assertEquals($e, c($f));
});

test('closure resolve arguments', function () {
    $f1 = function (Bar $p){};
    $e1 = 'function (\Foo\Bar $p){}';

    $f2 = function (Bar\Test $p){};
    $e2 = 'function (\Foo\Bar\Test $p){}';

    $f3 = function (Qux $p){};
    $e3 = 'function (\Foo\Baz $p){}';

    $f4 = function (Qux\Test $p){};
    $e4 = 'function (\Foo\Baz\Test $p){}';

    $f5 = function (\Foo $p){};
    $e5 = 'function (\Foo $p){}';

    $f6 = function (Foo $p){};
    $e6 = 'function (\\' . __NAMESPACE__ . '\Foo $p){}';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
    test()->assertEquals($e4, c($f4));
    test()->assertEquals($e5, c($f5));
    test()->assertEquals($e6, c($f6));
});

test('cloure resolve in body', function () {
    $f1 = function () { return new Bar(); };
    $e1 = 'function () { return new \Foo\Bar(); }';

    $f2 = function () { return new Bar\Test(); };
    $e2 = 'function () { return new \Foo\Bar\Test(); }';

    $f3 = function () { return new Qux(); };
    $e3 = 'function () { return new \Foo\Baz(); }';

    $f4 = function () { return new Qux\Test(); };
    $e4 = 'function () { return new \Foo\Baz\Test(); }';

    $f5 = function () { return new \Foo(); };
    $e5 = 'function () { return new \Foo(); }';

    $f6 = function () { return new Foo(); };
    $e6 = 'function () { return new \\' . __NAMESPACE__ . '\Foo(); }';


    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
    test()->assertEquals($e4, c($f4));
    test()->assertEquals($e5, c($f5));
    test()->assertEquals($e6, c($f6));
});

test('closure resolve static method', function () {

    $f1 = function () { return Bar::test(); };
    $e1 = 'function () { return \Foo\Bar::test(); }';

    $f2 = function () { return Bar\Test::test(); };
    $e2 = 'function () { return \Foo\Bar\Test::test(); }';

    $f3 = function () { return Qux::test(); };
    $e3 = 'function () { return \Foo\Baz::test(); }';

    $f4 = function () { return Qux\Test::test(); };
    $e4 = 'function () { return \Foo\Baz\Test::test(); }';

    $f5 = function () { return \Foo::test(); };
    $e5 = 'function () { return \Foo::test(); }';

    $f6 = function () { return Foo::test(); };
    $e6 = 'function () { return \\' . __NAMESPACE__ . '\Foo::test(); }';


    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
    test()->assertEquals($e3, c($f3));
    test()->assertEquals($e4, c($f4));
    test()->assertEquals($e5, c($f5));
    test()->assertEquals($e6, c($f6));
});

test('static inside closure', function () {
    $f1 = function() { return static::foo(); };
    $e1 = 'function() { return static::foo(); }';

    $f2 = function ($a) { return $a instanceof static; };
    $e2 = 'function ($a) { return $a instanceof static; }';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
});

test('self inside closure', function () {
    $f1 = function() { return self::foo(); };
    $e1 = 'function() { return self::foo(); }';

    $f2 = function ($a) { return $a instanceof self; };
    $e2 = 'function ($a) { return $a instanceof self; }';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
});

test('parent inside closure', function () {
    $f1 = function() { return parent::foo(); };
    $e1 = 'function() { return parent::foo(); }';

    $f2 = function ($a) { return $a instanceof parent; };
    $e2 = 'function ($a) { return $a instanceof parent; }';

    test()->assertEquals($e1, c($f1));
    test()->assertEquals($e2, c($f2));
});

test('interpolation1', function () {
    $f1 = function() { return "${foo}${bar}{$foobar}"; };
    $e1 = 'function() { return "${foo}${bar}{$foobar}"; }';

    test()->assertEquals($e1, c($f1));
});

// Helpers
function c(Closure $closure)
{
    $r = new ReflectionClosure($closure);
    return $r->getCode();
}

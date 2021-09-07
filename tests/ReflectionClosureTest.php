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
    expect(c($f))->toEqual($e);
});

test('new instance2', function () {
    $f = function (){ new A; };
    $e = 'function (){ new \Opis\Closure\Test\A; }';
    expect(c($f))->toEqual($e);

    $f = function (){ new A\B; };
    $e = 'function (){ new \Opis\Closure\Test\A\B; }';
    expect(c($f))->toEqual($e);

    $f = function (){ new \A; };
    $e = 'function (){ new \A; }';
    expect(c($f))->toEqual($e);

    $f = function (){ new A(new B, [new C]); };
    $e = 'function (){ new \Opis\Closure\Test\A(new \Opis\Closure\Test\B, [new \Opis\Closure\Test\C]); }';
    expect(c($f))->toEqual($e);

    $f = function (){ new self; new static; new parent; };
    $e = 'function (){ new self; new static; new parent; }';
    expect(c($f))->toEqual($e);
});

test('instance of', function () {
    $f = function (){ $c = null; $b = '\X\y'; v($c instanceof $b);};
    $e = 'function (){ $c = null; $b = \'\X\y\'; v($c instanceof $b);}';
    expect(c($f))->toEqual($e);
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

    expect(c($f1))->toEqual($e1);
    expect(c($f2))->toEqual($e2);
    expect(c($f3))->toEqual($e3);
    expect(c($f4))->toEqual($e4);
    expect(c($f5))->toEqual($e5);
    expect(c($f6))->toEqual($e6);
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


    expect(c($f1))->toEqual($e1);
    expect(c($f2))->toEqual($e2);
    expect(c($f3))->toEqual($e3);
    expect(c($f4))->toEqual($e4);
    expect(c($f5))->toEqual($e5);
    expect(c($f6))->toEqual($e6);
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


    expect(c($f1))->toEqual($e1);
    expect(c($f2))->toEqual($e2);
    expect(c($f3))->toEqual($e3);
    expect(c($f4))->toEqual($e4);
    expect(c($f5))->toEqual($e5);
    expect(c($f6))->toEqual($e6);
});

test('static inside closure', function () {
    $f1 = function() { return static::foo(); };
    $e1 = 'function() { return static::foo(); }';

    $f2 = function ($a) { return $a instanceof static; };
    $e2 = 'function ($a) { return $a instanceof static; }';

    expect(c($f1))->toEqual($e1);
    expect(c($f2))->toEqual($e2);
});

test('self inside closure', function () {
    $f1 = function() { return self::foo(); };
    $e1 = 'function() { return self::foo(); }';

    $f2 = function ($a) { return $a instanceof self; };
    $e2 = 'function ($a) { return $a instanceof self; }';

    expect(c($f1))->toEqual($e1);
    expect(c($f2))->toEqual($e2);
});

test('parent inside closure', function () {
    $f1 = function() { return parent::foo(); };
    $e1 = 'function() { return parent::foo(); }';

    $f2 = function ($a) { return $a instanceof parent; };
    $e2 = 'function ($a) { return $a instanceof parent; }';

    expect(c($f1))->toEqual($e1);
    expect(c($f2))->toEqual($e2);
});

test('interpolation1', function () {
    $f1 = function() { return "${foo}${bar}{$foobar}"; };
    $e1 = 'function() { return "${foo}${bar}{$foobar}"; }';

    expect(c($f1))->toEqual($e1);
});

// Helpers
function c(Closure $closure)
{
    $r = new ReflectionClosure($closure);
    return $r->getCode();
}

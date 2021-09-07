<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

use Closure;
use Opis\Closure\ReflectionClosure;
use Foo\{
    Bar as Baz,
};

test('resolve arguments', function () {
    $f1 = function (object $p){};
    $e1 = 'function (object $p){}';

    test()->assertEquals($e1, c($f1));
});

test('resolve return type', function () {
    $f1 = function (): object{};
    $e1 = 'function (): object{}';


    test()->assertEquals($e1, c($f1));
});

test('trailing comma', function () {
    $f1 = function (): Baz {};
    $e1 = 'function (): \Foo\Bar {}';

    test()->assertEquals($e1, c($f1));
});

// Helpers
function c(Closure $closure)
{
    $r = new ReflectionClosure($closure);
    return $r->getCode();
}

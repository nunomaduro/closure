<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

use Opis\Closure\ReflectionClosure;
use Foo\{
    Bar as Baz,
};

test('resolve arguments', function () {
    $f1 = function (object $p){};
    $e1 = 'function (object $p){}';

    expect(c($f1))->toEqual($e1);
});

test('resolve return type', function () {
    $f1 = function (): object{};
    $e1 = 'function (): object{}';


    expect(c($f1))->toEqual($e1);
});

test('trailing comma', function () {
    $f1 = function (): Baz {};
    $e1 = 'function (): \Foo\Bar {}';

    expect(c($f1))->toEqual($e1);
});

// Helpers
function c(Closure $closure)
{
    $r = new ReflectionClosure($closure);
    return $r->getCode();
}

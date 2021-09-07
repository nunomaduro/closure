<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

use Opis\Closure\ReflectionClosure;

// Fake
use Foo\{Bar, Baz as Qux};
use function Foo\f1;
use function Bar\{b1, b2 as b3};

test('resolve arguments', function () {
    $f1 = function (?Bar $p){};
    $e1 = 'function (?\Foo\Bar $p){}';

    $f2 = function (?Bar\Test $p){};
    $e2 = 'function (?\Foo\Bar\Test $p){}';

    $f3 = function (?Qux $p){};
    $e3 = 'function (?\Foo\Baz $p){}';

    $f4 = function (?Qux\Test $p){};
    $e4 = 'function (?\Foo\Baz\Test $p){}';

    $f5 = function (?array $p, ?string $x){};
    $e5 = 'function (?array $p, ?string $x){}';


    expect(c($f1))->toEqual($e1);
    expect(c($f2))->toEqual($e2);
    expect(c($f3))->toEqual($e3);
    expect(c($f4))->toEqual($e4);
    expect(c($f5))->toEqual($e5);
});

test('resolve return type', function () {
    $f1 = function (): ?Bar{};
    $e1 = 'function (): ?\Foo\Bar{}';

    $f2 = function (): ?Bar\Test{};
    $e2 = 'function (): ?\Foo\Bar\Test{}';

    $f3 = function (): ?Qux{};
    $e3 = 'function (): ?\Foo\Baz{}';

    $f4 = function (): ?Qux\Test{};
    $e4 = 'function (): ?\Foo\Baz\Test{}';

    $f5 = function (): ?\Foo{};
    $e5 = 'function (): ?\Foo{}';

    $f6 = function (): ?Foo{};
    $e6 = 'function (): ?\\' . __NAMESPACE__. '\Foo{}';

    $f7 = function (): ?array{};
    $e7 = 'function (): ?array{}';

    $f8 = function (): ?string{};
    $e8 = 'function (): ?string{}';

    $f9 = function (): void{};
    $e9 = 'function (): void{}';

    expect(c($f1))->toEqual($e1);
    expect(c($f2))->toEqual($e2);
    expect(c($f3))->toEqual($e3);
    expect(c($f4))->toEqual($e4);
    expect(c($f5))->toEqual($e5);
    expect(c($f6))->toEqual($e6);
    expect(c($f7))->toEqual($e7);
    expect(c($f8))->toEqual($e8);
    expect(c($f9))->toEqual($e9);
});

// Helpers
function c(Closure $closure)
{
    $r = new ReflectionClosure($closure);
    return $r->getCode();
}

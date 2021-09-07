<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */


test('recursive array', function () {
    $a = ['foo'];
    $a[] = &$a;
    $f = function () use($a){
        return $a[1][0];
    };
    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
    expect($u())->toEqual('foo');
});

test('recursive array2', function () {
    $a = ['foo'];
    $a[] = &$a;
    $f = function () use(&$a){
        return $a[1][0];
    };
    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
    expect($u())->toEqual('foo');
});

test('recursive array3', function () {
    $f = function () {
        return true;
    };
    $a = [$f];
    $a[] = &$a;

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
    $u = $u[1][0];
    expect($u())->toBeTrue();
});

test('recursive array4', function () {
    $a = [];
    $f = function () use($a) {
        return true;
    };
    $a[] = $f;
    $a[] = &$a;

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
    $u = $u[1][0];
    expect($u())->toBeTrue();
});

test('recursive array5', function () {
    $a = [];
    $f = function () use(&$a) {
        return true;
    };
    $a[] = $f;
    $a[] = &$a;

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
    $u = $u[1][0];
    expect($u())->toBeTrue();
});

test('recursive array6', function () {
    $o = new stdClass();
    $o->a = [];
    $f = function () {
        return true;
    };
    $a = &$o->a;
    $a[] = $f;
    $a[] = &$a;

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($o));
    $u = $u->a[1][0];
    expect($u())->toBeTrue();
});

test('recursive array7', function () {
    $o = new stdClass();
    $o->a = [];
    $f = function () use($o){
        return true;
    };
    $a = &$o->a;
    $a[] = $f;
    $a[] = &$a;

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($o));
    $u = $u->a[1][0];
    expect($u())->toBeTrue();
});

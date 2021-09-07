<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

use Opis\Closure\SecurityException;
use Opis\Closure\SerializableClosure;

uses(ClosureTest::class);

test('secure closure integrity fail', function () {
    if (method_exists($this, 'expectException')) {
        test()->expectException('\Opis\Closure\SecurityException');
    } else {
        test()->setExpectedException('\Opis\Closure\SecurityException');
    }

    $closure = function(){
        /*x*/
    };

    SerializableClosure::setSecretKey('secret');

    $value = serialize(new SerializableClosure($closure));
    $value = str_replace('*x*', '*y*', $value);
    unserialize($value);
});

test('json secure closure integrity fail', function () {
    if (method_exists($this, 'expectException')) {
        test()->expectException('\Opis\Closure\SecurityException');
    } else {
        test()->setExpectedException('\Opis\Closure\SecurityException');
    }

    $closure = function(){
        /*x*/
    };

    SerializableClosure::setSecretKey('secret');

    $value = serialize(new JsonSerializableClosure($closure));
    $value = str_replace('*x*', '*y*', $value);
    unserialize($value);
});

test('unsecured closure with security provider', function () {
    if (method_exists($this, 'expectException')) {
        test()->expectException('\Opis\Closure\SecurityException');
    } else {
        test()->setExpectedException('\Opis\Closure\SecurityException');
    }

    SerializableClosure::removeSecurityProvider();

    $closure = function(){
        /*x*/
    };

    $value = serialize(new SerializableClosure($closure));
    SerializableClosure::setSecretKey('secret');
    unserialize($value);
});

test('json unsecured closure with security provider', function () {
    if (method_exists($this, 'expectException')) {
        test()->expectException('\Opis\Closure\SecurityException');
    } else {
        test()->setExpectedException('\Opis\Closure\SecurityException');
    }

    SerializableClosure::removeSecurityProvider();

    $closure = function(){
        /*x*/
    };

    $value = serialize(new JsonSerializableClosure($closure));
    SerializableClosure::setSecretKey('secret');
    unserialize($value);
});

test('secured closure without securiy provider', function () {
    SerializableClosure::setSecretKey('secret');

    $closure = function(){
        return true;
    };

    $value = serialize(new SerializableClosure($closure));
    SerializableClosure::removeSecurityProvider();
    $closure = unserialize($value)->getClosure();
    test()->assertTrue($closure());
});

test('json secured closure without securiy provider', function () {
    SerializableClosure::setSecretKey('secret');

    $closure = function(){
        return true;
    };

    $value = serialize(new SerializableClosure($closure));
    SerializableClosure::removeSecurityProvider();
    $closure = unserialize($value)->getClosure();
    test()->assertTrue($closure());
});

test('invalid secured closure without securiy provider', function () {
    if (method_exists($this, 'expectException')) {
        test()->expectException('\Opis\Closure\SecurityException');
    } else {
        test()->setExpectedException('\Opis\Closure\SecurityException');
    }

    SerializableClosure::setSecretKey('secret');
    $closure = function(){
        /*x*/
    };

    $value = serialize(new SerializableClosure($closure));
    $value = str_replace('.', ',', $value);
    SerializableClosure::removeSecurityProvider();
    unserialize($value);
});

test('invalid json secured closure without securiy provider', function () {
    if (method_exists($this, 'expectException')) {
        test()->expectException('\Opis\Closure\SecurityException');
    } else {
        test()->setExpectedException('\Opis\Closure\SecurityException');
    }

    SerializableClosure::setSecretKey('secret');
    $closure = function(){
        /*x*/
    };

    $value = serialize(new JsonSerializableClosure($closure));
    $value = str_replace('hash', 'hash1', $value);
    SerializableClosure::removeSecurityProvider();
    unserialize($value);
});

test('mixed encodings', function () {
    $a = iconv('utf-8', 'utf-16', "Düsseldorf");
    $b = utf8_decode("Düsseldorf");

    $closure = function() use($a, $b) {
        return [$a, $b];
    };

    SerializableClosure::setSecretKey('secret');

    $value = serialize(new SerializableClosure($closure));
    $u = unserialize($value)->getClosure();
    $r = $u();

    test()->assertEquals($a, $r[0]);
    test()->assertEquals($b, $r[1]);
});

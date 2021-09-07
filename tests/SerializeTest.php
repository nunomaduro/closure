<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

use Opis\Closure\ReflectionClosure;
use stdClass;
use Closure;

test('custom serialization', function () {
    $f =  function ($value){
        return $value;
    };

    $a = new Abc($f);
    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
    test()->assertTrue($u->test(true));
});

test('custom serialization same objects', function () {
    $f =  function ($value){
        return $value;
    };

    $i = new Abc($f);
    $a = array($i, $i);
    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));

    test()->assertTrue($u[0] === $u[1]);
});

test('custom serialization this object1', function () {
    $a = new A2();
    $a = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
    test()->assertEquals('Hello, World!', $a->getPhrase());
});

test('custom serialization this object2', function () {
    $a = new A2();
    $a = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
    test()->assertTrue($a->getEquality());
});

test('custom serialization same closures', function () {
    $f =  function ($value){
        return $value;
    };

    $i = new Abc($f);
    $a = array($i, $i);
    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
    test()->assertTrue($u[0]->getF() === $u[1]->getF());
});

test('custom serialization same closures2', function () {
    $f =  function ($value){
        return $value;
    };

    $a = array(new Abc($f), new Abc($f));
    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
    test()->assertTrue($u[0]->getF() === $u[1]->getF());
});

test('private method clone', function () {
    $a = new Clone1();
    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
    test()->assertEquals(1, $u->value());
});

test('private method clone2', function () {
    $a = new Clone1();
    $f = function () use($a){
        return $a->value();
    };
    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
    test()->assertEquals(1, $u());
});

test('nested objects', function () {
    $parent = new Entity();
    $child = new Entity();
    $parent->children[] = $child;
    $child->parent = $parent;

    $f = function () use($parent, $child){
        return $parent === $child->parent;
    };

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
    test()->assertTrue($u());
});

test('nested objects2', function () {
    $child = new stdClass();
    $parent = new stdClass();
    $child->parent = $parent;
    $parent->childern = [$child];
    $parent->closure = function () use($child){
        return true;
    };
    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($parent))->closure;
    test()->assertTrue($u());
});

test('nested objects3', function () {
    $obj = new \stdClass;
    $obj->closure = function ($arg) use ($obj) {
        return $arg === $obj;
    };

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($obj));
    $c = $u->closure;
    test()->assertTrue($c($u));
});

test('nested objects4', function () {
    $parent = new \stdClass;
    $child1 = new \stdClass;

    $child1->parent = $parent;

    $parent->closure = function ($p) use ($child1) {
        return $child1->parent === $p;
    };

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($parent));
    $c = $u->closure;
    test()->assertTrue($c($u));
});

test('nested objects5', function () {
    $parent = new \stdClass;
    $child1 = new \stdClass;
    $child2 = new \stdClass;

    $child1->parent = $parent;
    $child2->parent = $parent;

    $parent->closure = function ($p) use ($child1, $child2) {
        return $child1->parent === $child2->parent && $child1->parent === $p;
    };

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($parent));
    $c = $u->closure;
    test()->assertTrue($c($u));
});

test('private property in parent class', function () {
    $instance = new ChildClass;

    $closure = function () use ($instance) {
        return $instance->getFoobar();
    };

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($closure));
    test()->assertSame(['test'], $u());
});

test('internal class1', function () {
    $date = new \DateTime();
    $date->setDate(2018, 2, 23);

    $closure = function () use($date){
        return $date->format('Y-m-d');
    };

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($closure));
    test()->assertEquals('2018-02-23', $u());
});

test('internal class2', function () {
    $date = new \DateTime();
    $date->setDate(2018, 2, 23);
    $instance = (object)['date' => $date];
    $closure = function () use($instance){
        return $instance->date->format('Y-m-d');
    };

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($closure));
    test()->assertEquals('2018-02-23', $u());
});

test('internal class3', function () {
    $date = new \DateTime();
    $date->setDate(2018, 2, 23);
    $instance = (object)['date' => $date];

    $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($instance));
    test()->assertEquals('2018-02-23', $u->date->format('Y-m-d'));
});

test('object reserialization', function () {
    $o = (object)['foo'=>'bar'];
    $s1 = \Opis\Closure\serialize($o);
    $o = \Opis\Closure\unserialize($s1);
    $s2 = \Opis\Closure\serialize($o);

    test()->assertEquals($s1, $s2);
});

test('is short closure', function () {
    $f = function () { };

    test()->assertFalse((new ReflectionClosure($f))->isShortClosure());
});

test('if this is correctly serialized', function () {
    $o = new Clone1();
    $f = $o->create();
    $f = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
    $f = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
    test()->assertEquals(1, $f());
});

test('test', function ($value) {
    $f = test()->f;
    return $f($value);
});

// Helpers
function __construct(Closure $f)
{
    test()->f = $f;
}

function getF()
{
    return test()->f;
}

function __clone()
{
}

function value()
{
    return test()->a;
}

function create() {
    return function () {
        return test()->a;
    };
}

function getFoobar()
{
    return test()->foobar;
}

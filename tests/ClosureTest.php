<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

use Opis\Closure\ReflectionClosure;
use Opis\Closure\SerializableClosure;

test('closure use return value', function () {
    $a = 100;
    $c = function() use($a) {
        return $a;
    };

    $u = s($c);

    expect($a)->toEqual($u());
});

test('closure use transformation', function () {
    $a = 100;

    $c = unserialize(serialize(new TransformingSerializableClosure(function () use ($a) {
        return $a;
    })));

    expect($c())->toEqual(100);
});

test('closure use return closure', function () {
    $a = function($p){
        return $p + 1;
    };
    $b = function($p) use($a){
        return $a($p);
    };

    $v = 1;
    $u = s($b);

    expect($u(1))->toEqual($v + 1);
});

test('closure use return closure by ref', function () {
    $a = function($p){
        return $p + 1;
    };
    $b = function($p) use(&$a){
        return $a($p);
    };

    $v = 1;
    $u = s($b);

    expect($u(1))->toEqual($v + 1);
});

test('closure use self', function () {

    $a = function() use (&$a){
        return $a;
    };
    $u = s($a);

    expect($u())->toEqual($u);
});

test('closure use self in array', function () {

    $a = array();

    $b = function() use(&$a){
        return $a[0];
    };

    $a[] = $b;

    $u = s($b);

    expect($u())->toEqual($u);
});

test('closure use self in object', function () {

    $a = new stdClass();

    $b = function() use(&$a){
        return $a->me;
    };

    $a->me = $b;

    $u = s($b);

    expect($u())->toEqual($u);
});

test('closure use self in multi array', function () {
    $a = array();
    $x = null;

    $b = function() use(&$x){
        return $x;
    };

    $c = function($i) use (&$a) {
        $f = $a[$i];
        return $f();
    };

    $a[] = $b;
    $a[] = $c;
    $x = $c;

    $u = s($c);

    expect($u(0))->toEqual($u);
});

test('closure use self in instance', function () {
    $i = new ObjSelf();
    $c = function ($c) use($i){
        return $c === $i->o;
    };
    $i->o = $c;
    $u = s($c);
    expect($u($u))->toBeTrue();
});

test('closure use self in instance2', function () {
    $i = new ObjSelf();
    $c = function () use(&$c, $i){
        return $c == $i->o;
    };
    $i->o = &$c;
    $u = s($c);
    expect($u())->toBeTrue();
});

test('closure serialization twice', function () {
    $a = function($p){
        return $p;
    };

    $b = function($p) use($a){
        return $a($p);
    };

    $u = s(s($b));

    expect($u('ok'))->toEqual('ok');
});

test('closure real serialization', function () {
    $f = function($a, $b){
        return $a + $b;
    };

    $u = s(s($f));
    expect($u(2, 3))->toEqual(5);
});

test('closure objectin object', function () {
    $f = function() use (&$f) {
        return $f;
    };

    $t = new ObjnObj();
    $t->func = $f;

    $t2 = new ObjnObj();
    $t2->func = $f;

    $t->subtest = $t2;

    $x = unserialize(serialize($t));

    $g = $x->func;
    $g = $g();

    $ok = $x->func == $x->subtest->func;
    $ok = $ok && ($x->subtest->func == $g);

    expect($ok)->toEqual(true);
});

test('closure nested', function () {
    $o = function($a) {

        // this should never happen
        if ($a === false) {
            return false;
        }

        $n = function ($b) {
            return !$b;
        };

        $ns = unserialize(serialize(new SerializableClosure($n)));

        return $ns(false);
    };

    $os = s($o);

    expect($os(true))->toEqual(true);
});

test('closure curly syntax', function () {
    $f = function (){
        $x = (object)array('a' => 1, 'b' => 3);
        $b = 'b';
        return $x->{'a'} + $x->{$b};
    };
    $f = s($f);
    expect($f())->toEqual(4);
});

test('closure bind to object', function () {
    $a = new A();

    $b = function(){
        return aPublic();
    };

    $b = $b->bindTo($a, __NAMESPACE__ . "\\A");

    $u = s($b);

    expect($u())->toEqual('public called');
});

test('closure bind to object scope', function () {
    $a = new A();

    $b = function(){
        return aProtected();
    };

    $b = $b->bindTo($a, __NAMESPACE__ . "\\A");

    $u = s($b);

    expect($u())->toEqual('protected called');
});

test('closure bind to object static scope', function () {
    $a = new A();

    $b = function(){
        return static::aStaticProtected();
    };

    $b = $b->bindTo(null, __NAMESPACE__ . "\\A");

    $u = s($b);

    expect($u())->toEqual('static protected called');
});

test('closure static', function () {
    $f = static function(){};
    $rc = new ReflectionClosure($f);
    expect($rc->isStatic())->toBeTrue();
});

test('closure static fail', function () {
    $f = static
        // This will not work
    function(){};
    $rc = new ReflectionClosure($f);
    expect($rc->isStatic())->toBeFalse();
});

test('create closure', function () {
    $closure = SerializableClosure::createClosure('$a, $b', 'return $a + $b;');

    test()->assertNotNull($closure);
    expect($closure instanceof Closure)->toBeTrue();
    expect($closure(7, 10))->toEqual(17);

    $closure = s($closure);

    test()->assertNotNull($closure);
    expect($closure instanceof Closure)->toBeTrue();
    expect($closure(5, 6))->toEqual(11);
});

test('closure scope remains the same', function () {
    $f = function () { static $i = 0;};
    $o = s($f);

    $rf = new ReflectionClosure($f);
    $ro = new ReflectionClosure($o);

    test()->assertNotNull($rf->getClosureScopeClass());
    test()->assertNotNull($ro->getClosureScopeClass());
    expect($ro->getClosureScopeClass()->name)->toEqual($rf->getClosureScopeClass()->name);
    expect($ro->getClosureScopeClass()->name)->toEqual($rf->getClosureScopeClass()->name);
    expect($ro->getClosureScopeClass()->name)->toEqual(__CLASS__);

    $f = $f->bindTo(null, null);
    $o = s($f);
    $rf = new ReflectionClosure($f);
    $ro = new ReflectionClosure($o);

    expect($rf->getClosureScopeClass())->toBeNull();
    expect($ro->getClosureScopeClass())->toBeNull();
});

// Helpers
function s($closure)
{
    if($closure instanceof Closure)
    {
        $closure = new SerializableClosure($closure);
    }

    return unserialize(serialize($closure))->getClosure();
}

function serialize() {

    SerializableClosure::enterContext();

    $object = serialize(array(
        'subtest' => test()->subtest,
        'func' => SerializableClosure::from(test()->func),
    ));

    SerializableClosure::exitContext();

    return $object;
}

function unserialize($data) {

    $data = unserialize($data);

    test()->subtest = $data['subtest'];
    test()->func = $data['func']->getClosure();
}

function aStaticProtected() {
    return 'static protected called';
}

function aProtected()
{
    return 'protected called';
}

function aPublic()
{
    return 'public called';
}

function __construct()
{
    test()->closure1 = function (){
        return test()->phrase;
    };
    test()->closure2 = function (){
        return $this;
    };
    test()->closure3 = function (){
        $c = test()->closure2;
        return $this === $c();
    };
}

function getPhrase()
{
    $c = test()->closure1;
    return $c();
}

function getEquality()
{
    $c = test()->closure3;
    return $c();
}

function transformUseVariables($data)
{
    foreach ($data as $key => $value) {
        $data[$key] = $value * 2;
    }

    return $data;
}

function resolveUseVariables($data)
{
    foreach ($data as $key => $value) {
        $data[$key] = $value / 2;
    }

    return $data;
}

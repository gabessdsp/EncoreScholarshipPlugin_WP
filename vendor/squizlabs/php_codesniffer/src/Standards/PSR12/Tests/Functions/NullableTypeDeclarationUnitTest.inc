<?php

class MyClass {
    public function validNullableTypeHinting(
        ?array $array = null,
        ?MyObject $object
   ): ?MyObject
    {
    }

    public function validWithFQN(
        ?\MyNameSpace\MyArray $array = null,
        ?\MyNameSpace\MyObject $object
   ): ?\MyNameSpace\MyObject
    {
    }

    public function validReturnTypeForThisSniff():?MyObject
    {
    }

    public function invalidTooMuchWhitespace(
        ? array $array = null,
        ? MyObject
   ):? MyObject
    {
    }

    public function invalidWithFQNTooMuchWhitespace(
        ? \MyNameSpace\MyArray $array = null,
        ? \MyNameSpace\MyObject
   ):? \MyNameSpace\MyObject
    {
    }
}

// valid in lambda
array_map(function(?int $integer = null): ?int {
    return $integer;
}, []);

// invalid in lambda
array_map(function(? int $integer = null): ? int {
    return $integer;
}, []);

interface MyInterface {
    public function invalidTypeHintsWithNewlinesAndComments(?
        string $varA,
        ? /* a comment, fixing is undesirable */ array $varB,
        ? // phpcs:ignore Standard.Cat.Sniff -- fixing is undesirable
        int $varC
   ) ?
        bool;
}

function testSelf(? self $self) : ? self {}
function testParent(? parent $parent) : ? parent {}
function testCallable(? callable $callable) : ? callable {}

// Issue #2552.
class TestTokenizingOfNullableVsInlineThen {
    public function testStatic() {
        $test = Something::one(self::CONSTANT) ?: '';
        $test = Something::one(static::CONSTANT) ?: '';
    }
}

// Issue #2641.
$foo = new static(
    is_null($a) ? foo($a) : $a,
    is_null($b) ? $b : $c
);

// Issue #2791.
class testInstanceOf() {
    function testIt() {
        $foo = $value instanceof static ? '(' . $value . ')' : $value;
        $bar = $value instanceof static ? function_call($value) : $value;
        $baz = $value instanceof static ? array($value) : $value;
        $bal = $value instanceof static ? \className::$property : $value;
        $bal = $value instanceof static ? CONSTANT_NAME : $value;
    }
}

// PHP 8.0: static return type.
function testStatic() : ? static {}

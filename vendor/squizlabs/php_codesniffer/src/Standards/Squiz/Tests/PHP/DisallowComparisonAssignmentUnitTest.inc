<?php
$var = TRUE;
$var = ($foo === $bar);
$var = ($foo * $bar);
$var = !$foo;
$var = ($foo || $bar);
$var = ($foo === TRUE);
$var = ($foo === TRUE
        || $bar === FALSE);
$var = (!$foo);

$var = is_array($foo);
$var = myFunction($one, $two);
$var = myFunction(
        'one',
        'two'
      );

for ($i = ($stackPtr + 1); $i < $endStatement; $i++) {
}

// These conditions are allowed by this sniff.
$var = myFunction(!$var);

$depthAdv = array(
             $this,
             !$directLinks,
             FALSE,
           );

$var = myFunction(
    $var,
    array(
     $this,
     !$directLinks,
     FALSE,
   );
);

for ($node = $fieldsTag->nextSibling; $node; $node = $node->nextSibling) {
    if ($node->nodeType !== XML_ELEMENT_NODE) {
        continue;
    }

    for ($node = $fields->nextSibling; $node; $node = $node->nextSibling) {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }
    }
}

$a = $b ? $c : $d;
$a = $b === true ? $c : $d;

$this->_args = $this->_getArgs(($_SERVER['argv'] ?? []));
$args = ($_SERVER['argv'] ?? []);

$a = [
    'a' => ($foo) ? $foo : $bar,
];

$a = [
    'a' => ($foo) ? fn() => return 1 : fn() => return 2,
];

$var = $foo->something(!$var);
$var = $foo?->something(!$var);

$callback = function ($value) {
    if ($value > 10) {
        return false;
    }
};

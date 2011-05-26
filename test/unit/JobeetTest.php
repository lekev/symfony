<?php
// test/unit/JobeetTest.php

require_once dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(9);

$t->is(Jobeet::slugify('sensio'),'sensio','::slugify() convert alls characters to lower case');
$t->is(Jobeet::slugify('sensio labs'),'sensio-labs','::slugify() replaces a white space by a -');
$t->is(Jobeet::slugify('sensio    labs'),'sensio-labs','::slugify() replaces several white space by a single -');
$t->is(Jobeet::slugify('paris,france'), 'paris-france','::slugify() removes - at the beginning of a string');
$t->is(Jobeet::slugify('   sensio'),'sensio', '::slugify() removes - at the end of a string');
$t->is(Jobeet::slugify('sensio  '),'sensio','::slugify() replaces non-ASCII character by a -');
$t->is(Jobeet::slugify(''),'n-a','::slugify() converts the empty string to n-a');
$t->is(Jobeet::slugify(' - '),'n-a','::slugify() converts a string that only contains non Ascci charachter to n-a');
if(function_exists('iconv'))
{
	$t->is(Jobeet::slugify('Développeur web'), 'developpeur-web','::slugify() removes accents');
}else{
	$t->skip('::slugify() remove accents -iconv not installed');
}
?>
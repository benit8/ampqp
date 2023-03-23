<?php

$finder = PhpCsFixer\Finder::create()
	->in(dirname(__DIR__) . '/examples')
	->in(dirname(__DIR__) . '/src')
;

$config = new PhpCsFixer\Config();
return $config->setRules([
		'@PSR12' => true,
		'@PHP81Migration' => true,
		'@PHP80Migration:risky' => true,

		// Allow declare() to be on the opening line
		'blank_line_after_opening_tag' => false,
		'concat_space' => ['spacing' => 'one'],
		'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
	])
	->setFinder($finder)
	->setIndent("\t")
;

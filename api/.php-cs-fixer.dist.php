<?php

$finder = PhpCsFixer\Finder::create()
  ->in(__DIR__)
  ->name('*.php')
  ->notName('*.blade.php')
  ->exclude('vendor')
  ->exclude('storage')
  ->exclude('bootstrap')
  ->exclude('public')
  ->ignoreDotFiles(true)
  ->ignoreVCS(true);

return (new PhpCsFixer\Config())
  ->setRules([
    "@PSR2" => true,
    "single_quote" => false
  ])->setFinder($finder);

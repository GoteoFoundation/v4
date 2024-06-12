<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false
        ]
    ])
    ->setFinder($finder);

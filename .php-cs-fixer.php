<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
;

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PSR2'                                 => true,
    'blank_line_after_opening_tag'          => true,
    'no_leading_namespace_whitespace'       => true,
    'no_blank_lines_after_class_opening'    => true,
    'no_trailing_comma_in_singleline_array' => true,
    'no_blank_lines_after_phpdoc'           => true,
    'concat_space'                          => ['spacing' => 'one'],
    'ordered_imports'                       => true,
    'blank_line_before_statement'           => true,
    'no_extra_blank_lines'                  => true,
    'no_unused_imports'                     => true,
    'no_whitespace_in_blank_line'           => true,
    'phpdoc_order'                          => true,
    'phpdoc_align'                          => ['tags' => ['param', 'return', 'throws', 'type', 'var']],
    'phpdoc_scalar'                         => true,
    'array_syntax'                          => ['syntax' => 'short'],
    'binary_operator_spaces'                => ['operators' => ['==' => 'align', '=' => 'align', '=>' => 'align']],
])
    ->setFinder($finder)
    ;

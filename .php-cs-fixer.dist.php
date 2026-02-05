<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('node_modules')
    ->append([
        'bin/console',
    ])
    ->path([
        'bin/',
        'config/',
        'public/',
        'src/',
        'tests/',
    ])
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'phpdoc_tag_type' => false,
        'phpdoc_indent' => false,
        'single_quote' => false,
        'concat_space' => false,
        'phpdoc_summary' => false,
        'trailing_comma_in_multiline' => false,
        'yoda_style' => false,
        'no_superfluous_phpdoc_tags' => false,
        'phpdoc_separation' => false,
        'no_empty_phpdoc' => false,
        'array_syntax' => false,
        'no_unused_imports' => false,
        'blank_line_before_statement' => false,
        'increment_style' => false,
        'ordered_imports' => false,
        'binary_operator_spaces' => false,
        'phpdoc_scalar' => false,
        'operator_linebreak' => false,
        'phpdoc_align' => false,
        'no_extra_blank_lines' => false,
        'class_attributes_separation' => false,
        'statement_indentation' => false,
        'single_line_throw' => false,
        'phpdoc_trim' => false,
        'no_unneeded_control_parentheses' => false,
        'cast_spaces' => false,
        'array_indentation' => false,
        'phpdoc_to_comment' => false,
        'no_trailing_comma_in_singleline' => false,
        'no_multiline_whitespace_around_double_arrow' => false,
        'whitespace_after_comma_in_array' => false,
        'single_line_comment_spacing' => false,
        'trim_array_spaces' => false,
        'fully_qualified_strict_types' => false,
        'phpdoc_no_package' => false,
        'no_singleline_whitespace_before_semicolons' => false,
        'function_declaration' => false,
        'global_namespace_import' => false,
        'align_multiline_comment' => false,
        'phpdoc_var_without_name' => false,
        'phpdoc_types_order' => false,
        'phpdoc_annotation_without_dot' => false,
        'ordered_types' => false,
        'no_useless_concat_operator' => false,
        'no_null_property_initialization' => false,
        'types_spaces' => false,
        'phpdoc_trim_consecutive_blank_line_separation' => false,
        'phpdoc_single_line_var_spacing' => false,
        'phpdoc_order' => false,
        'no_whitespace_in_blank_line' => false,
        'no_trailing_whitespace' => false,
        'no_short_bool_cast' => false,
        'no_alias_language_construct_call' => false,
        'method_argument_space' => false,
        'class_reference_name_casing' => false,
        'blank_line_between_import_groups' => false,
        'visibility_required' => false,
        'standardize_increment' => false,
        'single_line_comment_style' => false,
        'no_whitespace_before_comma_in_array' => false,
        'no_spaces_around_offset' => false,
        'no_empty_statement' => false,
        'no_empty_comment' => false,
        'blank_line_after_opening_tag' => false,
        'no_useless_else' => false,
        'single_space_around_construct' => false,
    ])
    ->setFinder($finder)
;

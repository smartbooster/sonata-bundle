<?php

namespace Smart\SonataBundle\Admin;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
trait ImportableAdminTrait
{
    public function getImportOptions(): array
    {
        return [
            'nb_max_row' => 2000,
            'delimiter' => ',',
            'fix_header_consistency' => false,
            'pre_import_transform_callback' => null,
            'identifier' => null,
            'identifier_callback' => null,
            'entity_transform_callback' => null,
        ];
    }
}

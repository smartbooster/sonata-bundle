<?php

namespace Smart\SonataBundle\Admin;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
interface ImportableAdminInterface
{
    /**
     * @return array Associative array with all properties from the entity that the end user can use during the import.
     * Each property must have the valid follwing format, ex:
     *  [
     *      'propertyName' => [
     *          'label' => 'Property label', # required
     *          'relationClass' => EntityRelation::class, # optionnal but if set there must be an identifier
     *          'relationIdentifier' => 'code',
     *      ],
     *      ...
     *  ]
     */
    public function getImportProperties(): array;

    /**
     * @return array list all options to configure the import behavior. Current available options :
     *  - nb_max_row (integer) maximum limit number of row allowed in the data content to import
     *  - delimiter (string) delimiter used to explode the row data
     *  - fix_header_consistency (boolean) allow to fix header consistency error
     */
    public function getImportOptions(): array;
}

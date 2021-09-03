<?php

namespace Smart\SonataBundle\Admin;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
interface ImportableAdminInterface
{
    /**
     * @return array list all field from the entity that the end user can use during the import batch
     */
    public function getImportHeader(): array;

    /**
     * @return array list all header labels to display in the help form and in the import preview
     */
    public function getImportHeaderLabels(): array;

    /**
     * @return array list all options to configure the import behavior. Current available options :
     *  - nb_max_row (integer) maximum limit number of row allowed in the data content to import
     *  - delimiter (string) delimiter used to explode the row data
     *  - fix_header_consistency (boolean) allow to fix header consistency error
     */
    public function getImportOptions(): array;
}

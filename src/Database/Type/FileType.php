<?php

namespace Unimatrix\Cake\Database\Type;

use Cake\Database\Type\StringType;

/**
 * FileType
 * Custom filetype for unimatrix.file
 *
 * @author Flavius
 * @version 1.1
 */
class FileType extends StringType
{
    /**
     * Marshal
     * Prevent the marhsal from changing the upload array into a string
     *
     * @param mixed $value Passed upload array
     * @return mixed
     */
    public function marshal($value) {
        if ($value === null)
            return null;

        if (is_array($value))
            return (array)$value;

        return '';
    }
}

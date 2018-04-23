<?php

namespace Unimatrix\Cake\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type;

/**
 * FileType
 * Custom filetype for unimatrix.file
 *
 * @author Flavius
 * @version 1.0
 */
class FileType extends Type
{
    /**
     * Marshal
     * Prevent the marhsal from changing the upload array into a string
     *
     * @param mixed $value Passed upload array
     * @return mixed
     */
    public function marshal($value) {
        return $value;
    }

    /**
     * {@inheritDoc}
     * @see \Cake\Database\Type::toPHP()
     */
    public function toPHP($value, Driver $driver) {
        return $value;
    }

    /**
     * {@inheritDoc}
     * @see \Cake\Database\Type::toDatabase()
     */
    public function toDatabase($value, Driver $driver) {
        return $value;
    }
}

<?php

namespace Unimatrix\Cake\Database\Type;

use Cake\Database\Type;

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
}

<?php

namespace Unimatrix\Cake\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use Unimatrix\Cake\Validation\UploadValidation;

class UploadValidationTest extends TestCase
{
    public function testFails() {
        $this->assertFalse(UploadValidation::isUnderPhpSizeLimit(['error' => UPLOAD_ERR_INI_SIZE]));
        $this->assertFalse(UploadValidation::isUnderFormSizeLimit(['error' => UPLOAD_ERR_FORM_SIZE]));
        $this->assertFalse(UploadValidation::isCompletedUpload(['error' => UPLOAD_ERR_PARTIAL]));
        $this->assertFalse(UploadValidation::isFileUpload(['error' => UPLOAD_ERR_NO_FILE]));
        $this->assertFalse(UploadValidation::isTemporaryDirectory(['error' => UPLOAD_ERR_NO_TMP_DIR]));
        $this->assertFalse(UploadValidation::isSuccessfulWrite(['error' => UPLOAD_ERR_CANT_WRITE]));
        $this->assertFalse(UploadValidation::isNotStoppedByExtension(['error' => UPLOAD_ERR_EXTENSION]));
    }
}

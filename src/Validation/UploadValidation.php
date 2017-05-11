<?php

namespace Unimatrix\Cake\Validation;

use Cake\Utility\Hash;

/**
 * Upload
 * Validate upload errors, used in UploadableBehavior
 *
 * @author Flavius
 * @version 0.1
 */
class UploadValidation
{
    /**
     * Check that the file does not exceed the max file size specified by PHP
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isUnderPhpSizeLimit($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_INI_SIZE;
    }

    /**
     * Check that the file does not exceed the max file size specified in the HTML Form
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isUnderFormSizeLimit($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_FORM_SIZE;
    }

    /**
     * Check that the file was completely uploaded
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isCompletedUpload($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_PARTIAL;
    }

    /**
     * Check that a file was uploaded
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isFileUpload($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Check that the temporary directory exists
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isTemporaryDirectory($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_NO_TMP_DIR;
    }

    /**
     * Check that the file was successfully written to the server
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isSuccessfulWrite($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_CANT_WRITE;
    }

    /**
     * Check that the upload was not stopped by an extension
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isNotStoppedByExtension($check) {
        return Hash::get($check, 'error') !== UPLOAD_ERR_EXTENSION;
    }
}

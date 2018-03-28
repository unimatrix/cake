<?php

namespace Unimatrix\Cake\Lib;

use Cake\Utility\Security;

/**
 * Paranoia
 * URL Safe encryption / decryption for integers|strings based on a secret key
 * If no key is provided it will use the security salt
 *
 * Usage example:
 * ---------------------------------
 * // controller
 * use Unimatrix\Utility\Lib\Paranoia;
 *
 * $encrypted = Paranoia::encrypt('string_to_encrypt');
 * $decrypted = Paranoia::decrypt('string_to_decrypt');
 *
 * @author Flavius
 * @version 1.1
 */
class Paranoia
{
    /**
     * Get secret key from either
     * the security salt or from parameter
     *
     * @param string $secret
     * @return string
     */
    private static function secret($secret = null) {
        return is_null($secret) ? Security::getSalt() : $secret;
    }

    /**
     * Encrypt
     * @param integer|string $input
     * @param string $secret Paranoia secret
     * @return boolean|string
     */
    public static function encrypt($input, $secret = null) {
        // no input?
        if(!$input)
            return false;

        // encrypt
        $encrypted = Security::encrypt($input, md5(self::secret($secret)));

        // encode
        return strtr(base64_encode($encrypted), '+/', '-_');
    }

    /**
     * Decrypt
     * @param string $input
     * @param string $secret Paranoia secret
     * @return boolean|string|boolean
     */
    public static function decrypt($input, $secret = null) {
        // no input?
        if(!$input)
            return false;

        // backwards 1.0 compatibility
        $input = strtr($input, '.', '=');

        // decode
        $decoded = base64_decode(str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT));
        if(!$decoded)
            return false;

        // decrypt
        return Security::decrypt($decoded, md5(self::secret($secret)));
    }
}

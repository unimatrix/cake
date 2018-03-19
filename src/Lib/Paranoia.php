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
 * @version 1.0
 */
class Paranoia
{
    /**
     * Get secret key from either
     * the security salt or from parameter
     *
     * @param string $s
     * @return string
     */
    private static function secret($s = null) {
        return is_null($s) ? Security::getSalt() : $s;
    }

    /**
     * Encrypt
     *
     * @param integer|string $a
     * @param string $s Paranoia secret
     * @return null|string
     */
    public static function encrypt($a = null, $s = null) {
        if(is_null($a))
            return $a;

        $b = Security::encrypt($a, md5(self::secret($s)));
        return strtr(base64_encode($b), '+/=', '-_.');
    }

    /**
     * Decrypt
     *
     * @param string $a
     * @param string $s Paranoia secret
     * @return null|integer|string
     */
    public static function decrypt($a = null, $s = null) {
        if(is_null($a))
            return $a;

        $b = base64_decode(strtr($a, '-_.', '+/='));
        return Security::decrypt($b, md5(self::secret($s)));
    }
}

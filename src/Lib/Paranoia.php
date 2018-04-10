<?php

namespace Unimatrix\Cake\Lib;

use Cake\Utility\Security;

/**
 * Paranoia
 * URL Safe encryption / decryption for integers|strings based on a secret key
 * You can also encode / decode based on a secret key using the highly insecure XOR algorithm
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
 * @version 1.2
 */
class Paranoia
{
    /**
     * Encrypt (using Cake's security class)
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

        // output
        return self::base64encode($encrypted);
    }

    /**
     * Decrypt (using Cake's security class)
     * @param string $input
     * @param string $secret Paranoia secret
     * @return boolean|string|boolean
     */
    public static function decrypt($input, $secret = null) {
        // no input?
        if(!$input)
            return false;

        // decode
        $decoded = self::base64decode($input);
        if(!$decoded)
            return false;

        // output
        return Security::decrypt($decoded, md5(self::secret($secret)));
    }

    /**
     * Encode (using the very insecure XOR)
     * @param string $input
     * @param string $secret Paranoia secret
     * @return boolean|string
     */
    public static function encode($input, $secret = null) {
        // no input?
        if(!$input)
            return false;

        // run xor
        $xored = self::xor($input, self::secret($secret));

        // output
        return self::base64encode($xored);
    }

    /**
     * Decode (using the very insecure XOR)
     * @param string $input
     * @param string $secret Paranoia secret
     * @return boolean|string
     */
    public static function decode($input, $secret = null) {
        // no input?
        if(!$input)
            return false;

        // decode
        $decoded = self::base64decode($input);
        if(!$decoded)
            return false;

        // output
        return self::xor($decoded, self::secret($secret));
    }

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

    // make base64 urlsafe
    private static $urlSafe = ['+/=' => '-_.'];

    /**
     * Perform a URL safe base64 encode
     * @param string $input
     * @return string
     */
    public static function base64encode($input) {
        return strtr(base64_encode($input), key(self::$urlSafe), current(self::$urlSafe));
    }

    /**
     * Perform a URL safe base 64 decode
     * @param string $input
     * @return string
     */
    public static function base64decode($input) {
        return base64_decode(str_pad(strtr($input, current(self::$urlSafe), key(self::$urlSafe)), strlen($input) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Perform the XOR
     * @param string $input
     * @param string $secret Paranoia secret
     * @return string
     */
    private static function xor($input, $secret = null) {
        // perform xor
        $inputSize = strlen($input);
        $secretSize = strlen($secret);
        for($y = 0, $x = 0; $y < $inputSize; $y++, $x++) {
            if($x >= $secretSize)
                $x = 0;

            $input[$y] = $input[$y] ^ $secret[$x];
        }

        // output
        return $input;
    }
}

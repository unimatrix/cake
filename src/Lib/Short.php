<?php

namespace Unimatrix\Cake\Lib;

/**
 * Short: Bijective conversion between natural numbers (IDs) and short strings
 *
 * Short::encode() takes an ID and turns it into a short string
 * Short::decode() takes a short string and turns it into an ID
 *
 * Features:
 * + large alphabet (51 chars) and thus very short resulting strings
 * + proof against offensive words (removed 'a', 'e', 'i', 'o' and 'u')
 * + unambiguous (removed 'I', 'l', '1', 'O' and '0')
 *
 * Example output:
 * 123456789 <=> pgK8p
 *
 * @package: https://github.com/delight-im/ShortURL
 */
class Short
{
	const ALPHABET = '23456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ-_';
	const BASE = 51; // strlen(self::ALPHABET)

	/**
	 * Encode
	 *
	 * @param int $num
	 * @return NULL|string
	 */
	public static function encode($num) {
		$str = null;
		while($num > 0) {
			$str = self::ALPHABET[($num % self::BASE)] . $str;
			$num = (int) ($num / self::BASE);
		}

		return $str;
	}

	/**
	 * Decode
	 *
	 * @param string $str
	 * @return number
	 */
	public static function decode($str) {
		$num = 0;
		for($i = 0; $i < strlen($str); $i++)
			$num = $num * self::BASE + strpos(self::ALPHABET, $str[$i]);

		return $num;
	}
}

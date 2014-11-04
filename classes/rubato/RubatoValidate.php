<?php
/**
 * RubatoValidate
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5 and later
 *
 * @package		rubato.RubatoValidate
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @update		2014-06-01	1.0.0
 */


class RubatoValidate {

	/**
	 * 文字数チェック
	 * @param	string	確認文字列
	 * @param	integer	最小桁数
	 * @param	integer	最大桁数
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isLength($value, $minLen=0, $maxLen=0, $isMultibyte=false) {
		$length = ($isMultibyte) ? mb_strlen($value) : strlen($value);
		if ($maxLen == 0) $maxLen = $length;

		return ($length >= $minLen && $length <= $maxLen);
	}

	/**
	 * 半角英数字チェック
	 * @param	string	確認文字列
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isASCII($value, $options="") {
		return preg_match("/^[\w{$options}]+$/", $value);
	}

	/**
	 * カタカナチェック
	 * @param	string	確認文字列
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isKanaFormat($value) {
		//return (!mb_ereg('[^ア-ンァィゥェォッャュョ０-９・ー　]', $value));
		//return (!ereg( '[^ァ-ヶネチツテトナニヌノハ・ーヽヾ゛゜０-９　]' ,$value));

		$test = mb_convert_kana($value, "k");
		if ($test == $value) return false;

		for ($i = 0; $i < mb_strlen($test); $i++) {
			if (mb_substr($value, $i, 1) ==  mb_substr($test, $i, 1)) return false;
		}

		return true;
	}

	/**
	 * 機種依存文字確認
	 * @param	string	確認文字列
	 * @param	string	変換前エンコード
	 * @param	string	変換後エンコード
	 * @return	boolean	(true:OK, false:機種依存文字有り)
	 */
	public static function isNGChar($value, $preEncode=null, $postEncode=null) {
		/**
		 *$denyList = array(
		 *	'①|②|③|④|⑤|⑥|⑦|⑧|⑨|⑩|⑪|⑫|⑬|⑭|⑮|⑯|⑰|⑱|⑲|⑳'
		 *	. '|Ⅰ|Ⅱ|Ⅲ|Ⅳ|Ⅴ|Ⅵ|Ⅶ|Ⅷ|Ⅸ\|Ⅹ|ⅰ|ⅱ|ⅲ|ⅳ|ⅴ|ⅵ|ⅶ|ⅷ|ⅸ|ⅹ'
		 *	. '|㍉|㌔|㌢|㍍|㌘|㌧|㌃|㌶|㍑|㍗|㌍|㌦|㌣|㌫|㍊|㌻'
		 *	. '|㎜|㎝|㎞|㎎|㎏|㏄|㎡|〝|〟|∮|∑|∟|⊿|￤|＇|＂'
		 *	. '|㏍|℡|㊤|㊥|㊦|㊧|㊨|㈱|㈲|㈹|㍾|㍽|㍼|㍻'
		 *	. '|炻|仼|僴|凬|匇|匤|﨎|咊|坙|﨏|塚|增|寬|峵|嵓|﨑|德|悅|愠|敎'
		 *	. '|昻|晥|晴|朗|栁|﨓|﨔|橫|櫢|淸|淲|瀨|凞|猪|甁|皂|皞|益|礰|礼'
		 *	. '|神|祥|福|竧|靖|精|綠|緖|羽|荢|﨟|薰|蘒|﨡|蠇|諸|譿|賴|赶|﨣'
		 *	. '|﨤|逸|郞|都|鄕|﨧|﨨|閒|隆|﨩|霻|靍|靑|飯|飼|館|馞|髙|魲|鶴|黑'
		 *);
		 *mb_regex_encoding("SJIS-win");
		 *foreach ($denyList as $val) {
		 *	if (mb_ereg($val, $str, $match)) return false;
		 *}
		 *return true;
		 */

		if ($value == "") return true;

		// 検査対象外文字を除外
		$exclusion = "〜|～|－";
		$exclusion = mb_convert_encoding($exclusion, $preEncode, "utf8");
		$value = mb_ereg_replace($exclusion, "", $value);

		if (!$preEncode) $preEncode = mb_internal_encoding();
		if (!$postEncode) $postEncode = "ISO-2022-JP";
		$test = mb_convert_encoding($value, $postEncode, $preEncode);
		$test = mb_convert_encoding($test, $preEncode, $postEncode);

		return ($value == $test);
	}

	/**
	 * メール形式チェック
	 * @param	string	確認文字列
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isMailFormat($value) {
		return preg_match('/^[\w\-\.\+\/]+@[\w][\w\-\.]*\.[A-Za-z]+$/', $value);
	}

	/**
	 * 携帯電話用メールドメインチェック
	 * @param	string	確認文字列
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isMobileMail($value) {
		return preg_match('/@('
			. 'docomo\.ne\.jp'
			. '|ezweb\.ne\.jp'
			. '|softbank\.ne\.jp'
			. '|disney\.ne\.jp'
			. '|[tdhcrknsq]\.vodafone\.ne\.jp'
			. ')$/', $value);
	}

	/**
	 * メールアカウントチェック
	 * @param	string	確認文字列
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isMailAccountFormat($value) {
		return preg_match('/^[\w\-\.\+\/]+$/', $value);
	}

	/**
	 * メールドメインチェック
	 * @param	string	確認文字列
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isDomainFormat($value) {
		return preg_match('/^[\w][\w\-\.]*\.[A-Za-z]+$/', $value);
	}

	/**
	 * 電話番号形式チェック
	 * @param	string	確認文字列
	 * @param	string	区切り文字
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isTelephoneFormat($value, $separator="") {
		$value2 = ereg_replace($separator, "", $value);
		if (strlen($value2) < 10 || strlen($value2) > 11) return false;

		$separator = addcslashes($separator, "-+*./ ");
		$pattern = '/^[0-9]{2,4}+' . $separator . '[0-9]{2,4}+' . $separator . '[0-9]{2,4}+$/';
		return preg_match($pattern, $value);
	}

	/**
	 * URL形式チェック
	 * @param	string	確認文字列
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isUrlFormat($value) {
		$value = preg_replace("/^(http|https):\/\//", "", $value);
		return preg_match('/^([\w][\w\-]*\.[\w\-\.\:]*[\w]+)([\w\-\.\:~\/\?#\+=%&]+)*$/', $value);
	}

	/**
	 * 郵便番号形式チェック
	 * @param	string	確認文字列
	 * @param	string	区切り文字
	 * @return	boolean	(true:OK, false:NG)
	 */
	public static function isZipCodeFormat($value, $separator="-") {
		return preg_match("/^[0-9]{3}({$separator}){0,1}[0-9]{4}$/", $value);
	}

}
?>
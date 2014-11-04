<?php
/**
 * RubatoForm
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5 and later
 *
 * @package		rubato.RubatoForm
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @create		2014-06-01	1.0.0
 * @update		2014-08-28	1.0.1
 */

require_once "RubatoValidate.php";

class RubatoForm {

	protected $_encode		= "UTF-8";	// 文字コード
	protected $_magicQuotes	= false;	// MagicQuotesの有効
	protected $_isNGCharCheck = false;	//機種依存文字チェックの使用
	protected $_ngCharEncode  = "JIS";	//機種依存文字チェックの検証で使用する文字コード

	protected $_config		= array();	// フォーム設定
	protected $_list		= array();	// 変数リスト
	protected $_data		= array();	// 入力されたデータ
	protected $_error		= array();	// 発生したエラーメッセージ
	protected $_message	= array(	// エラーメッセージの定義
		"text-char"		=> "※%sに使用できない文字が含まれています",
		"text-null"		=> "※%sが入力されていません",
		"text-format"	=> "※%sの形式に誤りがあります",
		"num-null"		=> "※%sが入力されていません",
		"num-format"	=> "※%sが数字ではありません",
		"num-len"		=> "※%sの桁数に誤りがあります",
		"select-null"	=> "※%sが選択されていません",
		"mail-null"		=> "※%sが入力されていません",
		"mail-format"	=> "※%sの形式に誤りがあります",
		"mail-diff"		=> "※%sが異なっています",
		"tel-null"		=> "※%sが入力されていません",
		"tel-format"	=> "※%sの形式に誤りがあります",
		"default-null"	=> "※%sが入力されていません",
		"default-format"=> "※%sの形式に誤りがあります",
		"default-len"	=> "※%sの文字数に誤りがあります",
		"default-diff"	=> "※%sが一致していません",
		"default"		=> "※%sに誤りがあります",
	);


	/**
	 * コンストラクター
	 */
	public function __construct($encode="") {
		mb_language( "ja" );

		if ($encode) $this->_encode = $encode;
		elseif (mb_internal_encoding()) $this->_encode = mb_internal_encoding();
		mb_internal_encoding($this->_encode);

		$this->_magicQuotes = get_magic_quotes_gpc();
	}


	/**
	 * setter / getter
	 */
	public function __set($key, $val) {
		$myKey = "_" . $key;
		if (isset($this->$myKey)) $this->$myKey = $val;
		else die($key . " is not found.");
	}

	public function __get($key) {
		$myKey = "_" . $key;
		return (isset($this->$myKey)) ? $this->$myKey : null;
	}


	/**
	 * 項目がエラーであるか
	 * @param	string	$key	項目ID
	 * @return	array	データ配列(keyは項目のID）
	**/
	public function isError($key) {
		return array_key_exists($key, $this->_error);
	}

	/**
	 * 入力データを取得する
	 * @param	boolean	$isJoin		配列時の結合
	 * @param	boolean	$isEscape	HTMLエンティティの変換
	 * @return	array	入力データ
	**/
	public function getData($isJoin=false, $isEscape=false) {
		$dataList = $this->_data;

		// データの調整
		foreach ($dataList as $id => $data) {
			// リスト変数を割り当てる（結合の場合は変数割り当て）
			if ($isJoin && isset($this->_config[$id]['list'])) {
				$key = $this->_config[$id]['list'];
				if (!is_array($data)) {
					if (!is_null($data) && array_key_exists($data, $this->_list[$key])) $data = $this->_list[$key][$data];
				} else {
					$newData = array();
					foreach ($data as $item) {
						$newData[] = $this->_list[$key][$item];
					}
					$data = $newData;
				}
			}

			// 配列の結合
			if (is_array($data) && $isJoin) {
				$separator = isset($this->_config[$id]['sepa']) ? $this->_config[$id]['sepa'] : ", ";
				$data = implode($separator, $data);
			}
			// HTMLエンティティの変換
			if ($isEscape) {
				if (is_array($data)) {
					foreach ($data as $key => $val)
						$data[$key] = htmlspecialchars($val);
					$dataList[$id] = $data;
				}
				else $dataList[$id] = htmlspecialchars($data);
			}
			else $dataList[$id] = $data;
		}

		return $dataList;
	}


	/**
	 * リクエストからデータを取得・調整する
	 * @param	string	$id	取得するリクエストのキー
	 * @return	mixed	データ
	 */
	protected function _getRequest($id) {
		if (!isset($_REQUEST[$id])) return null;

		$data = $_REQUEST[$id];
		if ($this->_magicQuotes) {
			if (is_array($data)) {
				foreach ($data as $i=>$value) {
					$data[$i] = stripslashes($value);
				}
			}
			else $data = stripslashes($data);
		}
		return $data;
	}


	/**
	 * エラーメッセージを取得する
	 * @return array エラーメッセージ配列(keyは項目のID）
	**/
	protected function _getMessage($name, $type) {
		if (!array_key_exists($type, $this->_message)) {
			$type = preg_replace("/^[a-z]+/", "default", $type);
			if (!array_key_exists($type, $this->_message)) $type = "default";
		}

		return sprintf($this->_message[$type], $name);
	}


	/**
	 * 入力データの取得・調整
	 * @return	Boolean	 true:成功, false:失敗
	 */
	public function parse($data=array()) {
		if (!$this->_config || !count($this->_config)) return false;

		$error = array();
		foreach ($this->_config as $id => $conf) {
			if (!isset($conf['type'])) $conf['type'] = "";

			if (array_key_exists("conf", $conf)) {
				//------------------------------------------
				// グループ項目の場合
				//------------------------------------------
				$this->_data[$id] = "";
				foreach ($conf['conf'] as $childId => $childConf) {
					if (!isset($childConf['type'])) $childConf['type'] = "";

					//$this->_data[$childId] = (isset($data[$childId])) ? $data[$childId] : $this->_getRequest($childId);
					if ($this->_getRequest($childId)) $this->_data[$childId] = $this->_getRequest($childId);
					elseif (isset($data[$childId]))   $this->_data[$childId] = $data[$childId];
					else $this->_data[$childId] = null;

					$msg = $this->check($this->_data[$childId], $childId, $childConf);
					if ($msg) $error[$childId] = $msg;
					if ($msg && !isset($error[$id])) $error[$id] = $msg;
				}

				if (array_key_exists("sepa", $conf)) {
					//--------------------------------------
					// グループ結合の場合
					//--------------------------------------
					$values = array();
					$isData = false;
					foreach ($conf['conf'] as $childId => $childConf) {
						if ($this->_data[$childId] != "") $isData = true;
						$values[] = $this->_data[$childId];
					}
					$this->_data[$id] = ($isData) ? implode($conf['sepa'], $values) : "";
				} else {
					//--------------------------------------
					// 同一項目チェックの場合
					//--------------------------------------
					if (isset($error[$id])) continue;
					$values = array();
					foreach ($conf['conf'] as $childId => $childConf) {
						$values[] = $this->_data[$childId];
					}
					if (count(array_unique($values)) != 1) {
						$type = preg_replace("/\-(.)+/", "", $childConf['type']);
						$error[$id] = $this->_getMessage($conf['name'], "{$type}-diff");
						continue;
					}
					$this->_data[$id] = $this->_data[$childId];
				}
			} else {
				//------------------------------------------
				// 単一項目の場合
				//------------------------------------------
				//$this->_data[$id] = (isset($data[$id])) ? $data[$id] : $this->_getRequest($id);
				if ($this->_getRequest($id)) $this->_data[$id] = $this->_getRequest($id);
				elseif (isset($data[$id]))   $this->_data[$id] = $data[$id];
				else $this->_data[$id] = null;
			}

			if (isset($error[$id])) continue;
			$msg = $this->check($this->_data[$id], $id, $conf);
			if ($msg) $error[$id] = $msg;
		}

		$this->_error = $error;
		return true;
	}


	/**
	 * 項目に対して入力チェックを行う
	 * @param	string	$data	入力値
	 * @param	string	$id		キー名
	 * @param	string	$conf	設定リスト
	 * @return	array	エラーメッセージ配列(keyは項目のID）
	**/
	public function check(&$data, $id, $conf) {
		if (!$conf['type']) return;

		$fullType = $conf['type'];
		$type = preg_replace("/\-(.)+/", "", $conf['type']);

		// 未入力チェック
		if (isset($conf['null']) && !$conf['null']) {
			if (is_array($data)) {
				if (!count($data)) return $this->_getMessage($conf['name'], "{$type}-null");
			} else {
				$data = trim($data);
				if ($data == "") return $this->_getMessage($conf['name'], "{$type}-null");
			}
		}
		if ($data == "") return;

		// 文字数が多すぎる場合（16ビットを制限とする）
		if (strlen($data) > 65536) {
			return $this->_getMessage($conf['name'], "{$type}-len");
		}

		// 形式別チェック
		switch ($fullType) {
			// テキスト型
			case "text":
			    if ($this->_isNGCharCheck && !RubatoValidate::isNGChar($data, $this->_encode, $this->_ngCharEncode))
					return $this->_getMessage($conf['name'], "text-char");
				break;
			// テキスト（全角）
			case "text-zen":
				$testData = mb_convert_kana($data, "KVAS", $this->_encode);
			    if ($this->_isNGCharCheck && !RubatoValidate::isNGChar($testData, $this->_encode, $this->_ngCharEncode))
					return $this->_getMessage($conf['name'], "text-char");
				$data = $testData;
				break;
			// テキスト（カナ）
			case "text-kana":
				$testData = mb_convert_kana($data, "KVCAS", $this->_encode);
			    if (!RubatoValidate::isKanaFormat($testData))
					return $this->_getMessage($conf['name'], "text-format");
				$data = $testData;
				break;
			// テキスト（半角英数）
			case "text-ascii":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isASCII($testData))
					return $this->_getMessage($conf['name'], "text-format");
				$data = $testData;
				break;

			// メールアドレス型
			case "mail":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isMailFormat($testData))
					return $this->_getMessage($conf['name'], "mail-format");
				elseif (isset($conf['mobile']) && $conf['mobile']) {
					if (!RubatoValidate::isMobileMail($testData))
						return $this->_getMessage($conf['name'], "mail-mobile");
				}
				$data = $testData;
				break;
			// メールアドレス型（アカウントのみ）
			case "mail-a":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isMailAccountFormat($testData))
					return $this->_getMessage($conf['name'], "mail-format");
				$data = $testData;
				break;
			// メールアドレス型（ドメインのみ）
			case "mail-d":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isDomainFormat($testData))
					return $this->_getMessage($conf['name'], "mail-format");
				$data = $testData;
				break;

			// URL型
			case "url":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isUrlFormat($testData))
					return $this->_getMessage($conf['name'], "url-format");
				$data = $testData;
				break;

			// 数字型
			case "num":
				$testData = mb_convert_kana($data, "n", $this->_encode);
				if (!is_numeric($testData))
					return $this->_getMessage($conf['name'], "num-format");
				$data = $testData;
				break;
			// 数字型（INT型）
			case "int":
				$testData = mb_convert_kana($data, "n", $this->_encode);
				if (!is_numeric($testData))
					return $this->_getMessage($conf['name'], "num-format");
				$testData += 0;
				if (!is_int($testData))
					return $this->_getMessage($conf['name'], "num-format");
				$data = $testData;
				break;

			// 電話番号型
			case "tel":
				$testData = mb_convert_kana($data, "n", $this->_encode);
				$separator = isset($conf['sepa']) ? $conf['sepa'] : "-";
			    if (!RubatoValidate::isTelephoneFormat($testData, $separator))
					return $this->_getMessage($conf['name'], "tel-format");
				$data = $testData;
				break;

			// 郵便番号型
			case "zip":
				$testData = mb_convert_kana($data, "n", $this->_encode);
				$separator = isset($conf['sepa']) ? $conf['sepa'] : "-";
			    if (!RubatoValidate::isZipCodeFormat($testData, $separator))
					return $this->_getMessage($conf['name'], "zip-format");
				$data = $testData;
				break;
		}

		// 文字数チェック
		if (isset($conf['len'])) {
			if (strpos($conf['len'], "-")) list($minLen, $maxLen) = explode("-", $conf['len']);
			else $minLen = $maxLen = $conf['len'];

			$isMultibyte = ($type == 'text') ? true : false;
			if (!RubatoValidate::isLength($data, $minLen, $maxLen, $isMultibyte))
					return $this->_getMessage($conf['name'], "{$type}-len");
		}

		return null;
	}

}

?>
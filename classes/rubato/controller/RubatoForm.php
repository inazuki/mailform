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
 * @package		rubato.controller.RubatoForm
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @create		2014-10-21	1.0.0
 * @update		2014-11-04	1.0.2
 */


require_once 'RubatoPage.php';


class RubatoForm extends RubatoPage {

/**
 * 継承しているプロパティー
 *	protected $_main		= null;		// Rubatoクラス
 *	protected $_template	= null;		// HTMLテンプレート array(アクション名=>ファイルPATH, ...)
 *	protected $_action		= null;		// アクション制御用
 *	protected $_actionName	= 'act';	// アクション制御用パラメーター名
 *	protected $_magicQuotes	= null;		// MagicQuotes
 *	protected $_encode		= "UTF-8";	// 文字コード
 */

	//------------------------------------------------------
	// 設定値として使用する変数
	//------------------------------------------------------
	protected $_sessionName = "form";	// セッション名
	protected $_config		= array();	// フォーム設定
	protected $_list		= array();	// リスト変数の値
	protected $_isNGCharCheck = false;	// 機種依存文字チェックの使用
	protected $_ngCharEncode  = "JIS";	// 機種依存文字チェックの検証で使用する文字コード
	protected $_message	= array(		// エラーメッセージの定義
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


	//------------------------------------------------------
	// 内部処理で使用する変数
	//------------------------------------------------------
	protected $_data		= array();	// 入力されたデータ
	protected $_error		= array();	// 発生したエラーメッセージ

	//------------------------------------------------------
	// クラス継承時の割込みアクション（オーバーライド用）
	//------------------------------------------------------
	public function actionParseAfter() {} //解析直後
	public function actionCommon()     {} //解析後・表示前（共通）
	public function actionInput()      {} //処理済み・画面表示前（入力時）
	public function actionConfirm()    {} //処理済み・画面表示前（確認時）
	public function actionComplete()   {} //処理済み・画面表示前（完了時）


	/**
	 * 各種パラメーターをセットする
	 * @param	mixed	$params	各種パラメーター
	 */
	public function setParams($params) {
		if (isset($params['session']))	$this->_sessionName	= $params['sessionName'];
		if (isset($params['encode']))	$this->_encode		= $params['encode'];
		if (isset($params['template']))	$this->_template	= $params['template'];
		if (isset($params['config']))	$this->_config		= $params['config'];
		if (isset($params['list']))		$this->_list		= $params['list'];
		if (isset($params['message']))	$this->_message		= $params['message'];
	}


	/**
	 * セッションにデータをセットする
	 * @param	String	$property	設定キー
	 * @param	mixed	$value		設定値
	 */
	public function setSession($property, $value) {
		if (!isset($_SESSION[$this->_sessionName])) $this->initSession();
		$_SESSION[$this->_sessionName][$property] = $value;
	}

	/**
	 * セッションからデータを取得する
	 * @param	String	$property	設定キー
	 * @return	mixed				設定値
	 */
	public function getSession($property) {
		return (isset($_SESSION[$this->_sessionName][$property])) ? $_SESSION[$this->_sessionName][$property] : null;
	}

	/**
	 * セッションを初期化する
	 */
	public function initSession() {
		$_SESSION[$this->_sessionName] = array();
	}


	/**
	 * 項目がエラーであるか
	 * @param	String	$key	項目ID
	 * @return	Boolean			エラーの有無
	 */
	public function isError($key) {
		return array_key_exists($key, $this->_error);
	}


	/**
	 * エラーメッセージのフォーマットをセットする
	 * @param	Array	$formats	エラーメッセージのフォーマット
	 */
	public function setMessageFormat($formats) {
		$this->_message = $formats;
	}

	/**
	 * エラーメッセージを作成する
	 * @param	String	$name	項目名
	 * @param	String	$type	項目タイプ
	 * @return	String 			エラーメッセージ
	 */
	public function generateMessage($name, $type) {
		if (!array_key_exists($type, $this->_message)) {
			$type = preg_replace("/^[a-z]+/", "default", $type);
			if (!array_key_exists($type, $this->_message)) $type = "default";
		}
		$format = isset($this->_message[$type]) ? $this->_message[$type] : "%s";

		return sprintf($format, $name);
	}


	/**
	 * 入力データを取得する
	 * @param	String	$keyName	取得するキー（null値は全データ配列）
	 * @param	Boolean	$isJoin		配列時の結合
	 * @param	Boolean	$isEscape	HTMLエンティティの変換
	 * @return	mixed	入力データ
	 */
	public function getData($keyName=null, $isJoin=false, $isEscape=false) {

		if (!is_null($keyName) && isset($this->_data[$keyName])) {
			$dataList = array($keyName => $this->_data[$keyName]);
		} else {
			$keyName = null;
			$dataList = $this->_data;
		}

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

		if (!is_null($keyName)) return $dataList[$keyName];
		else return $dataList;
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

					if ($this->getRequest($childId)) $this->_data[$childId] = $this->getRequest($childId);
					elseif (isset($data[$childId]))   $this->_data[$childId] = $data[$childId];
					else $this->_data[$childId] = null;

					// nameが未設定の場合は親設定を適用
					if (!isset($childConf['name'])) $childConf['name'] = $conf['name'];

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
						$error[$id] = $this->generateMessage($conf['name'], "{$type}-diff");
						continue;
					}
					$this->_data[$id] = $this->_data[$childId];
				}
			} else {
				//------------------------------------------
				// 単一項目の場合
				//------------------------------------------
				if ($this->getRequest($id)) $this->_data[$id] = $this->getRequest($id);
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
				if (!count($data)) return $this->generateMessage($conf['name'], "{$type}-null");
			} else {
				$data = trim($data);
				if ($data == "") return $this->generateMessage($conf['name'], "{$type}-null");
			}
		}
		if ($data == "") return;

		// 文字数が多すぎる場合（16ビットを制限とする）
		if (strlen($data) > 65536) {
			return $this->generateMessage($conf['name'], "{$type}-len");
		}

		// 形式別チェック
		switch ($fullType) {
			// テキスト型
			default:
			case "text":
			    if ($this->_isNGCharCheck && !RubatoValidate::isNGChar($data, $this->_encode, $this->_ngCharEncode))
					return $this->generateMessage($conf['name'], "text-char");
				break;
			// テキスト（全角）
			case "text-zen":
				$testData = mb_convert_kana($data, "KVAS", $this->_encode);
			    if ($this->_isNGCharCheck && !RubatoValidate::isNGChar($testData, $this->_encode, $this->_ngCharEncode))
					return $this->generateMessage($conf['name'], "text-char");
				$data = $testData;
				break;
			// テキスト（カナ）
			case "text-kana":
				$testData = mb_convert_kana($data, "KVCAS", $this->_encode);
			    if (!RubatoValidate::isKanaFormat($testData))
					return $this->generateMessage($conf['name'], "text-format");
				$data = $testData;
				break;
			// テキスト（半角英数）
			case "text-ascii":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isASCII($testData))
					return $this->generateMessage($conf['name'], "text-format");
				$data = $testData;
				break;

			// メールアドレス型
			case "mail":
			case "email":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isMailFormat($testData))
					return $this->generateMessage($conf['name'], "mail-format");
				elseif (isset($conf['mobile']) && $conf['mobile']) {
					if (!RubatoValidate::isMobileMail($testData))
						return $this->generateMessage($conf['name'], "mail-mobile");
				}
				$data = $testData;
				break;
			// メールアドレス型（アカウントのみ）
			case "mail-a":
			case "email-a":
			case "account":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isMailAccountFormat($testData))
					return $this->generateMessage($conf['name'], "mail-format");
				$data = $testData;
				break;
			// メールアドレス型（ドメインのみ）
			case "mail-d":
			case "email-d":
			case "domain":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isDomainFormat($testData))
					return $this->generateMessage($conf['name'], "mail-format");
				$data = $testData;
				break;

			// URL型
			case "url":
				$testData = mb_convert_kana($data, "a", $this->_encode);
			    if (!RubatoValidate::isUrlFormat($testData))
					return $this->generateMessage($conf['name'], "url-format");
				$data = $testData;
				break;

			// 数字型
			case "num":
			case "number":
				$testData = mb_convert_kana($data, "n", $this->_encode);
				if (!is_numeric($testData))
					return $this->generateMessage($conf['name'], "num-format");
				$data = $testData;
				break;
			// 数字型（INT型）
			case "int":
				$testData = mb_convert_kana($data, "n", $this->_encode);
				if (!is_numeric($testData))
					return $this->generateMessage($conf['name'], "num-format");
				$testData += 0;
				if (!is_int($testData))
					return $this->generateMessage($conf['name'], "num-format");
				$data = $testData;
				break;

			// 電話番号型
			case "tel":
			case "telephone":
			case "fax":
				$testData = mb_convert_kana($data, "n", $this->_encode);
				$separator = isset($conf['sepa']) ? $conf['sepa'] : "-";
			    if (!RubatoValidate::isTelephoneFormat($testData, $separator))
					return $this->generateMessage($conf['name'], "tel-format");
				$data = $testData;
				break;

			// 郵便番号型
			case "zip":
			case "zip-code":
			case "postal-code":
				$testData = mb_convert_kana($data, "n", $this->_encode);
				$separator = isset($conf['sepa']) ? $conf['sepa'] : "-";
			    if (!RubatoValidate::isZipCodeFormat($testData, $separator))
					return $this->generateMessage($conf['name'], "zip-format");
				$data = $testData;
				break;
		}

		// 文字数チェック
		if (isset($conf['len'])) {
			if (strpos($conf['len'], "-")) list($minLen, $maxLen) = explode("-", $conf['len']);
			else $minLen = $maxLen = $conf['len'];

			$isMultibyte = ($type == 'text') ? true : false;
			if (!RubatoValidate::isLength($data, $minLen, $maxLen, $isMultibyte))
					return $this->generateMessage($conf['name'], "{$type}-len");
		}

		return null;
	}


	/**
	 * フォーム処理
	 *  [action] default|input -> confirm -> complete
	 */
	public function action() {


		if ($this->_action == "default") {
			// セッションのデータを削除
			$_SESSION[$this->_sessionName] = array();

			// 以降は入力時と同様の処理にする
			$this->_action = "input";

		} else {
			// セッションからデータを取得
			if ($this->_action == "input" || $this->_action == "complete") {
				if (isset($_SESSION[$this->_sessionName]['data']))
					$this->_data = $_SESSION[$this->_sessionName]['data'];
			}

			// 解析・エラーチェック
			$this->parse($this->_data);
			$this->actionParseAfter(); //※割込みアクション
			if (count($this->_error)) $this->_action = "input";

			// データのアサイン（入力時のみ未結合）
			if ($this->_action == "input") $this->_main->view->assign("data", $this->getData(null, false));
			else $this->_main->view->assign("data", $this->getData(null, true));
		}

		// アクション別の処理
		$this->actionCommon(); //※割込みアクション

		switch ($this->_action) {
			case "input":
				if (count($this->_data)) $this->setSession('data', $this->_data);
				$this->actionInput(); //※割込みアクション
				break;
			case "confirm":
				if (count($this->_data)) $this->setSession('data', $this->_data);
				$this->actionConfirm(); //※割込みアクション
				break;
			case "complete":
				if (!$this->getSession('completed')) {
					$this->setSession('completed', true);
					$this->actionComplete(); //※割込みアクション
				}
				break;
		}

		$this->_main->view->assign("list", $this->_list);
		$this->_main->view->assign("error", $this->_error);
	}

}

?>
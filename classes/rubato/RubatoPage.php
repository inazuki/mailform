<?php
/**
 * RubatoPage
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5 and later
 *
 * @package		rubato.RubatoPage
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.1.0
 * @see			rubato.Rubato
 * @create		2014-10-23
 * @update		2014-11-08
 */

require_once 'Rubato.php';

class RubatoPage extends Rubato {

/** 継承しているプロパティー
	public $appName;		// アプリケーションの名称
	public $appStartTime;	// アプリケーションの開始時刻
	public $db;				// RubatoDB
	public $errorLog;		// RubatoErrorLog
	public $systemLog;		// RubatoSystemLog
	public $debugMode;		// デバッグモード
	public $debugLevel;		// デバッグレベル
 */

	public $view;						// Viewオブジェクト（主にSmarty）
	protected $_template	= null;		// アクション毎のテンプレートPATH
	protected $_action		= null;		// アクション制御用
	protected $_actionName	= 'act';	// アクション制御用パラメーター名

	private $_isForward	= false;		// 転送状態か（true:画面出力しない）


	/**
	 * コンストラクター
	 * @param	Integer	$controlType	コントローラーの種類
	 * @param	Boolean	$useDB			DBの使用
	 * @param	Boolean	$isSession		セッションの使用
	 */
	public function __construct($useDB=true, $isSession=true) {
		parent::__construct($useDB);

		if ($isSession) session_start();

		require_once 'RubatoViewWeb.php';
		$this->view = new RubatoViewWeb();

		// アクションを取得
		$this->_action = $this->getRequest($this->_actionName);
		if ($this->_action == '') $this->_action = 'default';
	}


	/**
	 * デストラクター
	 */
	public function __destruct() {
		// 転送状態の時は画面出力しない
		if ($this->_isForward) return;

		// テンプレートのファイルPATHを取得
		$action = ($this->_action != '') ? $this->_action : 'default';
		$template = null;
		if (is_array($this->_template)) {
			if (!array_key_exists($action, $this->_template)) $action = 'default';
			$template = (isset($this->_template[$action])) ? $this->_template[$action] : null;
		} elseif (!is_null($this->_template)) {
			$template = $this->_template;
		}

		// 画面の表示
		try {
			$this->view->display($template);
		} catch (Exception $exception) {
			$this->exceptionHandler($exception);
		}

		parent::__destruct();
	}


	/**
	 * リクエストからデータを取得する
	 * @param	String	$property	設定キー
	 * @return	mixed				設定値
	 */
	public function getRequest($property, $defaultValue=null) {
		if (!isset($_REQUEST[$property])) return $defaultValue;

		$data = $_REQUEST[$property];

		// マジッククォートが有効な場合にバックスラッシュを削除（※PHP5.4.0未満が対象）
		if (get_magic_quotes_gpc()) {
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
	 * 画面を転送する
	 */
	public function forward($url) {
		$this->_isForward = true;
		header("Location: " . $url);
	}

}

?>
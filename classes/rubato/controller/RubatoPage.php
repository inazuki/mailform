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
 * @package		rubato.controller.RubatoPage
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @create		2014-10-23	1.0.0
 * @update		2014-10-23	1.0.0
 */


class RubatoPage {

	protected $_main		= null;		// Rubatoクラス
	protected $_template	= null;		// アクション毎のテンプレートPATH
	protected $_action		= null;		// アクション制御用
	protected $_actionName	= 'act';	// アクション制御用パラメーター名
	protected $_magicQuotes	= null;		// MagicQuotes
	protected $_encode		= "UTF-8";	// 文字コード


	/**
	 * コンストラクター
	 * @param	Rubato	$mainObj	Rubatoクラス
	 * @param	Boolean	$isSession	セッションの使用
	 */
	public function __construct($mainObj=null, $isSession=true) {
		if ($mainObj) $this->_main = $mainObj;
		if ($isSession) session_start();

		// 言語・文字コードの設定
		mb_language('ja');
		mb_internal_encoding($this->_encode);
		$this->_magicQuotes = get_magic_quotes_gpc();

		// アクションを取得
		$this->_action = $this->getRequest($this->_actionName);
		if ($this->_action == '') $this->_action = 'default';
	}


	/**
	 * デストラクター
	 */
	public function __destruct() {
		$action = ($this->_action != '') ? $this->_action : 'default';

		// 画面の表示
		if (!$this->_main->view->isForward && !is_null($this->_template)) {
			$template = null;

			if (!is_array($this->_template)) {
				$template = $this->_template;
			} else {
				if (!array_key_exists($action, $this->_template)) $action = 'default';
				$template = (isset($this->_template[$action])) ? $this->_template[$action] : null;
			}

			if ($template != '') {
				try {
					$this->_main->view->display($template);
				} catch (Exception $exception) {
					$this->_main->exceptionHandler($exception);
				}
			}
		}

	}


	/**
	 * リクエストからデータを取得する
	 * @param	String	$property	設定キー
	 * @return	mixed				設定値
	 */
	public function getRequest($property) {
		if (!isset($_REQUEST[$property])) return null;

		$data = $_REQUEST[$property];
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

}

?>
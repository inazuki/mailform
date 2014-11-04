<?php
/**
 * RubatoViewAPI（PHP5.3未満用）
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 - 5.2.x
 * PEAR versions 1.9 and later
 *
 * @package		rubato.RubatoViewAPI
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @see			Smarty
 * @create		2014-09-01
 * @update		2014-09-01
 */


class RubatoViewAPI {

	public $debug = false;
	public $status = "success";
	public $message = null;
	private $_data = array();


	public function __construct() {
	}


	public function __set($key, $value) {
		$this->_data[$key] = $value;
	}

	public function __get($key) {
		return (isset($this->_data[$key])) ? $this->_data[$key] : null;
	}


	/**
	 * パラメーターをアサインする（セッターと同じ）
	 */
	public function assign($key, $value) {
		$this->_data[$key] = $value;
	}

	/**
	 * パラメーターを画面に出力する
	 */
	public function display($path="") {
		if (!$this->debug)  echo $this->getData(true);
		else print_r($this->getData(false));
	}

	/**
	 * エラー状態にする
	 * @param String	エラーメッセージ
	 */
	public function error($message) {
		$this->status = "error";
		$this->message = $message;
		$this->display();
	}


	/**
	 * データを取得する
	 * @param Boolean	JSON形式にするか
	 * @return	Array
	 *				status		success:成功, error:エラー
	 *				message		エラーメッセージ等
	 *				...			セットした（setter）変数
	 */
	public function getData($isJSON=true) {
		$data = array('status'=>$this->status, 'message'=>$this->message);
		foreach ($this->_data as $key => $value) $data[$key] = $value;
		return ($isJSON) ? json_encode($data) : $data;
	}

}

?>
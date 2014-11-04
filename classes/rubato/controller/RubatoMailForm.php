<?php
/**
 * RubatoMailForm
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5 and later
 *
 * @package		rubato.controller.RubatoMailForm
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @see			rubato.controller.RubatoForm
 * @create		2014-10-21	1.0.0
 * @update		2014-11-04	1.0.3
 */


require_once 'RubatoForm.php';


class RubatoMailForm extends RubatoForm {

	protected $_isNGCharCheck = true;	// 機種依存文字チェックの使用
	protected $_ngCharEncode  = "JIS";	// 機種依存文字チェックの検証で使用する文字コード

	protected $_csv;		// CSV設定
	protected $_mailUser;	// ユーザー宛メール設定
	protected $_mailAdmin;	// 管理者宛メール設定


	/**
	 * 各種パラメーターをセットする（拡張）
	 * @param	mixed	$params	各種パラメーター
	 */
	public function setParams($params) {
		parent::setParams($params);

		if (isset($params['csv']))			$this->_csv			= $params['csv'];
		if (isset($params['mail-user']))	$this->_mailUser	= $params['mail-user'];
		if (isset($params['mail-admin']))	$this->_mailAdmin	= $params['mail-admin'];
		if (isset($params['message']))		$this->_message		= $params['message'];
	}


	/**
	 * 割込み処理：処理済み・画面表示前（完了時）
	 */
	public function actionComplete() {
		// 送信日時をセット
		$this->_config['SEND_DATE'] = array("name" => "送信日時");
		$this->_data['SEND_DATE'] = date("Y-m-d H:i:s");
		$this->_main->view->assign("data", $this->getData(null, true));

		//CSVファイルに保存
		if (is_array($this->_csv)) $this->saveCsv($this->_csv);

		//メールの送信
		if (is_array($this->_mailUser) || is_array($this->_mailAdmin)) $this->actionMailSend();
	}


	/**
	 * メール送信処理
	 */
	public function actionMailSend() {
		//--------------------------------------------------
		// ユーザー宛メール
		//--------------------------------------------------
		if (isset($this->_mailUser['to']) && isset($this->_mailUser['body'])) {
			if (!file_exists($this->_mailUser['body'])
				&& !file_exists(TEMPLATE_PATH . "/" . $this->_mailUser['body']))
				throw new FileNotFoundException("メール本文テンプレート ( {$this->_mailUser['body']} ) がみつかりません", RubatoException::ERROR);

			if (!isset($this->_data[$this->_mailUser['to']])) {
				throw new Exception("メール設定に誤りがあります", RubatoException::ERROR);
			}

			$mailObj = new RubatoMail();
			$mailObj->setTo($this->_data[$this->_mailUser['to']]);
			$mailObj->setSubject($this->_mailUser['subject']);
			$mailObj->setBody($this->_main->view->fetch($this->_mailUser['body']));
			if (isset($this->_mailUser['from'])) $mailObj->setFrom($this->_mailUser['from']);
			if (isset($this->_mailUser['reply'])) $mailObj->setReplyTo($this->_mailUser['reply']);
			if (isset($this->_mailUser['cc'])) $mailObj->setCc($this->_mailUser['cc']);
			if (isset($this->_mailUser['bcc'])) $mailObj->setBcc($this->_mailUser['bcc']);
	
			$mailObj->send();
		}

		//--------------------------------------------------
		// 管理者宛メール
		//--------------------------------------------------
		if (isset($this->_mailAdmin['to']) && isset($this->_mailAdmin['body'])) {
			if (!file_exists($this->_mailAdmin['body'])
				&& !file_exists(TEMPLATE_PATH . "/" . $this->_mailAdmin['body']))
				throw new FileNotFoundException("メール本文テンプレート ( {$this->_mailAdmin['body']} ) がみつかりません", RubatoException::ERROR);

			$mailObj = new RubatoMail();
			$mailObj->setTo($this->_mailAdmin['to']);
			$mailObj->setSubject($this->_mailAdmin['subject']);
			$mailObj->setBody($this->_main->view->fetch($this->_mailAdmin['body']));
			if (isset($this->_mailAdmin['from'])) $mailObj->setFrom($this->_mailAdmin['from']);
			if (isset($this->_mailAdmin['reply'])) $mailObj->setReplyTo($this->_mailAdmin['reply']);
			if (isset($this->_mailAdmin['cc'])) $mailObj->setCc($this->_mailAdmin['cc']);
			if (isset($this->_mailAdmin['bcc'])) $mailObj->setBcc($this->_mailAdmin['bcc']);

			$mailObj->send();
		}
	}


	/**
	 * CSV形式で内容を保存する
	 * @param	array	$param
	 * 				string	savePath	CSVファイルの保存パス
	 * 				string	fileName	CSVファイル名
	 * 				string	separator	CSVファイルの区切り文字
	 * 				boolean	isColumn	カラムの出力
	 * 				array	addFirst	CSVの最初に付与するデータ
	 * 				array	addLast		CSVの最後に付与するデータ
	 * @return	boolean
	 */
	function saveCsv($param=array()) {
		// 保存PATH
		$savePath = (array_key_exists('savePath', $param) && $param['savePath'])
			? $param['savePath'] : dirname($_SERVER['SCRIPT_FILENAME']);

		// ファイル名
		$fileName = (array_key_exists('fileName', $param) && $param['fileName'])
			? $param['fileName'] : date("Ymd") . ".csv";
		foreach (array("Y","y","m","d","H","i","s","w","U") as $key) {
			$fileName = preg_replace("/%{$key}/", date($key), $fileName);
		}

		// ファイルPATH
		$filePath = $savePath . "/" . $fileName;

		// 区切り文字
		$separator = (array_key_exists('separator', $param))
			? $param['separator'] : ",";

		// カラムの出力
		$isColumn = (array_key_exists('isColumn', $param) && $param['isColumn']);

		// データ用
		$columns = array();
		$values = array();

		// 設定リストからCSVデータを作成
		$dataList = $this->getData(true, false);
		foreach ($this->_config as $id => $conf) {
			if (isset($conf['csv']) && !$conf['csv']) continue;

			$columns[] = $this->_escapeCsv($conf['name'], $separator);
			$values[] = $this->_escapeCsv($dataList[$id], $separator);
		}

		// 文字コードの変換
		if (!in_array(strtoupper($this->_encode), array("SJIS","SJIS-WIN","SHIFT_JIS","CP932"))) {
			foreach ($columns as $key=>$val) {
				$columns[$key] = mb_convert_encoding($val, "SJIS", $this->_encode);
			}
			foreach ($values as $key=>$val) {
				$values[$key] = mb_convert_encoding($val, "SJIS", $this->_encode);
			}
		}

		// ファイルが存在する場合は、カラムを追加しない
		if ($isColumn && is_file($filePath)) $isColumn = false;

		// CSVファイルを書き込む
		if ($fp = fopen($filePath, "a+")) {
			// カラムの出力
			if ($isColumn) fputs($fp, implode($separator, $columns) . "\n");
			// データの出力
			fputs($fp, implode($separator, $values) . "\n");
			fclose($fp);
			chmod($filePath, 0666);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * CSV形式用に文字列をエスケープする
	 * @param	string	$val			値
	 * @param	string	$separator		CSVファイルの区切り文字
	 * @return	void
	 */
	private function _escapeCsv($value, $separator=",") {
		if (preg_match("/\"/", $value)) $value = preg_replace("/\"/", "\"\"", $value);
		$value = "\"{$value}\"";

		return $value;
	}

}

?>
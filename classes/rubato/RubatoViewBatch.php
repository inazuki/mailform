<?php
/**
 * RubatoViewBatch
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 * PEAR versions 1.9 and later
 *
 * @package		rubato.RubatoViewBatch
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @see			RubatoView
 * @create		2014-09-01
 * @update		2014-09-01
 */

class RubatoViewBatch {

	public $isConsole			= false;		// コンソール出力の有効化
	public $isLogFile			= true;			// ファイル出力の有効化
	public $logFilePath			= LOGS_PATH;	// ログファイルの出力PATH
	public $logFileName			= "batch-old";	// ログファイルの名称
	public $logFileExt			= "log";		// ログファイルの拡張子
	public $logFileDateFormat	= "Ymd";		// ログファイルに付与する日付（date関数に準じる）

	/**
	 * コンストラクター
	 */
	public function __construct() {
	}


	/**
	 * デストラクター
	 */
	public function __destruct() {
	}


	/**
	 * メッセージの出力
	 * @param	String	$message	メッセージ
	 * @param	Boolean	$isLogger	ログの保存
	 */
	public function trace($message, $isLogger=true) {
		$message = date("Y-m-d H:i:s") . "\t" .  $message;
		$this->output($message, $isLogger);
	}


	/**
	 * エラー（主に例外発生時）
	 * @param	Exception	$exception	例外インスタンス
	 */
	public function error($exception) {
		$message = "[" . get_class($exception) . "] " . $exception->getMessage() . "\n"
				. $exception->getTraceAsString();
		$this->output($message);
	}


	/**
	 * メッセージの出力と保存
	 * @param	String	$message	メッセージ
	 * @param	Boolean	$isLogger	ログの保存
	 */
	public function output($message, $isLogger=true) {
		if ($this->isConsole) echo $message . "\n";
		
		if ($this->isLogFile && $isLogger) {
			$filePath = $this->logFilePath . "/" . $this->logFileName;
			if ($this->logFileDateFormat) $filePath .= "." . date($this->logFileDateFormat);
			if ($this->logFileExt) $filePath .= "." . $this->logFileExt;

			if (!file_exists($filePath)) {
				touch($filePath);
				chmod($filePath, 0644);
			}
			error_log($message . "\n", 3, $filePath);
		}
	}

}

?>
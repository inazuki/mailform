<?php
/**
 * RubatoSystemLog
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 *
 * @package		rubato.RubatoSystemLog
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @create		2014-09-15
 * @update		2014-09-15
 */

class RubatoSystemLog {

	public $isConsole		= false;		// コンソール（または画面）出力の有効化
	public $isFile			= true;			// ファイル出力の有効化
	public $filePath		= LOGS_PATH;	// ログファイルの出力PATH
	public $fileName		= "system";		// ログファイルの名称
	public $fileExt			= "log";		// ログファイルの拡張子
	public $fileDateFormat	= "Ymd";		// ログファイルに付与する日付（date関数に準じる）

	public $appName;			// アプリケーション名
	private $_processId;		// プロセスID（ログ追跡用のユニークなID）
	private $_startTime = 0;	//アプリケーションの開始時刻

	/**
	 * コンストラクター
	 * @param	Array	$params		各プロパティー設定
	 */
	public function __construct($params=null) {
		$this->_startTime = microtime(true);

		if (!is_null($params)) {
			foreach ($params as $key => $val) {
				if (!isset($this->$key)) continue;
				$this->$key = $val;
			}
		}

		$this->_processId = substr(md5(mt_rand()), 0, 8);
	}


	/**
	 * 実行時間の取得
	 * @param	Integer	$precision	ミリ秒の桁数
	 * @return	Float	実行時間
	 */
	public function getProcessingTime($precision=4) {
		$endTime = microtime(true);
		return round($endTime - $this->_startTime, $precision);
	}



	/**
	 * メッセージの出力
	 * @param	String	$message	メッセージ
	 */
	public function trace($message) {
		$message = date("Y-m-d H:i:s") . " " . $this->_processId
				. "\t" . $message;
		$this->output($message);
	}

	/**
	 * 
	 */
	public function traceStart($message) {
		$this->trace("[START] " . $message);
	}

	/**
	 * 
	 */
	public function traceEnd() {
		$this->trace("[END] " . $this->getProcessingTime() . "sec");
	}


	/**
	 * エラー（主に例外発生時）
	 * @param	Exception	$exception	例外インスタンス（※Stringの場合は文字列を出力）
	 */
	public function error($exception, $isLogger=true) {
		if (is_object($exception) && (get_class($exception) == 'Exception' || is_subclass_of($exception, 'Exception'))) {
			$message = "[ERROR] " . RubatoException::getExceptionName($exception)
						. " (" . $exception->getFile() . ":" . $exception->getLine() . ")\n";
			$message .= $exception->getMessage() . "\n";
			$message .= $exception->getTraceAsString();
			$this->trace($message, $isLogger);
		} else {
			$this->trace("[ERROR] {$exception}", $isLogger);
		}
	}



	/**
	 * エラー（主に例外発生時）
	 * @param	Exception	$exception	例外インスタンス
	 */
//	public function error($exception) {
//		$message = "[" . get_class($exception) . "] " . $exception->getMessage() . "\n"
//				. $exception->getTraceAsString();
//		$this->output($message);
//	}


	/**
	 * メッセージの出力と保存
	 * @param	String	$message	メッセージ
	 * @param	Boolean	$isLogger	ログの保存
	 */
	public function output($message, $isLogger=true) {
		if ($this->isConsole) echo $message . "\n";
		
		if ($this->isFile && $isLogger) {
			$filePath = $this->filePath . "/" . $this->fileName;
			if ($this->fileDateFormat) $filePath .= "." . date($this->fileDateFormat);
			if ($this->fileExt) $filePath .= "." . $this->fileExt;

			if (!file_exists($filePath)) {
				touch($filePath);
				chmod($filePath, 0664);
			}
			error_log($message . "\n", 3, $filePath);
		}
	}

}

?>
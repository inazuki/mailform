<?php
/**
 * Rubato
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.1.2 and later
 * PEAR versions 1.9 and later
 * Smarty versions 3.0 and later
 *
 * @package		rubato.Rubato
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.1
 * @create		2014-09-15
 * @update		2014-10-20
 */

require_once "RubatoException.php";
require_once "RubatoErrorLog.php";
require_once "RubatoSystemLog.php";
require_once "RubatoDebug.php";

class Rubato {

	public $appName = "rubato";	// アプリケーションの名称
	public $appStartTime = 0;	//アプリケーションの開始時刻

	public $db;			// DBオブジェクト
	public $view;		// Viewオブジェクト
	public $viewType;	// Viewタイプ
	public $debugMode;	// デバッグモード
	public $debugLevel;	// デバッグレベル
	public $errorLog;	// RubatoErrorLogオブジェクト
	public $systemLog;	// RubatoSystemLogオブジェクト

	const VIEW_TYPE_NONE  = 0;
	const VIEW_TYPE_WEB   = 1;
	const VIEW_TYPE_API   = 2;
	const VIEW_TYPE_BATCH = 3;

	/**
	 * コンストラクター
	 * @param	Boolean		DBの使用
	 * @param	Boolean		Smartyの使用
	 */
	public function __construct($useDB=true, $viewType=Rubato::VIEW_TYPE_WEB) {
		$this->appStartTime = microtime(true);
		$this->viewType = $viewType;

		//----------------------------------------------------------------------
		// 定数の設定：定数が未設定の場合に使用される
		// （※外部ファイルで設定できるようconstは使わないこと）
		//----------------------------------------------------------------------

		// ディレクトリーの区切り文字
		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

		// デバッグ設定
		$this->debugMode  = (defined("DEBUG_MODE"))  ? DEBUG_MODE  : false;
		$this->debugLevel = (defined("DEBUG_LEVEL")) ? DEBUG_LEVEL : RubatoException::ERROR | RubatoException::WARNING;

		// 文字コード
		if (!defined("WEB_CHAR"))	define("WEB_CHAR", "UTF8");		// WEBの文字コード
		if (!defined("MAIL_CHAR"))	define("MAIL_CHAR", "JIS");		// メールの文字コード

		// システムPATH
		if (!defined("ROOT_PATH")) define("ROOT_PATH", realpath(dirname(__FILE__) . "/../.."));	// WEBアプリのルートPATH
		if (!defined("DOCUMENT_PATH"))	define("DOCUMENT_PATH",		ROOT_PATH . "/htdocs");		// ドキュメントのルートPATH
		if (!defined("CLASS_PATH")) 	define("CLASS_PATH",		ROOT_PATH . "/classes");	// クラスPATH
		if (!defined("PEAR_PATH"))		define("PEAR_PATH",			CLASS_PATH . "/Pear");		// PEARのPATH
		if (!defined("SMARTY_PATH"))	define("SMARTY_PATH",		CLASS_PATH . "/Smarty");	// SmartyのPATH
		if (!defined("TEMPLATE_PATH"))	define("TEMPLATE_PATH",		ROOT_PATH . "/template");	// テンプレートのPATH
		if (!defined("TEMPORARY_PATH"))	define("TEMPORARY_PATH",	ROOT_PATH . "/tmp");		// 一時保存ディレクトリーのPATH
		if (!defined("LOGS_PATH"))		define("LOGS_PATH",	ROOT_PATH . "/logs");				// ログ出力ディレクトリーのPATH
		if (!defined("EXTEND_CLASS_PATH")) define("EXTEND_CLASS_PATH", null);					// 拡張クラスPATH


		//----------------------------------------------------------------------
		// PHP用の環境設定
		//----------------------------------------------------------------------

		// PATHの設定
		$classPath = array(dirname(__FILE__), CLASS_PATH, PEAR_PATH);
		if (!is_null(EXTEND_CLASS_PATH)) $classPath[] = EXTEND_CLASS_PATH;
		ini_set("include_path", implode(PATH_SEPARATOR, $classPath));
		unset($classPath);

		// 文字コードの設定
		mb_internal_encoding(WEB_CHAR);

		// デバッグの設定
		if ($this->debugMode) {
			ini_set("display_errors", "On");
			error_reporting(E_ALL);
		}
		else error_reporting(E_ERROR | E_WARNING | E_PARSE);

		// キャッチされない例外用
		set_exception_handler(array($this, 'exceptionHandler'));


		//----------------------------------------------------------------------
		// Viewの設定
		//----------------------------------------------------------------------
		if ($viewType) {
			require_once 'RubatoView.php';
			$this->view = RubatoView::factory($viewType);
			$this->view->debug = $this->debugMode;
		}

		// クラスの自動ロード
		//  - PHP5.1.2以降
		//  - Smartyのautoloadより後で指定
		spl_autoload_register(array($this, "autoloader"));


		//----------------------------------------------------------------------
		// DBの設定
		//----------------------------------------------------------------------
		$dbException = null;
		if ($useDB) {
			require_once 'RubatoDB.php';

			// DB用定数の設定
			if (!defined("DB_TYPE"))	define("DB_TYPE", "mysql");		// DBの種類
			if (!defined("DB_CHAR"))	define("DB_CHAR", "UTF8");		// 文字コード
			if (!defined("DB_HOST"))	define("DB_HOST", "localhost");	// ホスト名
			if (!defined("DB_PORT"))	define("DB_PORT", "3306");		// ポート番号
			if (!defined("DB_NAME"))	define("DB_NAME", "");			// DBの名前
			if (!defined("DB_USER"))	define("DB_USER", "");			// 接続ユーザー名
			if (!defined("DB_PASS"))	define("DB_PASS", "");			// 接続パスワード

			// DB接続インスタンスの生成
			$this->db = new RubatoDB();
		}

	}


	/**
	 * デストラクター
	 */
	public function __destruct() {
		if ($this->debugMode) RubatoDebug::info();
	}

	/**
	 * クラスの自動ロード
	 */
	private function autoloader($classname) {

		foreach(explode(":", ini_get('include_path')) as $includePath) {
			$filePath = $includePath . DS . $classname .".php";
			if (file_exists($filePath)) {
				include_once $filePath;
				return;
			}
		}

		throw new FileNotFoundException("{$classname}クラスのオートローディングができませんでした");
	}


	/**
	 * キャッチされない例外の処理
	 * @param Exception		例外オブジェクト
	 */
	public function exceptionHandler($exception) {
		$code = $exception->getCode();
		$codeName = RubatoException::getCodeName($code);

		// デバッグモード時はエラーを出力する
		if ($this->debugMode && ($code & $this->debugLevel || $code == 0)) {
			RubatoDebug::error(RubatoException::getExceptionName($exception), array(
				"Code"		=> $code . ($codeName ? " ({$codeName})" : ""),
				"Message"	=> $exception->getMessage(),
				"File"		=> $exception->getFile() . " (" . $exception->getLine() . ")",
				"Trace"		=> $exception->getTraceAsString(),
			));
		}

		// 致命的エラー・重大な警告時にはエラーログを残す
		if ($code & (RubatoException::ERROR | RubatoException::WARNING)) {
			$this->errorException($exception);
		}

		// 致命的エラー発生時の処理
		if ($code == RubatoException::ERROR || $code == 0) $this->sysytemErrorHandler($exception);
	}

	/**
	 * システムエラー処理後のハンドラー（※オーバーライド推奨）
	 */
	public function sysytemErrorHandler($exception=null) {
		die("システムエラーが発生した為、処理を中止しました");
	}


	/**
	 * 実行時間の取得
	 * @param	Integer	$precision	ミリ秒の桁数
	 * @return	Float	実行時間
	 */
	public function getProcessingTime($precision=4) {
		$endTime = microtime(true);
		return round($endTime - $this->appStartTime, $precision);
	}


	/**
	 * エラーログの初期化
	 */
	private function _initErrorLog() {
		if ($this->errorLog) return;
		$this->errorLog = new RubatoErrorLog(array(
			'fileName'	=> $this->appName . "-error",
			'logDir'	=> LOGS_PATH,
		));
	}

	/**
	 * エラー出力：メッセージ
	 */
	public function errorMessage($message, $file="", $line=null) {
		$this->_initErrorLog();
		$this->errorLog->message($message, $file, $line);
	}

	/**
	 * エラー出力：例外
	 */
	public function errorException($exception) {
		$this->_initErrorLog();
		$this->errorLog->exception($exception);
	}

}

?>
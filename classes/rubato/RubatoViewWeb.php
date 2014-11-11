<?php
/**
 * RubatoViewWeb
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5 and later
 *
 * @package		rubato.RubatoViewWeb
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @see			Smarty
 * @create		2014-09-01
 * @update		2014-09-01
 */

require_once SMARTY_PATH . DS . 'Smarty.class.php';

class RubatoViewWeb extends Smarty {

	/**
	 * コンストラクター
	 */
	public function __construct() {
		parent::__construct();

		$this->template_dir    = TEMPLATE_PATH . DS;
		$this->config_dir      = defined("SMARTY_CONFIG_DIR")      ? SMARTY_CONFIG_DIR      : SMARTY_PATH . DS ."configs" . DS;
		$this->compile_dir     = defined("SMARTY_COMPILE_DIR")     ? SMARTY_COMPILE_DIR     : TEMPORARY_PATH . DS . "compile" . DS;
		$this->cache_dir       = defined("SMARTY_CACHE_DIR")       ? SMARTY_CACHE_DIR       : TEMPORARY_PATH . DS . "cache" . DS;
		$this->left_delimiter  = defined("SMARTY_LEFT_DELIMITER")  ? SMARTY_LEFT_DELIMITER  : "{{";
		$this->right_delimiter = defined("SMARTY_RIGHT_DELIMITER") ? SMARTY_RIGHT_DELIMITER : "}}";
		$this->caching         = defined("SMARTY_CACHING")         ? SMARTY_CACHING         : false;
		$this->cache_lifetime  = defined("SMARTY_CACHE_LIFETIME")  ? SMARTY_CACHE_LIFETIME  : 30;
		$this->compile_check   = defined("SMARTY_COMPILE_CHECK")   ? SMARTY_COMPILE_CHECK   : true;

		// キャッシュが無効の場合はキャッシュをクリアする
		if (!$this->caching) $this->clearAllCache();

		// 出力用のディレクトリーの設定
		if (!is_dir($this->compile_dir))	@mkdir($this->compile_dir, 0777);
		if (!is_dir($this->compile_dir))	throw new DirectoryNotFoundException($this->compile_dir, RubatoException::ERROR);
		if (!is_dir($this->cache_dir))		@mkdir($this->cache_dir, 0777);
		if (!is_dir($this->cache_dir))		throw new DirectoryNotFoundException($this->cache_dir);
	}


	/**
	 * 画面を表示する（Smarty::displayのラッパー）
	 * @param	String	$path	ファイルPATH
	 */
	public function display($path="") {
		$dirName = dirname($_SERVER['SCRIPT_NAME']);
		if ($dirName == "/" || $dirName == ".") $dirName = "";

		$searchPathList = array();
		if ($path == "") {
			$fileName = basename($_SERVER['SCRIPT_NAME'], ".php") . ".html";

			// PATHが未指定の場合はスクリプト名のHTMLファイルを
			// テンプレートPATHとドキュメントPATHから検索
			$searchPathList[] = TEMPLATE_PATH . $dirName . DS . $fileName;
			$searchPathList[] = DOCUMENT_PATH . $dirName . DS . $fileName;

		} else {
			$fileName = basename($path);

			if (dirname($path) == DS || dirname($path) == ".") {
				// PATHがファイル名のみの場合は、テンプレートPATH・ドキュメントPATHより相対的に検索
				$searchPathList[] = TEMPLATE_PATH . $dirName . DS . $fileName;
				$searchPathList[] = TEMPLATE_PATH . DS . $fileName;
				$searchPathList[] = DOCUMENT_PATH . $dirName . DS . $fileName;
				$searchPathList[] = DOCUMENT_PATH . DS . $fileName;
			} elseif (strpos($path, DS) == 0) {
				// 絶対PATHの場合は、指定PATHのみ検索
				$searchPathList[] = $path;
			} else {
				// PATHにディレクトリーを含む場合は、テンプレートPATH・ドキュメントPATHより絶対的に検索
				$searchPathList[] = TEMPLATE_PATH . DS . $path;
				$searchPathList[] = DOCUMENT_PATH . DS . $path;
			}

		}

		// テンプレートファイルを検索
		$hitPath = null;
		foreach ($searchPathList as $searchPath) {
			if (is_file($searchPath)) {
				$hitPath = $searchPath;
				break;
			}
		}
		if (is_null($hitPath))
			throw new FileNotFoundException(
				"テンプレートファイルがみつかりません\n-- 検索PATH --\n# " . implode($searchPathList, "\n# "),
				RubatoException::ERROR
			);

		try {
			parent::display($hitPath);
		} catch (Exception $exception) {
			throw new RubatoSmartyException($exception->getMessage(), RubatoException::ERROR);
		}
	}

}

class RubatoSmartyException extends Exception { }

?>
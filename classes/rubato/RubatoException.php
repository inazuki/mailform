<?php
/**
 * RubatoException
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 *
 * @package		rubato.RubatoException
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @create		2014-09-15
 * @update		2014-09-15
 */

class RubatoException extends Exception {

	const ERROR			= 1;		//致命的エラー（推奨レベル：処理の中断、エラーログの記録）
	const WARNING		= 2;		//重大な警告  （推奨レベル：処理の続行、エラーログの記録）
	const NOTICE		= 8;		//軽微な警告  （推奨レベル：処理の続行）
	const ALL			= 32767;	//全ての状況

	public static $exceptionCodeName = array(
		self::ERROR   => "ERROR",
		self::WARNING => "WARNING",
		self::NOTICE  => "NOTICE",
		self::ALL     => "ALL",
	);

	/**
	 * 例外の名称を取得する
	 * @return	String
	 */
	public static function getExceptionName($exception) {
		return join('', array_slice(explode('\\', get_class($exception)), -1));
	}

	/**
	 * 例外コードの名称を取得する
	 * @return	String
	 */
	public static function getCodeName($code) {
		return (array_key_exists($code, self::$exceptionCodeName)) ? self::$exceptionCodeName[$code] : "";
	}

}

/**
 * 共通例外の定義
 */
class DirectoryNotFoundException extends RubatoException { }	// ディレクトリーが見つからない
class FileNotFoundException      extends RubatoException { }	// ファイルが見つからない
class DBConnectionException      extends RubatoException { }	// DB接続エラー
class SQLQueryException          extends RubatoException { }	// SQLエラー
class InvalidParameterException  extends RubatoException { }	// 致命的なパラメーター不備

?>
<?php
/**
 * メールフォーム: サーバー環境設定
 */

// ルートPATH
define('ROOT_PATH', realpath(dirname(__FILE__) . "/../"));

// クラスPATH
define('CLASS_PATH', ROOT_PATH . '/classes');

// テンプレートのPATH
define('TEMPLATE_PATH', ROOT_PATH . '/template');

// 一時保存ディレクトリのPATH（Smartyのコンパイルで使用）
define('TEMPORARY_PATH', ROOT_PATH . '/tmp');

// エラーログの出力PATH
define("LOGS_PATH",	 ROOT_PATH . "/tmp");

?>
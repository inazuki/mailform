<?php
/**
 * RubatoDebug
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 *
 * @package		rubato.RubatoDebug
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @create		2014-09-15
 * @update		2014-09-15
 */

class RubatoDebug {

	/**
	 * デバッグ情報を表示する
	 * @param	array	REQUEST|GET|POST|COOKIE|SESSION|SERVER
	 */
	public static function info($param=array('REQUEST', 'COOKIE', 'SESSION')) {
		$title = "Debug Information";

		$mixed = array();
		foreach ($param as $key) {
			$mixed[$key] = NULL;

			$data = null;
			switch ($key) {
				case "REQUEST":
					if (isset($_REQUEST))	$data = $_REQUEST;	break;
				case "GET":
					if (isset($_GET))		$data = $_GET;		break;
				case "POST":
					if (isset($_POST))		$data = $_POST;		break;
				case "COOKIE":
					if (isset($_COOKIE))	$data = $_COOKIE;	break;
				case "SESSION":
					if (isset($_SESSION))	$data = $_SESSION;	break;
				case "SERVER":
					if (isset($_SERVER))	$data = $_SERVER;	break;
			}
			if ($data) $mixed[$key] = $data;
		}
		self::out($title, $mixed);
	}


	public static function error($title, $data) {
		self::out($title, $data, "error");
	}


	public static function out($title, $data, $type="info") {
		switch ($type) {
			case "info":	$bgcolor = "#ffffee"; break;
			case "error":	$bgcolor = "#ffeeee"; break;
			default:		$bgcolor = "#ffffee"; break;
		}

		echo "\n<!-- Debug Information -->\n";
		echo "<pre style=\"background-color:{$bgcolor}; color:#000000; display:block; text-align:left;"
			. " border:dashed 1px #666666; margin:10px; padding:1em; font-size:12px;\">";
		echo "<p style=\"margin:0.5em 0;\"><strong>{$title}</strong></p>";

		if (is_array($data)) {
			foreach ($data as $key => $val) {
				echo "<p style=\"margin:0 0 0 1em;\">{$key}:</p>";
				echo "<blockquote style=\"margin:.5em 0 0 3em;\">";

				if (is_null($val)) echo "<span style=\"font-style:italic;\">NULL</span>";
				else print_r($val);

				echo "</blockquote>\n";
			}
		} else {
			echo "<p style=\"margin:0 0 0 1em;\">{$data}</p>";
		}

		echo "</pre>\n";
		echo "<!-- //Debug Information -->\n";
	}
}

?>
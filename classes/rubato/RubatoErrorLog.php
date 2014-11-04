<?php
/**
 * RubatoErrorLog
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 *
 * @package		rubato.RubatoErrorLog
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @create		2014-03-20
 * @update		2014-09-15
 */

class RubatoErrorLog {

	public $fileName;
	public $logDir;

	public function __construct($params=array()) {
		if (isset($params['fileName'])) $this->fileName = $params['fileName'];
		if (isset($params['logDir'])) $this->logDir = $params['logDir'];
	}

	public function message($message, $file="", $line=null) {
		$filePath = $this->logDir . DS . $this->fileName . "." . date("Ymd") . ".log";

		$text = date("Y-m-d H:i:s") . "\t";
		if ($file != "") {
			$text .= $file;
			if (!is_null($line)) $text .= ":" . $line;
		}
		$text .= "\t";
		$text .= $message . "\n";

		error_log($text, 3, $filePath);
	}

	public function exception($exception) {
		$message = "[" . RubatoException::getExceptionName($exception)."] " . $exception->getMessage();
		$message .= "\n".$exception->getTraceAsString();

		$this->message($message, $exception->getFile(), $exception->getLine());
	}

}

?>
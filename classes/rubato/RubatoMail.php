<?php
/**
 * RubatoForm
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5 and later
 *
 * @package		rubato.Form
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @create		2014-10-18	1.0.0
 * @update		2014-10-18	1.0.0
 */

class RubatoMail {

	protected $_encodeInput	= "UTF-8";	// 入力値の文字コード
	protected $_encodeMail	= "JIS";	// メールの文字コード

	private $_to;
	private $_cc;
	private $_bcc;
	private $_from;
	private $_replyTo;
	private $_returnPath;
	private $_header;
	private $_body;


	/**
	 * コンストラクター
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * 初期化
	 */
	public function init() {
		$this->setTo(null);
		$this->setCc(null);
		$this->setBcc(null);
		$this->setFrom(null);
		$this->setReplyTo(null);
		$this->setReturnPath(null);
		$this->setHeader(null);
		$this->setSubject(null);
		$this->setBody(null);
	}

	/**
	 * To
	 */
	public function setTo($value) {
		if (is_null($value)) $this->_to = array();
		elseif (!is_array($value)) $this->_to = array($value);
		else $this->_to = $value;
	}
	public function addTo($value) {
		$this->_to[] = $value;
	}

	/**
	 * CC
	 */
	public function setCc($value) {
		if (is_null($value)) $this->_cc = array();
		elseif (!is_array($value)) $this->_cc = array($value);
		else $this->_cc = $value;
	}
	public function addCc($value) {
		$this->_cc[] = $value;
	}

	/**
	 * BCC
	 */
	public function setBcc($value) {
		if (is_null($value)) $this->_bcc = array();
		elseif (!is_array($value)) $this->_bcc = array($value);
		else $this->_bcc = $value;
	}
	public function addBcc($value) {
		$this->_bcc[] = $value;
	}

	/**
	 * From
	 */
	public function setFrom($value) {
		$this->_from = $value;
	}

	/**
	 * Reply-To
	 */
	public function setReplyTo($value) {
		$this->_replyTo = $value;
	}

	/**
	 * Return-Path
	 */
	public function setReturnPath($value) {
		if (is_array($value)) $value = $value[0];
		$this->_returnPath = $value;
	}

	/**
	 * BCC
	 */
	public function setHeader($value) {
		if (is_null($value)) $this->_header = array();
		else $this->_header = $value;
	}
	public function addHeader($key, $value) {
		$this->_header[$key] = $value;
	}

	/**
	 * Subject
	 */
	public function setSubject($value) {
		$this->_subject = trim($value);
	}

	/**
	 * Body
	 */
	public function setBody($value) {
		$this->_body = $value;
	}


	/**
	 * メールを送信する
	 * @param	string	$to			宛先メール
	 * @param	string	$subject	件名
	 * @param	string	$body		本文
	 * @param	array	$options	付与ヘッダー
	 * @return	boolean
	 */
	public function send($to=null, $subject=null, $body=null, $options=null) {
		if (!is_null($to))      $this->setTo($to);
		if (!is_null($subject)) $this->setSubject($subject);
		if (!is_null($body))    $this->setBody($body);
		if (!is_null($options)) $this->setHeader($options);

		//--------------------------------------------------
		// 送信先
		//--------------------------------------------------
		if (!$this->_to || !count($this->_to)) return false;
		$mailTo = $this->generateMailColumn($this->_to);

		//--------------------------------------------------
		// 件名と本文
		//--------------------------------------------------
		$mailSubject = $this->_subject;
		$mailBody = preg_replace("/\r\n/", "\n", $this->_body);
		$mailBody = preg_replace("/\r/", "\n", $mailBody);

		// エンコードする
		if (strtoupper($this->_encodeMail) == "JIS") {
			$mailSubject = mb_encode_mimeheader($mailSubject, "JIS", 'B');
			$mailBody = mb_convert_encoding($mailBody, "JIS", $this->_encodeInput);
			$mailHeader = "MIME-Version: 1.0\n"
					. "Content-Type: text/plain; charset=ISO-2022-JP\n"
					. "Content-Transfer-Encoding: 7bit";
		} elseif (strtoupper($this->_encodeMail) == "SJIS") {
			$mailSubject = mb_encode_mimeheader($mailSubject, "SJIS", 'B');
			if (!in_array(strtoupper($this->_encodeInput), array("SJIS","SJIS-WIN","SHIFT_JIS","CP932")))
				$mailBody = mb_convert_encoding($mailBody, "SJIS", $this->_encodeInput);
			$mailHeader = "MIME-Version: 1.0\n"
					. "Content-Type: text/plain; charset=Shift_JIS\n"
					. "Content-Transfer-Encoding: 8bit";
		} elseif (strtoupper($this->_encodeMail) == "UTF8") {
			mb_language("uni");
			$mailSubject = mb_encode_mimeheader(mb_convert_encoding($mailSubject, mb_internal_encoding(), $this->_encodeInput));
			$mailBody = mb_convert_encoding($mailBody, "utf8", $this->_encodeInput);
			$mailHeader = "MIME-Version: 1.0\n"
						. "Content-Type: text/plain; charset=UTF-8\n"
						. "Content-Transfer-Encoding: 7bit";
		}

		//--------------------------------------------------
		// ヘッダー
		//--------------------------------------------------

		if (!count($this->_cc))        $this->_header["CC"]       = $this->generateMailColumn($this->_cc);
		if (!count($this->_bcc))       $this->_header["BCC"]      = $this->generateMailColumn($this->_bcc);
		if (!is_null($this->_from))    $this->_header["From"]     = $this->covertMailColumn($this->_from);
		if (!is_null($this->_replyTo)) $this->_header["Reply-To"] = $this->covertMailColumn($this->_replyTo);

		// 追加ヘッダー
		if (count($this->_header)) {
			foreach ($this->_header as $key => $val) {
				$mailHeader .= "\n{$key}: {$val}";
			}
		}

		// Return-Path
		$parameters = null;
		if (!is_null($this->_returnPath)) $parameters = "-f {$this->_returnPath}";

		// メールを送信する
		return mail($mailTo, $mailSubject, $mailBody, $mailHeader, $parameters);

	}


	/**
	 * メールアドレスと表示名をヘッダー用に変換する
	 * @param	string	$mailAddress	メールアドレス
	 * @param	string	$displayText	日本語表記
	 * @return	string
	 */
	public function covertMailColumn($mailAddress, $displayText=null) {
		if (is_array($mailAddress) && is_null($displayText)) {
			$displayText = $mailAddress[1];
			$mailAddress = $mailAddress[0];
		}
		if (is_null($displayText)) return $mailAddress;

		return mb_encode_mimeheader($displayText, $this->_encodeMail, 'B') . " <{$mailAddress}>";
	}

	/**
	 * 複数のメールアドレスからヘッダー用を生成する
	 * @params	Array	メールアドレス（または表示名との配列）
	 * @return	String
	 */
	private function generateMailColumn($mails) {
		$tmp = array();
		foreach ($mails as $item) {
			$tmp[] = $this->covertMailColumn($item);
		}
		return implode($tmp, ", ");
	}

}

?>
<?php
/**
 * メールフォーム
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5 and later
 *
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @see			rubato.controller.RubatoMailForm
 * @create		2014-11-01	1.0.0
 * @update		2014-11-01	1.0.0
 */

require_once "MailForm.config.php";
require_once "rubato/Rubato.php";
require_once "rubato/RubatoPageMailForm.php";

class MailForm extends RubatoPageMailForm {
	public function __construct($params) {
		parent::__construct(false, true); //RubatoPageForm($useDB=true, $isSession=true)
		$this->setParams($params);
		$this->action();
	}
}

?>
<?php
/**
 * RubatoView
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 *
 * @package		rubato.RubatoView
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.1
 * @create		2014-09-01
 * @update		2014-11-07
 */


class RubatoView {

	public static function factory($controlType) {
		switch ($controlType) {
			case (Rubato::WEB_PAGE):
			case (Rubato::WEB_LIST):
			case (Rubato::WEB_FORM):
				require_once 'RubatoViewWeb.php';
				return new RubatoViewWeb();
			case (Rubato::WEB_API):
				require_once 'RubatoViewAPI.php';
				return new RubatoViewAPI();
			case (Rubato::BATCH):
				require_once 'RubatoViewBatch.php';
				return new RubatoViewBatch();
		}

		return null;
	}

}
?>
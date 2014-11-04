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
 * @version		1.0.1
 * @create		2014-09-01
 * @update		2014-10-12
 */


class RubatoView {

	public static function factory($viewType) {
		switch ($viewType) {
			case (Rubato::VIEW_TYPE_WEB):
				require_once 'RubatoViewWeb.php';
				return new RubatoViewWeb();
			case (Rubato::VIEW_TYPE_API):
				require_once 'RubatoViewAPI.php';
				return new RubatoViewAPI();
			case (Rubato::VIEW_TYPE_BATCH):
				require_once 'RubatoViewBatch.php';
				return new RubatoViewBatch();
		}

		return null;
	}

}
?>
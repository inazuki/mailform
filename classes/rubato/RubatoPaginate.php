<?php
/**
 * RubatoPaginate
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 * PEAR versions 1.9 and later
 *
 * @package		rubato.RubatoPaginate
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.0
 * @create		2014-10-14
 * @update		2014-10-14
 */


class RubatoPaginate {

	// ページネートのタイプ
	const TYPE_SIMPLE_SORTED = 1;	// 単純に並べる
	const TYPE_CENTER_SPREAD = 2;	// 現在番号が中央になる
	private $_type		= self::TYPE_SIMPLE_SORTED;

	private $_rowCount	= 10;			// 1ページあたりの件数（行数）
	private $_colCount	= 10;			// ページ番号の表示数

	private $_url			= "";		// ページリンクのURL
	private $_requestName	= "p";		// ページリンクのページ番号パラメータ
	private $_optionParam	= array();	// ページリンクに追加付与するパラメータ

	private $_page		= 0;			// 現在のページ
	private $_pageTotal	= 0;			// 合計のページ数
	private $_rows		= 0;			// 合計のレコード数

	private $_pageFirst	= 0;			// ページネートの開始ページ番号
	private $_pageLast	= 0;			// ページネートの終了ページ番号


	public function __construct($params=array()) {
		if (!count($params)) return;
		foreach ($params as $key => $val) {
			$this->__set($key, $val);
		}
	}

	public function __set($key, $val) {
		$myKey = "_" . $key;
		if (isset($this->$myKey)) $this->$myKey = $val;
		else die($key . " is not found.");
	}

	public function __get($key) {
		$myKey = "_" . $key;
		if (isset($this->$myKey)) return $this->$myKey;
		else die($key . " is not found.");
	}

	/**
	 * ページリストを生成する
	 * @param	integer	合計のレコード数
	 * @param	integer	現在のページ
	 * @return	mixed	ページリスト情報
	 *	Array
	 *	(
	 *		[list] => Array		ページ番号リスト
	 *			(
	 *				[] => Array
	 *					(
	 *						[id]		ページ番号ID（引数用）
	 *						[number]	ページ番号（表示用）
	 *						[selected]	選択中
	 *						[url]		リンク用URL
	 *					)
	 *			)
	 *		[prev] => Array		前のページ
	 *			(
	 *				[id]		ページ番号ID（引数用）
	 *				[number]	ページ番号（表示用）
	 *				[url]		リンク用URL
	 *			)
	 *		[prev2] => Array	前のページ一覧
	 *		[next]  => Array	次のページ
	 *		[next2] => Array	次のページ一覧
	 *			(
	 *				prev と同様
	 *			)
	 *		[offset]	DB用オフセット
	 *		[rows]		合計件数
	 *	)
	 */
	public function generate($rows=null, $page=null) {
		//------------------------------
		// ページネートの準備
		//------------------------------

		// 全件数をセット
		if (!is_null($rows) && is_numeric($rows)) $this->_rows = $rows;

		// 現在ページ番号を取得
		if (!is_null($page) && is_numeric($page)) $this->_page = $page;

		// 全ページ数を算出
		$this->_pageTotal = ceil($this->_rows / $this->_rowCount);

		// 現在ページ番号が不正な場合は、0に設定する
		if (!is_numeric($this->_page) || $this->_page < 0) $this->_page = 0;

		// 現在ページ番号が最大ページ数を越えている場合は、最大ページ番号にする
		if (($this->_page * $this->_rowCount) >= $this->_rows) {
			$this->_page = $this->_pageTotal - 1;
		}
		if ($this->_page < 0) $this->_page = 0;

		// 追加パラメータの付与
		$optionParam = "";
		if (count($this->_optionParam)) {
			$tmpParam = array();
			foreach ($this->_optionParam as  $key => $val) {
				$tmpParam[] = "{$key}={$val}";
			}
			$optionParam = "&" . implode("&", $tmpParam);
		}


		//------------------------------
		// ページネートを生成する
		//------------------------------

		$pageList = array();

		// ページネートの開始を算出
		if ($this->_type == self::TYPE_SIMPLE_SORTED) {
			$this->_pageFirst = $this->_page - ($this->_page % $this->_colCount);
		} elseif ($this->_type == self::TYPE_CENTER_SPREAD) {
			//$this->_pageFirst = $this->_page - ceil($this->_colCount / 2) + 1;
			$this->_pageFirst = $this->_page - ceil($this->_colCount / 2);

			// 最終ページ未満になる場合は、開始番号を巻き戻す
			if ($this->_colCount > ($this->_pageTotal - $this->_pageFirst)) {
				$this->_pageFirst -= $this->_colCount - ($this->_pageTotal - $this->_pageFirst);
			}
		}
		if ($this->_pageFirst < 0) $this->_pageFirst = 0;

		// ページネートの終了ページ番号を算出
		$this->_pageLast = $this->_pageFirst + $this->_colCount - 1;
		if ($this->_pageLast > ($this->_pageTotal - 1)) $this->_pageLast = $this->_pageTotal - 1;
		if ($this->_pageLast < 0) $this->_pageLast = 0;

		// list: ページネート一覧
		$pageList['list'] = array();
		for ($i = $this->_pageFirst; $i <= $this->_pageLast; $i++) {
			$pageList['list'][] = array(
				"id"		=> $i,
				"number"	=> $i + 1,
				"selected"	=> ($this->page == $i),
				"url"		=> "{$this->_url}?{$this->_requestName}={$i}{$optionParam}",
			);
		}

		// prev: 現在ページより1ページ前
		if ($this->_page > 0) {
			$pageId = $this->_page - 1;
			$pageList['prev'] = array(
				"id"		=> $pageId,
				"number"	=> $pageId + 1,
				"url"		=> "{$this->_url}?{$this->_requestName}={$pageId}{$optionParam}",
			);
		}

		// prev2: 現在ページより1リスト前
		if (($this->_pageFirst - 1) > 0) {
			$pageId = $this->_pageFirst - 1;
			$pageList['prev2'] = array(
				"id"		=> $pageId,
				"number"	=> $pageId + 1,
				"url"		=> "{$this->_url}?{$this->_requestName}={$pageId}{$optionParam}",
			);
		}

		// next: 現在ページより1ページ次
		if ($this->_page < ($this->_pageTotal - 1)) {
			$pageId = $this->_page + 1;
			$pageList['next'] = array(
				"id"		=> $pageId,
				"number"	=> $pageId + 1,
				"url"		=> "{$this->_url}?{$this->_requestName}={$pageId}{$optionParam}",
			);
		}

		// next2: 現在ページより1リスト次
		if (($this->_pageLast) < ($this->_pageTotal - 1)) {
			$pageId = $this->_pageLast + 1;
			$pageList['next2'] = array(
				"id"		=> $pageId,
				"number"	=> $pageId + 1,
				"url"		=> "{$this->_url}?{$this->_requestName}={$pageId}{$optionParam}",
			);
		}

		// first: 最前ページ
		if ($this->_pageFirst != 0) {
			$pageId = 0;
			$pageList['first'] = array(
				"id"		=> $pageId,
				"number"	=> $pageId + 1,
				"url"		=> "{$this->_url}?{$this->_requestName}={$pageId}{$optionParam}",
			);
		}

		// last: 最終ページ
		if ($this->_pageLast < $this->_pageTotal - 1) {
			$pageId = $this->_pageTotal - 1;
			$pageList['last'] = array(
				"id"		=> $pageId,
				"number"	=> $pageId + 1,
				"url"		=> "{$this->_url}?{$this->_requestName}={$pageId}{$optionParam}",
			);
		}

		// offset: オフセット
		$pageList['offset'] = $this->_rowCount * $this->_page;

		// rows: 合計件数
		$pageList['rows'] = $this->_rows;

		return $pageList;
	}

	/**
	 * オフセットを取得する
	 */
	public function getOffset() {
		return $this->_rowCount * $this->_page;
	}

}
?>
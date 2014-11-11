<?php
/**
 * RubatoPageList
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5 and later
 *
 * @package		rubato.RubatoListPage
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.1.0
 * @see			rubato.RubatoPage
 * @create		2014-10-23
 * @update		2014-11-11
 */

require_once 'RubatoPage.php';

class RubatoPageList extends RubatoPage {

/** 継承しているプロパティー
	public $appName;		// アプリケーションの名称
	public $appStartTime;	// アプリケーションの開始時刻
	public $db;				// RubatoDB
	public $errorLog;		// RubatoErrorLog
	public $systemLog;		// RubatoSystemLog
	public $debugMode;		// デバッグモード
	public $debugLevel;		// デバッグレベル
	public $view;						// Viewオブジェクト（主にSmarty）
	protected $_template	= null;		// アクション毎のテンプレートPATH
	protected $_action		= null;		// アクション制御用
	protected $_actionName	= 'act';	// アクション制御用パラメーター名
 */

	/* 継承先で設定するプロパティー */
	protected $_sessionName		= "list";				// セッション名
	protected $_sortTargets		= array('id'=>'id');	// ソートに使用するキーとDBカラム
	protected $_sortDefaultKey	= 'id';					// ソートのデフォルトキー
	protected $_sortDefaultAsc	= 1;					// ソートのデフォルトの昇・降順（1:昇順, 0:降順）
	protected $_rowCount		= 10;					// 1ページあたりの表示件数
	protected $_pageCount		= 10;					// ページ番号の表示件数
	protected $_paramsRequest = array(					// リクエストのパラメーター名の設定
		'pageNumber'=> 'page',		//ページ番号
		'sortKey'	=> 'sort',		//ソートキー
		'sortAsc'	=> 'asc',		//ソート昇順フラグ
	);
	protected $_paramsView = array(						// VIEWにアサインするパラメーター名の設定
		'list'		=> 'list',		// データ一覧
		'sortKey'	=> 'sortKey',	// ソートキー
		'sortAsc'	=> 'sortAsc',	// ソート昇順フラグ
		'sortLinks' => 'sortLinks',	// ソート用リンク
		'pageLinks' => 'pageLinks',	// ページネーション用リンク
	);

	/* 内部処理で使用するプロパティー */
	protected $_pageNumber;		//ページ番号
	protected $_sortKey;		//ソートキー
	protected $_sortAsc;		//ソート昇順フラグ
	protected $_searchParams;	//検索パラメーター


	/**
	 * コンストラクター
	 * @param	Rubato	$mainObj	Rubatoクラス
	 * @param	Boolean	$isSession	セッションの使用
	 */
	public function __construct($useDB=true, $isSession=true) {
		parent::__construct($useDB, $isSession);

		// ページ番号を取得
		$this->_pageNumber = $this->getRequest($this->_paramsRequest['pageNumber'], 0);

		// ソートキーを取得
		$this->_sortKey = $this->getRequest($this->_paramsRequest['sortKey'], $this->_sortDefaultKey);

		// ソート昇順フラグを取得
		$this->_sortAsc = $this->getRequest($this->_paramsRequest['sortAsc'], $this->_sortDefaultAsc);
	}


	/**
	 * 対象を検索して一覧画面として出力する
	 */
	public function doSearch($listObj) {
		//--------------------------------------------------
		// ORDER
		//--------------------------------------------------
		if (!array_key_exists($this->_sortKey, $this->_sortTargets)) {
			$this->_sortKey = $this->_sortDefaultKey;
			$this->_sortAsc = $this->_sortDefaultAsc;
		}
		if (!$this->_sortAsc) $this->_sortAsc = 0;

		$orderKey = $this->_sortTargets[$this->_sortKey];
		$orderAsc = ($this->_sortAsc) ? "ASC" : "DESC";
		$orders = array($orderKey=>$orderAsc);

		$sortLinks = array();
		foreach (array_keys($this->_sortTargets) as $key) {
			$sortLinks[$key] = 0;
			if ($this->_sortKey != $key) $sortLinks[$key] = $this->_sortDefaultAsc;
			else $sortLinks[$key] = ($this->_sortAsc) ? 0 : 1;
		}

		//--------------------------------------------------
		// LIMIT
		//--------------------------------------------------
		$allRows = $listObj->find($this->db, $this->_searchParams, $orders, $this->_rowCount, ($this->_rowCount * $this->_pageNumber));

		// ページネーションを生成する
		require_once 'RubatoPaginate.php';
		$paginate = new RubatoPaginate(array(
			"requestName"	=> $this->_paramsRequest['pageNumber'],	// ページ番号のパラメーター名
			"rowCount"		=> $this->_rowCount,	// 1ページあたりの件数
			"colCount"		=> $this->_pageCount,	// ページ番号の表示数
		));
		$paginate->page = $this->_pageNumber;	// 現在のページ
		$paginate->rows = $allRows;				// アイテムの件数
		$pageLinks = $paginate->generate();

		// ページ番号が最終番号を超えている場合は、データを取得し直す
		if ($this->_pageNumber > $paginate->pageLast) {
			$this->_pageNumber = $paginate->pageLast;
			$listObj->find($this->db, $this->_searchParams, $orders, $this->_rowCount, ($this->_rowCount * $this->_pageNumber), false);
		}

		//--------------------------------------------------
		// VIEW
		//--------------------------------------------------
		$this->view->assign($this->_paramsView['list'],			$listObj->list);
		$this->view->assign($this->_paramsView['sortKey'],		$this->_sortKey);
		$this->view->assign($this->_paramsView['sortAsc'],		$this->_sortAsc);
		$this->view->assign($this->_paramsView['sortLinks'],	$sortLinks);
		$this->view->assign($this->_paramsView['pageLinks'],	$pageLinks);
	}

}

?>
<?php
/**
 * RubatoAbstractList
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 * PEAR versions 1.9 and later
 *
 * @package		rubato.abstract.RubatoAbstractList
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.1
 * @create		2014-09-01
 * @update		2014-10-20
 */

abstract class RubatoAbstractList {

	protected $_itemClass;	// アイテムとなるクラス名
	protected $_tableName;	// DBテーブル名
	protected $_primaryKey;	// DBのプライマリーキー
	protected $_columns;	// DBで使用するカラム名
	protected $_list;		// インスタンス一覧


	/**
	 * ゲッター
	 */
	public function __get($key) {
		switch ($key) {
			case 'rows':
			case 'count':
				return count($this->_list);
			case 'list':
				return $this->getList();
		}
		return null;
	}


	/**
	 * インスタンス一覧の取得
	 * @return	Array	インスタンス一覧
	 */
	public function getList() {
		return $this->_list;
	}


	/**
	 * SQLクエリーのSELECT～FROMまでを作成
	 *（※継承時のカスタム用として）
	 * @return	String	SQLクエリー（SELECT～FROM）
	 */
	protected function createQuerySelect() {
		$columns = $this->_columns;
		if (!in_array($this->_primaryKey, $columns)) $columns[] = $this->_primaryKey;
		return "SELECT " . implode($columns, ", ") . " FROM {$this->_tableName}";
	}


	/**
	 * データをDBから取得する
	 * （※複雑なDB取得を行う場合は、継承先で実装する事）
	 * @params	DB		DBインスタンス
	 * @params	Array	検索値
	 * 						array(key:キー, value:値), ...
	 * 						※キーが「-」の場合はプレースホルダにしない
	 * @params	Array	ORDER
	 * @params	Integer	制限件数
	 * @params	Integer	取得開始件数（オフセット）
	 * @params	Boolean	全件数を取得するか（LIMITした場合のリターン値が変動する）
	 * @return	Boolean	該当件数（※全件数指定の場合は全件数）
	 */
	public function find($db, $params=array(), $orders=null, $limit=null, $offset=null, $isCount=true) {
		$this->setTableInfo();

		// SELECT
		$querySelect = $this->createQuerySelect();

		// WHERE
		$queryWhereTmp = array();
		$queryWhere = "";
		$values = array();
		if (isset($params['-'])) {
			$queryWhereTmp[] = $params['-'];
			unset($params['-']);
		}
		foreach ($params as $key => $val) {
			$queryWhereTmp[] = "{$key} = ?";
			$values[] = $val;
		}
		if (count($queryWhereTmp)) $queryWhere = " WHERE " . implode($queryWhereTmp, " AND ");

		// ORDER
		$queryOrder = "";
		if (!is_null($orders) && count($orders)) {
			$queryOrder .= " ORDER BY";
			$i = 0;
			foreach ($orders as $key => $value) {
				if ($i != 0) $queryOrder .= ",";
				$queryOrder .= " {$key} {$value}";
				$i++;
			}
		}

		// LIMIT
		$limitQuery = "";
		if (!is_null($limit) && is_numeric($limit)) {
			$limitQuery = " LIMIT {$limit}";
			if (!is_null($offset) && is_numeric($offset)) $limitQuery .= " OFFSET {$offset}";
		}

		// 全件数を取得
		$numRowsAll = 0;
		if ($limit && $isCount) {
			$query = "SELECT COUNT({$this->_primaryKey}) AS count FROM {$this->_tableName}" . $queryWhere;
			try {
				$res = $db->query($query, $values);
			} catch (Exception $exception) {
				throw new SQLQueryException($db->getLastQuery(), RubatoException::ERROR);
			}
			if (!$res) return null;

			$rows = $res->fetchRow();
			$numRowsAll = $rows['count'];
			if (!$numRowsAll) return 0;
		}

		// 一覧を取得
		$query = $querySelect . $queryWhere . $queryOrder . $limitQuery;
		try {
			$res = $db->query($query, $values);
		} catch (Exception $exception) {
			throw new SQLQueryException($db->getLastQuery(), RubatoException::ERROR);
		}
		if (!$res) return null;

		$numRows = $res->numRows();
		if (!$numRows) return $numRowsAll;
		if ($numRowsAll > $numRows) $numRows = $numRowsAll;

		$this->_list = array();
		while ($data = $res->fetchRow()) {
			$this->_list[] = $data;
		}

		return $numRows;
	}


	/**
	 * アイテムクラスのテーブル情報をセットする
	 */
	protected function setTableInfo() {
		if (!is_null($this->_tableName)) return;

		$itemMaster = new $this->_itemClass();
		$tableInfo = $itemMaster->getTableInfo();
		$this->_tableName	= $tableInfo['tableName'];
		$this->_primaryKey	= $tableInfo['primaryKey'];
		$this->_columns		= $tableInfo['columns'];
	}

}

?>
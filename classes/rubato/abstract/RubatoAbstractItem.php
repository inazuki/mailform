<?php
/**
 * RubatoAbstractItem
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 * PEAR versions 1.9 and later
 *
 * @package		rubato.abstract.RubatoAbstractItem
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.1
 * @create		2014-09-01
 * @update		2014-10-20
 */

abstract class RubatoAbstractItem {

	protected $_tableName;	// DBテーブル名
	protected $_primaryKey;	// DBのプライマリーキー
	protected $_columns;	// DBで使用するカラム名
	protected $_id;			// ID
	protected $_data;		// 格納データ


	/**
	 * コンストラクター
	 */
	public function __construct($params=null) {
		$this->_data = array();

		foreach($this->_columns as $key) {
			$this->setData($key, null, false);
		}

		if (!is_null($params)) {
			foreach($params as $key => $value) {
				if ($key == $this->_primaryKey) $this->setId($value);
				$this->setData($key, $value, false);
			}
		}
	}

	/**
	 * セッター
	 */
	public function __set($key, $value) {
		$this->setData($key, $value, true);
	}

	/**
	 * ゲッター
	 */
	public function __get($key) {
		return $this->getData($key);
	}


	/**
	 * プロパティー値をセットする（初期状態として）
	 * @params	Array	設定値
	 * 						array(key:キー, value:値), ...
	 */
	public function setData($key, $value, $isUpdate=true) {
		if (!isset($this->_data[$key])) $this->_data[$key] = array();
		$this->_data[$key]['value'] = $value;
		$this->_data[$key]['update'] = $isUpdate;
	}

	/**
	 * プロパティー値を取得する
	 * @params	String	キー（※nullの場合はデータリスト）
	 */
	public function getData($key=null) {
		if (!is_null($key)) 
			return (isset($this->_data[$key])) ? $this->_data[$key]['value'] : null;

		$data = array();
		foreach($this->_data as $key => $item) {
			$data[$key] = $item['value'];
		}
		return $data;
	}


	/**
	 * プライマリーキーをセットする
	 * @params	Variant	プライマリーキー値
	 */
	public function setId($value) {
		$this->_id = $value;
	}

	/**
	 * プライマリーキーを取得する
	 * @return	Variant	プライマリーキー値
	 */
	public function getId() {
		return $this->_id;
	}


	/**
	 * テーブル情報を取得する
	 * @return	mixed	array(tabelName:テーブル名, columns:使用カラム, primaryKey:プライマリーキー名)
	 */
	public function getTableInfo() {
		return array(
			'tableName'		=> $this->_tableName,
			'primaryKey'	=> $this->_primaryKey,
			'columns'		=> $this->_columns,
		);
	}


	/**
	 * DBのデータを取得する
	 * @params	DB		DBインスタンス
	 * @params	Array	検索値
	 * 						array(key:キー, value:値), ...
	 * @return	Boolean	成功｜失敗
	 */
	public function find($db, $params, $types=null) {
		if (!is_array($params) || !count($params))
			throw new InvalidParameterException('パラメーターが不正です', RubatoException::ERROR);

		$query = "SELECT {$this->_primaryKey}, " . implode($this->_columns, ", ")
				. " FROM {$this->_tableName}"
				. " WHERE ";
		$query .= implode(array_keys($params), " = ? AND ") . " = ?";

		$values = array();
		foreach ($params as $key => $val) {
			$values[] = $val;
		}

		try {
			$res = $db->query($query, $values, $types);
		} catch (SQLQueryException $exception) {
			throw new SQLQueryException($db->getLastQuery(), RubatoException::ERROR);
		}

		if (!$res || $res->numRows() == 0) return false;

		$data = $res->fetchRow();
		foreach ($data as $key => $value) {
			if ($key == $this->_primaryKey) $this->setId($value);
			$this->setData($key, $value, false);
		}

		return true;
	}


	/**
	 * DBのデータを更新する
	 * @params	DB		DBインスタンス
	 * @params	Array	更新する値（nullの場合は内部データで更新）
	 * @return	Boolean	成功｜失敗
	 */
	public function update($db, $params=null) {
		// プライマリーキーが未設定の場合は更新しない
		if (is_null($this->_id)) return false;

		if (is_null($params)) {
			$params = array();
			foreach ($this->_columns as $key) {
				if (!isset($this->_data[$key]) || $this->_data[$key]['update'] != 1) continue;
				$params[$key] = $this->_data[$key]['value'];
			}
		}
		if (!count($params)) return false;

		$query = "UPDATE {$this->_tableName} SET ";
		$tmp = array();
		foreach (array_keys($params) as $key) {
			$tmp[] = "{$key} = :{$key}";
		}
		$query .= implode($tmp, ", ") . " WHERE {$this->_primaryKey} = :PRIMARYKEY";
		$params['PRIMARYKEY'] = $this->_id;

		try {
			$db->query($query, $params);
		} catch (SQLQueryException $exception) {
			throw new SQLQueryException($db->getLastQuery(), RubatoException::ERROR);
		}

		foreach ($this->_columns as $key) {
			if (!isset($this->_data[$key]) || $this->_data[$key]['update'] != 1) continue;
			$this->_data[$key]['update'] = 0;
		}

		return true;
	}


	/**
	 * DBにデータを挿入する
	 * @params	DB		DBインスタンス
	 * @return	Boolean	成功｜失敗
	 */
	public function insert($db) {
		// プライマリーキーが設定済みの場合は挿入しない
		if (!is_null($this->_id)) return false;

		$params = array();
		foreach ($this->_columns as $key) {
			if (!isset($this->_data[$key])) continue;
			$params[$key] = $this->_data[$key]['value'];
		}
		if (!count($params)) return false;

		$query = "INSERT INTO {$this->_tableName} (" . implode(array_keys($params), ", ") . ")"
				. " VALUES(:" . implode(array_keys($params), ", :") . ")";

		try {
			$db->query($query, $params);
			$this->_id = $db->mdb2->lastInsertId($this->_tableName, $this->_primaryKey);
		} catch (SQLQueryException $exception) {
			throw new SQLQueryException($db->getLastQuery(), RubatoException::ERROR);
		}

		return true;
	}


	/**
	 * DBのデータを削除する（※削除は継承クラス毎に処理する事）
	 * @params	DB		DBインスタンス
	 * @return	Boolean	Boolean	成功｜失敗
	 */
	abstract public function delete($db);

}

?>
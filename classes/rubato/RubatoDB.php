<?php
/**
 * RubatoDB
 *
 * Copyright (c) 2013-2014 rubato
 * This software is released under the MIT License.
 * http://opensource.org/licenses/mit-license.php
 *
 * PHP versions 5.0 and later
 * PEAR versions 1.9 and later
 *
 * @package		rubato.RubatoDB
 * @author		Tsuyoshi Inazuki <t.inazuki@rubato.jp>
 * @copyright	2013-2014 rubato
 * @license 	http://opensource.org/licenses/mit-license.php  MIT License
 * @version		1.0.1
 * @see			PEAR:MDB2
 * @create		2014-09-01
 * @update		2014-10-12
 */

require_once PEAR_PATH . "/MDB2.php";

class RubatoDB {

	private $_options = array(
		'type'	=> 'mysql',					// DBの種類
		'char'	=> 'UTF8',					// 文字コード
		'host'	=> 'localhost',				// ホスト名
		'port'	=> '3306',					// ポート番号
		'name'	=> '',						// DBの名前
		'user'	=> '',						// 接続ユーザー名
		'pass'	=> '',						// 接続パスワード
		'mode'	=> MDB2_FETCHMODE_ASSOC,	// デフォルトのフェッチモード
	);
	public $mdb2;


	public function __construct($options=array()) {
		// 定数が定義されている場合は最初に反映しておく
		if (defined("DB_TYPE")) $this->_options['type'] = DB_TYPE;
		if (defined("DB_CHAR")) $this->_options['char'] = DB_CHAR;
		if (defined("DB_HOST")) $this->_options['host'] = DB_HOST;
		if (defined("DB_PORT")) $this->_options['port'] = DB_PORT;
		if (defined("DB_NAME")) $this->_options['name'] = DB_NAME;
		if (defined("DB_USER")) $this->_options['user'] = DB_USER;
		if (defined("DB_PASS")) $this->_options['pass'] = DB_PASS;
		if (defined("DB_MODE")) $this->_options['mode'] = DB_MODE;

		// DBに接続する（オプションはオーバーライド）
		$this->connect($options);
	}


	public function setOptions($options) {
		foreach ($options as $key => $val) {
			$this->_options[$key ] = $val;
		}
	}


	/**
	 * DBに接続
	 * @param	Array	$options	DB接続情報・オプション
	 */
	public function connect($options=array()) {
		$this->setOptions($options);

		// DB接続インスタンスの生成
		$dsn = $this->_options['type'] . "://"
					. $this->_options['user'] . ":" . $this->_options['pass']
					. "@" . $this->_options['host'] . (($this->_options['port']) ? ":" . $this->_options['port'] : "")
					. "/" . $this->_options['name'];
		$this->mdb2 =& MDB2::connect($dsn);

		if (PEAR::isError($this->mdb2)) {
			throw new DBConnectionException($this->mdb2->getMessage(), RubatoException::ERROR);
			return;
		}

		if ($this->_options['mode']) $this->mdb2->setFetchMode($this->_options['mode']);
	}


	/**
	 * クエリーの実行
	 * @return MDB2::MDB2_BufferedResult
	 */
	public function query($query, $params=array(), $types=null) {
		if (!count($params)) {
			$result =& $this->mdb2->query($query);
			if (PEAR::isError($result)) throw new SQLQueryException($result->getMessage(), RubatoException::NOTICE);

		} else {
			$statement = $this->prepare($query, $types);

			if (!PEAR::isError($statement)) {
				$result = $this->execute($statement, $params);
				if (PEAR::isError($result)) throw new SQLQueryException($result->getMessage(), RubatoException::NOTICE);
				else $statement->free();
			}
		}

		return $result;
	}

	/**
	 * クエリーの準備（MDB2::prepareのラッパー）
	 * @param	String	クエリー
	 * @param	Array	データ型
	 * @return	MDB2::MDB2_Statement
	 */
	public function prepare($query, $types=null) {
		$statement = $this->mdb2->prepare($query, $types);
		if (PEAR::isError($statement)) throw new SQLQueryException($statement->getMessage(), RubatoException::NOTICE);

		return $statement;
	}

	/**
	 * ステートメントの実行（MDB2_Statement::executeのラッパー）
	 * @param	MDB2::MDB2_Statement
	 * @return	MDB2::MDB2_BufferedResult
	 */
	public function execute($statement, $params=array()) {
		$result = $statement->execute($params);
		if (PEAR::isError($result)) throw new SQLQueryException($result->getMessage(), RubatoException::NOTICE);

		return $result;
	}


	public function fetchOne($query, $params=array(), $types=null) {
		$res = $this->query($query, $params, $types);
		return $res->fetchOne();
	}
	public function getOne($query, $params=array(), $types=null) {
		return $this->fetchOne($query, $params, $types);
	}


	/**
	 * トランザクション：開始
	 */
	public function begin($savePoint=null) {
		return $this->mdb2->beginTransaction($savePoint);
	}

	/**
	 * トランザクション：コミット
	 */
	public function commit($savePoint=null) {
		return $this->mdb2->commit($savePoint);
	}

	/**
	 * トランザクション：ロールバック
	 */
	public function rollback($savePoint=null) {
		return $this->mdb2->rollback($savePoint);
	}


	/**
	 * 最後に実行したクエリーを取得する
	 */
	public function getLastQuery() {
		return $this->mdb2->last_query;
	}

}

?>
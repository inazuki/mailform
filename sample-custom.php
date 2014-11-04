<?php
/**
 * メールフォームサンプル：最小構成
 */


$params = array(

    // ユーザー宛のメール設定
    "mail-user" => array(
        "subject"   => "【お問い合わせ】お客様控え（テスト）",      // 件名
        "to"        => "email",                                     // 宛先に使用するフォーム項目の名称
        "from"      => array("sample@rubato.jp", "テスト送信者"),   // From
        "body"      => "sample-min/mail.user.txt",                  // メール本文のテンプレートPATH
    ),

    // HTMLテンプレートのPATH（※ template ディレクトリー以下）
    "template" => array(
        "input"     => "sample-min/input.html",     // 入力画面
        "confirm"   => "sample-min/confirm.html",   // 確認画面
        "complete"  => "sample-min/complete.html",  // 完了画面
    ),

    // フォームの設定
    "config" => array(
        "name"    => array(     // フォーム項目の名称 <input name="...">
            "name"  => "名前",  // 表示名
            "type"  => "text",  // データ型
            "null"  => false,   // 未入力可能か
        ),
        "email"   => array(
            "name"  => "メールアドレス",
            "type"  => "mail",
            "null"  => false,
        ),
    ),

);


// メールフォームのモジュールを呼び出す（適宜PATHを変更）
require_once "classes/MailForm.php";


// MailFormを継承したクラス
class MailFormCustom extends MailForm {

	/**
	 * 継承しているプロパティー
	 */
	protected $_template	= array();	// HTMLテンプレート array(アクション名=>ファイルPATH, ...)
	protected $_action		= null;		// アクション制御用
	protected $_actionName	= 'act';	// アクション制御用パラメーター名
	protected $_encode		= "UTF-8";	// 文字コード
	protected $_sessionName = "form";	// セッション名
	protected $_config		= array();	// フォーム設定
	protected $_list		= array();	// リスト変数の値
	protected $_isNGCharCheck = false;	// 機種依存文字チェックの使用
	protected $_ngCharEncode  = "JIS";	// 機種依存文字チェックの検証で使用する文字コード
	protected $_message	= array(		// エラーメッセージの定義
		"text-char"		=> "※%sに使用できない文字が含まれています",
		"text-null"		=> "※%sが入力されていません",
		"text-format"	=> "※%sの形式に誤りがあります",
		"num-null"		=> "※%sが入力されていません",
		"num-format"	=> "※%sが数字ではありません",
		"num-len"		=> "※%sの桁数に誤りがあります",
		"select-null"	=> "※%sが選択されていません",
		"mail-null"		=> "※%sが入力されていません",
		"mail-format"	=> "※%sの形式に誤りがあります",
		"mail-diff"		=> "※%sが異なっています",
		"tel-null"		=> "※%sが入力されていません",
		"tel-format"	=> "※%sの形式に誤りがあります",
		"default-null"	=> "※%sが入力されていません",
		"default-format"=> "※%sの形式に誤りがあります",
		"default-len"	=> "※%sの文字数に誤りがあります",
		"default-diff"	=> "※%sが一致していません",
		"default"		=> "※%sに誤りがあります",
	);
	protected $_csv;		// CSV設定
	protected $_mailUser;	// ユーザー宛メール設定
	protected $_mailAdmin;	// 管理者宛メール設定


	/**
	 * 初期実行
	 */
	public function __construct($params) {
		echo "<p>function __construct(): 初期実行</p>";

		parent::__construct($params);

		echo "<p>_data: ";
		print_r($this->_data);
		echo "</p>";

		echo "<p>function __construct(): フォーム処理後</p>";
	}


	/**
	 * 最終実行
	 */
	public function __destruct() {
		echo "<p>function __destruct(): 画面表示直前</p>";

		parent::__destruct();

		echo "<p>function __destruct(): 画面表示直後</p>";
	}


	/**
	 * クラス継承時の割込みアクション：解析直後
	 */
	public function actionParseAfter() {
		echo "<p>function actionParseAfter(): 解析直後</p>";
	}


	/**
	 * クラス継承時の割込みアクション：解析後・表示前（共通）
	 */
	public function actionCommon() {
		echo "<p>function actionCommon(): 解析後・表示前（共通）</p>";
	}


	/**
	 * クラス継承時の割込みアクション：解析後・表示前（共通）
	 */
	public function actionInput() {
		echo "<p>function actionInput: 処理済み・画面表示前（入力時）</p>";
	}


	/**
	 * クラス継承時の割込みアクション：解析後・表示前（共通）
	 */
	public function actionConfirm() {
		echo "<p>function actionConfirm(): 処理済み・画面表示前（確認時）</p>";
	}


	/**
	 * クラス継承時の割込みアクション：解析後・表示前（共通）
	 */
	public function actionComplete() {
		echo "<p>actionComplete(): 処理済み・画面表示前（完了時）</p>";
	}

}

// メールフォームを実行する
new MailFormCustom($params);


?>
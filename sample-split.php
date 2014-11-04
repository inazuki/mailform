<?php
/**
 * メールフォームサンプル：複数の入力項目で1項目とする
 */


$params = array(

    // ユーザー宛のメール設定
    "mail-user" => array(
        "subject"   => "【お問い合わせ】お客様控え（テスト）",      // 件名
        "to"        => "email",                                     // 宛先に使用するフォーム項目の名称
        "from"      => array("sample@rubato.jp", "テスト送信者"),   // From
        "body"      => "sample-split/mail.user.txt",                // メール本文のテンプレートPATH
    ),

    // HTMLテンプレートのPATH（※ template ディレクトリー以下）
    "template" => array(
        "input"     => "sample-split/input.html",     // 入力画面
        "confirm"   => "sample-split/confirm.html",   // 確認画面
        "complete"  => "sample-split/complete.html",  // 完了画面
    ),

    // フォームの設定
    "config" => array(
        "name"    => array(     // フォーム項目の名称 <input name="...">
            "name"  => "名前",  // 表示名
            "type"  => "text",  // データ型
            "null"  => false,   // 未入力可能か
            "sepa"  => " ",     // セパレーター文字（子項目の結合時に使用）
            "conf"  => array(   // 子項目（子項目の設定には、name type null len が使用できる）
                "sei" => array("name" => "姓",  "type" => "text",  "null" => false,  "len" => "1-10"),
                "mei" => array("name" => "名",  "type" => "text",  "null" => false,  "len" => "1-10")
            ),
        ),
        "email"   => array(
            "name"  => "メールアドレス",
            "type"  => "mail",
            "null"  => false,
            "sepa"  => "@",
            "len"   => "5-128",
            "conf"  => array(
                "account" => array("type" => "mail-a",  "null" => false),
                "domain"  => array("type" => "mail-d",  "null" => false)
            ),
        ),
        "tel"     => array(
            "name"  => "電話番号",
            "type"  => "tel",
            "null"  => true,
            "sepa"  => "-",
            "conf"  => array(
                "tel1" => array("type" => "num",  "null" => true),
                "tel2" => array("type" => "num",  "null" => true),
                "tel3" => array("type" => "num",  "null" => true)
            ),
        ),
    ),

);


// メールフォームのモジュールを呼び出す（適宜PATHを変更）
require_once "classes/MailForm.php";

// メールフォームを実行する
new MailForm($params);


?>
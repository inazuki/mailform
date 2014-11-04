<?php
/**
 * メールフォームサンプル：同一項目の再入力（差異）チェック
 */


$params = array(

    // ユーザー宛のメール設定
    "mail-user" => array(
        "subject"   => "【お問い合わせ】お客様控え（テスト）",      // 件名
        "to"        => "email",                                     // 宛先に使用するフォーム項目の名称
        "from"      => array("sample@rubato.jp", "テスト送信者"),   // From
        "body"      => "sample-diff/mail.user.txt",                  // メール本文のテンプレートPATH
    ),

    // HTMLテンプレートのPATH（※ template ディレクトリー以下）
    "template" => array(
        "input"     => "sample-diff/input.html",     // 入力画面
        "confirm"   => "sample-diff/confirm.html",   // 確認画面
        "complete"  => "sample-diff/complete.html",  // 完了画面
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
            "conf"  => array(
                "email1" => array("type" => "mail",  "null" => false),
                "email2" => array("type" => "mail",  "null" => false)
            ),
            //"sepa"  => "-", ※セパレーター文字の設定が無いと、同一項目チェックとなる
        ),
    ),

);


// メールフォームのモジュールを呼び出す（適宜PATHを変更）
require_once "classes/MailForm.php";

// メールフォームを実行する
new MailForm($params);


?>
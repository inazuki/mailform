<?php
/**
 * メールフォームサンプル：選択リストを使う
 */


$params = array(

    // ユーザー宛のメール設定
    "mail-user" => array(
        "subject"   => "【お問い合わせ】お客様控え（テスト）",      // 件名
        "to"        => "email",                                     // 宛先に使用するフォーム項目の名称
        "from"      => array("sample@rubato.jp", "テスト送信者"),   // From
        "body"      => "sample-list/mail.user.txt",                  // メール本文のテンプレートPATH
    ),

    // HTMLテンプレートのPATH（※ template ディレクトリー以下）
    "template" => array(
        "input"     => "sample-list/input.html",     // 入力画面
        "confirm"   => "sample-list/confirm.html",   // 確認画面
        "complete"  => "sample-list/complete.html",  // 完了画面
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
        "select1" => array(
            "name"  => "選択項目1",
            "type"  => "select",
            "null"  => true,
            "list"  => "LIST1"
        ),
        "select2" => array(
            "name"  => "選択項目2",
            "type"  => "select",
            "null"  => true,
            "list"  => "LIST2"
        ),
    ),

    // リストの設定
    "list"  => array(
        "LIST1" => array(
            "A" => "選択A",
            "B" => "選択B",
            "C" => "選択C",
        ),
        "LIST2" => array(
            "D" => "選択D",
            "E" => "選択E",
            "F" => "選択F",
        ),
    ),

);


// メールフォームのモジュールを呼び出す（適宜PATHを変更）
require_once "classes/MailForm.php";

// メールフォームを実行する
new MailForm($params);


?>
<?php
/**
 * メールフォームサンプル：エラーメッセージを変更
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

    // エラーメッセージの設定
    //   - タイプ別に「 {タイプ}-{エラー} => "%sの××がエラー" 」とメッセージを指定できます
    //   - メッセージの %s には、config->name で指定された表示名が適用されます
    "message" => array(
        "default"       => "※%sに誤りがあります",                      // 全てに一致しない場合に適用
        "default-null"  => "※%sが入力されていません",                  // 未入力時に適用
        "default-format"=> "※%sの形式に誤りがあります",                // 形式不適合時に適用
        "default-len"   => "※%sの文字数に誤りがあります",              // 文字数不適合時に適用
        "default-diff"  => "※%sが一致していません",                    // 
        "default-char"  => "※%sに使用できない文字が含まれています",    // 機種依存文字が使用された場合に適用
        "text-null"     => "※%sが未入力です",                          // テキスト型の未入力時に適用
    ),

);


// メールフォームのモジュールを呼び出す（適宜PATHを変更）
require_once "classes/MailForm.php";

// メールフォームを実行する
new MailForm($params);


?>
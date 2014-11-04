<?php
/**
 * メールフォームサンプル：CSVに保存する
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

    // CSVファイル出力の設定
    "csv" => array(
        "savePath"  => "./tmp",             // CSVの保存PATH
        "fileName"  => "form.%y%m%d.csv",   // CSVファイル名（日付フォーマットを使用可 ※下記参照）
        "separator" => ",",                 // CSVの区切り文字
        "isColumn"  => true,                // 1行目に見出し行を付与する
    ),
    /**
      ≪日付フォーマット≫
        %Y ･･･ 年（4桁）    %y ･･･ 年（2桁）     %m ･･･ 月           %d ･･･ 日
        %H ･･･ 時           %i ･･･ 分            %s ･･･ 秒
        %w ･･･ 曜日の数値（0:日曜 から 6:土曜）  %U ･･･ UNIXタイム
     */

);


// メールフォームのモジュールを呼び出す（適宜PATHを変更）
require_once "classes/MailForm.php";

// メールフォームを実行する
new MailForm($params);


?>
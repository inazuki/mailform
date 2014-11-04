<?php
/**
 * メールフォームサンプル：最大構成
 */


$params = array(

    // ユーザー宛のメール設定
    "mail-user" => array(
        "subject"   => "【お問い合わせ】お客様控え（テスト）",      // 件名
        "to"        => "email",                                     // 宛先に使用するフォーム項目の名称
        "from"      => array("sample@rubato.jp", "テスト送信者"),   // From
        "body"      => "sample-full/mail.user.txt",                  // メール本文のテンプレートPATH
    ),

    // 管理者宛のメール設定
    "mail-admin" => array(
        "subject"   => "【お問い合わせ】管理者控え（テスト）",      // 件名
        "to"        => "sample@rubato.jp",                          // 送信先
        "from"      => array("sample@rubato.jp", "テスト送信者"),   // From
        "body"      => "sample-full/mail.admin.txt",                 // メール本文のテンプレートPATH
    ),

    // HTMLテンプレートのPATH（※ template ディレクトリー以下）
    "template" => array(
        "input"     => "sample-full/input.html",     // 入力画面
        "confirm"   => "sample-full/confirm.html",   // 確認画面
        "complete"  => "sample-full/complete.html",  // 完了画面
    ),

    // フォームの設定
    "config" => array(
        "name"    => array( // フォーム項目の名称 <input name="...">
            "name"  => "名前",
            "type"  => "text",
            "null"  => false,
            "sepa"  => " ",
            "conf"  => array(
                "sei" => array("type" => "text",  "null" => false,  "len" => "1-10"),
                "mei" => array("type" => "text",  "null" => false,  "len" => "1-10")
            ),
        ),
        "kana"    => array(
            "name"  => "フリガナ",
            "type"  => "text",
            "null"  => true,
            "sepa"  => " ",
            "conf"  => array(
                "kana_sei" => array("type" => "text-kana",  "null" => true),
                "kana_mei" => array("type" => "text-kana",  "null" => true)
            ),
        ),
        "email"   => array(
            "name"  => "メールアドレス",
            "type"  => "mail",
            "null"  => false,
            "len"   => "5-128",
            "conf"  => array(
                "email1" => array("type" => "mail",  "null" => false),
                "email2" => array("type" => "mail",  "null" => false)
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
        "zip"     => array(
            "name"  => "郵便番号",
            "type"  => "zip",
            "null"  => true,
            "sepa"  => "-",
            "conf"  => array(
                "zip1" => array("type" => "num",  "null" => true,  "len" => "3"),
                "zip2" => array("type" => "num",  "null" => true,  "len" => "4"),
            ),
        ),
        "select1" => array(
            "name"  => "選択項目1",
            "type"  => "select",
            "null"  => true,
            "list"  => "LIST1",
        ),
        "select2" => array(
            "name"  => "選択項目2",
            "type"  => "select",
            "null"  => true,
            "list"  => "LIST2",
        ),
        "homepage"=> array(
            "name"  => "ホームページURL",
            "type"  => "url",
            "null"  => true,
        ),
        "note"    => array(
            "name"  => "備考",
            "type"  => "text",
            "null"  => true,
        ),
    ),

    // リストの設定
    "list" => array(
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

    // CSVファイル出力の設定
    "csv" => array(
        "savePath"  => "./tmp",             // CSVの保存PATH
        "fileName"  => "full.%y%m%d.csv",   // CSVファイル名（日付フォーマットを使用可 ※下記参照）
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
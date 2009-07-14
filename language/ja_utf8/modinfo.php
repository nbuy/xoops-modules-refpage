<?php
// Module Info

// The name of this module
define("_MI_TRACKBACK_NAME","参照元情報");

// A brief description of this module
define("_MI_TRACKBACK_DESC","ページのリンク参照の記録と表示を行う");

// Names of blocks for this module (Not all module has blocks)
define("_MI_TRACKBACK_BNAME","参照元情報");

define("_MI_TRACKBACK_SMENU1","サイト全体");

define("_MI_TRACKBACK_ADMENU1","参照情報の編集");
define("_MI_TRACKBACK_ADMENU2","参照元の検査");
define("_MI_TRACKBACK_ADMENU4","追跡記録の削除");

//Config
define("_MI_TBCF_EXCLUDE","非表示にする参照元");
define("_MI_TBCF_EXCLUDE_DESC","確認なしにリンクを非表示状態とする参照元");
define("_MI_TBCF_EXCLUDE_DEF","www.google.*\nsearch.yahoo.*\nsearch.msn.*\nwww.excite.co.jp/search/\nsearch.live.com\nsearch.goo.ne.jp\nsearch.www.infoseek.co.jp\ncgi.search.biglobe.ne.jp\n209.85.175\nwww.shinobi.jp/etc/");
define("_MI_TBCF_INCLUDE","表示する参照元");
define("_MI_TBCF_INCLUDE_DESC","確認なしにリンクを表示状態にする参照元");
define("_MI_TBCF_INCLUDE_DEF","mixi.jp");
define("_MI_TBCF_AUTOCHECK","参照元の自動確認");
define("_MI_TBCF_AUTOCHECK_DESC","参照元に自サイトへのリンクがあるかどうかの検査を行う");
define("_MI_TBCF_BLOCKSHOW", "ブロックに情報を表示");
define("_MI_TBCF_BLOCKSHOW_DESC", "参照元ブロックの中に参照元情報を表示する");
define("_MI_TBCF_LISTMAX", "表示する項目数");
define("_MI_TBCF_LISTMAX_DESC", "詳細情報ページに表示する項目数");
define("_MI_TBCF_TITLELEN", "タイトルの最大長");
define("_MI_TBCF_TITLELEN_DESC", "タイトル文字列の最大長 (〜255バイト)");
define("_MI_TBCF_CTEXTLEN", "切り出す参照本文の長さ");
define("_MI_TBCF_CTEXTLEN_DESC", "切り出す参照元の文字列の長さ (〜255バイト)");
define("_MI_TBCF_EXPIREDAY", "参照元の再確認日数");
define("_MI_TBCF_EXPIREDAY_DESC", "アクセス間隔がx日を越えたら再確認");
define("_MI_TBCF_THRESHOLD", "有効アクセス数");
define("_MI_TBCF_THRESHOLD_DESC", "有効なリンクと判断する参照数");
?>
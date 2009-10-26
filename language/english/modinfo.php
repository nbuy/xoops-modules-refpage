<?php
// Module Info

// The name of this module
define("_MI_REFPAGE_NAME","Refpage");

// A brief description of this module
define("_MI_REFPAGE_DESC","Records and Display refpage information");

// Names of blocks for this module (Not all module has blocks)
define("_MI_REFPAGE_BNAME","Refpage");

define("_MI_REFPAGE_SMENU1","All of site");

define("_MI_REFPAGE_ADMENU1","Edit refpage info");
define("_MI_REFPAGE_ADMENU2","Check for origin");
define("_MI_REFPAGE_ADMENU4","Delete expired");

//Templates
define("_MI_RP_INDEX_TPL","List of referenced pages");

//Configs
define("_MI_RPCF_EXCLUDE","Not display link");
define("_MI_RPCF_EXCLUDE_DESC","Not display link without confirm");
define("_MI_RPCF_EXCLUDE_DEF","www.google.*\nsearch.yahoo.*\nsearch.msn.*\nwww.excite.co.jp/search/\nsearch.live.com\nsearch.goo.ne.jp\nsearch.www.infoseek.co.jp\ncgi.search.biglobe.ne.jp\n209.85.175\nwww.shinobi.jp/etc/");
define("_MI_RPCF_INCLUDE","Display link");
define("_MI_RPCF_INCLUDE_DESC","Display link without confirm");
define("_MI_RPCF_INCLUDE_DEF","valid.example.com");
define("_MI_RPCF_AUTOCHECK","Automatic origin checking");
define("_MI_RPCF_AUTOCHECK_DESC","Checking refererer page has link to this site");
define("_MI_RPCF_BLOCKSHOW", "Show referer information in block");
define("_MI_RPCF_BLOCKSHOW_DESC", "Referer show only webmaster choose 'No'");
define("_MI_RPCF_LISTMAX", "Number of items in a page");
define("_MI_RPCF_LISTMAX_DESC", "Show items in detail page");
define("_MI_RPCF_TITLELEN", "Title string width");
define("_MI_RPCF_TITLELEN_DESC", "Title string maximum length (max 255chars)");
define("_MI_RPCF_CTEXTLEN", "Text around link from origin page");
define("_MI_RPCF_CTEXTLEN_DESC", "Text around link from origin page (max 255 chars)");
define("_MI_RPCF_EXPIREDAY", "Checking again days");
define("_MI_RPCF_EXPIREDAY_DESC", "Re-checking until last access over in this days");
define("_MI_RPCF_THRESHOLD", "Valid access threshold");
define("_MI_RPCF_THRESHOLD_DESC", "Minimum access count for valid link");
?>
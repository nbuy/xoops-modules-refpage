<?php
// Module Info

// The name of this module
define("_MI_TRACKBACK_NAME","Trackback");

// A brief description of this module
define("_MI_TRACKBACK_DESC","Records and Display trackback information");

// Names of blocks for this module (Not all module has blocks)
define("_MI_TRACKBACK_BNAME","Trackback");

define("_MI_TRACKBACK_SMENU1","All of site");

define("_MI_TRACKBACK_ADMENU1","Edit trackback info");
define("_MI_TRACKBACK_ADMENU2","Check for origin");
define("_MI_TRACKBACK_ADMENU4","Delete expired");

//Config
define("_MI_TBCF_EXCLUDE","Not display link");
define("_MI_TBCF_EXCLUDE_DESC","Not display link without confirm");
define("_MI_TBCF_EXCLUDE_DEF","www.google.*\nsearch.yahoo.*\nsearch.msn.*\nwww.excite.co.jp/search/\nsearch.live.com\nsearch.goo.ne.jp\nsearch.www.infoseek.co.jp\ncgi.search.biglobe.ne.jp\n209.85.175\nwww.shinobi.jp/etc/");
define("_MI_TBCF_INCLUDE","Display link");
define("_MI_TBCF_INCLUDE_DESC","Display link without confirm");
define("_MI_TBCF_INCLUDE_DEF","valid.example.com");
define("_MI_TBCF_AUTOCHECK","Automatic origin checking");
define("_MI_TBCF_AUTOCHECK_DESC","Checking refererer page has link to this site");
define("_MI_TBCF_BLOCKSHOW", "Show referer information in block");
define("_MI_TBCF_BLOCKSHOW_DESC", "Referer show only webmaster choose 'No'");
define("_MI_TBCF_LISTMAX", "Number of items in a page");
define("_MI_TBCF_LISTMAX_DESC", "Show items in detail page");
define("_MI_TBCF_TITLELEN", "Title string width");
define("_MI_TBCF_TITLELEN_DESC", "Title string maximum length (max 255chars)");
define("_MI_TBCF_CTEXTLEN", "Text around link from origin page");
define("_MI_TBCF_CTEXTLEN_DESC", "Text around link from origin page (max 255 chars)");
define("_MI_TBCF_EXPIREDAY", "Checking again days");
define("_MI_TBCF_EXPIREDAY_DESC", "Re-checking until last access over in this days");
define("_MI_TBCF_THRESHOLD", "Valid access threshold");
define("_MI_TBCF_THRESHOLD_DESC", "Minimum access count for valid link");
?>
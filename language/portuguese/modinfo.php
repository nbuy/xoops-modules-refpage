<?php
// Module Info

// The name of this module
define("_MI_TRACKBACK_NAME","Trackback");

// A brief description of this module
define("_MI_TRACKBACK_DESC","Informa��es sobre os registros e mostra os trackback");

// Names of blocks for this module (Not all module has blocks)
define("_MI_TRACKBACK_BNAME","Trackback");

define("_MI_TRACKBACK_SMENU1","Todo o site");

define("_MI_TRACKBACK_ADMENU1","Editar informa��es do trackback");
define("_MI_TRACKBACK_ADMENU2","Verificar a origem");
define("_MI_TRACKBACK_ADMENU4","Excluir os vencidos");

//Config
define("_MI_TBCF_EXCLUDE","N�o mostrar link sem checagem");
define("_MI_TBCF_EXCLUDE_DESC","Not display link without confirm");
define("_MI_TBCF_EXCLUDE_DEF","www.google.*\nsearch.yahoo.*\nsearch.msn.*\nwww.excite.co.jp/search/\nsearch.live.com\nsearch.goo.ne.jp\nsearch.www.infoseek.co.jp\ncgi.search.biglobe.ne.jp\n209.85.175\nwww.shinobi.jp/etc/");
define("_MI_TBCF_INCLUDE","Mostrar link sem checagem");
define("_MI_TBCF_INCLUDE_DESC","Display link without confirm");
define("_MI_TBCF_INCLUDE_DEF","valid.example.com");
define("_MI_TBCF_AUTOCHECK","Checagem autom�tica da origem");
define("_MI_TBCF_AUTOCHECK_DESC","Checking refererer page has link to this site");
define("_MI_TBCF_BLOCKSHOW", "Mostrar informa��o do trackback no bloco");
define("_MI_TBCF_BLOCKSHOW_DESC", "Referer show only webmaster choose 'No'");
define("_MI_TBCF_LISTMAX", "N�mero de �tens em uma p�gina");
define("_MI_TBCF_LISTMAX_DESC", "Show items in detail page");
define("_MI_TBCF_TITLELEN", "Comprimento dos caracteres do t�tulo");
define("_MI_TBCF_TITLELEN_DESC", "Comprimento dos caracteres do t�tulo (..255)");
define("_MI_TBCF_CTEXTLEN", "Texto em volta");
define("_MI_TBCF_CTEXTLEN_DESC", "Texto em volta do link da p�gina de origem (..255)");
define("_MI_TBCF_EXPIREDAY", "Re-checagem dias");
define("_MI_TBCF_EXPIREDAY_DESC", "Re-checagem at� �ltimo acesso sobre estes dias");
define("_MI_TBCF_THRESHOLD", "validar acesso incial");
define("_MI_TBCF_THRESHOLD_DESC", "Minimum access count for valid link");
?>

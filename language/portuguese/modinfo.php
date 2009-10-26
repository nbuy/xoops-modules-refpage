<?php
// Module Info

// The name of this module
define("_MI_REFPAGE_NAME","Refpage");

// A brief description of this module
define("_MI_REFPAGE_DESC","Informações sobre os registros e mostra os refpage");

// Names of blocks for this module (Not all module has blocks)
define("_MI_REFPAGE_BNAME","Refpage");

define("_MI_REFPAGE_SMENU1","Todo o site");

define("_MI_REFPAGE_ADMENU1","Editar informações do refpage");
define("_MI_REFPAGE_ADMENU2","Verificar a origem");
define("_MI_REFPAGE_ADMENU4","Excluir os vencidos");

//Templates
define("_MI_RP_INDEX_TPL","List of referenced pages");

//Configs
define("_MI_RPCF_EXCLUDE","Não mostrar link sem checagem");
define("_MI_RPCF_EXCLUDE_DESC","Not display link without confirm");
define("_MI_RPCF_EXCLUDE_DEF","www.google.*\nsearch.yahoo.*\nsearch.msn.*\nwww.excite.co.jp/search/\nsearch.live.com\nsearch.goo.ne.jp\nsearch.www.infoseek.co.jp\ncgi.search.biglobe.ne.jp\n209.85.175\nwww.shinobi.jp/etc/");
define("_MI_RPCF_INCLUDE","Mostrar link sem checagem");
define("_MI_RPCF_INCLUDE_DESC","Display link without confirm");
define("_MI_RPCF_INCLUDE_DEF","valid.example.com");
define("_MI_RPCF_AUTOCHECK","Checagem automática da origem");
define("_MI_RPCF_AUTOCHECK_DESC","Checking refererer page has link to this site");
define("_MI_RPCF_BLOCKSHOW", "Mostrar informação do referer no bloco");
define("_MI_RPCF_BLOCKSHOW_DESC", "Referer show only webmaster choose 'No'");
define("_MI_RPCF_LISTMAX", "Número de ítens em uma página");
define("_MI_RPCF_LISTMAX_DESC", "Show items in detail page");
define("_MI_RPCF_TITLELEN", "Comprimento dos caracteres do título");
define("_MI_RPCF_TITLELEN_DESC", "Comprimento dos caracteres do título (..255)");
define("_MI_RPCF_CTEXTLEN", "Texto em volta");
define("_MI_RPCF_CTEXTLEN_DESC", "Texto em volta do link da página de origem (..255)");
define("_MI_RPCF_EXPIREDAY", "Re-checagem dias");
define("_MI_RPCF_EXPIREDAY_DESC", "Re-checagem até último acesso sobre estes dias");
define("_MI_RPCF_THRESHOLD", "validar acesso incial");
define("_MI_RPCF_THRESHOLD_DESC", "Minimum access count for valid link");
?>

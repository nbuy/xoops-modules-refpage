<?php
# trackback -> refpage upgrade
# $Id: oninstall.php,v 1.1 2009/10/31 10:05:53 nobu Exp $

global $xoopsDB;
# rename trackback* -> refpage*
define('OLDTBL', $xoopsDB->prefix("trackback"));
define('NEWTBL', $xoopsDB->prefix("refpage"));

if ($xoopsDB->query("SELECT * FROM ".OLDTBL."_ref LIMIT 1")) {
    report_message(" Add new table: <b>$table</b>");
    foreach (array('', '_ref', '_log') as $pfix) {
	report_message("Replace: trackback$pfix -&gt; refpage$pfix");
	$xoopsDB->query('DROP TABLE `'.NEWTBL.$pfix.'`');
	$xoopsDB->query('RENAME TABLE `'.OLDTBL.$pfix.'`  TO `'.NEWTBL.$pfix.'`');
    }
}

function report_message($msg) {
    global $msgs;		// module manager's variable
    static $first = true;
    if ($first) {
	$msgs[] = "Update Database...";
	$first = false;
    }
    $msgs[] = "&nbsp;&nbsp; $msg";
}

$handler =& xoops_gethandler('groupperm');

?>

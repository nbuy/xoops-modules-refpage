<?php
include("../../../mainfile.php");
include_once(XOOPS_ROOT_PATH."/class/xoopsmodule.php");
include(XOOPS_ROOT_PATH."/include/cp_functions.php");
if ( $xoopsUser ) {
	$xoopsModule = XoopsModule::getByDirname("trackback");
	if ( !$xoopsUser->isAdmin($xoopsModule->mid()) ) { 
		redirect_header($xoopsConfig['xoops_url']."/",3,_NOPERM);
		exit();
	}
} else {
	redirect_header($xoopsConfig['xoops_url']."/",3,_NOPERM);
	exit();
}

$modbase = dirname(dirname(__FILE__));
$inc = $modbase.'/language/'.$xoopsConfig['language'].'/main.php';
if (file_exists($inc)) include_once $inc;
else {				// fallback
    $inc = $modbase.'/language/english/main.php';
    if (file_exists($inc)) include_once $inc;
}
?>

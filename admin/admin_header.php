<?php
include("../../../mainfile.php");
include_once(XOOPS_ROOT_PATH."/class/xoopsmodule.php");
include(XOOPS_ROOT_PATH."/include/cp_functions.php");
//error_reporting(E_ALL);
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
$modbase = XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar('dirname');
include_once "$modbase/cache/config.php";

    function putCache($tag, $content) {
	$file = XOOPS_ROOT_PATH."/modules/".preg_replace('/\//', "/cache/", $tag);
	$fp = fopen($file, "w");
	if ($fp) {
	    fwrite($fp, "<?php\n$content\n?>");
	    fclose($fp);
	} else {
	    redirect_header("index.php",5,sprintf(_MUSTWABLE, $file));
	    exit();
	}
    }
?>

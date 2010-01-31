<?php
// $Id: refpage.php,v 1.19 2010/01/31 06:04:36 nobu Exp $
function b_refpage_log_show($options) {
    global $xoopsDB, $trackConfig;
    $myts =& MyTextSanitizer::getInstance();

    if (empty($trackConfig)) {
	$config_handler =& xoops_gethandler('config');
	$dirname = basename(dirname(dirname(__FILE__)));
	$module_handler =& xoops_gethandler('module');
	$module =& $module_handler->getByDirname($dirname);
	$trackConfig = $config_handler->getConfigsByCat(0, $module->getVar('mid'));
    }

    // ** refpage recoding **

    $ref = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:"";
    $uri = $_SERVER["REQUEST_URI"];
    $uri = preg_replace('/\/index.php$/', '/', $uri);
    $uri = preg_replace('|^(/modules/mydownloads/singlefile\.php\?)cid=\d+&|', '$1', $uri);
    $uri = preg_replace('/[&\?]?PHPSESSID=[^&]*/', '', $uri);
    $uri = preg_replace('/[&\?]?easiestml_lang=(ja|en)/', '', $uri);
    $uriq= $xoopsDB->quoteString($uri);
    $now = time();

    $tbl = $xoopsDB->prefix("refpage");
    $tbr = $xoopsDB->prefix("refpage_ref");

    $sql = "SELECT track_id,disable FROM $tbl WHERE track_uri=$uriq";
    $result = $xoopsDB->query($sql);
    // referere self site, 2nd will be fake referer (Robots?).
    if (preg_match("/^".preg_quote(XOOPS_URL,"/")."\//", $ref)) $ref="";
    elseif (preg_match("/^".preg_quote(XOOPS_URL,"/")."\$/", $ref)) $ref="";
    elseif (!preg_match("/^https?:\/\//i", $ref)) $ref=""; // no http (fake?)
    if ($xoopsDB->getRowsNum($result)) {
	list($tid, $disable) = $xoopsDB->fetchRow($result);
    } else {
	// new page register
	if ($ref!="") {
	    $xoopsDB->queryF("INSERT INTO $tbl(track_uri, since) VALUES($uriq, $now)");
	    $result = $xoopsDB->query($sql);
	    list($tid, $disable) = $xoopsDB->fetchRow($result);
	} else {
	    $disable = 0;
	    $tid = 0;
	}
    }

    $block = array();
    if ($disable) return $block; // disable in this page

    if ($tid && !empty($ref)) {
	$refq= $xoopsDB->quoteString($ref);
	$result = $xoopsDB->query("SELECT ref_id,nref,mtime,checked FROM $tbr WHERE ref_url=$refq AND track_from=$tid");
	$ip = $_SERVER["REMOTE_ADDR"];
	$log = $xoopsDB->prefix("refpage_log");
	$exreg = list_to_regexp($trackConfig['exclude']);
	$inreg = list_to_regexp($trackConfig['include']);
	$checked = 0;
	$linked = 0;
	if ($xoopsDB->getRowsNum($result)) {
	    // already registered
	    list($rid, $refno, $mtime, $checked) = $xoopsDB->fetchRow($result);
	    // check valid reference. (is it not reload?)
	    // remove expire entry
	    $xoopsDB->queryF("DELETE FROM $log WHERE atime<".($now-3600));
	    $result = $xoopsDB->queryF("SELECT log_id FROM $log WHERE tfrom=$tid AND rfrom=$rid AND ip='$ip'");
	    if ($xoopsDB->getRowsNum($result)) {
		list($lid) = $xoopsDB->fetchRow($result);
		$xoopsDB->queryF("UPDATE $log SET atime=$now WHERE log_id=$lid");
	    } else {
		if ($exreg!='' && preg_match($exreg, $ref)) {
		    $checked = 1;	// No link without check
		} elseif ($inreg!='' && preg_match($inreg, $ref)) {
		    $checked = 1;
		    $linked = 1;	// Link without check
		} elseif ($trackConfig['auto_check']) {
		    // get fail or expire get text
		    if (!$checked ||
			($now-$mtime) > $trackConfig['expire']*24*3600) {
			list($title, $ctext, $linked, $checked) = refpage_get_details($ref,$uri);
			if ($checked) {	// keep old value when fail to get.
			    $title=$xoopsDB->quoteString($title);
			    $ctext=$xoopsDB->quoteString($ctext);
			    $xoopsDB->queryF("UPDATE $tbr SET checked=$checked, linked=$linked, title=$title, context=$ctext WHERE ref_id=$rid");
			}
		    }
		}

		$xoopsDB->queryF("UPDATE $tbr SET nref=nref+1, mtime='$now' WHERE ref_id=$rid");
		$refno++;
		$xoopsDB->queryF("INSERT INTO $log(atime, tfrom, rfrom, ip) VALUES($now, $tid, $rid, '$ip')");
	    }
	} else {
	    // new register
	    $xoopsDB->queryF("INSERT INTO $tbr(since,track_from,ref_url)".
			     " VALUES($now, $tid, $refq)");
	    // check origin page, there is link exist?
	    $title = '';
	    $ctext = '';
	    if ($exreg!='' && preg_match($exreg, $ref)) {
		$checked = 1;	// No link without check
	    } elseif ($inreg!='' && preg_match($inreg, $ref)) {
		$checked = 1;
		$linked = 1;	// Link without check
	    } elseif ($trackConfig['auto_check']) {
		list($title, $ctext, $linked, $checked) = refpage_get_details($ref,$uri);
		$title=$xoopsDB->quoteString($title);
		$ctext=$xoopsDB->quoteString($ctext);
	    }
	    $xoopsDB->queryF("UPDATE $tbr SET nref=nref+1, checked=$checked, linked=$linked, title=$title, context=$ctext, mtime='$now' WHERE track_from=$tid AND ref_url=$refq");
	    $result = $xoopsDB->query("SELECT ref_id FROM $tbr WHERE track_from=$tid AND ref_url=$refq");
	    list($rid) = $xoopsDB->fetchRow($result);
	    $xoopsDB->queryF("INSERT INTO $log(atime, tfrom, rfrom, ip) VALUES($now, $tid, $rid, '$ip')");
	    $refno = 1;
	}
    }

    // refpage show block build

    if (!$trackConfig['block_show']) return $block;
    $block['title'] = _MB_REFPAGE_TITLE;
    if ($tid) {
	$cond = "track_from=$tid AND linked=1 AND nref>=".$trackConfig['threshold'];
	$max=$options[0];
	$trim=$options[1];
	$result = $xoopsDB->query($sql="SELECT SUM(nref) nref, MIN(ref_url) url, title FROM $tbr WHERE $cond GROUP BY title ORDER BY nref DESC");
	$rows = array();
	$nrec = $xoopsDB->getRowsNum($result);
	if ($nrec) {
	    $n = $max;
	    while ($n-- && $data =$xoopsDB->fetchArray($result)) {
		$title = $data['title'];
		if ($title=="") {
		    $title = preg_replace('/\/.*$/', '', preg_replace('/^https?:\/\//', '', $data['url']));
		}
	        $alt = "";
		if (strlen($title)>$trim) {
		    $data['alt'] = " title='$title'";
		    $title=mysubstr($title, 0, $trim)."..";
		}
		$data['title'] = $title;
		$data['url'] = htmlspecialchars($data['url']);
		$rows[] = $data;
	    }
	    $block['rows']=&$rows;
	    if ($nrec>$max) {
		$block['rest'] = sprintf(_MB_REFPAGE_REST, $nrec-$max);
	    }
	    if (mod_allow_access($dirname)) {
		$block['more'] = XOOPS_URL."/modules/$dirname/index.php?id=$tid";
	    }
	}
    }
    return $block;
}

function b_refpage_log_edit($options) {
    return _MB_REFPAGE_MAX."&nbsp;<input name='options[0]' value='".$options[0]."' /><br />\n".
	_MB_REFPAGE_LEN."&nbsp;<input name='options[1]' value='".$options[1]."' />\n";
}

function list_to_regexp($l) {
    $l = trim($l);
    if ($l == '') return '';
    return '/^https?:\/\/('.preg_replace(array('/\n*$/', '/\n\n+/','/\r?\n/', '/\./','/\*/', '/\//'),array('', "\n", '|', '\.', '.*', '\/'), $l).')/';
}

//
// block local functions
//

// substrings with support multibytes (--with-mbstring)
// duplicate in ../function.php - umm..
if (!function_exists("mysubstr")) {
    if (function_exists("mb_strcut")) {
	function mysubstr($s, $f, $l) {
	    return mb_strcut($s, $f, $l, _CHARSET);
	}
    } else {
	function mysubstr($s, $f, $l) {
	    return substr($s, $f, $l);
	}
    }
}

function refpage_get_details($ref, $uri) {
    global $trackConfig;

    $linked = 0;
    $checked = 0;
    $title = '';
    $ctext = '';

    // includes Snoopy class for remote file access
    require_once(XOOPS_ROOT_PATH."/class/snoopy.php");
    $snoopy = new Snoopy;
    //TIMEOUT
    $snoopy->read_timeout = 10;
    //URL
    $snoopy->fetch($ref);
    //GET DATA
    $page = $snoopy->results;

    if($snoopy->error) $page = "";
 
    if ($page) {
	if (XOOPS_USE_MULTIBYTES && function_exists("mb_convert_encoding")) {
	    $page = mb_convert_encoding($page, _CHARSET, "ISO-2022-JP,JIS,EUC-JP,UTF-8,Shift_JIS");
	}
	$pat = "/<title[^>]*>(.*)<\/title>/i";
	if (preg_match($pat, $page, $d)) {
	    $title = $d[1];
	    $len = 255;
	    if (strlen($title)>$len) $title=mysubstr($title, 0, $len-2)."..";
	    $page = preg_replace($pat, "", $page);
	}
	$anc = "/<a\\s+href=([\"'])?".preg_quote(XOOPS_URL.$uri,"/")."\\1(>|\s[^>]*>)/i";
	$root = "/<a\\s+href=([\"']?)".preg_quote(XOOPS_URL."/","/")."\\1(>|\s[^>]*>)/i";
	if (preg_match($anc, $page)) {
	    $F = preg_split($anc, $page, 2);
	    $linked = 1;
	} elseif (preg_match($root, $page)) {
	    $F = preg_split($root, $page, 2);
	    $linked = 1;
	}
	if ($linked) {
	    // cut out text from orign page
	    function strip_string($s) {
		return preg_replace('/\s+/', ' ', urldecode(strip_tags($s)));
	    }
	    $l = min($trackConfig['ctext_len'],255);
	    $pre = ltrim(strip_string($F[0]));
	    list($a, $post)=preg_split('/<\/a>/i', $F[1], 2);
	    $post = rtrim(strip_string($post));
	    $m = intval($l/2);
	    $ctext=mysubstr($pre, max(strlen($pre)-$m+1,0),$m)."<u>".strip_tags($a)."</u>";
	    $m = $l-strlen($ctext);
	    $ctext.=mysubstr($post, 0, min(strlen($post),$m));
	    $ctext = mysubstr($ctext, 0, $l);
	    if (preg_match('/<u>/', $ctext)&&!preg_match('/<\/u>/', $ctext)) {
		$ctext = mysubstr($ctext,0,$l-4)."</u>";
	    }
	}
	if ($linked == 1 || strip_tags($page) != "") $checked=1;
    }
    return array($title, $ctext, $linked, $checked);
}

function mod_allow_access($dirname) {
    global $xoopsUser;

    $modhandler =& xoops_gethandler('module');
    $mod =& $modhandler->getByDirname($dirname);
    $handler =& xoops_gethandler('groupperm');
    return (!empty($xoopsUser) &&
	    $handler->checkRight("module_read", $mod->getVar("mid"), $xoopsUser->getGroups())) ||
	$handler->checkRight("module_read", $mod->getVar("mid"), XOOPS_GROUP_ANONYMOUS);
}

?>

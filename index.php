<?php
// trackback module for XOOPS (user side code)
// $Id: index.php,v 1.15 2010/01/11 10:39:37 nobu Exp $
include("header.php");
include_once "functions.php";

$base = XOOPS_URL."/modules/".$xoopsModule->dirname();
$basedir = XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname();

$track_id =isset($_GET['id'])?intval($_GET['id']):"";
$page = isset($_GET['page'])?intval($_GET['page']):1;
$detail = isset($_GET['detail']);

$tblstyle="border='0' cellspacing='1' cellpadding='3' class='outer' width='100%'";
include(XOOPS_ROOT_PATH."/header.php");

$breadcrumbs = array();
$breadcrumbs[] = array('url'=>'index.php', 'name'=>_TB_INDEX);

if (!isset($xoopsModuleConfig['threshold'])) $xoopsModuleConfig['threshold'] = 1;

if ($track_id == "all") {
    $xoopsOption['template_main'] = 'refpage_referer.html';
    $order = "nref DESC";
    $opt = "";
    if (isset($_GET['order'])) {
	switch ($_GET['order']) {
	case 'time':
	    $order = "mtime DESC";
	    $opt .= "&order=time";
	    break;
	}
    }
    $cond = "linked=1 AND track_id=track_from AND disable=0 AND nref>=".$xoopsModuleConfig['threshold'];
    $title = "";
    $result = $xoopsDB->query("SELECT COUNT(title) FROM ".TBR.",".TBL." WHERE $cond");
    list($nrec) = $xoopsDB->fetchRow($result);
    $start = ($page>1)?($page-1)*$xoopsModuleConfig['list_max']:0;
    $breadcrumbs[] = array('name'=>_TB_ALLPAGE);
    $xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
    $result = $xoopsDB->query("SELECT * FROM ".TBR.",".TBL." WHERE $cond ORDER BY $order", $xoopsModuleConfig['list_max'], $start);
    if ($nrec) {
	$popt = ($page>1)?"&page=$page":"";
	$ordstr = sprintf($order=="nref DESC"?"<b>%s</b>":"<a href='index.php?id=all$popt'>%s</a>",_TB_ORDER_NREF).
	    " | ".sprintf($order=="mtime DESC"?"<b>%s</b>":"<a href='index.php?id=all&order=time$popt'>%s</a>",_TB_ORDER_TIME);
	$xoopsTpl->assign('page_control', make_page_index("$ordstr - "._TB_PAGE, $nrec, $page, " <a href='index.php?id=$track_id$opt&page=%d'>(%d)</a>"));

	$referers = array();
	while ($data=$xoopsDB->fetchArray($result)) {
	    $data['seq'] = ++$start;
	    $data['cdate'] = formatTimestamp($data['since'], "m");
	    $data['mdate'] = formatTimestamp($data['mtime'], "m");
	    $uri = $data['track_uri'];
	    $linkto = " "._TB_LINKTO." <a href='$uri'>".uri_to_name($uri)."</a>";
	    if (empty($data['title'])) $data['title'] = myurldecode($data['ref_url']);
	    $referers[] = $data;
	}
	$xoopsTpl->assign('referers', $referers);
    }
} elseif ( empty($track_id) ) {
    // list of tracking pages
    $cond = "track_id=track_from AND disable=0 AND linked=1 AND nref>=".$xoopsModuleConfig['threshold']." GROUP BY track_from ";
    $result = $xoopsDB->query("SELECT track_from FROM ".TBL.",".TBR." WHERE $cond");
    $nrec = $xoopsDB->getRowsNum($result);
    $start = ($page>1)?($page-1)*$xoopsModuleConfig['list_max']:0;
    $result = $xoopsDB->query("SELECT track_id AS tid, track_uri AS uri, COUNT(ref_id) AS refs FROM ".TBL.",".TBR." WHERE $cond ORDER BY track_uri", $xoopsModuleConfig['list_max'], $start);
    if ($nrec) {
	$xoopsOption['template_main'] = 'refpage_index.html';

	$xoopsTpl->assign('page_control', make_page_index(_TB_PAGE, $nrec, $page, " <a href='index.php?page=%d'>(%d)</a>"));
	$pages = array();
	while ($pg=$xoopsDB->fetchArray($result)) {
	    $start++;
	    $pg['seq'] = $start;
	    $pg['name'] = uri_to_name($pg['uri']);
	    $pages[] = $pg;
	}
	$xoopsTpl->assign('pages', $pages);
    }
} else {
    // a tracking page
    $xoopsOption['template_main'] = 'refpage_referer.html';
    $order = "nref DESC";
    $opt = "";
    if (isset($_GET['order'])) {
	switch ($_GET['order']) {
	case 'time':
	    $order = "mtime DESC";
	    $opt .= "&order=time";
	    break;
	}
    }
    $result = $xoopsDB->query("SELECT track_uri,disable FROM ".TBL." WHERE track_id=$track_id");
    list($uri, $disable) = $xoopsDB->fetchRow($result);
    if (!isset($disable) || $disable) {
	redirect_header("index.php",1,_TB_NOPAGE);
	exit();
    }

    $cond = "track_from=$track_id AND linked=1 AND nref>=".$xoopsModuleConfig['threshold'];
    if ($detail) {
	$sql = "SELECT COUNT(ref_id) FROM ".TBR." WHERE $cond";
	$result = $xoopsDB->query($sql);
	list($nrec) = $xoopsDB->fetchRow($result);
    } else {
	$sql = "SELECT title, count(1) FROM ".TBR." WHERE $cond GROUP BY title";
	$result = $xoopsDB->query($sql);
	$nrec = $xoopsDB->GetRowsNum($result);
    }
    $start = ($page>1)?($page-1)*$xoopsModuleConfig['list_max']:0;
    $breadcrumbs[] = array('name'=>uri_to_name($uri));
    
    $xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
    if ($detail) {			// summary by "title"
	$sql = "SELECT * FROM ".TBR." WHERE $cond ORDER BY $order";
    } else {
	$sql = "SELECT SUM(nref) nref,COUNT(ref_id) n, title, ".
	    " MAX(mtime) mtime, MIN(since) since,".
	    " MIN(ref_url) ref_url, MAX(context) context".
	    " FROM ".TBR." WHERE $cond".
	    " GROUP BY title ORDER BY $order";
    }
    $result = $xoopsDB->query($sql, $xoopsModuleConfig['list_max'], $start);
    if ($nrec) {
	$popt = ($page>1)?"&page=$page":"";
	$ordstr = sprintf($order=="nref DESC"?"<b>%s</b>":"<a href='index.php?id=$track_id$popt'>%s</a>",_TB_ORDER_NREF).
	    " | ".sprintf($order=="mtime DESC"?"<b>%s</b>":"<a href='index.php?id=$track_id&order=time$popt'>%s</a>",_TB_ORDER_TIME);
	$xoopsTpl->assign('page_control', make_page_index("$ordstr - "._TB_PAGE, $nrec, $page, " <a href='index.php?id=$track_id$opt&page=%d'>(%d)</a>"));
	$referers = array();
	while ($data=$xoopsDB->fetchArray($result)) {
	    $data['seq'] = ++$start;
	    $data['cdate'] = formatTimestamp($data['since'], "m");
	    $data['mdate'] = formatTimestamp($data['mtime'], "m");
	    if (!$detail && $data['n']>1) {	// url list
		$rsub = $xoopsDB->query("SELECT nref, ref_url, mtime FROM ".TBR." WHERE $cond AND title=".$xoopsDB->quoteString($data['title'])." ORDER BY $order", 20);
		$refs = array();
		$refn = array();
		while (list($nref, $url)=$xoopsDB->fetchRow($rsub)) {
		    $refs[] = $url;
		    $refn[] = $nref;
		}
		$data["refs"] = $refs;
		$data["refn"] = $refn;
		$data["ref_url"] = $refs[0];
	    }
	    if (empty($data['title'])) $data['title'] = myurldecode($data['ref_url']);
	    $referers[] = $data;
	}
	$xoopsTpl->assign('referers', $referers);
    }
}
include (XOOPS_ROOT_PATH."/footer.php");
?>

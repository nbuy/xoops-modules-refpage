<?php
// trackback module for XOOPS (admin side code)
// $Id: index.php,v 1.14 2009/07/13 07:03:10 nobu Exp $
include("admin_header.php");
include_once("../functions.php");

$dir = $xoopsModule->dirname();

$op = "";
if ( isset($_GET['op']) ) $op = $_GET['op'];
if ( isset($_POST['op']) ) $op = $_POST['op'];
$page = isset($_GET['page'])?$_GET['page']:1;
$start = ($page>1)?($page-1)*$xoopsModuleConfig['list_max']:0;

$myts =& MyTextSanitizer::getInstance();
$tbl = $xoopsDB->prefix("trackback");
$tbr = $xoopsDB->prefix("trackback_ref");
$tblstyle="border='0' cellspacing='1' cellpadding='3' class='bg2' width='100%'";

switch ($op) {
case 'edit_update':
    edit_update();
    exit;
case 'check_update':
    check_update();
    exit;
}

if( ! empty( $_GET['lib'] ) ) {
    global $mydirpath;
    $mydirpath = dirname(dirname(__FILE__));
    $mydirname = basename($mydirpath);
    // common libs (eg. altsys)
    $lib = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , $_GET['lib'] ) ;
    $page = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_GET['page'] ) ;
    
    if( file_exists( XOOPS_TRUST_PATH.'/libs/'.$lib.'/'.$page.'.php' ) ) {
	include XOOPS_TRUST_PATH.'/libs/'.$lib.'/'.$page.'.php' ;
	} else if( file_exists( XOOPS_TRUST_PATH.'/libs/'.$lib.'/index.php' ) ) {
	include XOOPS_TRUST_PATH.'/libs/'.$lib.'/index.php' ;
    } else {
	die( 'wrong request' ) ;
    }
    exit;
}

xoops_cp_header();
include "mymenu.php";

switch ($op) {
 case 'edit':
     track_edit($start, $page);
     break;
 case 'check':
     track_check($start, $page);
     break;
 case 'disable':
     track_disalbe();
     break;
 case 'expire':
     if (isset($_POST['commit'])) commit_expire();
     else track_expire();
     break;
 default:
     track_list($start, $page);
     break;
}

xoops_cp_footer();
exit;

function track_list($start, $page) {
    global $xoopsDB, $tbl, $tbr, $xoopsModuleConfig;
    global $tags, $tblstyle;
    echo "<h4>"._AM_TRACKBACK_LIST."</h4>";
    $mode = isset($_GET['m'])?$_GET['m']:"";
    $opt="";
    if ($mode == 'all') {
	$cond = '';
	$opt="&m=all";
    } elseif ($mode == 'dis') {
	$cond = "disable=1 AND";
	$opt="&m=dis";
    }
    else $cond = "disable=0 AND";
    echo "<form action='index.php' method='get'>".
	_AM_ENTRY_VIEW." ".myselect("m", array(''=>_AM_ENTRY_ENABLE,'dis'=>_AM_ENTRY_DISABLE, 'all'=>_AM_ENTRY_ALL), $mode).
	" <input type='submit' value='"._SUBMIT."' />\n</form>";

    $result = $xoopsDB->query("SELECT track_id FROM $tbl,$tbr WHERE $cond track_id=track_from GROUP BY track_id");
    $nrec = $xoopsDB->getRowsNum($result);
    $result = $xoopsDB->query("SELECT track_id, track_uri,count(ref_id),sum(linked), disable FROM $tbl,$tbr WHERE $cond track_id=track_from GROUP BY track_id ORDER BY track_uri", $xoopsModuleConfig['list_max'], $start);
    if ($nrec) {
	$pctrl = make_page_index(_AM_PAGE, $nrec, $page, " <a href='index.php?op=list&page=%d$opt'>(%d)</a>");
	echo $pctrl;
	echo "<form action='index.php' method='post'>";
	echo "<table $tblstyle>\n";
	echo "<tr class='bg1'><th>"._AM_DISABLE."</th><th>"._AM_TRACKBACK_PAGE."</th><th>"._AM_REF_SHOWS."</th><th>"._AM_REF_LINKS."</th></tr>\n";
	$nc = 1;
	
	while (list($tid, $uri, $count, $nlink, $disable) = $xoopsDB->fetchRow($result)) {
	    $bg = $disable?'dis':$tags[($nc++ % 2)];
	    echo "<tr class='$bg'>".
		"<td style='text-align: center'><input type='checkbox' name='disable[$tid]' ".($disable?"checked":"")." /><input type='hidden' name='trid[$tid]' value='1' /></td>".
		"<td><a href='index.php?op=edit&tid=$tid'>".uri_to_name($uri)."</a></td>".
		"<td style='text-align: center'>$nlink</th><td style='text-align: center'>$count</td>".
		"</tr>\n";
	}
	echo "</table>\n";
	echo "<input type='hidden' name='op' value='disable' />".
	    "<input type='hidden' name='m' value='$mode' />".
	    "<input type='submit' value='"._AM_SUBMIT_DISABLE."' />".
	    "</form>\n";
	echo $pctrl;
    } else {
	echo _AM_TRACK_NODATA;
    }
}

function track_edit($start, $page) {
    global $xoopsDB, $tbl, $tbr, $xoopsModuleConfig;
    global $tags, $tblstyle;
    $tid = $_GET['tid'];
    $result = $xoopsDB->query("SELECT track_uri, disable FROM $tbl WHERE track_id=$tid");
    list($uri, $disable)=$xoopsDB->fetchRow($result);
    echo "<h4 style='text-align:left;'>"._AM_TRACKBACK_PAGE."</h4>";

    $result = $xoopsDB->query("SELECT count(ref_id) FROM $tbr WHERE track_from=$tid");
    list($nrec) = $xoopsDB->fetchRow($result);
    $result = $xoopsDB->query("SELECT * FROM $tbr WHERE track_from=$tid ORDER BY linked DESC, ref_url", $xoopsModuleConfig['list_max'], $start);
    echo "<p>"._AM_TRACK_TARGET.": <a href='index.php'>"._AM_TRACK_LIST."</a> &gt;&gt; <a href='$uri'>".uri_to_name($uri)."</a>".
	($disable?" - "._AM_DISABLE_MODE:"")."</p>";
    $pctrl = make_page_index(_AM_PAGE, $nrec, $page, " <a href='index.php?op=edit&tid=$tid&page=%d$opt'>(%d)</a>");
    echo $pctrl;
    if ($nrec) {
	echo "<form action='index.php' method='post'>";
	echo "<table $tblstyle>\n";
	echo "<tr class='bg1'><th>"._AM_REF_URL."</th></tr>\n";
	$nc = 1;
	while ($data = $xoopsDB->fetchArray($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $rid = $data['ref_id'];
	    $mkl = $data['linked']?"checked":"";
	    $start++;
	    $fbox = ($data['mtime']>10)?" "._AM_FLUSH_INFO.":<input type='checkbox' name='flush[$rid]' />":"";
	    echo "<tr class='$bg'><td>".
		"<input type='checkbox' name='link[$rid]' $mkl />".
		"<input type='hidden' name='refid[$rid]' value='ok' />".
		"$start. ".($data['checked']?"":" ("._AM_UNCHECK.")").
		make_track_item($data, $fbox).
		"</td>".
		"</tr>\n";
	}
	echo "</table>\n";
	echo "<input type='hidden' name='op' value='edit_update' />".
	    "<input type='hidden' name='tid' value='$tid' />".
	    "<input type='submit' value='"._AM_SUBMIT_LINK."' />".
	    "</form>\n";
    }
    echo $pctrl;

}

function edit_update() {
    global $xoopsDB, $tbl, $tbr;
    $tid = $_POST['tid'];
    $sets = "";
    $resets = "";
    $flushs = "";
    $link = isset($_POST['link'])?$_POST['link']:array();
    $flush = isset($_POST['flush'])?$_POST['flush']:array();
    foreach ($_POST['refid'] as $i => $v) {
	if (isset($link[$i])) {
	    $sets .= ($sets==""?"":" OR ")."ref_id=$i";
	} else {
	    $resets .= ($resets==""?"":" OR ")."ref_id=$i";
	}
	if (isset($flush[$i])) {
	    $flushs = ($resets==""?"":" OR ")."ref_id=$i";
	}
    }
    if ($resets != "") $xoopsDB->query("UPDATE $tbr SET linked=0 WHERE ($resets) AND track_from=$tid");
    if ($sets != "") $xoopsDB->query("UPDATE $tbr SET linked=1 WHERE ($sets) AND track_from=$tid");
    if ($flushs != "") $xoopsDB->query("UPDATE $tbr SET mtime=1 WHERE ($sets) AND track_from=$tid");
    redirect_header("index.php?op=edit&tid=$tid",1,_AM_DBUPDATED);
    exit();
}

function track_check() {
    global $xoopsDB, $tbl, $tbr;
    global $tags, $tblstyle;
    echo "<h4'>"._AM_TRACKBACK_CHECK."</h4>";
    $result = $xoopsDB->query("SELECT * FROM $tbl,$tbr WHERE track_from=track_id AND checked=0 ORDER BY ref_id");
    $n = $xoopsDB->getRowsNum($result);
    if ($n) {
	echo "<script>
<!--
function myCheckAll(formname, switchid, group) {
	var ele = document.forms[formname].elements;
	var switch_cbox = xoopsGetElementById(switchid);
	for (var i = 0; i < ele.length; i++) {
		var e = ele[i];
		if ( (e.name != switch_cbox.name) && (e.id==group) && (e.type == 'checkbox') ) {
			e.checked = switch_cbox.checked;
		}
	}
}
-->
</script>";
	$allbox = "<input name='allbox' id='allbox' onclick='myCheckAll(\"refchk\", \"allbox\", \"check\");' type='checkbox' value='Check All' />";
	echo "<form action='index.php' method='post' name='refchk'>";
	echo $allbox." "._AM_CHECKALL_CHECK;
	echo "<table $tblstyle>\n";
	echo "<tr class='bg1'><th nowrap>"._AM_REF_CHECKED."</th><th>"._AM_REF_URL."</th></tr>\n";
	$nc = 1;
	while ($data = $xoopsDB->fetchArray($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $tid = $data['track_id'];
	    $uri = $data['track_uri'];
	    $rid = $data['ref_id'];
	    $url = $data['ref_url'];
	    $title = $data['title'];
	    $uri = $data['track_uri'];
	    $linkto = " "._TB_LINKTO." <a href='$uri'>".uri_to_name($uri)."</a>";
	    $clickmark = "target='_blank' onclick='javascript:document.forms[\"refchk\"].elements[\"check[$rid]\"].checked=true;'";
	    $mkl = $data['linked']?"checked":"";
	    $start++;
	    echo "<tr class='$bg'><td style='text-align:center;'><input type='checkbox' name='check[$rid]' id='check' /></td>".
		"<td>$start. ".
		"<input type='checkbox' name='link[$rid]' $mkl />".
		"<input type='hidden' name='refid[$rid]' value='ok' /> ".
		make_track_item($data, $linkto, $clickmark)."</td></tr>\n";
	}
	echo "</table>\n";
	echo "<p><input type='hidden' name='op' value='check_update' />".
	    "<input type='submit' value='"._SUBMIT."' /></p>".
	    "</form>\n";
    } else {
	echo _AM_NO_UNCHECKED;
    }
}

function check_update() {
    global $xoopsDB, $tbl, $tbr;
    if (isset($_POST['link'])) {
	$cond = "";
	foreach ($_POST['link'] as $i => $v) {
	    if ($cond=="") $cond = "ref_id=$i";
	    else $cond .= " OR ref_id=$i";
	}
	$xoopsDB->query("UPDATE $tbr SET linked=0 WHERE checked=0");
	$xoopsDB->query("UPDATE $tbr SET linked=1 WHERE $cond");
    }
    if (isset($_POST['check'])) {
	$cond = "";
	foreach ($_POST['check'] as $i => $v) {
	    if ($cond=="") $cond = "ref_id=$i";
	    else $cond .= " OR ref_id=$i";
	}
	$xoopsDB->query("UPDATE $tbr SET checked=1 WHERE ($cond)");
    }
    redirect_header("index.php?op=check",1,_AM_DBUPDATED);
    exit();
}

function track_disalbe() {
    global $xoopsDB, $tbl;

    $disable = isset($_POST['disable'])?$_POST['disable']:array();
    $resets = "";
    $sets = "";
    foreach ($_POST['trid'] as $tid => $v) {
	if (isset($disable[$tid])) {
	    $sets .= ($sets==""?"":" OR ")."track_id=$tid";
	} else {
	    $resets .= ($resets==""?"":" OR ")."track_id=$tid";
	}
    }
    if ($resets!="") $xoopsDB->query("UPDATE $tbl SET disable=0 WHERE $resets");
    if ($sets!="") $xoopsDB->query("UPDATE $tbl SET disable=1 WHERE $sets");
    
    redirect_header("index.php".(empty($_POST['m'])?"":"?m=".$_POST['m']),1,_AM_DBUPDATED);
    exit();
}

function expire_priod($days) {
    return time()-$days*24*3600;
}

function track_expire() {
    global $xoopsDB, $tbl, $tbr, $xoopsModuleConfig;

    echo "<h4>"._AM_TRACK_EXPIRED."</h4>";
    $days = isset($_POST['days'])
	?intval($_POST['days']):$xoopsModuleConfig['expire'];
    $threshold = isset($_POST['threshold'])
	?intval($_POST['threshold']):$xoopsModuleConfig['threshold'];
    $linked = isset($_POST['linked'])?intval($_POST['linked']):0;
    $lcond = ($linked)?" OR linked=1":"";
    $past = expire_priod($days);
    $res = $xoopsDB->query("SELECT count(*) FROM $tbr WHERE mtime<$past AND nref<=$threshold$lcond");
    list($nrec) = $xoopsDB->fetchRow($res);
    echo "<p>".sprintf(_AM_EXPIRE_COND1, $days).
	sprintf(_AM_EXPIRE_COND2, $threshold)."</p>";
    echo "<p>".sprintf(_AM_EXPIRE_RECORDS, $nrec)."</p>";
    echo "<form action='index.php' method='post'>
<input type='hidden' name='op' value='expire' />
<table>
<tr><td>"._AM_EXPIRE_DAYS."</td><td><input type='text' size='4' name='days' value='$days' /></td></tr>
<tr><td>"._AM_EXPIRE_REFS."</td><td><input type='text' size='4' name='threshold' value='$threshold' /></td></tr>
<tr><td>"._AM_EXPIRE_LINK."</td><td>".
	myradio("linked", array(1=>_AM_DO, 0=>_AM_DONT), $linked)."</td></tr>
<tr><td colspan='2' align='center'><input type='submit' name='commit' value='"._DELETE."' />
<input type='submit' name='confirm' value='"._AM_CONFIRM."' /></td></tr>
</table>
</form>";
}

function commit_expire() {
    global $xoopsDB, $tbr, $xoopsModuleConfig;
    $past = expire_priod(intval($_POST['days']));
    $lcond = intval($_POST['linked'])?" OR linked=0":"";
    $res = $xoopsDB->query("DELETE FROM $tbr WHERE mtime<$past AND nref<=".intval($_POST['threshold']).$lcond);
    $res = $xoopsDB->query("DELETE track_id, track_uri FROM $tbl WHERE track_id NOT IN (SELECT track_from FROM $tbr GROUP BY track_from)");
    redirect_header("index.php",1,_AM_DBUPDATED);
    exit;
 }

function show_menu() {
    global $xoopsModule, $xoopsDB, $tbr, $adminmenu;

    include_once("menu.php");
    $base = XOOPS_URL."/modules/".$xoopsModule->dirname();
    foreach ($adminmenu as $v) {
	$title = $v['title'];
	$link = $v['link'];
	echo "<p> - <b><a href='$base/$link'>$title</a></b></p>\n";
    }

    $result = $xoopsDB->query("SELECT count(ref_id) FROM $tbr WHERE checked=0");
    list($ck) = $xoopsDB->fetchRow($result);
    if ($ck) {
	echo "<p><a href='index.php?op=check'>".sprintf(_AM_UNCHECKED,$ck)."</a></p>";
    }

}

function myradio($name, $value, $def) {
    $body = "";
    foreach ($value as $i => $v) {
	$ck = ($i == $def)?" checked":"";
	$body .= "<input type='radio' name='$name' value='$i' $ck />&nbsp;$v&nbsp;";
    }
    return $body;
}

function myselect($name, $value, $def) {
    $body = "<select name='$name'>\n";
    foreach ($value as $i => $v) {
	$ck = ($i == $def)?" selected":"";
	$body .= "<option value='$i'$ck>$v</option>\n";
    }
    return $body."</select>";
}
?>

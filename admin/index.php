<?php
// trackback module for XOOPS (admin side code)
// $Id: index.php,v 1.11 2008/02/05 07:50:35 nobu Exp $
include("admin_header.php");
include_once("../functions.php");

$dir = $xoopsModule->dirname();

$op = "";
if ( isset($HTTP_GET_VARS['op']) ) $op = $HTTP_GET_VARS['op'];
if ( isset($HTTP_POST_VARS['op']) ) $op = $HTTP_POST_VARS['op'];
$page = isset($HTTP_GET_VARS['page'])?$HTTP_GET_VARS['page']:1;
$start = ($page>1)?($page-1)*$trackConfig['list_max']:0;

$myts =& MyTextSanitizer::getInstance();
$tbl = $xoopsDB->prefix("trackback");
$tbr = $xoopsDB->prefix("trackback_ref");
$tblstyle="border='0' cellspacing='1' cellpadding='3' class='bg2' width='100%'";
switch ($op) {
 case 'config_update':
     config_update();
     break;
 case 'edit':
     track_edit($start, $page);
     break;
 case 'edit_update':
     edit_update();
     break;
 case 'check':
     track_check($start, $page);
     break;
 case 'check_update':
     check_update();
     break;
 case 'disable':
     track_disalbe();
     break;
 case 'config':
     break;
 case 'expire':
     if (isset($HTTP_POST_VARS['commit'])) commit_expire();
     else track_expire();
     break;
 default:
     xoops_cp_header();
     if ($op == "") show_menu();
     track_list($start, $page);
     xoops_cp_footer();
     break;
}

function track_list($start, $page) {
    global $xoopsDB, $HTTP_GET_VARS, $tbl, $tbr, $trackConfig;
    global $tags, $tblstyle;
    OpenTable();
    echo "<h4>"._AM_TRACKBACK_LIST."</h4>";
    $mode = isset($HTTP_GET_VARS['m'])?$HTTP_GET_VARS['m']:"";
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
    $result = $xoopsDB->query("SELECT track_id, track_uri,count(ref_id),sum(linked), disable FROM $tbl,$tbr WHERE $cond track_id=track_from GROUP BY track_id ORDER BY track_uri", $trackConfig['list_max'], $start);
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
    CloseTable();
}

function track_edit($start, $page) {
    global $xoopsDB, $HTTP_GET_VARS, $tbl, $tbr, $trackConfig;
    global $tags, $tblstyle;
    $tid = $HTTP_GET_VARS['tid'];
    $result = $xoopsDB->query("SELECT track_uri, disable FROM $tbl WHERE track_id=$tid");
    list($uri, $disable)=$xoopsDB->fetchRow($result);
    xoops_cp_header();
    OpenTable();
    echo "<h4 style='text-align:left;'>"._AM_TRACKBACK_PAGE."</h4>";

    $result = $xoopsDB->query("SELECT count(ref_id) FROM $tbr WHERE track_from=$tid");
    list($nrec) = $xoopsDB->fetchRow($result);
    $result = $xoopsDB->query("SELECT * FROM $tbr WHERE track_from=$tid ORDER BY linked DESC, ref_url", $trackConfig['list_max'], $start);
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

    CloseTable();
    xoops_cp_footer();
}

function edit_update() {
    global $xoopsDB, $HTTP_POST_VARS, $tbl, $tbr;
    $tid = $HTTP_POST_VARS['tid'];
    $sets = "";
    $resets = "";
    $flushs = "";
    $link = isset($HTTP_POST_VARS['link'])?$HTTP_POST_VARS['link']:array();
    $flush = isset($HTTP_POST_VARS['flush'])?$HTTP_POST_VARS['flush']:array();
    foreach ($HTTP_POST_VARS['refid'] as $i => $v) {
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
    xoops_cp_header();
    OpenTable();
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
    CloseTable();
    xoops_cp_footer();
}

function check_update() {
    global $xoopsDB, $HTTP_POST_VARS, $tbl, $tbr;
    if (isset($HTTP_POST_VARS['link'])) {
	$cond = "";
	foreach ($HTTP_POST_VARS['link'] as $i => $v) {
	    if ($cond=="") $cond = "ref_id=$i";
	    else $cond .= " OR ref_id=$i";
	}
	$xoopsDB->query("UPDATE $tbr SET linked=0 WHERE checked=0");
	$xoopsDB->query("UPDATE $tbr SET linked=1 WHERE $cond");
    }
    if (isset($HTTP_POST_VARS['check'])) {
	$cond = "";
	foreach ($HTTP_POST_VARS['check'] as $i => $v) {
	    if ($cond=="") $cond = "ref_id=$i";
	    else $cond .= " OR ref_id=$i";
	}
	$xoopsDB->query("UPDATE $tbr SET checked=1 WHERE ($cond)");
    }
    redirect_header("index.php?op=check",1,_AM_DBUPDATED);
    exit();
}

function track_disalbe() {
    global $xoopsDB, $HTTP_POST_VARS, $tbl;

    $disable = isset($HTTP_POST_VARS['disable'])?$HTTP_POST_VARS['disable']:array();
    $resets = "";
    $sets = "";
    foreach ($HTTP_POST_VARS['trid'] as $tid => $v) {
	if (isset($disable[$tid])) {
	    $sets .= ($sets==""?"":" OR ")."track_id=$tid";
	} else {
	    $resets .= ($resets==""?"":" OR ")."track_id=$tid";
	}
    }
    if ($resets!="") $xoopsDB->query("UPDATE $tbl SET disable=0 WHERE $resets");
    if ($sets!="") $xoopsDB->query("UPDATE $tbl SET disable=1 WHERE $sets");
    
    redirect_header("index.php".(empty($HTTP_POST_VARS['m'])?"":"?m=".$HTTP_POST_VARS['m']),1,_AM_DBUPDATED);
    exit();
}

if ( $op == "config" ) {
    xoops_cp_header();
    OpenTable();
    echo "<h4>" ._AM_TRACKBACK_CONFIG. "</h4>\n";

    $cfg = "$modbase/cache/config.php";
    if (!function_exists("getCache") && !is_writable($cfg)) {
	echo "<p style='color: red;'>".sprintf(_MUSTWABLE,$cfg)."</p>\n";
    }

    echo "<form action='index.php' method='post'>\n".
	"<table>\n<tr><td class='nw'>"._AM_TRACK_AUTOCHECK."</td><td>".
	myradio("autocheck", array(1=>_AM_DO, 0=>_AM_DONT), $trackConfig['auto_check'])."</td></tr>\n".
	"<tr><td class='nw'>"._AM_TRACK_BLOCKHIDE."</td><td>".
	myradio("blockhide", array(0=>_AM_DO, 1=>_AM_DONT), $trackConfig['block_hide'])."</td></tr>\n".
	"<tr><td class='nw'>"._AM_TRACK_LIST_MAX."</td><td>".
	"<input size='4' name='listmax' value='".$trackConfig['list_max']."' /></td></tr>\n".
	"<tr><td class='nw'>"._AM_TRACK_TITLELEN."</td><td>".
	"<input size='4' name='titlelen' value='".$trackConfig['title_len']."' /></td></tr>\n".
	"<tr><td class='nw'>"._AM_TRACK_CTEXTLEN."</td><td>".
	"<input size='4' name='ctextlen' value='".$trackConfig['ctext_len']."' /></td></tr>\n".
	"<tr><td class='nw'>"._AM_TRACK_EXPIREDAY."</td><td>".
	"<input size='4' name='expireday' value='".$trackConfig['expire']."' /></td></tr>\n".
	"<tr><td class='nw'>"._AM_TRACK_THRESHOLD."</td><td>".
	"<input size='4' name='threshold' value='".$trackConfig['threshold']."' /></td></tr>\n".
	"</table>\n";

    echo "<p><b>"._AM_TRACK_EXCLUDE."</b></p>\n".
	"<textarea name='exclude' rows='5' cols='60'>".htmlspecialchars($trackConfig['exclude'])."</textarea>\n".
	"<p><b>"._AM_TRACK_INCLUDE."</b></p>".
	"<textarea name='include' rows='5' cols='60'>".htmlspecialchars($trackConfig['include'])."</textarea>\n".
	"<input type='hidden' name='op' value='config_update' />\n".
	"<p><input type='submit' value='"._SUBMIT."' /></p>\n".
	"</form>\n";
    CloseTable();
    xoops_cp_footer();
}

function config_update() {
    global $HTTP_POST_VARS, $xoopsModule;
    $config="global \$trackConfig;\n".
	"\$trackConfig['exclude']=\"".crlf2nl($HTTP_POST_VARS['exclude'])."\";\n".
	"\$trackConfig['include']=\"".crlf2nl($HTTP_POST_VARS['include'])."\";\n".
	"\$trackConfig['auto_check']=".intval($HTTP_POST_VARS['autocheck']).";\n".
	"\$trackConfig['block_hide']=".intval($HTTP_POST_VARS['blockhide']).";\n".
	"\$trackConfig['list_max']=".intval($HTTP_POST_VARS['listmax']).";\n".
	"\$trackConfig['title_len']=".intval($HTTP_POST_VARS['titlelen']).";\n".
	"\$trackConfig['ctext_len']=".intval($HTTP_POST_VARS['ctextlen']).";\n".
	"\$trackConfig['expire']=".intval($HTTP_POST_VARS['expireday']).";\n".
	"\$trackConfig['threshold']=".intval($HTTP_POST_VARS['threshold']).";";
    putCache($xoopsModule->dirname()."/config.php", $config);
    redirect_header("index.php?op=config",1,_AM_DBUPDATED);
    exit();
}

function expire_priod($days) {
    return time()-$days*24*3600;
}

function track_expire() {
    global $xoopsDB, $tbl, $tbr, $trackConfig, $HTTP_POST_VARS;

    xoops_cp_header();

    OpenTable();
    echo "<h4>"._AM_TRACK_EXPIRED."</h4>";
    $days = isset($HTTP_POST_VARS['days'])
	?intval($HTTP_POST_VARS['days']):$trackConfig['expire'];
    $threshold = isset($HTTP_POST_VARS['threshold'])
	?intval($HTTP_POST_VARS['threshold']):$trackConfig['threshold'];
    $linked = isset($HTTP_POST_VARS['linked'])?intval($HTTP_POST_VARS['linked']):0;
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
    CloseTable();
    xoops_cp_footer();
}

function commit_expire() {
    global $xoopsDB, $tbr, $trackConfig, $HTTP_POST_VARS;
    $past = expire_priod(intval($HTTP_POST_VARS['days']));
    $lcond = intval($HTTP_POST_VARS['linked'])?" OR linked=0":"";
    $res = $xoopsDB->query("DELETE FROM $tbr WHERE mtime<$past AND nref<=".intval($HTTP_POST_VARS['threshold']).$lcond);
    $res = $xoopsDB->query("DELETE track_id, track_uri FROM $tbl WHERE track_id NOT IN (SELECT track_from FROM $tbr GROUP BY track_from)");
    redirect_header("index.php",1,_AM_DBUPDATED);
    exit;
 }

function show_menu() {
    global $xoopsModule, $xoopsDB, $tbr;

    OpenTable();
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

    CloseTable();
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
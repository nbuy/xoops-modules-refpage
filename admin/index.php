<?php
include("admin_header.php");
//include_once(XOOPS_ROOT_PATH."/class/xoopsformloader.php");
//include_once(XOOPS_ROOT_PATH."/class/xoopscomments.php");
//include_once(XOOPS_ROOT_PATH."/class/xoopslists.php");
include_once("../functions.php");
$dir = $xoopsModule->dirname();
$basedir = XOOPS_ROOT_PATH."/modules/$dir";
$base = XOOPS_URL."/modules/$dir";

$op = "list";
if ( isset($HTTP_GET_VARS['op']) ) $op = $HTTP_GET_VARS['op'];
if ( isset($HTTP_POST_VARS['op']) ) $op = $HTTP_POST_VARS['op'];

$myts =& MyTextSanitizer::getInstance();
$tbl = $xoopsDB->prefix("trackback");
$tbr = $xoopsDB->prefix("trackback_ref");

if ( $op == "list" ) {
    xoops_cp_header();
    OpenTable();
    echo "<h4 style='text-align:left;'>"._AM_TRACKBACK_LIST."</h4>";
    $result = $xoopsDB->query("SELECT count(ref_id) FROM $tbr WHERE checked=0");
    list($ck) = $xoopsDB->fetchRow($result);
    if ($ck) {
	echo "<p><a href='index.php?op=check'>".sprintf(_AM_UNCHECKED,$ck)."</a></p>";
    }
    if ( isset($HTTP_GET_VARS['op']) ) $op = $HTTP_GET_VARS['op'];
    $mode = isset($HTTP_GET_VARS['m'])?$HTTP_GET_VARS['m']:"";
    if ($mode == 'all') $cond = '';
    elseif ($mode == 'dis') $cond = "disable=1 AND";
    else $cond = "disable=0 AND";
    echo "<form action='index.php' method='get'>".
	_AM_ENTRY_VIEW." ".myselect("m", array(''=>_AM_ENTRY_ENABLE,'dis'=>_AM_ENTRY_DISABLE, 'all'=>_AM_ENTRY_ALL), $mode).
	" <input type='submit' value='"._SUBMIT."' />\n</form>";

    $result = $xoopsDB->query("SELECT track_id, track_uri,count(ref_id), disable FROM $tbl,$tbr WHERE $cond track_id=track_from GROUP BY track_id ORDER BY track_id");
    $n = $xoopsDB->getRowsNum($result);
    if ($n) {
	echo "<form action='index.php' method='post'>";
	echo "<table class='bg2' cellspacing='1' border='0'>\n";
	echo "<tr class='bg1'><th>"._AM_DISABLE."</th><th>"._AM_TRACKBACK_PAGE."</th><th>"._AM_REF_LINKS."</th></tr>\n";
	$nc = 1;
	$sw = array(0=>_AM_ENTRY_ENABLE, 1=>_AM_ENTRY_DISABLE);
	
	while (list($tid, $uri, $count, $disable) = $xoopsDB->fetchRow($result)) {
	    $bg = $disable?'dis':$tags[($nc++ % 2)];
	    echo "<tr class='$bg'>".
		"<td style='text-align: center'>".myselect("disable[$tid]", $sw, $disable)."</td>".
		"<td><a href='index.php?op=edit&tid=$tid'>".uri_to_name($uri)."</a></td>".
		"<td style='text-align: center'>$count</td>".
		"</tr>\n";
	}
	echo "</table>\n";
	echo "<input type='hidden' name='op' value='disable' />".
	    "<input type='submit' value='"._AM_SUBMIT_DISABLE."' />".
	    "</form>\n";
    } else {
	echo _AM_TRACK_NODATA;
    }
    CloseTable();
    xoops_cp_footer();
    exit();
}

if ( $op == "edit" ) {
    $tid = $HTTP_GET_VARS['tid'];
    $result = $xoopsDB->query("SELECT track_uri, disable FROM $tbl WHERE track_id=$tid");
    list($uri, $disable)=$xoopsDB->fetchRow($result);
    xoops_cp_header();
    OpenTable();
    echo "<h4 style='text-align:left;'>"._AM_TRACKBACK_PAGE."</h4>";

    $result = $xoopsDB->query("SELECT * FROM $tbr WHERE track_from=$tid ORDER BY ref_id");
    $n = $xoopsDB->getRowsNum($result);
    echo "<p>"._AM_TRACK_TARGET.": <a href='$uri'>".uri_to_name($uri)."</a>".
	($disable?" - "._AM_DISABLE_MODE:"")."</p>";
    if ($n) {
	echo "<form action='index.php' method='post'>";
	echo "<table class='bg2' cellspacing='1' border='0'>\n";
	echo "<tr class='bg1'><th>"._AM_TRACK_SHOW."</th><th>"._AM_REF_URL."</th><th>"._AM_REF_COUNT."</th><th>"._AM_REF_CHECKED."</th></tr>\n";
	$nc = 1;
	while ($data = $xoopsDB->fetchArray($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $url = $data['ref_url'];
	    $rid = $data['ref_id'];
	    $mkl = $data['linked']?"checked":"";
	    $mkc = $data['checked'];
	    $title = $data['title'];
	    if ($title == '') $title = strim($url);
	    echo "<tr class='$bg'>".
		"<td style='text-align: center'><input type='checkbox' name='link[$rid]' $mkl /></td>".
		"<td><a href='$url'>$title</a><div style='font-size: xx-small; text-align: left;'>".strim($url, 80)."</div></td>".
		"<td style='text-align: center'>".$data['nref']."</td>".
		"<td style='text-align: center'>".($mkc?_AM_CHECK:_AM_UNCHECK)."</a></td>".
		"</tr>\n";
	}
	echo "</table>\n";
	echo "<input type='hidden' name='op' value='edit_update' />".
	    "<input type='hidden' name='tid' value='$tid' />".
	    "<input type='submit' value='"._SUBMIT."' />".
	    "</form>\n";
    }
    CloseTable();
    xoops_cp_footer();
    exit();
}

if ( $op == "edit_update" ) {
    $tid = $HTTP_POST_VARS['tid'];
    if (isset($HTTP_POST_VARS['link'])) {
	$cond = "";
	foreach ($HTTP_POST_VARS['link'] as $i => $v) {
	    if ($cond=="") $cond = "ref_id=$i";
	    else $cond .= " OR ref_id=$i";
	}
	$xoopsDB->query("UPDATE $tbr SET linked=0 WHERE track_from=$tid");
	$xoopsDB->query("UPDATE $tbr SET linked=1 WHERE ($cond) AND track_from=$tid");
    }
    redirect_header("index.php?op=edit&tid=$tid",1,_AM_DBUPDATED);
    exit();
}

if ( $op == "check" ) {
    xoops_cp_header();
    OpenTable();
    echo "<h4 style='text-align:left;'>"._AM_TRACKBACK_CHECK."</h4>";
    $result = $xoopsDB->query("SELECT track_id,track_uri, ref_id,ref_url,title, context, nref, linked FROM $tbl,$tbr WHERE track_from=track_id AND checked=0 ORDER BY ref_id");
    $n = $xoopsDB->getRowsNum($result);
    if ($n) {
	echo "<form action='index.php' method='post' name='refchk'>";
	echo "<table class='bg2' cellspacing='1' cellpadding='2' border='0'>\n";
	echo "<tr class='bg1'><th>"._AM_TRACK_TARGET."</th><th>"._AM_REF_CHECKED."</th><th>"._AM_REF_URL."</th><th>"._AM_REF_COUNT."</th><th>"._AM_TRACK_SHOW."</th></tr>\n";
	$nc = 1;
	while ($data = $xoopsDB->fetchArray($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $tid = $data['track_id'];
	    $uri = $data['track_uri'];
	    $rid = $data['ref_id'];
	    $url = $data['ref_url'];
	    $title = $data['title'];
	    $mkl = $data['linked']?"checked":"";
	    if ($title == '') $title = strim($url);
	    echo "<tr class='$bg'>".
		"<td><a href='$uri'>".uri_to_name($uri)."</a></td>".
		"<td style='text-align: center'><input type='checkbox' name='check[$rid]' /></td>".
		"<td><a href='$url' target='_blank' onclick='javascript:document.forms[\"refchk\"].elements[\"check[$rid]\"].checked=true;'>$title</a><div style='font-size: xx-small; text-align: left;'>".strim($url, 80)."</div></td>".
		"<td style='text-align: center'>".$data['nref']."</td>".
		"<td style='text-align: center'><input type='checkbox' name='link[$rid]' $mkl /></td>".
		"</tr>\n";
	}
	echo "</table>\n";
	echo "<input type='hidden' name='op' value='check_update' />".
	    "<input type='submit' value='"._SUBMIT."' />".
	    "</form>\n";
    } else {
	echo _AM_NO_UNCHECKED;
    }
    CloseTable();
    xoops_cp_footer();
    exit();
}

if ( $op == "check_update" ) {
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

if ( $op == "disable" ) {
    if (isset($HTTP_POST_VARS['disable'])) {
	foreach ($HTTP_POST_VARS['disable'] as $tid => $v) {
	    $xoopsDB->query("UPDATE $tbl SET disable=$v WHERE track_id=$tid");
	}
	redirect_header("index.php",1,_AM_DBUPDATED);
    } else {
	redirect_header("index.php",1,_AM_NODISABLE);
    }
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
	"<tr><td class='nw'>"._AM_TRACK_TITLELEN."</td><td>".
	"<input size='4' name='titlelen' value='".$trackConfig['title_len']."' /></td></tr>\n".
	"<tr><td class='nw'>"._AM_TRACK_CTEXTLEN."</td><td>".
	"<input size='4' name='ctextlen' value='".$trackConfig['ctext_len']."' /></td></tr>\n".
	"<tr><td class='nw'>"._AM_TRACK_EXPIREDAY."</td><td>".
	"<input size='4' name='expireday' value='".$trackConfig['expire']."' /></td></tr>\n".
	"</table>\n";

    echo "<p><b>"._AM_TRACK_EXCLUDE."</b></p>\n".
	"<textarea name='exclude' rows='5' cols='60'>".htmlspecialchars($trackConfig['exclude'])."</textarea>\n".
	"<p><b>"._AM_TRACK_INCLUDE."</b></p>".
	"<textarea name='include' rows='5' cols='60'>".htmlspecialchars($trackConfig['include'])."</textarea>\n".
	"<p><b>"._AM_TRACK_STRIP."</b></p>".
	"<textarea name='stripargs' rows='5' cols='60'>".htmlspecialchars($trackConfig['strip_args'])."</textarea>\n".
	"<input type='hidden' name='op' value='config_update' />\n".
	"<p><input type='submit' value='"._SUBMIT."' /></p>\n".
	"</form>\n";
    CloseTable();
    xoops_cp_footer();
    exit();
}

if ( $op == "config_update" ) {
    //header('Content-Type: text/plain; Charset=EUC-JP');
    //phpinfo(INFO_VARIABLES);
    $config="\$trackConfig['exclude']=\"".$HTTP_POST_VARS['exclude']."\";\n".
	"\$trackConfig['include']=\"".$HTTP_POST_VARS['include']."\";\n".
	"\$trackConfig['strip_args']=\"".$HTTP_POST_VARS['stripargs']."\";\n".
	"\$trackConfig['auto_check']=".$HTTP_POST_VARS['autocheck'].";\n".
	"\$trackConfig['block_hide']=".$HTTP_POST_VARS['blockhide'].";\n".
	"\$trackConfig['title_len']=".$HTTP_POST_VARS['titlelen'].";\n".
	"\$trackConfig['ctext_len']=".$HTTP_POST_VARS['ctextlen'].";\n".
	"\$trackConfig['expire']=".$HTTP_POST_VARS['expireday'].";\n";
    putCache($xoopsModule->dirname()."/config.php", $config);
    redirect_header("index.php?op=config",1,_AM_DBUPDATED);
    exit();
}

if ( $op == "delete" ) {
	xoops_cp_header();
	$poll = new XoopsPoll($HTTP_GET_VARS['poll_id']);
	OpenTable();
	echo "<h4 style='text-align:left;'>".sprintf(_AM_RUSUREDEL,$poll->getVar("question"))."</h4>\n";
	echo "<table><tr><td>\n";
	echo myTextForm("index.php?op=delete_ok&poll_id=".$poll->getVar("poll_id")."", _YES);
	echo "</td><td>\n";
	echo myTextForm("index.php?op=list", _NO);
	echo "</td></tr></table>\n";
	CloseTable();
	xoops_cp_footer();
	exit();
}

if ( $op == "delete_ok" ) {
	$poll = new XoopsPoll($HTTP_GET_VARS['poll_id']);
	if ( $poll->delete() != false ) {
		XoopsPollOption::deleteByPollId($poll->getVar("poll_id"));
		XoopsPollLog::deleteByPollId($poll->getVar("poll_id"));
		poll_update_cache();
		// delete comments for this poll
		$com = new XoopsComments($xoopsDB->prefix("pollexcomments"));
		$criteria = array("item_id=".$poll->getVar("poll_id")."", "pid=0");
		$commentsarray = $com->getAllComments($criteria);
		foreach($commentsarray as $comment){
			$comment->delete();
		}
	}
	redirect_header("index.php",1,_AM_DBUPDATED);
	exit();
}

if ( $op == "restart" ) {
	$poll = new XoopsPoll($HTTP_GET_VARS['poll_id']);
	$poll_form = new XoopsThemeForm(_AM_RESTARTPOLL, "poll_form", "index.php");
	$expire_text = new XoopsFormText(_AM_EXPIRATION."<br /><small>"._AM_FORMAT."<br />".sprintf(_AM_CURRENTTIME, formatTimestamp(time(), "Y-m-d H:i:s"))."</small>", "end_time", 20, 19);
	$poll_form->addElement($expire_text);
	$notify_yn = new XoopsFormRadioYN(_AM_NOTIFY, "notify", 1);
	$poll_form->addElement($notify_yn);
	$reset_yn = new XoopsFormRadioYN(_AM_RESET, "reset", 0);
	$poll_form->addElement($reset_yn);
	$op_hidden = new XoopsFormHidden("op", "restart_ok");
	$poll_form->addElement($op_hidden);
	$poll_id_hidden = new XoopsFormHidden("poll_id", $poll->getVar("poll_id"));
	$poll_form->addElement($poll_id_hidden);
	$submit_button = new XoopsFormButton("", "poll_submit", _AM_RESTART, "submit");
	$poll_form->addElement($submit_button);
	xoops_cp_header();
	OpenTable();
	$poll_form->display();
	CloseTable();
	xoops_cp_footer();
	exit();
}

if ( $op == "restart_ok" ) {
	$poll = new XoopsPoll($poll_id);
	if ( !empty($end_time) ) {
		$end_time = userTimeToServerTime(strtotime($end_time), $xoopsUser->timezone());
		$poll->setVar("end_time", $end_time);echo $end_time;
	} else {
		$poll->setVar("end_time", time() + (86400 * 10));
	}
	if ( $notify == 1 && $end_time > time() ) {
		// if notify, set mail status to "not mailed"
		$poll->setVar("mail_status", POLL_NOTMAILED);
	} else {
		// if not notify, set mail status to already "mailed"
		$poll->setVar("mail_status", POLL_MAILED);
	}
	if ( $reset == 1 ) {
		// reset all logs
		XoopsPollLog::deleteByPollId($poll->getVar("poll_id"));
		XoopsPollOption::resetCountByPollId($poll->getVar("poll_id"));
	}
	if (!$poll->store()) {
		echo $poll->getErrors();
		exit();
	}
	$poll->updateCount();
	poll_update_cache();
	redirect_header("index.php",1,_AM_DBUPDATED);
	exit();
}

if ( $op == "log" ) {
	$poll = new XoopsPoll($poll_id);
	$poll_form = new XoopsThemeForm(_AM_VIEWLOG, "poll_form", "index.php");
	$author_label = new XoopsFormLabel(_AM_AUTHOR, "<a href='".XOOPS_URL."/userinfo.php?uid=".$poll->getVar("user_id")."'>".XoopsUser::getUnameFromId($poll->getVar("user_id"))."</a>");
	$poll_form->addElement($author_label);
	$question_text = new XoopsFormLabel(_AM_POLLQUESTION, $myts->sanitizeForDisplay($poll->getVar("question")));
	$poll_form->addElement($question_text);
	//$desc_tarea = new XoopsFormLabel(_AM_POLLDESC, $myts->sanitizeForDisplay($poll->getVar("description"),1,0,1));
	//$poll_form->addElement($desc_tarea);
	$date = formatTimestamp($poll->getVar("end_time"), "Y-m-d H:i:s");
	$restart_label = new XoopsFormLabel(_AM_EXPIRATION, sprintf(_AM_EXPIREDAT, $date));
	$poll_form->addElement($restart_label);
	$n = $poll->getVotersCount();
	if ($n>=0) {
	    $poll_form->addElement(new XoopsFormLabel(_AM_ELECTORATERS, $n));
	    
	}
	$options_arr = XoopsPollOption::getAllByPollId($poll_id);
	$opts_text = array();
	foreach($options_arr as $option){
		$opts_text[$option->getVar("option_id")] = $option->getVar("option_text");
	}
	$order = "time ASC";
	$limit = 50;
	$total = XoopsPollLog::getTotalVotesByPollId($poll_id);
	if (!isset($start)) $start = 0;
	$polled_arr = XoopsPollLog::getAllByPollId($poll_id, $order, $limit,$start);
	$result = "<tr class='bg2'><th>"._AM_VOTE_DATE."</th><th>".
	    _AM_VOTE_USER."</th><th>"._AM_VOTE_OPT."</th><th>".
	    _AM_VOTE_IP."</th></tr>\n";
	foreach($polled_arr as $vote){
		$opts = $opts_text[$vote->getVar("option_id")];
		$ip = $vote->getVar("ip");
		$uid = $vote->getVar("user_id");
		$uname = $uid?"<a href='".XOOPS_URL."/userinfo.php?uid=$uid'>".XoopsUser::getUnameFromId($vote->getVar("user_id"))."</a>":$xoopsConfig['anonymous'];
		$result .= 
"<tr><td>".formatTimestamp($vote->getVar("time"), "Y-m-d H:i:s")."</td>".
"<td>$uname</td><td>$opts</td><td>$ip</td></tr>\n";
	}
	if ($total>$limit) {
	    $pg = "$base/admin/index.php?op=log&amp;poll_id=$poll_id";
	    $n=0;
	    $ank = "";
	    for ($i=0; $i<$total; $i+=$limit) {
		$n++;
		$ank .= ($start == $i)?"($n) ":"<a href='$pg&amp;start=$i'>$n</a> ";
	    }
	    $result .= "<tr class='bg2'><td colspan='4' style='text-align: center;'>$ank</td></tr>\n";
	}
	$result_label = new XoopsFormLabel(_AM_POLLRESULTS, _AM_VOTES.":$total<br /><table>$result</table>");
	$poll_form->addElement($result_label);
	xoops_cp_header();
	OpenTable();
	$poll_form->display();
	CloseTable();
	xoops_cp_footer();
	exit();
}

if ( $op == "quickupdate" ) {
	$count = count($poll_id);
	for ( $i = 0; $i < $count; $i++ ) {
		$display[$i] = empty($display[$i]) ? 0 : 1;
		$weight[$i] = empty($weight[$i]) ? 0 : $weight[$i];
		if ( $display[$i] != $old_display[$i] || $weight[$i] != $old_weight[$i] ) {
			$poll = new XoopsPoll($poll_id[$i]);
			$poll->setVar("display", $display[$i]);
			$poll->setVar("weight", intval($weight[$i]));
			$poll->store();
		}
	}
	poll_update_cache();
	redirect_header("index.php",1,_AM_DBUPDATED);
	exit();
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

function poll_update_cache(){
	$polls = XoopsPoll::getAll(array("display=1"), true, "weight ASC, end_time DESC");
	$contents = "";
	foreach ( $polls as $poll ) {
		$contents .= "<p>";
		$renderer = new XoopsPollRenderer($poll);
		$contents .= $renderer->renderForm();
		$contents .= "</p>";
	}

	echo "<pre>$contents</pre>";

	if (function_exists("putCache")) {
	    putCache("pollex/pollsblock.inc.php", $contents);
	} else {
	    global $basedir;
	    $filename = "$basedir/cache/pollsblock.inc.php";
	    if ( !is_writable($filename) ) {
		// attempt to chmod 666
		if ( !chmod($filename, 0666) ) {
			xoops_cp_header();
			printf(_MUSTWABLE, "<b>".$filename."</b>");
			xoops_cp_footer();
			exit();
		}
	    }
	    $file = fopen($filename, "w");
	    if ( fwrite($file, $contents) == -1) {
		return;
	    }
	    fclose($file);
	}
}
?>
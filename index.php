<?php
// ------------------------------------------------------------------------- //
//                XOOPS - PHP Content Management System                      //
//                       <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------- //
// Based on:								     //
// myPHPNUKE Web Portal System - http://myphpnuke.com/	  		     //
// PHP-NUKE Web Portal System - http://phpnuke.org/	  		     //
// Thatware - http://thatware.org/					     //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //
include("header.php");
$base = XOOPS_URL."/modules/".$xoopsModule->dirname();
$basedir = XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname();

if (!empty($HTTP_GET_VARS['id'])) {
    $track_id = intval($HTTP_GET_VARS['id']);
}

$tbl = $xoopsDB->prefix("trackback");
$tbr = $xoopsDB->prefix("trackback_ref");
$tblstyle="border='0' cellspacing='1' cellpadding='3' class='bg2'";
include_once "functions.php";

if ( empty($track_id) ) {
    include(XOOPS_ROOT_PATH."/header.php");
    OpenTable();
    echo "<h4>"._MI_TRACKBACK_NAME."</h4>";
    $result = $xoopsDB->query("SELECT track_id, track_uri, count(ref_id) FROM $tbl,$tbr WHERE track_id=track_from AND disable=0 AND linked=1 GROUP BY track_from ORDER BY track_id");
    if ($xoopsDB->getRowsNum($result)) {
	echo "<table $tblstyle>\n";
	echo "<tr class='bg1'><th>"._TB_TRACKPAGE."</th><th>"._TB_REF_SOURCE."</th></tr>\n";
	$nc = 1;
	while (list($tid, $uri, $refs)=$xoopsDB->fetchRow($result)) {
	    $bg = $tags[($nc++ % 2)];
	    echo "<tr class='$bg'><td><a href='index.php?id=$tid'>".uri_to_name($uri)."</a></td><td style='text-align:center'>$refs</a></td></tr>\n";
	}
	echo "</table>\n";
    }
    CloseTable();
    include (XOOPS_ROOT_PATH."/footer.php");
} else {
    $result = $xoopsDB->query("SELECT track_uri,disable FROM $tbl WHERE track_id=$track_id");
    list($uri, $disable) = $xoopsDB->fetchRow($result);
    if (!isset($disable) || $disable) {
	redirect_header("index.php",1,_TB_NOPAGE);
	exit();
    }

    include(XOOPS_ROOT_PATH."/header.php");
    OpenTable();
    echo "<h4>"._MI_TRACKBACK_NAME."</h4>";
    $result = $xoopsDB->query("SELECT * FROM $tbr WHERE track_from=$track_id AND linked=1 ORDER BY nref DESC");
    echo "<p>"._TB_TRACKPAGE.": <a href='index.php'>"._TB_INDEX."</a> &gt;&gt; <a href='$uri'>".uri_to_name($uri)."</a></p>\n";
    if ($xoopsDB->getRowsNum($result)) {
	echo "<table $tblstyle>\n";
	$nc = 1;
	while ($data=$xoopsDB->fetchArray($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $rdate = formatTimestamp($data['since'], "m");
	    $url = $data['ref_url'];
	    $nref = $data['nref'];
	    $title = $data['title'];
	    if ($title == '') $title = strim($url);
	    if ($data['context'] != '') {
		$ctext = "...".preg_replace('/<u>/', "<u class='anc'>", $data['context'])."...";
	    } else {
		$ctext = "";
	    }
	    echo "<tr class='$bg'><td><a href='$url'>$title</a>".
		"<div style='font-size: small; text-align: left;' class='context'>$ctext</div>".
		"<div style='font-size: xx-small; text-align: left;'>"._TB_REF_COUNT.":$nref [$rdate] "._TB_REF_URL." <a href='$url'>$url</a></span></div>".
		"</td></tr>\n";
	}
	echo "</table>\n";
    }
    CloseTable();
    include (XOOPS_ROOT_PATH."/footer.php");
}
?>
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
$tags = preg_match("/^XOOPS 1\\./",XOOPS_VERSION)?array("bg1","bg3"):array("even","odd");

function strim($s, $l) {
    if (strlen($s)<$l) return $s;
    $h = intval(($l-3)/2);
    $t = strlen($s)-$h+3;
    return substr($s,0,$h)."...".substr($s,$t);
}

if ( empty($track_id) ) {
    include(XOOPS_ROOT_PATH."/header.php");
    OpenTable();
    echo "<h4>"._MI_TRACKBACK_NAME."</h4>";
    $result = $xoopsDB->query("SELECT track_id, track_uri, count(ref_id) FROM $tbl,$tbr WHERE track_id=track_from AND checked=1 GROUP BY track_from ORDER BY track_id");
    if ($xoopsDB->getRowsNum($result)) {
	echo "<table border='0' cellpadding='2' cellspacing='1' class='bg2'>\n";
	echo "<tr class='bg1'><th>"._TB_TRACKPAGE."</th><th>"._TB_REF_SOURCE."</th><th><br/></th></tr>\n";
	$nc = 1;
	while (list($tid, $uri, $refs)=$xoopsDB->fetchRow($result)) {
	    $bg = $tags[($nc++ % 2)];
	    echo "<tr class='$bg'><td><a href='index.php?id=$tid'>$uri</a></td><td align='right'>$refs</a></td><td><a href='$uri'>"._TB_TRACKED."</a></td></tr>\n";
	}
	echo "</table>\n";
    }
    CloseTable();
    include (XOOPS_ROOT_PATH."/footer.php");
} else {
    include(XOOPS_ROOT_PATH."/header.php");
    OpenTable();
    echo "<h4>"._MI_TRACKBACK_NAME."</h4>";
    $result = $xoopsDB->query("SELECT track_uri FROM $tbl WHERE track_id=$track_id");
    list($uri) = $xoopsDB->fetchRow($result);
    $result = $xoopsDB->query("SELECT nref, ref_url, since FROM $tbr WHERE track_from=$track_id AND checked=1 ORDER BY nref DESC");
    echo "<p>"._TB_TRACKPAGE.": <a href='$uri'>$uri</a></p>\n";
    if ($xoopsDB->getRowsNum($result)) {
	echo "<table border='0' cellpadding='2' cellspacing='1' class='bg2'>\n";
	echo "<tr class='bg1'><th>"._TB_REF_COUNT."</th><th>"._TB_REF_URL."</th><th>"._TB_REF_DATE."</th></tr>\n";
	$nc = 1;
	while (list($nref, $url, $d)=$xoopsDB->fetchRow($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $rdate = formatTimestamp($d, "m");
	    echo "<tr class='$bg'><td align='right'>$nref</td><td><a href='$url'>".strim($url, 60)."</a></td><td>$rdate</td></tr>\n";
	}
	echo "</table>\n";
    }
    CloseTable();
    include (XOOPS_ROOT_PATH."/footer.php");
}
?>
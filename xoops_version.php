<?php
$modversion['name'] = _MI_TRACKBACK_NAME;
$modversion['version'] = 0.3;
$modversion['description'] = _MI_TRACKBACK_DESC;
$modversion['author'] = "Nobuhiro Yasutomi ( http://mysite.ddo.jp/ )";
$modversion['credits'] = "Nobuhiro Yasutomi";
$modversion['help'] = "trackback.html";
$modversion['license'] = "GPL see LICENSE";
$modversion['official'] = 0;
$modversion['image'] = preg_match("/^XOOPS 2/",XOOPS_VERSION)?
		"trackback_slogo2.png":"trackback_slogo.png";
$modversion['dirname'] = "trackback";

// Sql file (must contain sql generated by phpMyAdmin or phpPgAdmin)
// All tables should not have any prefix!
$modversion['sqlfile']['mysql'] = "sql/mysql.sql";
//$modversion['sqlfile']['postgresql'] = "sql/pgsql.sql";

// Tables created by sql file (without prefix!)
$modversion['tables'][0] = "trackback";
$modversion['tables'][1] = "trackback_ref";
$modversion['tables'][2] = "trackback_log";

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

//Blocks
$modversion['blocks'][1]['file'] = "trackback.php";
$modversion['blocks'][1]['name'] = _MI_TRACKBACK_BNAME;
$modversion['blocks'][1]['description'] = "Logging trackback and show it";
$modversion['blocks'][1]['show_func'] = "b_trackback_log_show";
$modversion['blocks'][1]['edit_func'] = "b_trackback_log_edit";
// Show Referer|Lists|Strict Check
$modversion['blocks'][1]['options'] = "8|20";

// Menu
$modversion['hasMain'] = 1;
?>
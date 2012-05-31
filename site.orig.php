<?php
/*********************************************************************************** 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

This script is just to set up stuff that will be used for mailqueue.php
rename it to site.php when you use it. Assumes you have PEAR on system that you are
pointing to specifically.        

***********************************************************************************/

$paths = array();
$paths["appbase"]     = "/home/youraccount"; // production
$paths["siteBase"]    = $paths["appbase"]   . "/public_html"; // production
$paths["adminBase"]   = $paths["siteBase"]  . "/admin";
$paths["includes"]    = $paths["adminBase"] . "/includes";
$paths["pear"]        = $paths["appbase"]   . "/PEAR-1.7.1";
$paths["newsletters"] = $paths["appbase"]   . "/newsletters";

ini_set("include_path", join(":", array($_ENV["include_path"], $paths["pear"], $paths["includes"])));

require_once($paths["pear"]."/MDB2-2.5.0b1/MDB2.php");
require_once($paths["includes"]."/general.php"); // functions

$webSite        = "www.somewebsitename.com";
$siteName       = "Some Website Name";
$siteEmail      = "somewebsite@somemailserver.com";
$webmasterEmail = "webmaster@somemailserver.com";

$test = false; // false for production

if($test != true) {
  // production  db // change to local if neccessary.
  $database = "your_db";
  $dbuser   = $database;
  $dbpass   = "";
  $mail_database = "db_mail_log";
  $mail_dbuser   = "user_mail_log";
  $mail_dbpass   = "";

} else {
  // dev  db
  $database = "db_dev";
  $dbuser   = $database;
  $dbpass   = "";

  $mail_database = "dev_mail_log";
  $mail_dbuser   = "dev_mail_log";
  $mail_dbpass   = "";
}

$dbhost   = "localhost";

//diode($db = MDB2::connect("mysql://$dbuser:$dbpass@$dbhost/$database", "Check connect string."));
//$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
//$db->loadModule('Extended');

diode($mlog = MDB2::connect("mysql://$mail_dbuser:$mail_dbpass@$dbhost/$mail_database", "Check connect string."));
$mlog->setFetchMode(MDB2_FETCHMODE_ASSOC);
$mlog->loadModule('Extended');


function dieOnDbError($obj, $msg = "") {
  if (MDB2::isError($obj)) {
	die("<b>".$obj->getMessage()."</b><br>".htmlentities($msg));
  }
}

function diode($obj, $msg = "") {
  dieOnDbError($obj, $msg);
}
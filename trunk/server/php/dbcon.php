<?php
$dbuser = "chatuser";
$password = "chatuser";
$server = "127.0.0.1";
$database = "chat";
$table = "chatmessages";

$link = mysql_connect($server, $dbuser, $password);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db($database, $link);
if (!$db_selected) {
    die ('Can\'t use ' . $database . ' : ' . mysql_error());
}

?>
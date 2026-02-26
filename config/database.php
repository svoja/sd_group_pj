<?php
$host = "127.0.0.1";
$username = "root";
$password = "";
$database = "group_pj";
$mysqli = new mysqli("127.0.0.1", "root", "", "group_pj");

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}
?>
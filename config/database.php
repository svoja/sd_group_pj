<?php
$host = "localhost";
$username = "root";
$password = "2549";
$database = "group_pj";
$mysqli = new mysqli($host, $username, $password, $database);

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}
?>
<?php
$host = "mysql";
$port = 3306;
$user = "root";
$pass = "root";
$db   = "sd_group"; // หรือ mydb แล้วแต่ของจริง

$mysqli = new mysqli($host, $user, $pass, $db, $port);

if ($mysqli->connect_error) {
  die("Connection failed: " . $mysqli->connect_error);
}
?>
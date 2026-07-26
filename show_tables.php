<?php
$conn = mysqli_connect("localhost", "root", "", "miniproject");
$res = $conn->query("SHOW TABLES");
while($r = $res->fetch_array()) echo $r[0] . "\n";
?>

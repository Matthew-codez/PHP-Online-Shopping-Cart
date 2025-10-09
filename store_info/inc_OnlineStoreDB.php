<?php

$DBConnect = @new mysqli("localhost", "root", "", "ggg");
if (mysqli_connect_errno()) {
    die("Database connection failed");
}
?>
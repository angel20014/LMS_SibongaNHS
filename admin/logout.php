<?php
include('include/dbcon.php');
include('session.php');

$logout_query = mysqli_query($con, "SELECT * FROM admin WHERE admin_id = $id_session");
$row = mysqli_fetch_array($logout_query);
$user = $row['firstname'] . " " . $row['middlename'] . " " . $row['lastname'];

session_start();
session_destroy();

   header("Location: ../index.php");
?>

<?php
include('include/dbcon.php');
include('session.php');

// ✅ Fetch teacher data instead of admin
$logout_query = mysqli_query($con, "SELECT * FROM teachers WHERE teacher_id = $id_session");
$row = mysqli_fetch_array($logout_query);
$user = $row['firstname'] . " " . $row['middlename'] . " " . $row['lastname'];

// ✅ Destroy session
session_start();
session_destroy();

// ✅ Redirect to login page
header("Location: ../index.php");
exit();
?>

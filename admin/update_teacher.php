<?php 
include('include/dbcon.php');

$id            = $_POST['teacher_id'];
$firstname     = strtoupper(trim($_POST['firstname']));
$middlename    = strtoupper(trim($_POST['middlename']));
$lastname      = strtoupper(trim($_POST['lastname']));
$username      = trim($_POST['username']);
$contact       = trim($_POST['contact_number']);
$address       = strtoupper(trim($_POST['address']));

// ✅ Validate contact number
if (!preg_match('/^09[0-9]{9}$/', $contact)) {
    die("Invalid contact number. Must start with 09 and be exactly 11 digits.");
}

mysqli_query($con, "UPDATE teachers SET 
    firstname = '$firstname',
    middlename = '$middlename',
    lastname = '$lastname',
    username = '$username',
    contact_number = '$contact',
    address = '$address'
    WHERE teacher_id = '$id'
") or die(mysqli_error($con));

header("Location: user.php");
exit;
?>

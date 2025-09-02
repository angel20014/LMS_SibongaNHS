<?php
include('include/dbcon.php');

$id          = $_POST['student_id'];
$firstname   = strtoupper(trim($_POST['firstname']));
$middlename  = strtoupper(trim($_POST['middlename']));
$lastname    = strtoupper(trim($_POST['lastname']));
$grade_level = trim($_POST['grade_level']);
$section     = trim($_POST['section']);
$address     = strtoupper(trim($_POST['address']));
$contact     = trim($_POST['contact_number']);

// ✅ Validate contact number (must start with 09 and be 11 digits)
if (!preg_match('/^09[0-9]{9}$/', $contact)) {
    die("Invalid contact number. Must start with 09 and be exactly 11 digits.");
}

mysqli_query($con, "UPDATE students SET 
    firstname = '$firstname',
    middlename = '$middlename',
    lastname = '$lastname',
    grade_level = '$grade_level',
    section = '$section',
    address = '$address',
    contact_number = '$contact'
    WHERE student_id = '$id'
") or die(mysqli_error($con));

header("Location: user.php");
exit;
?>

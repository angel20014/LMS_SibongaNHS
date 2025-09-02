<?php
include('include/dbcon.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id          = $_POST['admin_id'];

    // Escape all inputs
    $firstname         = mysqli_real_escape_string($con, $_POST['firstname']);
    $middlename        = mysqli_real_escape_string($con, $_POST['middlename']);
    $lastname          = mysqli_real_escape_string($con, $_POST['lastname']);
    $address           = mysqli_real_escape_string($con, $_POST['address']);
    $contact_number    = mysqli_real_escape_string($con, $_POST['contact_number']);
    $security_question = mysqli_real_escape_string($con, $_POST['security_question']);
    $security_answer   = mysqli_real_escape_string($con, $_POST['security_answer']);
    $username          = mysqli_real_escape_string($con, $_POST['username']);
    $password          = mysqli_real_escape_string($con, $_POST['password']); // hash in production

    $admin_image       = $_FILES['admin_image']['name'];
    $target_dir        = "upload/";

    // Build SQL query
    $sql = "UPDATE admin SET 
                firstname = '$firstname',
                middlename = '$middlename',
                lastname = '$lastname',
                address = '$address',
                contact_number = '$contact_number',
                security_question = '$security_question',
                security_answer = '$security_answer',
                username = '$username',
                password = '$password'";

    // Handle image upload if exists
    if (!empty($admin_image)) {
        $target_file = $target_dir . basename($admin_image);
        if (move_uploaded_file($_FILES['admin_image']['tmp_name'], $target_file)) {
            $sql .= ", admin_image = '$admin_image'";
        } else {
            echo "<script>alert('Image upload failed.'); window.history.back();</script>";
            exit;
        }
    }

    $sql .= " WHERE admin_id = '$admin_id'";

    if (mysqli_query($con, $sql)) {
        echo "<script>alert('Admin updated successfully.'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Error updating admin: " . mysqli_error($con) . "'); window.history.back();</script>";
    }
}
?>

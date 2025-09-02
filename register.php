<?php
include('include/dbcon.php');

if (isset($_POST['register'])) {
    $usertype = $_POST['usertype'];

    $firstname = strtoupper(mysqli_real_escape_string($con, $_POST['firstname']));
    $middlename = strtoupper(mysqli_real_escape_string($con, $_POST['middlename']));
    $lastname = strtoupper(mysqli_real_escape_string($con, $_POST['lastname']));
    $address = strtoupper(mysqli_real_escape_string($con, $_POST['address'])); 
    $username = mysqli_real_escape_string($con, $_POST['username']);  
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $security_question = mysqli_real_escape_string($con, $_POST['security_question']);
    $security_answer = mysqli_real_escape_string($con, $_POST['security_answer']);
    $contact_number = mysqli_real_escape_string($con, $_POST['contact_number']);
    $date_registered = date('Y-m-d H:i:s');

    // Validate contact number (must start with 09 and be exactly 11 digits)
    if (!preg_match('/^09[0-9]{9}$/', $contact_number)) {
        echo "<script>alert('Contact number must start with 09 and be exactly 11 digits.'); window.history.back();</script>";
        exit;
    }

    function uploadImage($input_name) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === 0) {
            $filename = 'upload/' . time() . '_' . basename($_FILES[$input_name]['name']);
            move_uploaded_file($_FILES[$input_name]['tmp_name'], $filename);
            return $filename;
        }
        return '';
    }

    $uploaded_image = uploadImage('image');

    if (empty($uploaded_image)) {
        echo "<script>alert('User photo is required.'); window.location='index.php';</script>";
        exit;
    }

    // Check if username already exists
    $check_username = mysqli_query($con, "SELECT username FROM (
        SELECT username FROM students
        UNION
        SELECT username FROM teachers
        UNION
        SELECT username FROM admin
    ) AS all_users WHERE username = '$username'");

    if (mysqli_num_rows($check_username) > 0) {
        echo "<script>alert('Username already exists. Please choose a different one.'); window.history.back();</script>";
        exit;
    }

    if ($usertype === 'student') {
        $studentid_number = strtoupper(mysqli_real_escape_string($con, $_POST['studentid_number']));
        $grade_level = strtoupper(mysqli_real_escape_string($con, $_POST['grade_level']));
        $section = strtoupper(mysqli_real_escape_string($con, $_POST['section']));

        // Check student ID duplication
        $check_id = mysqli_query($con, "SELECT * FROM students WHERE studentid_number = '$studentid_number'");
        if (mysqli_num_rows($check_id) > 0) {
            echo "<script>alert('Student ID number already exists.'); window.history.back();</script>";
            exit;
        }

        $query = "INSERT INTO students (
                    studentid_number, firstname, middlename, lastname, address, grade_level, section,
                    username, password, security_question, security_answer,
                    contact_number, student_image, date_registered
                  ) VALUES (
                    '$studentid_number', '$firstname', '$middlename', '$lastname', '$address', '$grade_level', '$section',
                    '$username', '$password', '$security_question', '$security_answer',
                    '$contact_number', '$uploaded_image', '$date_registered'
                  )";

        $success = mysqli_query($con, $query);
        $msg = $success ? 'Student registered successfully!' : 'Error: Student registration failed.';
    }

    elseif ($usertype === 'teacher') {
        $query = "INSERT INTO teachers (
                    firstname, middlename, lastname, address, username, password,
                    security_question, security_answer, contact_number, teacher_image, date_registered
                  ) VALUES (
                    '$firstname', '$middlename', '$lastname', '$address', '$username', '$password',
                    '$security_question', '$security_answer', '$contact_number', '$uploaded_image', '$date_registered'
                  )";

        $success = mysqli_query($con, $query);
        $msg = $success ? 'Teacher registered successfully!' : 'Error: Teacher registration failed.';
    }

    elseif ($usertype === 'admin') {
        $query = "INSERT INTO admin (
                    firstname, middlename, lastname, address, username, password,
                    admin_image, security_question, security_answer,
                    contact_number, date_registered
                  ) VALUES (
                    '$firstname', '$middlename', '$lastname', '$address', '$username', '$password',
                    '$uploaded_image', '$security_question', '$security_answer',
                    '$contact_number', '$date_registered'
                  )";

        $success = mysqli_query($con, $query);
        $msg = $success ? 'Admin registered successfully!' : 'Error: Admin registration failed.';
    }

    else {
        echo "<script>alert('Invalid user type.'); window.location='index.php';</script>";
        exit;
    }

    echo "<script>alert('$msg'); window.location.href='index.php?showLogin=1';</script>";
}
?>

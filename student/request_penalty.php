<?php
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Ensure student is logged in
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['penalty_id'])) {
    $penalty_id = intval($_POST['penalty_id']);

    // ✅ Update penalties status → Requesting Approval
    $query = "
        UPDATE penalties 
        SET status = 'Requesting Approval'
        WHERE id = '$penalty_id'
        AND status = 'Pending'
        LIMIT 1
    ";

    if (mysqli_query($con, $query)) {
        $_SESSION['success'] = "Penalty approval request sent to Admin.";
    } else {
        $_SESSION['error'] = "Something went wrong. Please try again.";
    }
}

// ✅ Redirect back to penalties page
header("Location: penalties.php");
exit();

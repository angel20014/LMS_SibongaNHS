<?php
include('include/dbcon.php');

$field = $_POST['field'];
$value = mysqli_real_escape_string($con, $_POST['value']);
$response = ['exists' => false];

if ($field === 'username') {
    $query = "SELECT username FROM (
        SELECT username FROM students
        UNION
        SELECT username FROM teachers
        UNION
        SELECT username FROM admin
    ) AS all_users WHERE username = '$value'";
} elseif ($field === 'studentid_number') {
    $query = "SELECT studentid_number FROM students WHERE studentid_number = '$value'";
} else {
    echo json_encode($response);
    exit;
}

$result = mysqli_query($con, $query);
if (mysqli_num_rows($result) > 0) {
    $response['exists'] = true;
}

echo json_encode($response);
?>

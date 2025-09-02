<?php
include('include/dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'];
    $input = mysqli_real_escape_string($con, $_POST['username'] ?? '');

    function find_user_data($con, $input) {
        foreach (['admin', 'teachers'] as $table) {
            $sql = "SELECT * FROM $table WHERE username = '$input'";
            $result = mysqli_query($con, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                return [
                    'table' => $table,
                    'question' => $row['security_question'],
                    'answer' => $row['security_answer'],
                    'username' => $row['username']
                ];
            }
        }

        $sql = "SELECT * FROM students WHERE username = '$input' OR studentid_number = '$input'";
        $result = mysqli_query($con, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return [
                'table' => 'students',
                'question' => $row['security_question'],
                'answer' => $row['security_answer'],
                'username' => $row['username'] 
            ];
        }

        return false;
    }

    if ($step == 1) {
        $user = find_user_data($con, $input);
        if ($user) {
            echo json_encode([
                'success' => true,
                'question' => $user['question'],
                'table' => $user['table'],
                'username' => $user['username']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Username or Student ID not found']);
        }

    } elseif ($step == 2) {
        $secret_answer = $_POST['secret_answer'];
        $table = $_POST['table'];
        $username = $_POST['username'];

        $sql = "SELECT * FROM $table WHERE username = '$username' AND security_answer = '$secret_answer'";
        $result = mysqli_query($con, $sql);
        echo json_encode(['success' => mysqli_num_rows($result) > 0]);
    } elseif ($step == 3) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $table = $_POST['table'];
        $username = $_POST['username'];

        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            exit;
        }

        $update = mysqli_query($con, "UPDATE $table SET password = '$new_password' WHERE username = '$username'");
        echo json_encode(['success' => $update]);
    }
}
?>

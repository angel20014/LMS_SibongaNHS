<?php
include('../include/dbcon.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $query = mysqli_query($con, "SELECT * FROM teachers WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($query) == 1) {
        $row = mysqli_fetch_assoc($query);

        $_SESSION['id'] = $row['teacher_id'];
        $_SESSION['role'] = "teacher";

        header("Location: dashboard.php"); // redirect to teacher dashboard
        exit();
    } else {
        echo "<script>alert('Invalid username or password!'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Login</title>
</head>
<body>
    <h2>Teacher Login</h2>
    <form method="POST" action="">
        <label>Username</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>

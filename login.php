<?php
session_start();
include('include/dbcon.php');

$max_attempts = 3;
$lock_duration = 300; // 5 minutes
$current_time = time();
$locked = false;
$remaining = 0;

// Initialize session arrays if not exist
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = [];
if (!isset($_SESSION['lock_time'])) $_SESSION['lock_time'] = [];

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Check if this user is locked
    if (isset($_SESSION['login_attempts'][$username]) && $_SESSION['login_attempts'][$username] >= $max_attempts) {
        $elapsed = $current_time - ($_SESSION['lock_time'][$username] ?? 0);
        if ($elapsed < $lock_duration) {
            $locked = true;
            $remaining = $lock_duration - $elapsed;
        } else {
            // Lock period expired, reset attempts
            $_SESSION['login_attempts'][$username] = 0;
            unset($_SESSION['lock_time'][$username]);
            $locked = false;
        }
    }

    if (!$locked) {
        $user_found = false;

        // Check students
        $query_student = mysqli_query($con, "SELECT * FROM students WHERE username='$username' AND password='$password'");
        if (mysqli_num_rows($query_student) > 0) {
            $row = mysqli_fetch_assoc($query_student);
            $_SESSION['id'] = $row['student_id'];
            $_SESSION['fullname'] = $row['firstname'].' '.$row['lastname'];
            $_SESSION['role'] = 'student';
            $_SESSION['login_attempts'][$username] = 0;
            $user_found = true;
            echo "<script>
                alert('Login Successful!');
                window.location.href='student/dashboard.php';
            </script>";
            exit();
        }

        // Check teachers
        $query_teacher = mysqli_query($con, "SELECT * FROM teachers WHERE username='$username' AND password='$password'");
        if (mysqli_num_rows($query_teacher) > 0) {
            $row = mysqli_fetch_assoc($query_teacher);
            $_SESSION['id'] = $row['teacher_id'];
            $_SESSION['fullname'] = $row['firstname'].' '.$row['lastname'];
            $_SESSION['role'] = 'teacher';
            $_SESSION['login_attempts'][$username] = 0;
            $user_found = true;
            echo "<script>
                alert('Login Successful!');
                window.location.href='teacher/dashboard.php';
            </script>";
            exit();
        }

        // Check admin
        $query_admin = mysqli_query($con, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
        if (mysqli_num_rows($query_admin) > 0) {
            $row = mysqli_fetch_assoc($query_admin);
            $_SESSION['id'] = $row['admin_id'];
            $_SESSION['fullname'] = $row['firstname'].' '.$row['lastname'];
            $_SESSION['role'] = 'admin';
            $_SESSION['login_attempts'][$username] = 0;
            $user_found = true;
            echo "<script>
                alert('Login Successful!');
                window.location.href='admin/dashboard.php';
            </script>";
            exit();
        }

        // Login failed
        if (!$user_found) {
            $_SESSION['login_attempts'][$username] = ($_SESSION['login_attempts'][$username] ?? 0) + 1;

            if ($_SESSION['login_attempts'][$username] >= $max_attempts) {
                $_SESSION['lock_time'][$username] = time();
                $locked = true;
                $remaining = $lock_duration;
            } else {
                $remaining_attempts = $max_attempts - $_SESSION['login_attempts'][$username];
                echo "<script>
                    alert('Invalid username or password! You have $remaining_attempts attempt(s) left.');
                    window.location.href='index.php';
                </script>";
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<style>
body { text-align:center; margin-top:50px; font-family:sans-serif; }
input { padding:8px; width:200px; margin-bottom:10px; }
button { padding:8px 16px; margin-top:10px; }
.back-btn { margin-top:20px; background:#ccc; border:none; cursor:pointer; padding:8px 16px; }
</style>
</head>
<body>
<?php if ($locked): ?>
    <h3>Your account for <b><?php echo htmlspecialchars($username); ?></b> is temporarily locked due to too many login attempts.</h3>
    <p>Please wait:</p>
    <h1 id="timer"><?php echo gmdate("i:s", $remaining); ?></h1>
<?php else: ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="login">Login</button>
    </form>
<?php endif; ?>

<button class="back-btn" onclick="window.history.back();">Back</button>

<?php if ($locked): ?>
<script>
var remaining = <?php echo $remaining; ?>;
var timerElement = document.getElementById('timer');

var interval = setInterval(function() {
    remaining--;
    if (remaining <= 0) {
        clearInterval(interval);
        location.reload(); // show login form again
    }
    var minutes = Math.floor(remaining / 60);
    var seconds = remaining % 60;
    timerElement.textContent = ('0' + minutes).slice(-2) + ':' + ('0' + seconds).slice(-2);
}, 1000);
</script>
<?php endif; ?>
</body>
</html>

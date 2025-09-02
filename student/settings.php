<?php
include('header.php');
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['id'];
$errors = [];
$success = "";

// Fetch current user info including security_answer
$user_query = mysqli_query($con, "SELECT username, password, security_question, security_answer FROM students WHERE student_id = '$student_id'") or die(mysqli_error($con));
$student = mysqli_fetch_assoc($user_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $new_username = trim(mysqli_real_escape_string($con, $_POST['username']));
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $security_answer = trim(mysqli_real_escape_string($con, $_POST['security_answer']));

    $errors = [];

    // Validate new password & confirmation if provided
    if (!empty($new_password) || !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $errors[] = "New password and confirmation do not match.";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }
    }

    // Validate username
    if (empty($new_username)) {
        $errors[] = "Username cannot be empty.";
    } else {
        $check_query = mysqli_query($con, "SELECT student_id FROM students WHERE username = '$new_username' AND student_id != '$student_id'");
        if (mysqli_num_rows($check_query) > 0) {
            $errors[] = "Username is already taken.";
        }
    }

    // Validate security answer
    if (empty($security_answer)) {
        $errors[] = "Security answer cannot be empty.";
    }

    if (empty($errors)) {
        $update_fields = "username = '$new_username', security_answer = '$security_answer'";

        if (!empty($new_password)) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_fields .= ", password = '$new_password_hash'";
        }

        $update_query = "UPDATE students SET $update_fields WHERE student_id = '$student_id'";

        if (mysqli_query($con, $update_query)) {
            $success = "Settings updated successfully.";
            $student['username'] = $new_username;
            $student['security_answer'] = $security_answer;
        } else {
            $errors[] = "Failed to update settings. Please try again.";
        }
    }
}


?>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> Account Settings</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row justify-content-center">
    <div class="col-md-12 col-sm-9 col-xs-12">
        <div class="x_panel">
            <div class="x_title d-flex justify-content-between align-items-center">
                <h2><i class="fa fa-cog"></i> Account Settings</h2>
                <!-- Button triggers modal -->
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#changeSettingsModal" style="float:right;>
                    <i class="fa fa-edit"></i> Change Settings
                </button>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error) echo "<li>" . htmlspecialchars($error) . "</li>"; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <!-- Display current info readonly -->
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" 
                           value="<?php echo htmlspecialchars($student['username']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" class="form-control" value="********" readonly>
                </div>

                <div class="form-group">
                    <label>Security Question</label>
                    <input type="text" class="form-control" 
                           value="<?php echo htmlspecialchars($student['security_question']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Security Answer</label>
                    <input type="text" class="form-control" 
                           value="<?php echo htmlspecialchars($student['security_answer'] ?? ''); ?>" readonly>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Modal -->
<div class="modal fade" id="changeSettingsModal" tabindex="-1" role="dialog" aria-labelledby="changeSettingsLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" action="" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="changeSettingsLabel"><i class="fa fa-edit"></i> Change Account Settings</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size:1.5rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" class="form-control" 
                 value="<?php echo htmlspecialchars($student['username']); ?>" required>
        </div>

        <div class="form-group">
           <label>Current Password</label>
        <input type="text" class="form-control" 
       value="<?php echo htmlspecialchars($student['password']); ?>" readonly>

        </div>


        <div class="form-group">
          <label>Security Question</label>
          <input type="text" class="form-control" 
                 value="<?php echo htmlspecialchars($student['security_question']); ?>" readonly>
        </div>

        <div class="form-group">
          <label>Security Answer</label>
          <input type="text" name="security_answer" class="form-control" 
                 value="<?php echo htmlspecialchars($student['security_answer'] ?? ''); ?>" required>
        </div>

        <hr>

        <div class="form-group">
          <label>New Password <small>(leave blank if not changing)</small></label>
          <input type="password" name="new_password" class="form-control" minlength="6" placeholder="New password">
        </div>

        <div class="form-group">
          <label>Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" minlength="6" placeholder="Confirm new password">
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" name="update_settings" class="btn btn-success">
          <i class="fa fa-save"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<?php include('footer.php'); ?>

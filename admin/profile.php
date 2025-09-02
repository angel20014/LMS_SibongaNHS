<?php
include('header.php');
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$admin_id = $_SESSION['id'];

// Handle profile update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Convert all input fields to uppercase before saving
    $firstname = strtoupper(mysqli_real_escape_string($con, $_POST['firstname']));
    $middlename = strtoupper(mysqli_real_escape_string($con, $_POST['middlename']));
    $lastname = strtoupper(mysqli_real_escape_string($con, $_POST['lastname']));
    $address = strtoupper(mysqli_real_escape_string($con, $_POST['address']));
    $contact_number = mysqli_real_escape_string($con, $_POST['contact_number']); // contact number usually stays as is

    $update_query = "
        UPDATE admin SET 
            firstname='$firstname',
            middlename='$middlename',
            lastname='$lastname',
            address='$address',
            contact_number='$contact_number'
        WHERE admin_id='$admin_id'
    ";

    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Profile updated successfully.'); window.location.href='profile.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to update profile.');</script>";
    }
}

// Fetch latest admin info after potential update
$user_query = mysqli_query($con, "SELECT * FROM admin WHERE admin_id = '$admin_id'") or die(mysqli_error($con));
$admin = mysqli_fetch_assoc($user_query);
?>

<style>
/* Make all text inputs show uppercase letters visually */
input[type="text"] {
    text-transform: uppercase;
}
</style>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> My Profile</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row justify-content-center">
    <div class="col-md-14 col-sm-16 col-xs-16">
        <div class="x_panel">
            <div class="x_title d-flex justify-content-between align-items-center">
                <h2><i class="fa fa-user"></i> My Profile</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editProfileModal" style="float:right;">
                    <i class="fa fa-edit"></i> Edit Profile
                </button>
                <div class="clearfix"></div>
            </div>
            <div class="x_content text-center">

                <?php 
                $imgPath = "upload/" . $admin['admin_image'];
                $imgSrc = (!empty($admin['admin_image']) && file_exists($imgPath)) ? $imgPath : "images/user.png";
                ?>
                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Profile Image" 
                     class="img-thumbnail mb-3" 
                     style="width:200px; height:200px; object-fit:cover; border-radius:50%;">

                <table class="table table-bordered table-striped mx-auto" 
                       style="max-width:900px; text-align:left; font-size:1.1rem;">
                    <tbody>
                        
                        <tr><th>First Name</th><td><?php echo htmlspecialchars($admin['firstname']); ?></td></tr>
                        <tr><th>Middle Name</th><td><?php echo htmlspecialchars($admin['middlename']); ?></td></tr>
                        <tr><th>Last Name</th><td><?php echo htmlspecialchars($admin['lastname']); ?></td></tr>
                        <tr><th>Address</th><td><?php echo htmlspecialchars($admin['address']); ?></td></tr>
                        <tr><th>Contact Number</th><td><?php echo htmlspecialchars($admin['contact_number']); ?></td></tr>
                        <tr><th>Date Registered</th><td><?php echo date('M d, Y', strtotime($admin['date_registered'])); ?></td></tr>
                    </tbody>
                </table>
                
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form method="POST" action="" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileLabel"><i class="fa fa-edit"></i> Edit Profile</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <div class="form-row">
          <div class="form-group col-md-6">
           
          </div>
          <div class="form-group col-md-6">
            <label>Date Registered</label>
            <input type="text" class="form-control" value="<?php echo date('M d, Y', strtotime($admin['date_registered'])); ?>" readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-4">
            <label>First Name</label>
            <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($admin['firstname']); ?>" required>
          </div>
          <div class="form-group col-md-4">
            <label>Middle Name</label>
            <input type="text" name="middlename" class="form-control" value="<?php echo htmlspecialchars($admin['middlename']); ?>">
          </div>
          <div class="form-group col-md-4">
            <label>Last Name</label>
            <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($admin['lastname']); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label>Address</label>
          <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($admin['address']); ?>" required>
        </div>

        <div class="form-row">
          <div class="form-group col-md-4">
            <label>Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($admin['contact_number']); ?>" required>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" name="update_profile" class="btn btn-success">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php include('footer.php'); ?>

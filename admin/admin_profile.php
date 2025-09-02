<?php include ('header.php'); ?>

        <div class="page-title">
            <div class="title_left">
                <h3>
					<small>Home /</small> Profile Information
                </h3>
            </div>
        </div>
        <div class="clearfix"></div>
 
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <ul class="nav navbar-right panel_toolbox">
<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_session = $_SESSION['id'] ?? null; // adjust if your session variable is named differently

$admin_type = null;

if ($id_session) {
    $user_query = mysqli_query($con, "SELECT * FROM admin WHERE admin_id = '$id_session'")
        or die(mysqli_error($con));

    if ($user_row = mysqli_fetch_array($user_query)) {
        $admin_type = $user_row['admin_type'] ?? null;
    }
}

// Show button only if admin_type is Admin
if ($admin_type === 'Admin') {
?>
    <li>
        <a href="add_admin.php" style="background:none;">
            <button class="btn btn-primary"><i class="fa fa-plus"></i> Add Admin</button>
        </a>
    </li>
<?php
}
?>

                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                        <!-- If needed 
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    <i class="fa fa-wrench"></i>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#">Settings 1</a></li>
                                    <li><a href="#">Settings 2</a></li>
                                </ul>
                            </li>
						-->
                            <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <!-- content starts here -->

						<div class="table-responsive">
    <table cellpadding="0" cellspacing="0" border="0" 
           class="table table-striped table-bordered" id="example">
        <thead>
            <tr>
                <th>Image</th>
                <th>Admin ID</th>
                <th>Full Name</th>
                <th>Address</th>
                <th>Contact Number</th>
                <th>Security Question</th>
                <th>Security Answer</th>
                <th>Username</th>
                <th>Password</th>
                <th>Date Registered</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $result = mysqli_query($con, "SELECT * FROM admin ORDER BY admin_id DESC") 
                  or die(mysqli_error($con));
        while ($row = mysqli_fetch_array($result)) {
            $id = $row['admin_id'];
        ?>
            <tr>
                <!-- Profile Image -->
                <td>
                    <?php if (!empty($row['admin_image'])): ?>
                        <img src="upload/<?php echo $row['admin_image']; ?>" 
                             width="80px" height="80px" 
                             style="border:2px solid #ccc; border-radius:5px;">
                    <?php else: ?>
                        <img src="images/user.png" width="80px" height="80px" 
                             style="border:2px solid #ccc; border-radius:5px;">
                    <?php endif; ?>
                </td>

                <!-- Basic Info -->
                <td><?php echo $row['admin_id']; ?></td>
                <td><?php echo $row['firstname']; ?></td>
                <td><?php echo $row['middlename']; ?></td>
                <td><?php echo $row['lastname']; ?></td>
                <td><?php echo $row['address']; ?></td>
                <td><?php echo $row['contact_number']; ?></td>

                <!-- Security -->
                <td><?php echo $row['security_question']; ?></td>
                <td><?php echo $row['security_answer']; ?></td>

                <!-- Login -->
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['password']; ?></td>

                <!-- Date -->
                <td><?php echo $row['date_registered']; ?></td>

                <!-- Action Buttons -->
                <td>
                    <a class="btn btn-primary" href="view_admin.php?admin_id=<?php echo $id; ?>">
                        <i class="fa fa-search"></i>
                    </a>
                    <a class="btn btn-warning" href="edit_admin.php?admin_id=<?php echo $id; ?>">
                        <i class="fa fa-edit"></i>
                    </a>
                    <a class="btn btn-danger" href="#delete<?php echo $id; ?>" data-toggle="modal">
                        <i class="glyphicon glyphicon-trash icon-white"></i>
                    </a>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="delete<?php echo $id; ?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <i class="glyphicon glyphicon-user"></i> Delete Admin
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-danger">
                                        Are you sure you want to delete <?php echo $row['firstname'] . ' ' . $row['lastname']; ?>?
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-inverse" data-dismiss="modal">No</button>
                                    <a href="delete_admin.php?admin_id=<?php echo $id; ?>" class="btn btn-primary">Yes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

						
                        <!-- content ends here -->
                    </div>
                </div>
            </div>
        </div>

<?php include ('footer.php'); ?>
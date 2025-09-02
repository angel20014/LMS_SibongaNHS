<?php include ('header.php'); ?>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> Admin Profile</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-info"></i> Admin Information</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <?php
                    $user_query  = mysqli_query($con,"SELECT * FROM admin WHERE admin_id = '$id_session'")or die(mysqli_error());
                    $user_row = mysqli_fetch_array($user_query);
                    ?>
                    <?php if ($user_row['admin_id'] == 'Admin') { ?>
                        <li>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addAdminModal">
                                <i class="fa fa-plus"></i> Add Admin
                            </button>
                        </li>
                    <?php } ?>
                   
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

                <div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Image</th>
                <th>Full Name</th>
                <th>Contact Number</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = mysqli_query($con, "SELECT * FROM admin ORDER BY admin_id ASC") or die(mysqli_error());
            $modals = ""; // store all modals here

            while ($row = mysqli_fetch_array($result)) {
                $id = $row['admin_id'];
            ?>
            <tr>
                <td>
                    <?php if ($row['admin_image'] != ""): ?>
                        <img src="upload/<?php echo $row['admin_image']; ?>" width="80" height="80" style="border-radius:5px;">
                    <?php else: ?>
                        <img src="images/user.png" width="80" height="80" style="border-radius:5px;">
                    <?php endif; ?> 
                </td> 
                <td><?php echo $row['firstname'] . " " . $row['middlename'] . " " . $row['lastname']; ?></td>
                <td><?php echo $row['contact_number']; ?></td>
                <td>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#viewModal<?php echo $id; ?>">
                        <i class="fa fa-search"></i>
                    </button>
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?php echo $id; ?>">
                        <i class="fa fa-edit"></i>
                    </button>
                </td>
            </tr>
            <?php
            // store modal HTML in variable
			$imgSrc = !empty($row['admin_image']) ? 'upload/'.$row['admin_image'] : 'images/user.png';

$modals .= '

<!-- View Modal -->
<div class="modal fade" id="viewModal'.$id.'" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-user"></i> Admin Details</h5>
            </div>
            <div class="modal-body text-center">
                <!-- Profile Image -->
                <img src="'.$imgSrc.'" class="img-thumbnail mb-3" width="120">

                <!-- Info Table -->
                <div class="table-responsive">
                    <table class="table table-borderless text-left w-75 mx-auto">
                        <tbody>
                            <tr>
                                <th style="width:40%;">Full Name:</th>
                                <td>'.htmlspecialchars($row['firstname'].' '.$row['middlename'].' '.$row['lastname']).'</td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td>'.htmlspecialchars($row['address']).'</td>
                            </tr>
                            <tr>
                                <th>Contact Number:</th>
                                <td>'.htmlspecialchars($row['contact_number']).'</td>
                            </tr>
                            <tr>
                                <th>Security Question:</th>
                                <td>'.htmlspecialchars($row['security_question']).'</td>
                            </tr>
                            <tr>
                                <th>Security Answer:</th>
                                <td>'.htmlspecialchars($row['security_answer']).'</td>
                            </tr>
                            <tr>
                                <th>Username:</th>
                                <td>'.htmlspecialchars($row['username']).'</td>
                            </tr>
                            <tr>
                                <th>Password:</th>
                                <td>'.htmlspecialchars($row['password']).'</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


           <!-- Edit Modal -->
<div class="modal fade" id="editModal'.$id.'" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Admin</h5>
            </div>
            <div class="modal-body">
                <form action="update_admin.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="admin_id" value="'.$id.'">

                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstname" class="form-control" value="'.$row['firstname'].'">
                    </div>

                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middlename" class="form-control" value="'.$row['middlename'].'">
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastname" class="form-control" value="'.$row['lastname'].'">
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" value="'.$row['address'].'">
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="'.$row['contact_number'].'">
                    </div>

					<div class="form-group">
    					<label>Security Question</label>
    					<input type="text" name="security_question" class="form-control" value="'.$row['security_question'].'" readonly>
                    </div>


                    <div class="form-group">
                        <label>Security Answer</label>
                        <input type="text" name="security_answer" class="form-control" value="'.$row['security_answer'].'">
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="'.$row['username'].'">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="text" name="password" class="form-control" value="'.$row['password'].'">
                    </div>

                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="admin_image" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

    		';
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Output all modals after the table -->
<?php echo $modals; ?>


<?php include ('footer.php'); ?>

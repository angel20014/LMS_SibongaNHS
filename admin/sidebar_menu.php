<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
include('include/dbcon.php');
$admin_id = $_SESSION['id'];

$user_query = mysqli_query($con, "SELECT * FROM admin WHERE admin_id='$admin_id'") or die(mysqli_error($con));
$row = mysqli_fetch_array($user_query);
?>

<div class="col-md-3 left_col">
    <div class="left_col scroll-view">

    <div class="navbar nav_title" style="border: 0; text-align: center;">
    <a href="dashboard.php" class="site_title" style="display: inline-block; text-decoration: none; color: inherit;">
        <span style="display: inline-block; font-size: 14px; line-height: 1.4; font-family: Arial, sans-serif;">
            <strong style="display: block; font-size: 22px; font-family: Verdana, sans-serif;">
                LIBRARY
            </strong>
            <span style="font-size: 16px;">MANAGEMENT SYSTEM</span>
        </span>
    </a>
</div>

        <div class="clearfix"></div>

    <div class="profile">
        <div class="profile_pic">
            <?php
                $admin_img_path = "upload/" . $row['admin_image'];
                if (!empty($row['admin_image']) && file_exists($admin_img_path)): ?>
                    <img src="<?php echo $admin_img_path; ?>" class="profile_img">
                <?php else: ?>
                    <img src="images/user.png" class="profile_img">
            <?php endif; ?>
        </div>
        <div class="profile_info">
            <span>Welcome, <?php echo $row['firstname']; ?></span>
        </div>
    </div>
</a>

<style>
.profile {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.profile_pic {
    margin-top: 10px; /* minimal space */
}

.profile_pic img {
    height: 75px;
    width: 75px;
    border-radius: 50%;
    border: 1px solid rgba(52, 73, 94, 0.44);
    background: #fff;
    object-fit: cover;
}

.profile_info span {
    font-size: 14px;
    color: #ECF0F1;
    font-weight: 300;
    white-space: nowrap;
}

</style>

                    <!-- /menu prile quick info -->


    
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <ul class="nav side-menu">
                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
                    <li><a href="user.php"><i class="fa fa-users"></i> Manage Users</a></li>
                    <li><a href="admin.php"><i class="fa fa-user"></i> Admin</a></li>
                    <li><a href="book.php"><i class="fa fa-book"></i> Manage Books</a></li>
                </ul>

                <ul class="nav side-menu">
                    <li><a href="borrowed.php"><i class="fa fa-book"></i> Borrowed Books</a></li>
                    <li><a href="returned_book.php"><i class="fa fa-book"></i> Returned Books</a></li>
                    <li><a href="penalty_list.php"><i class="fa fa-exclamation-triangle"></i> Penalty List</a></li>
                    <li><a href="report.php"><i class="fa fa-file"></i> Reports</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>


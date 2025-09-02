<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

include('include/dbcon.php');
$student_id = $_SESSION['id'];

// Fetch logged-in student details
$user_query = mysqli_query($con, "SELECT * FROM students WHERE student_id='$student_id'") or die(mysqli_error($con));
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
                    $student_img_path = "upload/" . $row['student_image'];
                    if (!empty($row['student_image']) && file_exists($student_img_path)): ?>
                        <img src="<?php echo $student_img_path; ?>" class="profile_img">
                    <?php else: ?>
                        <img src="images/user.png" class="profile_img">
                <?php endif; ?>
            </div>
            <div class="profile_info">
                <span>Welcome, <?php echo htmlspecialchars($row['firstname']); ?></span>
            </div>
        </div>

        <style>
        .profile {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .profile_pic {
            margin-top: 10px;
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

        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <ul class="nav side-menu">
                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
                    <li><a href="available_books.php"><i class="fa fa-book"></i> Available Books</a></li>
                    <li><a href="my_borrowed_books.php"><i class="fa fa-book"></i> My Borrowed Books</a></li>
                    <li><a href="my_returned_books.php"><i class="fa fa-book"></i> My Returned Books</a></li>
                    <li><a href="profile.php"><i class="fa fa-user"></i> My Profile</a></li>
                    <li><a href="settings.php"><i class="fa fa-cog"></i> Settings</a></li>

                </ul>
            </div>
        </div>

    </div>
</div>

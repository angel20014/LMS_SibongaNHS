<?php
date_default_timezone_set('Asia/Manila');
include('include/dbcon.php');

$student_id = $_SESSION['id']; 

// 🔹 User info
$user_query = mysqli_query($con, "SELECT * FROM students WHERE student_id='$student_id'") or die(mysqli_error($con));
$row = mysqli_fetch_array($user_query);

// 🔹 Main notifications query (all time, for dropdown)
$notif_query = mysqli_query($con, "
    SELECT b.borrow_book_id AS id, bk.book_title, b.date_borrowed AS date_action, b.due_date, NULL AS admin_first, NULL AS admin_middle, NULL AS admin_last, 'Borrowed' AS notif_type
    FROM borrow_book b
    LEFT JOIN book bk ON b.book_id = bk.book_id
    WHERE b.student_id = '$student_id' AND b.borrowed_status = 'borrowed'

    UNION ALL

    SELECT b.borrow_book_id AS id, bk.book_title, b.due_date AS date_action, b.due_date, NULL AS admin_first, NULL AS admin_middle, NULL AS admin_last, 'Overdue' AS notif_type
    FROM borrow_book b
    LEFT JOIN book bk ON b.book_id = bk.book_id
    WHERE b.student_id = '$student_id' AND b.borrowed_status = 'overdue'

    UNION ALL

    SELECT r.return_book_id AS id, bk.book_title, r.date_borrowed AS date_action, NULL AS due_date, NULL AS admin_first, NULL AS admin_middle, NULL AS admin_last, 'Pending' AS notif_type
    FROM return_book r
    LEFT JOIN book bk ON r.book_id = bk.book_id
    WHERE r.student_id = '$student_id' AND r.return_status = 'Pending'

    UNION ALL

    SELECT r.return_book_id AS id, bk.book_title, r.date_returned AS date_action, NULL AS due_date,
           a.firstname AS admin_first, a.middlename AS admin_middle, a.lastname AS admin_last, 'Accepted' AS notif_type
    FROM return_book r
    LEFT JOIN book bk ON r.book_id = bk.book_id
    LEFT JOIN admin a ON r.admin_id = a.admin_id
    WHERE r.student_id = '$student_id' AND r.return_status = 'Accepted'

    ORDER BY date_action DESC
") or die(mysqli_error($con));


// 🔹 TODAY only count for badge
$today_notif_query = mysqli_query($con, "
    SELECT * FROM (
        SELECT b.date_borrowed AS date_action
        FROM borrow_book b
        WHERE b.student_id = '$student_id' AND b.borrowed_status = 'borrowed'

        UNION ALL

        SELECT b.due_date AS date_action
        FROM borrow_book b
        WHERE b.student_id = '$student_id' AND b.borrowed_status = 'overdue'

        UNION ALL

        SELECT r.date_borrowed AS date_action
        FROM return_book r
        WHERE r.student_id = '$student_id' AND r.return_status = 'Pending'

        UNION ALL

        SELECT r.date_returned AS date_action
        FROM return_book r
        WHERE r.student_id = '$student_id' AND r.return_status = 'Accepted'
    ) AS all_notifs
    WHERE DATE(date_action) = CURDATE()
") or die(mysqli_error($con));

$total_notifs = mysqli_num_rows($today_notif_query);
function shortTimeAgo($time) {
    $time = strtotime($time);
    $diff = time() - $time;
    if ($diff < 60) return $diff . "s";
    elseif ($diff < 3600) return floor($diff / 60) . "m";
    elseif ($diff < 86400) return floor($diff / 3600) . "h";
    elseif ($diff < 604800) return floor($diff / 86400) . "d";
    elseif ($diff < 2592000) return floor($diff / 604800) . "w";
    elseif ($diff < 31536000) return floor($diff / 2592000) . "mo";
    else return floor($diff / 31536000) . "y";
}
?>


<!-- top navigation -->
<div class="top_nav">
    <div class="nav_menu">
        <nav role="navigation">
            <div class="nav toggle">
                <a id="menu_toggle" style="display: flex; align-items: center; gap: 5px;">
                    <i class="fa fa-bars"></i>
                    <span>Student</span>
                </a>
            </div>

            <ul class="nav navbar-nav navbar-right">
                <!-- Profile Dropdown -->
                <li>
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown">
                        <?php if (!empty($row['student_image']) && file_exists("upload/" . $row['student_image'])): ?>
                            <img src="upload/<?php echo $row['student_image']; ?>" style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <img src="images/user.png" style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover;">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($row['firstname']); ?>
                        <span class="fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu animated fadeInDown pull-right">
                        <li><a href="profile.php"><i class="fa fa-user pull-right"></i> My Profile</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#logoutModal"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
                    </ul>
                </li>

                <!-- Penalty Notifications -->
<li class="dropdown">
    <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown">
        <i class="fa fa-exclamation-triangle penalty-icon" style="font-size:28px; color:#333; margin-top:7px;"></i>
        <?php 
        $penalty_count = mysqli_num_rows(mysqli_query(
    $con, 
    "SELECT p.* 
     FROM penalties p
     INNER JOIN borrow_book b ON p.borrow_book_id = b.borrow_book_id
     WHERE b.student_id = '$student_id' 
     AND (p.status = 'Pending' OR p.status = 'Requesting Approval')"
));

        if ($penalty_count > 0): ?>
            <span class="badge bg-red"><?php echo $penalty_count; ?></span>
        <?php endif; ?>
    </a>

    <!-- Dropdown -->
    <ul class="dropdown-menu list-unstyled msg_list animated fadeInDown" style="width:400px;">
        <li class="dropdown-header" style="font-weight:bold; font-size:16px; padding:10px; border-bottom:1px solid #ddd;">
            Penalty Messages
        </li>

        <?php
        $penalties = mysqli_query($con, "
            SELECT p.* 
            FROM penalties p
            INNER JOIN borrow_book b ON p.borrow_book_id = b.borrow_book_id
            WHERE b.student_id = '$student_id'
            ORDER BY p.created_at DESC
        ");

        if (mysqli_num_rows($penalties) > 0) {
            while ($p = mysqli_fetch_assoc($penalties)) {
                echo "<li style='padding:10px; border-bottom:1px solid #eee;'>
                        <i class='fa fa-exclamation-circle text-danger'></i> 
                        <b>".htmlspecialchars($p['book_title'])."</b><br>
                        <span>".htmlspecialchars($p['message'])."</span><br>
                        <small><i>Status: ".ucfirst($p['status'])." | ".date('M d, Y', strtotime($p['created_at']))."</i></small><br>";

                // 🔹 Only show button if still pending
                if ($p['status'] == 'Pending') {
    echo "<form method='POST' action='request_penalty.php' style='margin-top:5px;'>
            <input type='hidden' name='penalty_id' value='".$p['id']."'>
            <button type='submit' class='btn btn-xs btn-primary'>
                Request Approval
            </button>
          </form>";
} elseif ($p['status'] == 'Requesting Approval') {
    echo "<span class='label label-warning'>Waiting for Admin Approval</span>";
} elseif ($p['status'] == 'Approved') {
    echo "<span class='label label-success'>Approved</span>";
}


                echo "</li>";
            }
        } else {
            echo "<li style='padding:10px; text-align:center;'>No penalty messages.</li>";
        }
        ?>

        <li>
            <div class="text-center">
                <a href="penalties.php"><strong>See All</strong> <i class="fa fa-angle-right"></i></a>
            </div>
        </li>
    </ul>
</li>


                <!-- Notifications Bell -->
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown">
                        <i class="fa fa-bell" style="font-size:28px; color:#333; margin-top:7px;"></i>
                        <?php if ($total_notifs > 0): ?>
                            <span class="badge bg-red" style="margin-top:4px; margin-right:2px;"><?php echo $total_notifs; ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu list-unstyled msg_list animated fadeInDown" style="width:400px;">
    <li class="dropdown-header" style="font-weight:bold; font-size:16px; padding:10px; border-bottom:1px solid #ddd;">Notifications</li>

    <?php while ($n = mysqli_fetch_assoc($notif_query)) { ?>
        <li>
            <a>
                <span class="image">
                    <?php if ($n['notif_type'] == 'Borrowed') { ?>
                        <i class="fa fa-book text-primary"></i>
                    <?php } elseif ($n['notif_type'] == 'Overdue') { ?>
                        <i class="fa fa-exclamation-circle text-danger"></i>
                    <?php } elseif ($n['notif_type'] == 'Accepted') { ?>
                        <i class="fa fa-check text-success"></i>
                    <?php } elseif ($n['notif_type'] == 'Pending') { ?>
                        <i class="fa fa-clock text-info"></i>
                    <?php } ?>
                </span>

                <span>
                    <?php if ($n['notif_type'] == 'Borrowed') { ?>
                        You borrowed <b><?php echo $n['book_title']; ?></b>.<br>
                        <small>Due: <?php echo date("M d, Y", strtotime($n['due_date'])); ?></small>

                    <?php } elseif ($n['notif_type'] == 'Overdue') { ?>
                        Your book <b><?php echo $n['book_title']; ?></b> is overdue!<br>
                        <small>Due on <?php echo date("M d, Y", strtotime($n['due_date'])); ?></small>

                    <?php } elseif ($n['notif_type'] == 'Pending') { ?>
                        Your return request for <b><?php echo $n['book_title']; ?></b> is still pending.<br>
                        <small>Submitted: <?php echo date("M d, Y - h:i A", strtotime($n['date_action'])); ?></small>

                    <?php } elseif ($n['notif_type'] == 'Accepted') { ?>
                        Your return request for <b><?php echo $n['book_title']; ?></b> was accepted.<br>
                        <small>
                            Accepted by 
                            <?php 
                                // ✅ Format admin name: First M. Last
                                $middleInitial = $n['admin_middle'] ? strtoupper(substr($n['admin_middle'], 0, 1)) . ". " : "";
                                echo $n['admin_first'] . " " . $middleInitial . $n['admin_last']; 
                            ?> 
                            on <?php echo date("M d, Y - h:i A", strtotime($n['date_action'])); ?>
                        </small>
                    <?php } ?>
                </span>
            </a>
        </li>
    <?php } ?>

    <?php if (mysqli_num_rows($notif_query) == 0) { ?>
        <li><div class="text-center"><strong>No Notifications</strong></div></li>
    <?php } ?>

    <li><div class="text-center"><a href="notifications.php"><strong>See All Notifications</strong> <i class="fa fa-angle-right"></i></a></div></li>
</ul>


                </li>
            </ul>
        </nav>
    </div>
</div>


<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
      </div>
      <div class="modal-body">
        Do you really want to log out, <?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <a href="logout.php" class="btn btn-danger">Log Out</a>
      </div>
    </div>
  </div>
</div>
<!-- /top navigation -->

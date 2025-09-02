<?php
date_default_timezone_set('Asia/Manila');
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$admin_id = $_SESSION['id']; 
$user_query = mysqli_query($con, "SELECT * FROM admin WHERE admin_id='$admin_id'") or die(mysqli_error($con));
$row = mysqli_fetch_array($user_query);

$notifications = [];

// 1. Overdue
$o_query = mysqli_query($con, "
    SELECT 'overdue' AS type, bk.book_title, s.firstname, s.lastname, b.due_date AS date_field
    FROM borrow_book b
    LEFT JOIN book bk ON b.book_id = bk.book_id
    LEFT JOIN students s ON b.student_id = s.student_id
    WHERE b.date_returned IS NULL AND b.due_date < NOW()
") or die(mysqli_error($con));
while ($o = mysqli_fetch_assoc($o_query)) $notifications[] = $o;

// 2. Due Soon
$d_query = mysqli_query($con, "
    SELECT 'due_soon' AS type, bk.book_title, s.firstname, s.lastname, b.due_date AS date_field
    FROM borrow_book b
    LEFT JOIN book bk ON b.book_id = bk.book_id
    LEFT JOIN students s ON b.student_id = s.student_id
    WHERE b.date_returned IS NULL AND b.due_date >= NOW() AND b.due_date <= DATE_ADD(NOW(), INTERVAL 1 DAY)
") or die(mysqli_error($con));
while ($d = mysqli_fetch_assoc($d_query)) $notifications[] = $d;

// 3. Pending
$p_query = mysqli_query($con, "
    SELECT 'pending' AS type, bk.book_title, s.firstname, s.lastname, r.date_borrowed AS date_field
    FROM return_book r
    LEFT JOIN book bk ON r.book_id = bk.book_id
    LEFT JOIN students s ON r.student_id = s.student_id
    WHERE r.return_status = 'Pending'
") or die(mysqli_error($con));
while ($p = mysqli_fetch_assoc($p_query)) $notifications[] = $p;

// 4. Borrowed
$b_query = mysqli_query($con, "
    SELECT 'borrowed' AS type, bk.book_title, s.firstname, s.lastname, b.date_borrowed AS date_field
    FROM borrow_book b
    LEFT JOIN book bk ON b.book_id = bk.book_id
    LEFT JOIN students s ON b.student_id = s.student_id
") or die(mysqli_error($con));
while ($b = mysqli_fetch_assoc($b_query)) $notifications[] = $b;

// 5. Returned
$r_query = mysqli_query($con, "
    SELECT 'returned' AS type, bk.book_title, s.firstname, s.lastname, b.date_returned AS date_field
    FROM borrow_book b
    LEFT JOIN book bk ON b.book_id = bk.book_id
    LEFT JOIN students s ON b.student_id = s.student_id
    WHERE b.date_returned IS NOT NULL
") or die(mysqli_error($con));
while ($r = mysqli_fetch_assoc($r_query)) $notifications[] = $r;

// 6. New Books
$nb_query = mysqli_query($con, "
    SELECT 'new_book' AS type, book_title, '' AS firstname, '' AS lastname, date_added AS date_field
    FROM book
") or die(mysqli_error($con));
while ($nb = mysqli_fetch_assoc($nb_query)) $notifications[] = $nb;

// 7. New Students
$ns_query = mysqli_query($con, "
    SELECT 'new_student' AS type, '' AS book_title, firstname, lastname, date_registered AS date_field
    FROM students
") or die(mysqli_error($con));
while ($ns = mysqli_fetch_assoc($ns_query)) $notifications[] = $ns;

// 8. New Teachers
$nt_query = mysqli_query($con, "
    SELECT 'new_teacher' AS type, '' AS book_title, firstname, lastname, date_registered AS date_field
    FROM teachers
") or die(mysqli_error($con));
while ($nt = mysqli_fetch_assoc($nt_query)) $notifications[] = $nt;

// ✅ Sort notifications by latest date_field
usort($notifications, function($a, $b) {
    return strtotime($b['date_field']) - strtotime($a['date_field']);
});

// ✅ Count only TODAY’s notifications for badge
$total_notifs = 0;
foreach ($notifications as $n) {
    if (date("Y-m-d", strtotime($n['date_field'])) == date("Y-m-d")) {
        $total_notifs++;
    }
}
?>

<!-- top navigation -->
<div class="top_nav">
    
    <div class="nav_menu">
        <nav role="navigation">
            <div class="nav toggle">
                <a id="menu_toggle" style="display: flex; align-items: center; gap: 5px; margin-bottom: 15px;">
        <i class="fa fa-bars"></i>
        <span>ADMIN</span>
    </a>
            </div>

            <ul class="nav navbar-nav navbar-right">
                <!-- Profile Dropdown -->
                <li>
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown">
                        <?php if (!empty($row['admin_image']) && file_exists("upload/" . $row['admin_image'])): ?>
                            <img src="upload/<?php echo $row['admin_image']; ?>" style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <img src="images/user.png" style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover;">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($row['firstname']); ?>
                        <span class="fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu animated fadeInDown pull-right">
                        <li><a href="profile.php"><i class="fa fa-user pull-right"></i> My Profile</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#logoutModal"><i class="fa fa-sign-out pull-right"></i> Log Out  </a></li>

                    </ul>
                </li>
<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
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

                <!-- Notifications Bell -->
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" onclick="clearNotifCount()">
    <i class="fa fa-bell" style="font-size:28px; color:#333; margin-top:7px;"></i>
    <?php if ($total_notifs > 0): ?>
        <span id="notif-badge" class="badge bg-red" style="margin-top:4px; margin-right:2px;"><?php echo $total_notifs; ?></span>
    <?php endif; ?>
</a>

                    <!--  Notifications Dropdown -->
<ul class="dropdown-menu list-unstyled msg_list animated fadeInDown" style="min-width:500px; max-width:800px; max-height:500px; overflow-y:auto;">

    <li class="dropdown-header" style="font-weight:bold; font-size:16px; padding:10px; border-bottom:1px solid #ddd;">
        Notifications
    </li>

    <?php 
    $today = date("Y-m-d");
    if ($total_notifs == 0): ?>
        <li><div class="text-center"><strong>No Notifications</strong></div></li>
    <?php else: ?>
        <?php foreach ($notifications as $n): ?>
            <?php $is_today = (date("Y-m-d", strtotime($n['date_field'])) == $today); ?>
             <li style="padding:10px; border-bottom:1px solid #ddd; <?php echo $is_today ? 'background-color:lightgray;  border-radius:5px;' : ''; ?>">
                <a style="display:block; text-decoration:none; color:#333;">
                    
                <?php if ($n['type'] == 'overdue'): ?>
                        <i class="fa fa-exclamation-circle text-danger"></i> 
                        <b><?= $is_today ? "<strong>Overdue (Today)</strong>" : "Overdue"; ?>:</b> <?= $n['book_title']; ?>
                        <br><small>
                            Borrowed by <?= $n['firstname'].' '.$n['lastname']; ?> – 
                            <?= date("M d, Y h:i A", strtotime($n['date_field'])); ?>
                            <?php if ($is_today): ?> <?php endif; ?>
                        </small>

                    <?php elseif ($n['type'] == 'due_soon'): ?>
                        <i class="fa fa-hourglass-half text-warning"></i> 
                        <b><?= $is_today ? "<strong>Due Soon (Today)</strong>" : "Due Soon"; ?>:</b> <?= $n['book_title']; ?>
                        <br><small>
                            Borrowed by <?= $n['firstname'].' '.$n['lastname']; ?> – 
                            <?= date("M d, Y h:i A", strtotime($n['date_field'])); ?>
                            <?php if ($is_today): ?> <?php endif; ?>
                        </small>

                    <?php elseif ($n['type'] == 'pending'): ?>
                        <i class="fa fa-clock text-info"></i> 
                        <b><?= $is_today ? "<strong>Pending Return (Today)</strong>" : "Pending Return"; ?>:</b> <?= $n['book_title']; ?>
                        <br><small>
                            <?= $n['firstname'].' '.$n['lastname']; ?> – 
                            <?= date("M d, Y h:i A", strtotime($n['date_field'])); ?>
                            <?php if ($is_today): ?> <?php endif; ?>
                        </small>

                    <?php elseif ($n['type'] == 'borrowed'): ?>
                        <i class="fa fa-book text-primary"></i> 
                        <b><?= $is_today ? "<strong>Borrowed (Today)</strong>" : "Borrowed"; ?>:</b> <?= $n['book_title']; ?>
                        <br><small>
                            <?= $n['firstname'].' '.$n['lastname']; ?> – 
                            <?= date("M d, Y h:i A", strtotime($n['date_field'])); ?>
                            <?php if ($is_today): ?> <?php endif; ?>
                        </small>

                    <?php elseif ($n['type'] == 'returned'): ?>
                        <i class="fa fa-undo text-success"></i> 
                        <b><?= $is_today ? "<strong>Returned (Today)</strong>" : "Returned"; ?>:</b> <?= $n['book_title']; ?>
                        <br><small>
                            <?= $n['firstname'].' '.$n['lastname']; ?> – 
                            <?= date("M d, Y h:i A", strtotime($n['date_field'])); ?>
                            <?php if ($is_today): ?> <?php endif; ?>
                        </small>

                    <?php elseif ($n['type'] == 'new_book'): ?>
                        <i class="fa fa-plus text-secondary"></i> 
                        <b><?= $is_today ? "<strong>New book added today</strong>" : "New Book"; ?>:</b> <?= $n['book_title']; ?>
                        <br><small>
                            Added – <?= date("M d, Y h:i A", strtotime($n['date_field'])); ?>
                            <?php if ($is_today): ?> <?php endif; ?>
                        </small>

                    <?php elseif ($n['type'] == 'new_student'): ?>
                        <i class="fa fa-user text-success"></i> 
                        <b><?= $is_today ? "<strong>New student registered today)</strong>" : "New Student"; ?>:</b> <?= $n['firstname'].' '.$n['lastname']; ?>
                        <br><small>
                            Registered – <?= date("M d, Y h:i A", strtotime($n['date_field'])); ?>
                            <?php if ($is_today): ?> <?php endif; ?>
                        </small>

                    <?php elseif ($n['type'] == 'new_teacher'): ?>
                        <i class="fa fa-user-tie text-primary"></i> 
                        <b><?= $is_today ? "<strong>New teacher registered today)</strong>" : "New Teacher"; ?>:</b> <?= $n['firstname'].' '.$n['lastname']; ?>
                        <br><small>
                            Registered – <?= date("M d, Y h:i A", strtotime($n['date_field'])); ?>
                            <?php if ($is_today): ?><?php endif; ?>
                        </small>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>

    <li>
        <div class="text-center">
            <a href="notifications_page.php"><strong>See All Notifications</strong> <i class="fa fa-angle-right"></i></a>
        </div>
    </li>
</ul>
             </li>
            </ul>
        </nav>
    </div>
</div>

<script>
function clearNotifCount() {
 
    let badge = document.getElementById("notif-badge");
    if (badge) badge.style.display = "none";

    fetch("mark_read.php");
}
</script>

<!-- /top navigation -->

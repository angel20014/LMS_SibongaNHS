<?php
include('header.php');
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Only allow teachers
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../index.php");
    exit();
}

$teacher_id = $_SESSION['id'];

// Current time
$now = date("M d, Y h:i:s a");

// Fetch borrow, return, and profile registration events
$query = "
SELECT 
    b.book_title,
    br.date_borrowed,
    br.due_date,
    rb.date_returned,
    rb.return_status,
    br.borrowed_status,
    a.firstname AS admin_firstname,
    a.lastname AS admin_lastname
FROM borrow_book br
LEFT JOIN return_book rb ON br.borrow_book_id = rb.borrow_book_id
LEFT JOIN admin a ON rb.admin_id = a.admin_id
JOIN book b ON br.book_id = b.book_id
WHERE br.teacher_id = '$teacher_id'

UNION ALL

SELECT 'Profile Registered' AS book_title,
       t.date_registered AS date_borrowed,
       NULL AS due_date,
       NULL AS date_returned,
       NULL AS return_status,
       'profile_registered' AS borrowed_status,
       NULL AS admin_firstname,
       NULL AS admin_lastname
FROM teachers t
WHERE t.teacher_id = '$teacher_id'

ORDER BY date_borrowed DESC";

$result = mysqli_query($con, $query) or die(mysqli_error($con));
?>

<style>
.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 12px;
    border-bottom: 1px solid #eaeaea;
}
.notification-item:last-child {
    border-bottom: none;
}
.notification-icon {
    font-size: 24px;
    margin-right: 12px;
    color: #007bff;
}
.notification-content {
    flex: 1;
}
.notification-time {
    font-size: 12px;
    color: #777;
}
.notification-title {
    font-weight: bold;
    margin-bottom: 4px;
}
.notification-message {
    margin-bottom: 4px;
}
</style>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> Notifications</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <strong>Notifications</strong>
            </div>
            <div class="card-body p-0">
    <?php
    $today_header_shown = false;
    $earlier_header_shown = false;
    $today_date = date("Y-m-d");

    while ($row = mysqli_fetch_assoc($result)):
        $status = strtolower($row['borrowed_status']);
        $title = htmlspecialchars($row['book_title']);
        
        // Pick the correct date field
        $date_field = $row['date_borrowed'] ?? $row['date_returned'];
        $date_str = $date_field ? date("Y-m-d", strtotime($date_field)) : $today_date;
        $is_today = ($date_str === $today_date);

        switch ($status) {
            case 'borrowed':
                $message = "You borrowed \"$title\".";
                $icon = 'fa-book';
                $time_display = date("M d, Y h:i:s a", strtotime($row['date_borrowed']));
                break;
            case 'returned':
            case 'accepted':
                $admin_name = trim($row['admin_firstname'] . ' ' . $row['admin_lastname']);
                $message = "You returned \"$title\" successfully" . ($admin_name ? " accepted by $admin_name." : "");
                $icon = 'fa-check-circle';
                $time_display = date("M d, Y h:i:s a", strtotime($row['date_returned']));
                break;
            case 'rejected':
                $message = "Your return request for \"$title\" was rejected.";
                $icon = 'fa-times-circle';
                $time_display = date("M d, Y h:i:s a", strtotime($row['date_returned']));
                break;
            case 'profile_registered':
                $message = "You registered your teacher profile.";
                $icon = 'fa-user-plus';
                $time_display = date("M d, Y h:i:s a", strtotime($row['date_borrowed']));
                break;
            default:
                $message = "Notification for \"$title\".";
                $icon = 'fa-info-circle';
                $time_display = $now;
        }

        // Insert section headers
        if ($is_today && !$today_header_shown): ?>
            <div class="px-3 py-2 bg-light font-weight-bold border-bottom">Today</div>
            <?php $today_header_shown = true; ?>
        <?php elseif (!$is_today && !$earlier_header_shown): ?>
            <div class="px-3 py-2 bg-light font-weight-bold border-bottom">Earlier</div>
            <?php $earlier_header_shown = true; ?>
        <?php endif; ?>

        <div class="notification-item <?= $is_today ? 'bg-warning-light' : '' ?>">
            <i class="fa <?= $icon ?> notification-icon"></i>
            <div class="notification-content">
                <div class="notification-title"><?= $title ?></div>
                <div class="notification-message"><?= $message ?></div>
                <div class="notification-time"><?= $time_display ?></div>
            </div>
        </div>
    <?php endwhile; ?>
</div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

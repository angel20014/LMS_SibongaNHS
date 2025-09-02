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

$student_id = $_SESSION['id']; // logged-in student
date_default_timezone_set('Asia/Manila'); // Philippine timezone

// Helper: time ago
if (!function_exists('shortTimeAgo')) {
    function shortTimeAgo($time) {
        $periods = ["second", "minute", "hour", "day", "week", "month", "year"];
        $lengths = [60, 60, 24, 7, 4.35, 12, 10];
        $now = time();
        $difference = $now - strtotime($time);

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }
        $difference = round($difference);
        if ($difference != 1) {
            $periods[$j] .= "s";
        }
        return "$difference $periods[$j] ago";
    }
}

// 🔹 Collect all notifications for this student
$notifications = [];

// Borrow / return notifications (only for this student)
$borrow_return = mysqli_query($con, "
    SELECT b.borrow_book_id, bo.book_title, 
           b.date_borrowed, b.due_date, b.date_returned, b.borrowed_status,
           a.firstname, a.middlename, a.lastname
    FROM borrow_book b
    LEFT JOIN book bo ON b.book_id = bo.book_id
    LEFT JOIN admin a ON b.admin_id = a.admin_id
    WHERE b.student_id = '$student_id'
") or die(mysqli_error($con));


while ($n = mysqli_fetch_assoc($borrow_return)) {
    $notifications[] = [
        'type'   => $n['borrowed_status'], // borrowed | returned
        'title'  => $n['book_title'],
        'date'   => ($n['borrowed_status'] == 'returned') ? $n['date_returned'] : $n['date_borrowed'],
        'due'    => $n['due_date'],
        'id'     => $n['borrow_book_id']
    ];
}

// 🔹 Pending Return Requests (by this student)
$pending_query = mysqli_query($con, "
    SELECT r.return_book_id, bk.book_title, r.date_borrowed
    FROM return_book r
    LEFT JOIN book bk ON r.book_id = bk.book_id
    WHERE r.student_id = '$student_id' AND r.return_status = 'Pending'
") or die(mysqli_error($con));

while ($p = mysqli_fetch_assoc($pending_query)) {
    $notifications[] = [
        'type'   => 'pending',   // mark as pending return
        'title'  => $p['book_title'],
        'date'   => $p['date_borrowed'],
        'due'    => null,
        'id'     => $p['return_book_id']
    ];
}

// 🔹 New books (students also see them)
$new_books = mysqli_query($con, "
    SELECT book_id, book_title, date_added
    FROM book
") or die(mysqli_error($con));

while ($b = mysqli_fetch_assoc($new_books)) {
    $notifications[] = [
        'type'   => 'new_book',
        'title'  => $b['book_title'],
        'date'   => $b['date_added'],
        'due'    => null,
        'id'     => null
    ];
}

// Sort all notifications by date DESC
usort($notifications, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Split into today and earlier
$today_notifications = [];
$earlier_notifications = [];
$today_date = date("Y-m-d");

foreach ($notifications as $n) {
    if (date("Y-m-d", strtotime($n['date'])) === $today_date) {
        $today_notifications[] = $n;
    } else {
        $earlier_notifications[] = $n;
    }
}
?>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> Notifications</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-bell"></i> My Notifications</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

                <?php if (count($notifications) > 0) { ?>

                    <?php if (count($today_notifications) > 0) { ?>
                        <h4 class="text-success">Today</h4>
                        <ul class="list-unstyled msg_list">
                            <?php foreach ($today_notifications as $n) { ?>
                                <li>
                                    <a>
                                        <span class="image">
                                            <?php if ($n['type'] == 'returned') { ?>
                                                <i class="fa fa-undo text-success"></i>
                                            <?php } elseif ($n['type'] == 'borrowed') { ?>
                                                <i class="fa fa-book text-primary"></i>
                                            <?php } elseif ($n['type'] == 'pending') { ?>
                                                <i class="fa fa-clock text-warning"></i>
                                            <?php } elseif ($n['type'] == 'new_book') { ?>
                                                <i class="fa fa-plus-circle text-secondary"></i>
                                            <?php } ?>
                                        </span>
                                        <span>
                                            <?php if ($n['type'] == 'new_book') { ?>
                                                <b>New Book:</b> <?php echo htmlspecialchars($n['title']); ?>
                                            <?php } elseif ($n['type'] == 'pending') { ?>
                                                <b>Pending Return:</b> <?php echo htmlspecialchars($n['title']); ?>
                                            <?php } else { ?>
                                                <b><?php echo ucfirst($n['type']); ?>:</b> <?php echo htmlspecialchars($n['title']); ?>
                                            <?php } ?>
                                        </span>
                                        <span class="message">
                                            <small>
                                                <?php if ($n['type'] == 'borrowed') { ?>
                                                    Borrowed on: <?php echo date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                <?php } elseif ($n['type'] == 'returned') { ?>
                                                    Returned on: <?php echo date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                <?php } elseif ($n['type'] == 'pending') { ?>
                                                    Request made: <?php echo date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                <?php } elseif ($n['type'] == 'new_book') { ?>
                                                    Added on: <?php echo date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                <?php } ?>
                                                (<?php echo shortTimeAgo($n['date']); ?>)
                                            </small>
                                        </span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                        <hr>
                    <?php } ?>

                    <?php if (count($earlier_notifications) > 0) { ?>
                        <h4 class="text-muted">Earlier</h4>
                        <ul class="list-unstyled msg_list">
                            <?php foreach ($earlier_notifications as $n) { ?>
                                <li>
                                    <a>
                                        <span class="image">
                                            <?php if ($n['type'] == 'returned') { ?>
                                                <i class="fa fa-undo text-success"></i>
                                            <?php } elseif ($n['type'] == 'borrowed') { ?>
                                                <i class="fa fa-book text-primary"></i>
                                            <?php } elseif ($n['type'] == 'pending') { ?>
                                                <i class="fa fa-clock text-warning"></i>
                                            <?php } elseif ($n['type'] == 'new_book') { ?>
                                                <i class="fa fa-plus-circle text-secondary"></i>
                                            <?php } ?>
                                        </span>
                                        <span>
                                            <?php if ($n['type'] == 'new_book') { ?>
                                                <b>New Book:</b> <?php echo htmlspecialchars($n['title']); ?>
                                            <?php } elseif ($n['type'] == 'pending') { ?>
                                                <b>Pending Return:</b> <?php echo htmlspecialchars($n['title']); ?>
                                            <?php } else { ?>
                                                <b><?php echo ucfirst($n['type']); ?>:</b> <?php echo htmlspecialchars($n['title']); ?>
                                            <?php } ?>
                                        </span>
                                        <span class="message">
                                            <small>
                                                <?php if ($n['type'] == 'borrowed') { ?>
                                                    Borrowed on: <?php echo date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                <?php } elseif ($n['type'] == 'returned') { ?>
                                                    Returned on: <?php echo date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                <?php } elseif ($n['type'] == 'pending') { ?>
                                                    Request made: <?php echo date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                <?php } elseif ($n['type'] == 'new_book') { ?>
                                                    Added on: <?php echo date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                <?php } ?>
                                                (<?php echo shortTimeAgo($n['date']); ?>)
                                            </small>
                                        </span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>

                <?php } else { ?>
                    <div class="alert alert-info text-center">No notifications yet.</div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

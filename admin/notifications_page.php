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

date_default_timezone_set('Asia/Manila');

// 🔹 Helper: Time Ago
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

// 🔹 Collect all notifications
$notifications = [];

// 1. Borrow / Return
$q1 = mysqli_query($con, "
    SELECT b.borrow_book_id, bo.book_title, s.firstname, s.lastname, 
           b.date_borrowed, b.due_date, b.date_returned, b.borrowed_status
    FROM borrow_book b
    LEFT JOIN students s ON b.student_id = s.student_id
    LEFT JOIN book bo ON b.book_id = bo.book_id
") or die(mysqli_error($con));

while ($r = mysqli_fetch_assoc($q1)) {
    $status = $r['borrowed_status'];
    $date   = ($status == 'returned') ? $r['date_returned'] : $r['date_borrowed'];

    // Mark overdue / due soon
    if ($status == 'borrowed' && strtotime($r['due_date']) < time()) {
        $status = 'overdue';
    } elseif ($status == 'borrowed' && strtotime($r['due_date']) <= strtotime("+2 days")) {
        $status = 'due_soon';
    }

    $notifications[] = [
        'type'  => $status,
        'title' => $r['book_title'],
        'name'  => $r['firstname'] . " " . $r['lastname'],
        'date'  => $date,
        'due'   => $r['due_date'],
        'id'    => $r['borrow_book_id']
    ];
}

// 2. Pending Return Requests
$q2 = mysqli_query($con, "
    SELECT r.return_book_id, bk.book_title, r.date_borrowed, s.firstname, s.lastname
    FROM return_book r
    LEFT JOIN book bk ON r.book_id = bk.book_id
    LEFT JOIN students s ON r.student_id = s.student_id
    WHERE r.return_status = 'Pending'
") or die(mysqli_error($con));

while ($r = mysqli_fetch_assoc($q2)) {
    $notifications[] = [
        'type'  => 'pending',
        'title' => $r['book_title'],
        'name'  => $r['firstname'] . " " . $r['lastname'],
        'date'  => $r['date_borrowed'],
        'due'   => null,
        'id'    => $r['return_book_id']
    ];
}

// 3. New Books
$q3 = mysqli_query($con, "SELECT book_id, book_title, date_added FROM book") or die(mysqli_error($con));
while ($r = mysqli_fetch_assoc($q3)) {
    $notifications[] = [
        'type'  => 'new_book',
        'title' => $r['book_title'],
        'name'  => '',
        'date'  => $r['date_added'],
        'due'   => null,
        'id'    => null
    ];
}

// 4. New Students
$q4 = mysqli_query($con, "SELECT student_id, firstname, lastname, date_registered FROM students") or die(mysqli_error($con));
while ($r = mysqli_fetch_assoc($q4)) {
    $notifications[] = [
        'type'  => 'new_student',
        'title' => $r['firstname'] . " " . $r['lastname'],
        'name'  => '',
        'date'  => $r['date_registered'],
        'due'   => null,
        'id'    => null
    ];
}

// 5. New Teachers
$q5 = mysqli_query($con, "SELECT teacher_id, firstname, lastname, date_registered FROM teachers") or die(mysqli_error($con));
while ($r = mysqli_fetch_assoc($q5)) {
    $notifications[] = [
        'type'  => 'new_teacher',
        'title' => $r['firstname'] . " " . $r['lastname'],
        'name'  => '',
        'date'  => $r['date_registered'],
        'due'   => null,
        'id'    => null
    ];
}

// 🔹 Sort by latest date
usort($notifications, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});


// 🔹 Split Today / Earlier
$today = [];
$earlier = [];
$today_date = date("Y-m-d");

foreach ($notifications as $n) {
    if (date("Y-m-d", strtotime($n['date'])) === $today_date) {
        $today[] = $n;
    } else {
        $earlier[] = $n;
    }
}

// 🔹 Icon map
function notifIcon($type) {
    switch ($type) {
        case 'returned': return '<i class="fa fa-undo text-success"></i>';
        case 'borrowed': return '<i class="fa fa-book text-primary"></i>';
        case 'pending': return '<i class="fa fa-clock text-warning"></i>';
        case 'overdue': return '<i class="fa fa-exclamation-circle text-danger"></i>';
        case 'due_soon': return '<i class="fa fa-hourglass-half text-orange"></i>';
        case 'new_book': return '<i class="fa fa-plus-circle text-secondary"></i>';
        case 'new_student': return '<i class="fa fa-user text-info"></i>';
        case 'new_teacher': return '<i class="fa fa-user-tie text-purple"></i>';
        default: return '<i class="fa fa-bell"></i>';
    }
}
?>

<style>body {
        overflow-y: hidden; 
    }</style>

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
                <h2><i class="fa fa-bell"></i> Notifications</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
            <div style="max-height:400px; overflow-y:auto; padding-right:5px;">

                <?php if (count($notifications) > 0) { ?>

                    <?php if (count($today) > 0) { ?>
                        <h4 class="text-success">Today</h4>
                        <ul class="list-unstyled msg_list">
                            <?php foreach ($today as $n) { ?>
                                <li>
                                    <a>
                                        <span class="image"><?= notifIcon($n['type']); ?></span>
                                        <span>
                                            <b><?= ucfirst(str_replace("_", " ", $n['type'])); ?>:</b> <?= htmlspecialchars($n['title']); ?>
                                        </span>
                                        <span class="message">
                                            <?php if ($n['name']) { ?> By <?= $n['name']; ?><br><?php } ?>
                                            <small>
                                                <?= date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                (<?= shortTimeAgo($n['date']); ?>)
                                            </small>
                                        </span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                        <hr>
                    <?php } ?>

                    <?php if (count($earlier) > 0) { ?>
                        <h4 class="text-muted">Earlier</h4>
                        <ul class="list-unstyled msg_list">
                            <?php foreach ($earlier as $n) { ?>
                                <li>
                                    <a>
                                        <span class="image"><?= notifIcon($n['type']); ?></span>
                                        <span>
                                            <b><?= ucfirst(str_replace("_", " ", $n['type'])); ?>:</b> <?= htmlspecialchars($n['title']); ?>
                                        </span>
                                        <span class="message">
                                            <?php if ($n['name']) { ?> By <?= $n['name']; ?><br><?php } ?>
                                            <small>
                                                <?= date("M d, Y - h:i A", strtotime($n['date'])); ?>
                                                (<?= shortTimeAgo($n['date']); ?>)
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

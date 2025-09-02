<?php
include('header.php');
include('include/dbcon.php');

$student_id = $_SESSION['id'];
$today = date('Y-m-d');

$notif_messages = [];

// ✅ Due date reminders (only books not yet returned)
$due_query = mysqli_query($con, "
    SELECT b.book_title, br.due_date, br.borrowed_status
    FROM borrow_book br
    JOIN book b ON br.book_id = b.book_id
    WHERE br.student_id = '$student_id'
    AND br.due_date IS NOT NULL
    AND br.borrowed_status != 'Returned'
");

while ($row = mysqli_fetch_assoc($due_query)) {
    $dueDate = strtotime($row['due_date']);
    $todayTime = strtotime($today);

    if ($dueDate >= $todayTime) {
        $daysLeft = floor(($dueDate - $todayTime) / (60 * 60 * 24));

        if ($daysLeft == 0) {
            $notif_messages[] = [
                "icon" => "<i class='fa fa-clock text-warning'></i>",
                "msg"  => "You must return <b>" . htmlspecialchars($row['book_title']) . "</b> TODAY (Due date: " . date("M d, Y", $dueDate) . ")."
            ];
        } elseif ($daysLeft > 0) {
            $notif_messages[] = [
                "icon" => "<i class='fa fa-clock text-info'></i>",
                "msg"  => "You have <b>$daysLeft day(s)</b> left to return <b>" . htmlspecialchars($row['book_title']) . "</b> (Due date: " . date("M d, Y", $dueDate) . ")."
            ];
        }
    }
}

// ✅ Penalty messages (joined via borrow_book_id)
$penalty_query = mysqli_query($con, "
    SELECT p.book_title, p.message, p.status, p.created_at
    FROM penalties p
    JOIN borrow_book br ON p.borrow_book_id = br.borrow_book_id
    WHERE br.student_id = '$student_id'
    ORDER BY p.created_at DESC
");

while ($penalty = mysqli_fetch_assoc($penalty_query)) {
    $notif_messages[] = [
        "icon" => "<i class='fa fa-exclamation-triangle text-danger'></i>",
        "msg"  => "📌 Penalty for <b>" . htmlspecialchars($penalty['book_title']) . "</b>: " 
                . htmlspecialchars($penalty['message']) 
                . " <span class='badge badge-secondary'>Status: " . ucfirst($penalty['status']) . "</span>"
    ];
}
?>



<style>
    body {
        overflow-y: auto;
    }
    .tile-container {
        background-color: #f2f2f2;
        border-radius: 10px;
        padding: 35px;
        margin: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }
    .count {
        font-size: 50px;
        font-weight: bold;
        color: green;
        margin-top: 10px;
    }
    .count_top {
        font-size: 20px;
        font-weight: bold;
    }
    .tile_row {
        display: flex;
        flex-wrap: wrap;
        padding: 10px;
    }
    .tile_stats_count {
        min-width: 160px;
        flex: 0 0 auto;
    }
    .recent-table {
        background: white;
        padding: 15px;
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .recent-table h4 {
        margin-bottom: 15px;
    }

    .notif-container {
    position: fixed;
    top: 70px; /* adjust if navbar exists */
    left: 50%;
    transform: translateX(-50%);
    width: 350px;
    z-index: 2000;
    text-align: center;
}

/* Each notification box */
.notif-box {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    padding: 6px 15px;
    margin-bottom: 12px;
    font-size: 10px;
    text-align: left;
    animation: slideDown 0.4s ease;
    position: relative;
}

/* Icon & text inline */
.notif-box span {
    display: inline-block;
    vertical-align: middle;
}

.notif-close {
    position: absolute;
    top: 6px;
    right: 10px;
    cursor: pointer;
    font-size: 16px;
    color: #888;
}

.notif-close:hover {
    color: #000;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="notif-container" id="notifContainer">
    <?php foreach ($notif_messages as $index => $notif): ?>
        <div class="notif-box" id="notif-<?php echo $index; ?>">
            <span><?php echo $notif["icon"]; ?></span>
            <span><?php echo $notif["msg"]; ?></span>
            <span class="notif-close" onclick="closeNotif(<?php echo $index; ?>)">✖</span>
        </div>
    <?php endforeach; ?>
</div>

<div class="tile_row">

    <!-- Books Borrowed by Student -->
    <div class="tile_stats_count tile-container">
        <?php 
        $borrow_result = mysqli_query($con, "SELECT * FROM borrow_book WHERE student_id='$student_id'");
        ?>
        <span class="count_top"><i class="fa fa-book"></i> My Borrowed Books</span>
        <div class="count"><?php echo mysqli_num_rows($borrow_result); ?></div>
    </div>

    <!-- Books Returned by Student -->
<div class="tile_stats_count tile-container">
    <?php 
    $return_result = mysqli_query($con, "
        SELECT * FROM return_book 
        WHERE student_id='$student_id' 
        AND return_status != 'pending'
    ");
    ?>
    <span class="count_top"><i class="fa fa-book"></i> My Returned Books</span>
    <div class="count"><?php echo mysqli_num_rows($return_result); ?></div>
</div>


    <!-- Pending Return Requests -->
    <div class="tile_stats_count tile-container">
        <?php 
       $pending_return_result = mysqli_query($con, "SELECT * FROM return_book WHERE student_id='$student_id' AND return_status='pending'");
        ?>
        <span class="count_top"><i class="fa fa-clock-o"></i> Pending Returns</span>
        <div class="count"><?php echo mysqli_num_rows($pending_return_result); ?></div>
    </div>

</div>

<div class="row">

    <!-- Recently Borrowed Books by Student -->
    <div class="col-md-4">
        <div class="recent-table">
            <h4>📖 My Books Borrowed Today</h4>
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date Borrowed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $today = date('Y-m-d');
                    $borrow_query = mysqli_query($con, "
                        SELECT b.book_title, br.date_borrowed
                        FROM borrow_book br
                        JOIN book b ON br.book_id = b.book_id
                        WHERE br.student_id = '$student_id'
                        AND DATE(br.date_borrowed) = '$today'
                        ORDER BY br.date_borrowed DESC
                    ");

                    if (mysqli_num_rows($borrow_query) == 0) {
                        echo '<tr><td colspan="2" class="text-center">No books borrowed today.</td></tr>';
                    } else {
                        while ($borrow = mysqli_fetch_assoc($borrow_query)) {
                            echo "<tr>
                                <td>".htmlspecialchars($borrow['book_title'])."</td>
                                <td>".htmlspecialchars($borrow['date_borrowed'])."</td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recently Returned Books by Student -->
    <div class="col-md-4">
        <div class="recent-table">
            <h4>📚 My Books Returned Today</h4>
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date Returned</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $return_query = mysqli_query($con, "
    SELECT b.book_title, r.date_returned
    FROM return_book r
    JOIN book b ON r.book_id = b.book_id
    WHERE r.student_id = '$student_id'
    AND r.return_status != 'pending'
    AND DATE(r.date_returned) = '$today'
    ORDER BY r.date_returned DESC
");


                    if (mysqli_num_rows($return_query) == 0) {
                        echo '<tr><td colspan="2" class="text-center">No books returned today.</td></tr>';
                    } else {
                        while ($return = mysqli_fetch_assoc($return_query)) {
                            echo "<tr>
                                <td>".htmlspecialchars($return['book_title'])."</td>
                                <td>".htmlspecialchars($return['date_returned'])."</td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pending Return Requests -->
    <div class="col-md-4">
        <div class="recent-table">
            <h4>⏳ My Pending Return Requests</h4>
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date Requested</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pending_return_query = mysqli_query($con, "
                    SELECT b.book_title, r.date_returned
                    FROM return_book r
                    JOIN book b ON r.book_id = b.book_id
                    WHERE r.student_id = '$student_id'
                    AND r.return_status = 'pending'
                    ORDER BY r.date_returned DESC
                    ");


                    if (mysqli_num_rows($pending_return_query) == 0) {
                        echo '<tr><td colspan="2" class="text-center">No pending return requests.</td></tr>';
                    } else {
                        while ($pending = mysqli_fetch_assoc($pending_return_query)) {
                            // Show date_returned as "Date Requested" for pending returns
                            $date_requested = $pending['date_returned'] ? $pending['date_returned'] : '-';
                            echo "<tr>
                                <td>".htmlspecialchars($pending['book_title'])."</td>
                                <td>".htmlspecialchars($date_requested)."</td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
            
        </div>
    </div>

</div>
<script>
function closeNotif(id) {
    var notif = document.getElementById("notif-" + id);
    if (notif) {
        notif.style.display = "none";
    }
}
</script>
<?php include('footer.php'); ?>

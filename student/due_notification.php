<?php
include('include/dbcon.php');
$student_id = $_SESSION['id'];

// Today's date
$today = date('Y-m-d');

// Initialize notifications array
$notifications = [];

// 1. Books borrowed today
$borrow_query = mysqli_query($con, "
    SELECT b.book_title, br.date_borrowed
    FROM borrow_book br
    JOIN book b ON br.book_id = b.book_id
    WHERE br.student_id = '$student_id'
    AND DATE(br.date_borrowed) = '$today'
");
while ($borrow = mysqli_fetch_assoc($borrow_query)) {
    $notifications[] = [
        'type' => 'borrowed',
        'message' => 'You borrowed "' . htmlspecialchars($borrow['book_title']) . '".',
        'date' => date("M d, Y h:i A", strtotime($borrow['date_borrowed']))
    ];
}

// 2. Books returned (accepted) today
$return_query = mysqli_query($con, "
    SELECT b.book_title, r.date_returned
    FROM return_book r
    JOIN book b ON r.book_id = b.book_id
    WHERE r.student_id = '$student_id'
    AND r.return_status = 'accepted'
    AND DATE(r.date_returned) = '$today'
");
while ($return = mysqli_fetch_assoc($return_query)) {
    $notifications[] = [
        'type' => 'returned',
        'message' => 'Your return of "' . htmlspecialchars($return['book_title']) . '" was accepted.',
        'date' => date("M d, Y h:i A", strtotime($return['date_returned']))
    ];
}

// 3. Pending return requests
$pending_query = mysqli_query($con, "
    SELECT b.book_title, r.date_returned
    FROM return_book r
    JOIN book b ON r.book_id = b.book_id
    WHERE r.student_id = '$student_id'
    AND r.return_status = 'pending'
");
while ($pending = mysqli_fetch_assoc($pending_query)) {
    $date_requested = $pending['date_returned'] ? $pending['date_returned'] : '-';
    $notifications[] = [
        'type' => 'pending',
        'message' => 'Pending return request for "' . htmlspecialchars($pending['book_title']) . '".',
        'date' => $date_requested
    ];
}

// 4. Books due today
$due_query = mysqli_query($con, "
    SELECT b.book_title, br.due_date
    FROM borrow_book br
    JOIN book b ON br.book_id = b.book_id
    WHERE br.student_id = '$student_id'
    AND DATE(br.due_date) = '$today'
");
while ($due = mysqli_fetch_assoc($due_query)) {
    $notifications[] = [
        'type' => 'due',
        'message' => '"' . htmlspecialchars($due['book_title']) . '" is due today.',
        'date' => date("M d, Y", strtotime($due['due_date']))
    ];
}


// Display notifications inside a container
if (!empty($notifications)):
?>
<div class="container mt-3">
    <?php foreach ($notifications as $note): ?>
        <?php
        // Set alert class based on type
        switch ($note['type']) {
            case 'borrowed': $alert_class = 'alert-primary'; break;
            case 'returned': $alert_class = 'alert-success'; break;
            case 'pending': $alert_class = 'alert-warning'; break;
            case 'due': $alert_class = 'alert-danger'; break;
            default: $alert_class = 'alert-info';
        }
        ?>
        <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
            <?php echo $note['message']; ?> 
            <small class="text-muted"><?php echo $note['date']; ?></small>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endforeach; ?>
</div>

<!-- Include Bootstrap JS if not already included -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<?php endif; ?>

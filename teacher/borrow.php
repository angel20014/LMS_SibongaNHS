<?php 
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../index.php");
    exit();
}

$teacher_id = intval($_SESSION['id']); 
$book_id    = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

// Force quantity to 1
$qty = 1;

if ($book_id <= 0) {
    die("Invalid request.");
}

// ✅ Check for overdue or pending returns
$pendingQuery = mysqli_query($con, "
    SELECT COUNT(DISTINCT bb.book_id) AS borrowed_count
    FROM borrow_book bb
    LEFT JOIN return_book rb ON bb.borrow_book_id = rb.borrow_book_id
    WHERE bb.teacher_id = $teacher_id
      AND (bb.borrowed_status = 'borrowed' OR rb.return_status = 'Pending')
");
$pendingData = mysqli_fetch_assoc($pendingQuery);

// Check if teacher has 1 or more borrowed books
if ($pendingData['borrowed_count'] >= 1) {
   $message = "You cannot borrow more than 1 book at a time. Please return your borrowed book first. If you have a pending return, please contact the librarian to approve your return request.";
    header("Location: available_books.php?error=" . urlencode($message));
    exit();
}

// ✅ Check book availability
$checkBook = mysqli_query($con, "SELECT book_title, book_copies FROM book WHERE book_id = $book_id");
if (!$checkBook || mysqli_num_rows($checkBook) === 0) {
    die("Book not found.");
}
$book = mysqli_fetch_assoc($checkBook);

if ($book['book_copies'] < $qty) {
    $message = "Not enough copies available. Only {$book['book_copies']} left.";
    header("Location: available_books.php?error=" . urlencode($message));
    exit();
}

// ✅ Compute due date (1 day only)
$due_date = date('Y-m-d', strtotime('+1 day'));

// ✅ Insert into borrow_book
$insert = mysqli_query($con, "
    INSERT INTO borrow_book (book_id, teacher_id, date_borrowed, due_date, borrowed_status, quantity)
    VALUES ($book_id, $teacher_id, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), 'borrowed', $qty)
") or die(mysqli_error($con));

// ✅ Deduct from book stock
$update = mysqli_query($con, "
    UPDATE book 
    SET book_copies = book_copies - $qty 
    WHERE book_id = $book_id
") or die(mysqli_error($con));

// ✅ Redirect back with success message
$message = "You successfully borrowed '{$book['book_title']}'. Due date is on $due_date.";
header("Location: available_books.php?success=" . urlencode($message));
exit();

?>

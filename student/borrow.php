<?php
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Only allow logged-in students
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = intval($_SESSION['id']); 
$book_id    = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

if ($book_id <= 0) {
    die("Invalid request.");
}

// ✅ Check if student has an overdue book
$checkOverdue = mysqli_query($con, "
    SELECT * FROM borrow_book 
    WHERE student_id = $student_id 
      AND borrowed_status = 'borrowed'
      AND due_date < NOW()
      AND date_returned IS NULL
");
if ($checkOverdue && mysqli_num_rows($checkOverdue) > 0) {
    $message = "You cannot borrow a new book until you return your overdue book.";
    header("Location: available_books.php?error=" . urlencode($message));
    exit();
}

// ✅ Check if student already borrowed a book (limit = 1)
$checkBorrow = mysqli_query($con, "
    SELECT * FROM borrow_book 
    WHERE student_id = $student_id 
      AND borrowed_status = 'borrowed'
");
if ($checkBorrow && mysqli_num_rows($checkBorrow) > 0) {
    $message = "You are only allowed to borrow 1 book at a time. Please return your current book before borrowing another.";
    header("Location: available_books.php?error=" . urlencode($message));
    exit();
}

// ✅ Check book availability
$checkBook = mysqli_query($con, "SELECT book_title, book_copies FROM book WHERE book_id = $book_id");
if (!$checkBook || mysqli_num_rows($checkBook) === 0) {
    die("Book not found.");
}
$book = mysqli_fetch_assoc($checkBook);

if ($book['book_copies'] < 1) {
    $message = "Not enough copies available. Only {$book['book_copies']} left.";
    header("Location: available_books.php?error=" . urlencode($message));
    exit();
}

// ✅ Compute due date (1 day from now)
$due_date = date('Y-m-d', strtotime('+1 day'));

// ✅ Insert into borrow_book (only 1 book allowed)
$insert = mysqli_query($con, "
    INSERT INTO borrow_book (book_id, student_id, date_borrowed, due_date, borrowed_status, quantity)
    VALUES ($book_id, $student_id, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), 'borrowed', 1)
") or die(mysqli_error($con));


// ✅ Deduct from book stock
$update = mysqli_query($con, "
    UPDATE book 
    SET book_copies = book_copies - 1 
    WHERE book_id = $book_id
") or die(mysqli_error($con));

// ✅ Redirect back with success message
$message = "You successfully borrowed '{$book['book_title']}'. Due date is on $due_date.";
header("Location: available_books.php?success=" . urlencode($message));
exit();
?>

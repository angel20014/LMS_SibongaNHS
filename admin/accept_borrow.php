<?php
include('include/dbcon.php');

if (isset($_GET['borrow_id'])) {
    $borrow_id = intval($_GET['borrow_id']);

    // Update the borrow_book status to accepted
    $sql = "UPDATE borrow_book SET borrowed_status = 'accepted' WHERE borrow_book_id = $borrow_id";

    if (mysqli_query($con, $sql)) {
        echo "<script>alert('Borrow request accepted successfully.'); window.location='borrowed_books.php';</script>";
    } else {
        echo "<script>alert('Error accepting borrow request: " . mysqli_error($con) . "'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.history.back();</script>";
}
?>

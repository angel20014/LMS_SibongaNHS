<?php
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$admin_id = intval($_SESSION['id']); // ✅ Logged-in admin ID

if (isset($_GET['return_ids']) && isset($_GET['status'])) {
    $return_ids_raw = $_GET['return_ids'];
    $status = mysqli_real_escape_string($con, $_GET['status']);

    if (in_array($status, ['Accepted', 'Rejected'])) {

        // Sanitize IDs
        $return_ids_raw = preg_replace('/[^0-9,]/', '', $return_ids_raw);
        $return_ids = array_filter(array_map('intval', explode(',', $return_ids_raw)));

        if (!empty($return_ids)) {
            $ids_list = implode(',', $return_ids);

            // ✅ Get related book info
            $result = mysqli_query($con, "
                SELECT 
                    rb.return_book_id,
                    rb.book_id,
                    rb.borrow_book_id,
                    rb.return_status,
                    COALESCE(rb.quantity, bb.quantity, 1) AS quantity
                FROM return_book rb
                LEFT JOIN borrow_book bb ON rb.borrow_book_id = bb.borrow_book_id
                WHERE rb.return_book_id IN ($ids_list)
            ") or die(mysqli_error($con));

            $books_to_update = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $books_to_update[] = $row;
            }

            // ✅ Update return_book table with status
            mysqli_query($con, "
                UPDATE return_book 
                SET return_status = '$status', date_returned = NOW(), admin_id = $admin_id
                WHERE return_book_id IN ($ids_list)
            ") or die(mysqli_error($con));

            // ✅ If Accepted, update borrow_book + restore book copies
            if ($status === 'Accepted') {
                foreach ($books_to_update as $book) {
                    $book_id = (int)$book['book_id'];
                    $borrow_book_id = (int)$book['borrow_book_id'];
                    $qty = (int)$book['quantity'];
                    $prev_status = $book['return_status'];

                    // ⚠️ Prevent double increment if already accepted before
                    if ($prev_status !== 'Accepted') {

                        // Mark borrow_book as returned
                        mysqli_query($con, "
                            UPDATE borrow_book 
                            SET borrowed_status = 'returned', date_returned = NOW()
                            WHERE borrow_book_id = $borrow_book_id
                        ") or die(mysqli_error($con));

                        // Restore copies
                        mysqli_query($con, "
                            UPDATE book
                            SET book_copies = book_copies + $qty
                            WHERE book_id = $book_id
                        ") or die(mysqli_error($con));
                    }
                }
            }
        }
    }
}

header("Location: returned_book.php");
exit();
?>

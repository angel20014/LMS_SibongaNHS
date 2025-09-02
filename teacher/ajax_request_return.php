<?php
include('include/dbcon.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$teacher_id = intval($_SESSION['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_book_id'])) {
    $borrow_id = intval($_POST['borrow_book_id']);

    // Fetch the borrow record
    $borrow_query = mysqli_query($con, "
        SELECT * FROM borrow_book 
        WHERE borrow_book_id = $borrow_id AND teacher_id = $teacher_id
    ") or die(mysqli_error($con));

    if ($borrow_query && mysqli_num_rows($borrow_query) === 1) {
        $borrow_data = mysqli_fetch_assoc($borrow_query);

        // Check if a return request already exists
        $existing_return_query = mysqli_query($con, "
            SELECT * FROM return_book WHERE borrow_book_id = $borrow_id
        ") or die(mysqli_error($con));

        if ($existing_return_query && mysqli_num_rows($existing_return_query) === 0) {
            $quantity = intval($borrow_data['quantity']); // Include borrowed quantity

            // Insert return request
            $insert_query = mysqli_query($con, "
                INSERT INTO return_book 
                (borrow_book_id, book_id, admin_id, teacher_id, date_borrowed, due_date, return_status, quantity)
                VALUES (
                    {$borrow_data['borrow_book_id']},
                    {$borrow_data['book_id']},
                    {$borrow_data['admin_id']},
                    $teacher_id,
                    '{$borrow_data['date_borrowed']}',
                    '{$borrow_data['due_date']}',
                    'Pending',
                    $quantity
                )
            ") or die(mysqli_error($con));

            if ($insert_query) {
                // Update borrow status to pending_return
                mysqli_query($con, "
                    UPDATE borrow_book 
                    SET borrowed_status = 'pending_return' 
                    WHERE borrow_book_id = $borrow_id
                ") or die(mysqli_error($con));

                echo json_encode(['status' => 'success', 'message' => 'Return requested successfully']);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to request return']);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Return request already exists']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Borrow record not found']);
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
exit();
?>

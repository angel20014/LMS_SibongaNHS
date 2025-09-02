<?php
include('include/dbcon.php');
header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id     = $_POST['borrow_id'] ?? null;
    $borrower_name = $_POST['borrower_name'] ?? '';
    $borrower_type = $_POST['borrower_type'] ?? '';
    $book_title    = $_POST['book_title'] ?? '';
    $due_date      = $_POST['due_date'] ?? '';
    $message       = $_POST['message'] ?? '';

    if (!$borrow_id || !$borrower_name || !$book_title || !$message) {
        echo json_encode([
            "success" => false,
            "error"   => "Missing required fields"
        ]);
        exit;
    }

    // Insert penalty record
    $stmt = $con->prepare("
        INSERT INTO penalties 
            (borrow_book_id, borrower_name, borrower_type, book_title, due_date, message, created_at, status) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Pending')
    ");
    $stmt->bind_param("isssss", $borrow_id, $borrower_name, $borrower_type, $book_title, $due_date, $message);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode([
            "success" => false,
            "error"   => $stmt->error
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        "success" => false,
        "error"   => "Invalid request method"
    ]);
}
?>

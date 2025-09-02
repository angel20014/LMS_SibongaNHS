<?php
include('include/dbcon.php');
header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id = $_POST['borrow_id'] ?? null;

    if (!$borrow_id) {
        echo json_encode(["success" => false, "error" => "Missing borrow_id"]);
        exit;
    }

    // Check if penalty exists for this borrow_book_id
    $check = $con->prepare("SELECT id, status FROM penalties WHERE borrow_book_id = ?");
    $check->bind_param("i", $borrow_id);
    $check->execute();
    $res = $check->get_result();
    $penalty = $res->fetch_assoc();
    $check->close();

    if (!$penalty) {
        echo json_encode(["success" => false, "error" => "No penalty found for this borrow_book_id"]);
        exit;
    }

    if ($penalty['status'] === 'Approved') {
        echo json_encode(["success" => true, "message" => "Already approved"]);
        exit;
    }

    // Update penalty status to Approved
    $stmt = $con->prepare("UPDATE penalties SET status = 'Approved' WHERE borrow_book_id = ?");
    $stmt->bind_param("i", $borrow_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error ?: "No rows updated. Maybe already approved?"
        ]);
    }

    $stmt->close();
}
?>

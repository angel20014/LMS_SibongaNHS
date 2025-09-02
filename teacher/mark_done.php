<?php
include('include/dbcon.php'); // your database connection

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['penaltyId'])){
    echo json_encode(['success'=>false, 'message'=>'Invalid input']);
    exit;
}

$penaltyId = intval($data['penaltyId']);

// Update status to 'Done'
$update = "UPDATE penalties SET status='Done' WHERE id=?";
$stmt = $conn->prepare($update);
$stmt->bind_param("i", $penaltyId);

if($stmt->execute()){
    echo json_encode(['success'=>true, 'message'=>'Task marked as done. Admin will approve.']);
} else {
    echo json_encode(['success'=>false, 'message'=>'Failed to update status']);
}

$stmt->close();
$conn->close();
?>

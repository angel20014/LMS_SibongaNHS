<?php
include('include/dbcon.php'); // your database connection

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['id'])){
    echo json_encode(['success'=>false, 'message'=>'Invalid input']);
    exit;
}

$penaltyId = intval($data['id']);

// Update status to 'Approved'
$update = "UPDATE penalties SET status='Approved' WHERE id=?";
$stmt = $conn->prepare($update);
$stmt->bind_param("i", $penaltyId);

if($stmt->execute()){
    echo json_encode(['success'=>true, 'message'=>'Task approved successfully']);
} else {
    echo json_encode(['success'=>false, 'message'=>'Failed to approve task']);
}

$stmt->close();
$conn->close();
?>

<?php
include('include/dbcon.php');

if(isset($_POST['borrow_ids'])){
    $borrow_ids = $_POST['borrow_ids'];
    
    $query = "SELECT b.book_title, bb.quantity, bb.date_borrowed, bb.due_date, bb.borrowed_status
              FROM borrow_book bb
              LEFT JOIN book b ON bb.book_id = b.book_id
              WHERE bb.borrow_book_id IN ($borrow_ids)";
    
    $result = mysqli_query($con, $query);
    $data = [];
    
    while($row = mysqli_fetch_assoc($result)){
        // Format dates
        $row['date_borrowed'] = date("M d, Y h:i A", strtotime($row['date_borrowed']));
        $row['due_date'] = date("M d, Y h:i A", strtotime($row['due_date']));
        $data[] = $row;
    }
    
    echo json_encode($data);
}
?>

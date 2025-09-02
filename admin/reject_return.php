<?php
include('include/dbcon.php');

if (isset($_GET['return_id'])) {
    $return_id = intval($_GET['return_id']);

    $update = mysqli_query($con, "UPDATE return_book SET return_status = 'Rejected' WHERE return_book_id = $return_id");

    if ($update) {
        
        header("Location: returned_book.php?msg=rejected");
        exit();
    } else {
        echo "Error rejecting return: " . mysqli_error($con);
    }
} else {
    echo "No return ID specified.";
}

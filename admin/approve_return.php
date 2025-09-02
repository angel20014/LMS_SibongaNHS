<?php
include('include/dbcon.php');

if (isset($_GET['return_id'])) {
    $return_id = intval($_GET['return_id']);

    $update = mysqli_query($con, "UPDATE return_book SET return_status = 'Approved' WHERE return_book_id = $return_id");

    if ($update) {
       
        header("Location: returned_book.php?msg=approved");
        exit();
    } else {
        echo "Error approving return: " . mysqli_error($con);
    }
} else {
    echo "No return ID specified.";
}

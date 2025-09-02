<?php
include('include/dbcon.php');

$datefrom = mysqli_real_escape_string($con, $_POST['datefrom'] ?? date('Y-m-d'));
$dateto = mysqli_real_escape_string($con, $_POST['dateto'] ?? date('Y-m-d'));

$status = $_POST['status'];

$where = "WHERE date(date_transaction) BETWEEN '$datefrom' AND '$dateto'";
if ($status !== "---All---") {
    $where .= " AND detail_action = '$status'";
}

$result = mysqli_query($con,"SELECT * FROM report 
    LEFT JOIN book ON report.book_id = book.book_id 
    LEFT JOIN user ON report.user_id = user.user_id 
    $where 
    ORDER BY report.report_id DESC") or die(mysqli_error());

echo '<div class="table-responsive"><table class="table table-striped">';
echo '<thead><tr><th>Members Name</th><th>Book Title</th><th>Task</th><th>Person In Charge</th><th>Date Transaction</th></tr></thead>';
echo '<tbody>';

while ($row = mysqli_fetch_array($result)) {
    $user_name = $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname'];
    echo '<tr>';
    echo '<td>' . $user_name . '</td>';
    echo '<td>' . $row['book_title'] . '</td>';
    echo '<td>' . $row['detail_action'] . '</td>';
    echo '<td>' . $row['admin_name'] . '</td>';
    echo '<td>' . date("M d, Y h:i:s a", strtotime($row['date_transaction'])) . '</td>';
    echo '</tr>';
}
echo '</tbody></table></div>';
?>

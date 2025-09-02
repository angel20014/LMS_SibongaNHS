<?php 
include('session.php');
include('include/dbcon.php');

// Get filters
$datefrom = !empty($_POST['datefrom']) ? $_POST['datefrom'] : date('Y-m-d');
$dateto   = !empty($_POST['dateto']) ? $_POST['dateto'] : date('Y-m-d');
$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : 'all';

$datefrom = mysqli_real_escape_string($con, $datefrom);
$dateto   = mysqli_real_escape_string($con, $dateto);
$report_type = mysqli_real_escape_string($con, $report_type);

// Fetch transactions between selected dates
$query = mysqli_query($con, "
    SELECT bb.borrow_book_id, bb.book_id, bb.student_id, bb.teacher_id, bb.date_borrowed, bb.due_date, bb.borrowed_status,
           rb.return_book_id, rb.date_returned, rb.return_status,
           b.book_title,
           s.firstname AS student_first, s.middlename AS student_middle, s.lastname AS student_last,
           t.firstname AS teacher_first, t.middlename AS teacher_middle, t.lastname AS teacher_last,
           a.firstname AS admin_first, a.middlename AS admin_middle, a.lastname AS admin_last
    FROM borrow_book bb
    LEFT JOIN return_book rb ON bb.borrow_book_id = rb.borrow_book_id
    LEFT JOIN book b ON bb.book_id = b.book_id
    LEFT JOIN students s ON bb.student_id = s.student_id
    LEFT JOIN teachers t ON bb.teacher_id = t.teacher_id
    LEFT JOIN admin a ON a.admin_id = rb.admin_id OR a.admin_id = bb.admin_id
    WHERE (DATE(bb.date_borrowed) BETWEEN '$datefrom' AND '$dateto')
       OR (DATE(rb.date_returned) BETWEEN '$datefrom' AND '$dateto')
    ORDER BY bb.borrow_book_id DESC
") or die(mysqli_error($con));
?>

<!DOCTYPE html>
<html>
<head>
    <title>SNHS Library Management System - Report Print</title>
    <style>
        .container { width:100%; margin:auto; }
        table { width:100%; border-collapse: collapse; margin-top:20px; }
        table th, table td { border:1px solid #000; padding:8px; text-align:center; }
        .table-striped tbody tr:nth-child(odd) { background-color:#f9f9f9; }
        @media print { #print { display:none; } }
        #print { width:90px; height:30px; font-size:16px; background:white; border-radius:4px; margin:20px; cursor:pointer; border:1px solid #000; }
        .line1, .line2, .line3, .line4 { text-align:center; margin:0; line-height:1.5; }
        .line1 { font-family:'Times New Roman'; font-size:16px; }
        .line2 { font-family:'Old English Text MT'; font-size:22px; }
        .line3 { font-family:'Arial Black'; font-size:16px; }
        .line4 { font-family:'Georgia'; font-size:14px; }
    </style>
    <script>
        function printPage() { window.print(); }
    </script>
</head>
<body>
<div class="container">
    <img src="images/logo1.png" style="float:left; width:100px; height:100px;">
    <img src="images/logo.png" style="float:right; width:100px; height:100px;">
    <p class="line1">Republic of the Philippines</p>
    <p class="line2">Department of Education</p>
    <p class="line3">SIBONGA NATIONAL HIGH SCHOOL</p>
    <p class="line4">Poblacion, Sibonga, Cebu</p>
    <div style="clear:both;"></div>

    <button id="print" onclick="printPage()">Print</button>

    <p style="margin-top:40px; font-size:14pt; font-weight:bold; margin-left: 400px;">Library Transactions Report</p>
    <b style="float:right;">Date Prepared: <?php include('currentdate.php'); ?></b>
    <p><b>Date From:</b> <?php echo date("F d, Y", strtotime($datefrom)); ?> to <?php echo date("F d, Y", strtotime($dateto)); ?></p>
    <p><b>Report Type:</b> 
        <?php 
            if ($report_type == "borrowed") echo "Borrowed Only";
            elseif ($report_type == "returned") echo "Returned Only";
            else echo "All Transactions";
        ?>
    </p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Book Title</th>
                <th>Task</th>
                <th>Person In Charge</th>
                <th>Date Transaction</th>
            </tr>
        </thead>
        <tbody>
        <?php
        while ($row = mysqli_fetch_assoc($query)):
            // Borrower Name
            if (!empty($row['student_first'])) {
                $member_name = $row['student_first'].' '.$row['student_middle'].' '.$row['student_last'];
            } elseif (!empty($row['teacher_first'])) {
                $member_name = $row['teacher_first'].' '.$row['teacher_middle'].' '.$row['teacher_last'];
            } else {
                $member_name = 'N/A';
            }

            // Admin Name
            $admin_name = !empty($row['admin_first']) ? $row['admin_first'].' '.$row['admin_middle'].' '.$row['admin_last'] : 'N/A';

            // Borrowed Row
            if ($report_type == "all" || $report_type == "borrowed"):
        ?>
        <tr>
            <td><?php echo htmlspecialchars($member_name); ?></td>
            <td><?php echo htmlspecialchars($row['book_title']); ?></td>
            <td>Borrowed</td>
            <td><?php echo htmlspecialchars($admin_name); ?></td>
            <td><?php echo date("M d, Y h:i:s a", strtotime($row['date_borrowed'])); ?></td>
        </tr>
        <?php
            endif;

            // Returned Row (if exists)
            if (!empty($row['return_status']) && ($report_type == "all" || $report_type == "returned")):
        ?>
        <tr>
            <td><?php echo htmlspecialchars($member_name); ?></td>
            <td><?php echo htmlspecialchars($row['book_title']); ?></td>
            <td>Returned</td>
            <td><?php echo htmlspecialchars($admin_name); ?></td>
            <td><?php echo date("M d, Y h:i:s a", strtotime($row['date_returned'])); ?></td>
        </tr>
        <?php
            endif;
        endwhile;
        ?>
        </tbody>
    </table>

    <?php
    $user_query = mysqli_query($con,"SELECT * FROM admin WHERE admin_id='$id_session'") or die(mysqli_error());
    $admin = mysqli_fetch_array($user_query);
    ?>
    <p style="margin-top: 50px;"><strong>Prepared by:</strong><br><br>
    <?php echo $admin['firstname']." ".$admin['lastname']; ?></p>
</div>
</body>
</html>

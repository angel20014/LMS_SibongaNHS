<?php  
include('session.php');
include ('include/dbcon.php');
?>
<html>
<head>
    <title>SNHS Library Management System</title>
    <style>
.container {
    width:100%;
    margin:auto;
}

.table {
    width: 100%;
    margin-bottom: 20px;
    border-collapse: collapse;
}   
tr,td,th{
    border:1px solid black;
}
.table-striped tbody > tr:nth-child(odd) > td,
.table-striped tbody > tr:nth-child(odd) > th {
    background-color: #f9f9f9;
}

@media print{
    #print { display:none; }
    #close { display:none; }
    .modal-header, .modal-footer { display:none; }
}

#print, #close {
    width: 90px;
    height: 30px;
    font-size: 16px;
    background: white;
    border-radius: 4px;
    margin:5px;
    cursor:pointer;
}

.line1 {
  font-family: 'Times New Roman', serif;
  font-size: 16px;
  line-height: 1.6;
}

.line2 {
  font-family: 'Old English Text MT', 'Blackletter', serif;
  font-size: 22px;
  font-weight: normal;
  line-height: 1.6;
}

.line3 {
  font-family: 'Arial Black', sans-serif;
  font-size: 16px;
  letter-spacing: 1px;
  line-height: 1.8;
}

.line4 {
  font-family: 'Georgia', serif;
  font-size: 14px;
  line-height: 1.6;
}
    </style>

<script>
function printPage() {
    window.print();
}
</script>
</head>

<body>
<div class="container">
    <div id="header">
        <br/>
        <img src="images/logo1.png" style="margin-top:-17px; float:left; margin-left:115px; margin-bottom:-6px; width:100px; height:100px;">
        <img src="images/logo.png" style="margin-top:-17px; float:right; margin-right:115px; width:100px; height:100px;" >
        <center><div class="line1">Republic of the Philippines</div></center>
        <center><div class="line2" style="margin-top:-10px;">Department of Education</div></center>
        <center><div class="line3" style="margin-top:-10px;">SIBONGA NATIONAL HIGH SCHOOL</div></center>
        <center><div class="line4" style="margin-top:-10px;">Poblacion, Sibonga, Cebu</div></center>
                
        <button id="print" onclick="printPage()">Print</button>    
        <button id="close" onclick="window.close()">Close</button>    

        <p style="margin-left:440px; margin-top:50px; font-size:14pt; font-weight:bold;">
            Returned Books Information
        </p>
        <div align="right">
            <b style="color:blue;">Date Prepared:</b>
            <?php include('currentdate.php'); ?>
        </div>
        <br/>

<?php
// ✅ Get date range from modal (if provided)
$datefrom = isset($_GET['datefrom']) ? $_GET['datefrom'] : '';
$dateto   = isset($_GET['dateto']) ? $_GET['dateto'] : '';

$query = "
    SELECT 
        borrow_book.borrow_book_id,
        borrow_book.date_borrowed,
        borrow_book.due_date,
        borrow_book.date_returned,
        borrow_book.borrowed_status,
        book.book_barcode,
        book.book_title,
        students.firstname AS student_firstname,
        students.middlename AS student_middlename,
        students.lastname AS student_lastname,
        students.grade_level AS student_level,
        students.section AS student_section,
        teachers.firstname AS teacher_firstname,
        teachers.middlename AS teacher_middlename,
        teachers.lastname AS teacher_lastname
    FROM borrow_book
    LEFT JOIN book ON borrow_book.book_id = book.book_id
    LEFT JOIN students ON borrow_book.student_id = students.student_id
    LEFT JOIN teachers ON borrow_book.teacher_id = teachers.teacher_id
    WHERE borrow_book.borrowed_status = 'returned'
";

// ✅ If modal selected dates, filter
if (!empty($datefrom) && !empty($dateto)) {
    $query .= " AND DATE(borrow_book.date_returned) BETWEEN '$datefrom' AND '$dateto'";
}

$query .= " ORDER BY borrow_book.date_returned DESC";

$return_query = mysqli_query($con, $query) or die(mysqli_error($con));
$return_count = mysqli_num_rows($return_query);
?>

<!-- ✅ Show selected date range -->
<?php if (!empty($datefrom) && !empty($dateto)): ?>
    <p style="font-size:13pt; font-weight:bold; margin-left:28px;">
        Showing returned records from <span style="color:red;"><?php echo date("M d, Y", strtotime($datefrom)); ?></span> 
        to <span style="color:red;"><?php echo date("M d, Y", strtotime($dateto)); ?></span>
    </p>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Barcode</th>
            <th>Borrower Name</th>
            <th>Borrower Type</th>
            <th>Grade Level</th>
            <th>Section</th>
            <th>Title</th>
            <th>Date Borrowed</th>
            <th>Due Date</th>
            <th>Date Returned</th>
        </tr>
    </thead>   
    <tbody>
<?php
while ($return_row = mysqli_fetch_array($return_query)) {
    if (!empty($return_row['student_firstname'])) {
        $borrower_name = $return_row['student_firstname']." ".$return_row['student_middlename']." ".$return_row['student_lastname'];
        $borrower_type = "Student";
        $borrower_level = $return_row['student_level'];
        $borrower_section = $return_row['student_section'];
    } elseif (!empty($return_row['teacher_firstname'])) {
        $borrower_name = $return_row['teacher_firstname']." ".$return_row['teacher_middlename']." ".$return_row['teacher_lastname'];
        $borrower_type = "Teacher";
        $borrower_level = "-";
        $borrower_section = "-";
    } else {
        $borrower_name = "Unknown";
        $borrower_type = "-";
        $borrower_level = "-";
        $borrower_section = "-";
    }
?>
<tr>
    <td style="text-align:center;"><?php echo $return_row['book_barcode']; ?></td>
    <td style="text-transform: capitalize; text-align:center;"><?php echo $borrower_name; ?></td>
    <td style="text-align:center;"><?php echo $borrower_type; ?></td>
    <td style="text-align:center;"><?php echo $borrower_level; ?></td>
    <td style="text-align:center;"><?php echo $borrower_section; ?></td>
    <td style="text-transform: capitalize; text-align:center;"><?php echo $return_row['book_title']; ?></td>
    <td style="text-align:center;"><?php echo date("M d, Y h:i:s a",strtotime($return_row['date_borrowed'])); ?></td>
    <td style="text-align:center;"><?php echo date("M d, Y h:i:s a",strtotime($return_row['due_date'])); ?></td>
    <td style="text-align:center;"><?php echo date("M d, Y h:i:s a",strtotime($return_row['date_returned'])); ?></td>
</tr>
<?php 
}
if ($return_count <= 0){
    echo '
        <tr>
            <td colspan="9" class="alert alert-danger text-center">No returned books found in this date range</td>
        </tr>
    ';
}
?>
    </tbody> 
</table>

<br /><br />
<?php
$user_query = mysqli_query($con,"SELECT * FROM admin WHERE admin_id='$id_session'") or die(mysqli_error());
$row = mysqli_fetch_array($user_query);
?>
<h2><i class="glyphicon glyphicon-user"></i> 
<?php 
echo '<span style="color:blue; font-size:15px;">Prepared by:<br /><br /> '.$row['firstname'].' '.$row['lastname'].'</span>';
?>
</h2>
    </div>
</div>
</body>
</html>

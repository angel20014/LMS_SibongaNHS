<?php
include('session.php');
include('include/dbcon.php');

// Get filter values
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$date_to   = isset($_GET['date_to']) ? $_GET['date_to'] : null;
$option    = isset($_GET['print_option']) ? $_GET['print_option'] : 'all';

// Date condition
$date_condition = "";
if ($date_from && $date_to) {
    $date_condition = "AND date_registered BETWEEN '$date_from' AND '$date_to'";
}
?>
<html>
<head>
    <title>SNHS Library Management System</title>
    <style>
        .container { width: 100%; margin: auto; }
        .table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .table-striped tbody > tr:nth-child(odd) > td,
        .table-striped tbody > tr:nth-child(odd) > th { background-color: #f9f9f9; }
        @media print { #print { display: none; } }
        #print { width: 90px; height: 30px; font-size: 18px; background: white; border-radius: 4px; margin-left: 28px; cursor: pointer; }
        .line1 { font-family: 'Times New Roman', serif; font-size: 16px; line-height: 1.6; }
        .line2 { font-family: 'Old English Text MT', 'Blackletter', serif; font-size: 22px; font-weight: normal; line-height: 1.6; }
        .line3 { font-family: 'Arial Black', sans-serif; font-size: 16px; letter-spacing: 1px; line-height: 1.8; }
        .line4 { font-family: 'Georgia', serif; font-size: 14px; line-height: 1.6; }
    </style>
    <script> function printPage() { window.print(); } </script>
</head>

<body>
<div class="container">
    <div id="header">
        <br/>
        <img src="images/logo1.png" style="margin-top:-17px; float:left; margin-left:115px; margin-bottom:-6px; width:100px; height:100px;">
        <img src="images/logo.png" style="margin-top:-17px; float:right; margin-right:115px; width:100px; height:100px;">
        <center><div class="line1">Republic of the Philippines</div></center>
        <center><div class="line2" style="margin-top:-10px;">Department of Education</div></center>
        <center><div class="line3" style="margin-top:-10px;">SIBONGA NATIONAL HIGH SCHOOL</div></center>
        <center><div class="line4" style="margin-top:-10px;">Poblacion, Sibonga, Cebu</div></center>

        <button type="submit" id="print" onclick="printPage()">Print</button>
        <p style="margin-left:30px; margin-top:50px; font-size:14pt; font-weight:bold;">
            <?php
            if ($option == "students") echo "Students List";
            elseif ($option == "teachers") echo "Teachers List";
            else echo "Students and Teachers List";
            ?>
        </p>

        <div align="right">
            <b style="color:blue;">Date Prepared:</b>
            <?php include('currentdate.php'); ?>
        </div>
        <br/>

        <table class="table table-striped" border="1" cellpadding="5">
            <thead>
            <tr>
                <th>Full Name</th>
                <th>Contact</th>
                <th>Type</th>
                <th>Grade Level</th>
                <th>Section</th>
                <th>Date Registered</th>
            </tr>
            </thead>
            <tbody>
            
            <?php if ($option == "all" || $option == "students") { ?>
            <!-- STUDENTS -->
            <?php
            $student_query = mysqli_query($con, "SELECT * FROM students WHERE 1=1 $date_condition ORDER BY date_registered DESC") or die(mysqli_error($con));
            while ($student = mysqli_fetch_array($student_query)) {
                $fullname = $student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname'];
                $contact = $student['contact_number'];
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $fullname; ?></td>
                    <td style="text-align:center;"><?php echo $contact; ?></td>
                    <td style="text-align:center;">Student</td>
                    <td style="text-align:center;"><?php echo $student['grade_level']; ?></td>
                    <td style="text-align:center;"><?php echo $student['section']; ?></td>
                    <td style="text-align:center;"><?php echo $student['date_registered']; ?></td>
                </tr>
            <?php } } ?>

            <?php if ($option == "all" || $option == "teachers") { ?>
            <!-- TEACHERS -->
            <?php
            $teacher_query = mysqli_query($con, "SELECT * FROM teachers WHERE 1=1 $date_condition ORDER BY date_registered DESC") or die(mysqli_error($con));
            while ($teacher = mysqli_fetch_array($teacher_query)) {
                $fullname = $teacher['firstname'] . ' ' . $teacher['middlename'] . ' ' . $teacher['lastname'];
                $contact = $teacher['contact_number'];
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $fullname; ?></td>
                    <td style="text-align:center;"><?php echo $contact; ?></td>
                    <td style="text-align:center;">Teacher</td>
                    <td style="text-align:center;">N/A</td>
                    <td style="text-align:center;">N/A</td>
                    <td style="text-align:center;"><?php echo $teacher['date_registered']; ?></td>
                </tr>
            <?php } } ?>
            
            </tbody>
        </table>

        <br/><br/>

        <?php
        $user_query = mysqli_query($con, "SELECT * FROM admin WHERE admin_id='$id_session'") or die(mysqli_error($con));
        $row = mysqli_fetch_array($user_query);
        ?>
        <h2>
            <i class="glyphicon glyphicon-user"></i>
            <?php echo '<span style="color:blue; font-size:15px;">Prepared by:<br/><br/>' . $row['firstname'] . ' ' . $row['lastname'] . '</span>'; ?>
        </h2>
    </div>
</div>
</body>
</html>

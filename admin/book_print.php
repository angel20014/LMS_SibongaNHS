<?php
ob_start();
include('include/dbcon.php');
include('session.php');

// Get filters from modal
$date_from     = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to       = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$category      = isset($_GET['category']) ? $_GET['category'] : 'all';
$status_option = isset($_GET['status_option']) ? $_GET['status_option'] : 'all';

// Build query
$query = "SELECT book.*, category.category_name 
          FROM book 
          LEFT JOIN category ON book.category_id = category.category_id 
          WHERE 1=1";

// Date filter
if (!empty($date_from) && !empty($date_to)) {
    $query .= " AND DATE(book.date_added) BETWEEN '$date_from' AND '$date_to'";
}

// Category filter
if ($category != 'all') {
    $query .= " AND category.category_name = '" . mysqli_real_escape_string($con, $category) . "'";
}

// Status filter
if ($status_option != 'all') {
    $query .= " AND book.status = '" . mysqli_real_escape_string($con, $status_option) . "'";
}

$query .= " ORDER BY book.book_id DESC";
$result = mysqli_query($con, $query) or die(mysqli_error($con));
?>
<html>
<head>
    <title>SNHS Library Management System - Book Print</title>
    <style>
        .container { width:100%; margin:auto; }
        .table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .table th, .table td { border: 1px solid black; padding: 6px; font-size: 13px; }
        .table-striped tbody > tr:nth-child(odd) > td,
        .table-striped tbody > tr:nth-child(odd) > th { background-color: #f9f9f9; }
        @media print{ #print { display:none; } }
        #print { width: 90px; height: 30px; font-size: 18px; background: white; border-radius: 4px; margin-left:28px; cursor:pointer; }
        .line1 { font-family: 'Times New Roman', serif; font-size: 16px; line-height: 1.6; }
        .line2 { font-family: 'Old English Text MT', 'Blackletter', serif; font-size: 22px; font-weight: normal; line-height: 1.6; }
        .line3 { font-family: 'Arial Black', sans-serif; font-size: 16px; letter-spacing: 1px; line-height: 1.8; }
        .line4 { font-family: 'Georgia', serif; font-size: 14px; line-height: 1.6; }
    </style>
    <script>function printPage() { window.print(); }</script>
</head>
<body>
<div class="container">
    <div id="header">
        <br/>
        <img src="images/logo.png" style="margin-top:-17px; float:left; margin-left:115px; margin-bottom:-6px; width:100px; height:100px;">
        <img src="images/logo1.png" style="margin-top:-17px; float:right; margin-right:115px; width:100px; height:100px;" >
        
        <center><div class="line1">Republic of the Philippines</div></center>
        <center><div class="line2" style="margin-top:-10px;">Department of Education</div></center>
        <center><div class="line3" style="margin-top:-10px;">SIBONGA NATIONAL HIGH SCHOOL</div></center>
        <center><div class="line4" style="margin-top:-10px;">Poblacion, Sibonga, Cebu</div></center>

        <button type="submit" id="print" onclick="printPage()">Print</button>    
        <p style="margin-left:30px; margin-top:50px; font-size:14pt; font-weight:bold;">
            Book List 
            <?php 
                if($category!='all'){ echo " - ".$category; } 
                if($status_option!='all'){ echo " (".$status_option.")"; }
                if(!empty($date_from) && !empty($date_to)){ echo " [".$date_from." to ".$date_to."]"; }
            ?>
        </p>
        <div align="right">
            <b style="color:blue;">Date Prepared:</b>
            <?php include('currentdate.php'); ?>
        </div>            
        <br/><br/><br/>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Publication</th>
                    <th>Publisher</th>
                    <th>Copyright</th>
                    <th>Copies</th>
                    <th>Category</th>
                    <th>Status</th>
                </tr>
            </thead>   
            <tbody>
            <?php 
            if(mysqli_num_rows($result) > 0){
                while ($row= mysqli_fetch_array($result)){ ?>
                    <tr>
                        <td style="text-align:center;"><?php echo $row['book_barcode']; ?></td>
                        <td style="text-align:center;"><?php echo $row['book_title']; ?></td>
                        <td style="text-align:center;"><?php echo $row['author']; ?></td>
                        <td style="text-align:center;"><?php echo $row['isbn']; ?></td>
                        <td style="text-align:center;"><?php echo $row['book_pub']; ?></td>
                        <td style="text-align:center;"><?php echo $row['publisher_name']; ?></td>
                        <td style="text-align:center;"><?php echo $row['copyright_year']; ?></td> 
                        <td style="text-align:center;"><?php echo $row['book_copies']; ?></td> 
                        <td style="text-align:center;"><?php echo $row['category_name']; ?></td> 
                        <td style="text-align:center;"><?php echo $row['status']; ?></td> 
                    </tr>
                <?php } 
            } else {
                echo '<tr><td colspan="10" style="text-align:center; color:red;">No records found</td></tr>';
            }
            ?>
            </tbody> 
        </table> 

        <br /><br />
        <?php
            $user_query=mysqli_query($con,"SELECT * FROM admin WHERE admin_id='$id_session'")or die(mysqli_error($con));
            $row=mysqli_fetch_array($user_query);
        ?>        
        <h2><i class="glyphicon glyphicon-user"></i> 
            <?php echo '<span style="color:blue; font-size:15px;">Prepared by:'."<br /><br /> ".$row['firstname']." ".$row['lastname']." ".'</span>';?>
        </h2>
    </div>
</div>
</body>
</html>

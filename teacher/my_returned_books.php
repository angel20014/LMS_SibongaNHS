<?php
include('header.php');
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Only allow teachers
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../index.php");
    exit();
}

$teacher_id = $_SESSION['id'];

// Fetch returned books grouped by book
$returned_query = mysqli_query($con, "
    SELECT 
        MIN(bb.borrow_book_id) AS borrow_book_id,
        bb.book_id,
        b.book_title,
        b.book_image,
        COUNT(*) AS quantity,
        MIN(bb.date_borrowed) AS date_borrowed,
        MAX(bb.due_date) AS due_date,
        MAX(bb.date_returned) AS date_returned,
        (SELECT rb.return_status 
         FROM return_book rb 
         WHERE rb.book_id = bb.book_id AND rb.teacher_id = '$teacher_id'
         ORDER BY rb.borrow_book_id DESC LIMIT 1) AS return_status,
        (SELECT rb.admin_id 
         FROM return_book rb 
         WHERE rb.book_id = bb.book_id AND rb.teacher_id = '$teacher_id'
         ORDER BY rb.borrow_book_id DESC LIMIT 1) AS admin_id,
        (SELECT a.firstname 
         FROM return_book rb 
         JOIN admin a ON rb.admin_id = a.admin_id 
         WHERE rb.book_id = bb.book_id AND rb.teacher_id = '$teacher_id'
         ORDER BY rb.borrow_book_id DESC LIMIT 1) AS admin_firstname,
        (SELECT a.lastname 
         FROM return_book rb 
         JOIN admin a ON rb.admin_id = a.admin_id 
         WHERE rb.book_id = bb.book_id AND rb.teacher_id = '$teacher_id'
         ORDER BY rb.borrow_book_id DESC LIMIT 1) AS admin_lastname,
        'returned' AS borrowed_status
    FROM borrow_book bb
    JOIN book b ON bb.book_id = b.book_id
    WHERE bb.teacher_id = '$teacher_id' AND bb.borrowed_status = 'returned'
    GROUP BY bb.book_id
    ORDER BY date_returned DESC
") or die(mysqli_error($con));

$modals = "";
?>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> My Returned Books</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-book"></i> Returned Books</h2>
                <ul class="nav navbar-right panel_toolbox"></ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Book Image</th>
                                <th>Book Title</th>
                                <th>Quantity</th>
                                <th>Date Borrowed</th>
                                <th>Due Date</th>
                                <th>Date Returned</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($returned_query)) {
                                $id = $row['borrow_book_id'];
                                $imgSrc = !empty($row['book_image']) && file_exists('upload/'.$row['book_image'])
                                    ? 'upload/'.$row['book_image']
                                    : 'images/no-image.png';

                                $date_borrowed = date('M d, Y h:i A', strtotime($row['date_borrowed']));
$due_date = date('M d, Y h:i A', strtotime($row['due_date']));
$date_returned = $row['date_returned'] ? date('M d, Y h:i A', strtotime($row['date_returned'])) : '-';


                                $status_text = htmlspecialchars($row['borrowed_status']);
                            ?>
                            <tr>
                                <td><img src="<?php echo $imgSrc; ?>" alt="Book Image" width="80" style="border-radius:5px;"></td>
                                <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo $date_borrowed; ?></td>
                                <td><?php echo $due_date; ?></td>
                                <td><?php echo $date_returned; ?></td>
                                <td><?php echo $status_text; ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#viewModal<?php echo $id; ?>">
                                        <i class="fa fa-search"></i> View
                                    </button>
                                </td>
                            </tr>

                            <?php
                            $modals .= '
                            <div class="modal fade" id="viewModal'.$id.'" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel'.$id.'" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="viewModalLabel'.$id.'"><i class="fa fa-book"></i> Returned Book Details</h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="'.$imgSrc.'" alt="Book Image" class="img-thumbnail mb-3" width="120">

                                            <table class="table table-borderless text-left mx-auto w-75">
                                                <tbody>
                                                    <tr><th>Book Title:</th><td>'.htmlspecialchars($row['book_title']).'</td></tr>
                                                    <tr><th>Quantity:</th><td>'.$row['quantity'].'</td></tr>
                                                    <tr><th>Date Borrowed:</th><td>'.$date_borrowed.'</td></tr>
                                                    <tr><th>Due Date:</th><td>'.$due_date.'</td></tr>
                                                    <tr><th>Date Returned:</th><td>'.$date_returned.'</td></tr>
                                                    <tr><th>Received By:</th><td>'.htmlspecialchars($row['admin_firstname'].' '.$row['admin_lastname']).'</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Output all modals -->
                <?php echo $modals; ?>

            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

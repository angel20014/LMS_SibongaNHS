<?php
include('include/dbcon.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['id'];

// Handle POST before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_return'])) {
    $borrow_id = intval($_POST['return_book_id']);

    $borrow_data = mysqli_fetch_assoc(mysqli_query($con, "
        SELECT * FROM borrow_book WHERE borrow_book_id = $borrow_id AND student_id = '$student_id'
    "));

    if ($borrow_data) {
        $existing_return = mysqli_fetch_assoc(mysqli_query($con, "
            SELECT * FROM return_book WHERE borrow_book_id = $borrow_id
        "));

        if (!$existing_return) {
            $insert = mysqli_query($con, "
                INSERT INTO return_book (borrow_book_id, book_id, admin_id, student_id, date_borrowed, due_date, return_status)
                VALUES (
                    '{$borrow_data['borrow_book_id']}',
                    '{$borrow_data['book_id']}',
                    '{$borrow_data['admin_id']}',
                    '{$borrow_data['student_id']}',
                    '{$borrow_data['date_borrowed']}',
                    '{$borrow_data['due_date']}',
                    'Pending'
                )
            ");

            if ($insert) {
                mysqli_query($con, "
                    UPDATE borrow_book 
                    SET borrowed_status = 'pending_return' 
                    WHERE borrow_book_id = $borrow_id
                ");
                header("Location: my_borrowed_books.php?msg=return_requested");
                exit();
            }
        }
    }

    header("Location: my_borrowed_books.php?msg=failed");
    exit();
}

// Fetch borrowed books
$borrow_query = mysqli_query($con, "
    SELECT 
        bb.borrow_book_id, 
        bb.book_id, 
        bb.admin_id,
        bb.date_borrowed, 
        bb.due_date, 
        bb.date_returned, 
        bb.borrowed_status,
        b.book_title, 
        b.book_image,
        rb.return_status
    FROM borrow_book bb
    JOIN book b ON bb.book_id = b.book_id
    LEFT JOIN return_book rb 
        ON rb.book_id = bb.book_id 
        AND rb.student_id = bb.student_id
    WHERE bb.student_id = '$student_id'
    ORDER BY bb.date_borrowed DESC
") or die(mysqli_error($con));

// Only now include header.php (safe, because no redirect will be called after this)
include('header.php');

$modals = ""; // to store modals
?>

<style>
    .pending-row {
    background-color: #fff3cd !important; /* soft yellow */
}

</style>
<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> My Borrowed Books</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-book"></i> Borrowed Books</h2>
                <ul class="nav navbar-right panel_toolbox">
                    
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Book Image</th>
                                <th>Book Title</th>
                                <th>Date Borrowed</th>
                                <th>Due Date</th>
                                <th>Date Returned</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($borrow_query)) {
                                $id = $row['borrow_book_id'];
                                $imgSrc = !empty($row['book_image']) && file_exists('upload/'.$row['book_image'])
                                    ? 'upload/'.$row['book_image']
                                    : 'images/no-image.png';

                                // Format dates nicely or show "-"
                                $date_borrowed = date('M d, Y', strtotime($row['date_borrowed']));
                                $due_date = date('M d, Y', strtotime($row['due_date']));
                                $date_returned = $row['date_returned'] ? date('M d, Y', strtotime($row['date_returned'])) : '-';

                                $status_text = '';
if ($row['return_status'] === 'Accepted') {
    $status_text = 'Accepted';
} elseif ($row['return_status'] === 'Rejected') {
    $status_text = 'Rejected';
} elseif ($row['borrowed_status'] === 'pending_return') {
    $status_text = 'Pending Return';
} elseif ($row['borrowed_status'] === 'returned') {
    $status_text = 'Returned';
} else {
    $status_text = 'Borrowed';
}
                            ?>
                            <tr class="<?php echo ($row['return_status'] === 'Pending' || $row['borrowed_status'] === 'pending_return') ? 'pending-row' : ''; ?>">
    <td><img src="<?php echo $imgSrc; ?>" alt="Book Image" width="80" style="border-radius:5px;"></td>
    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
    <td><?php echo $date_borrowed; ?></td>
    <td><?php echo $due_date; ?></td>
    <td><?php echo $date_returned; ?></td>
    <td><?php echo $status_text; ?></td>
    <td>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#viewModal<?php echo $id; ?>">
            <i class="fa fa-search"></i> View
        </button>

        <?php if ($row['borrowed_status'] !== 'pending_return' && $row['borrowed_status'] !== 'returned'): ?>
            <button class="btn btn-success btn-sm btn-return" data-id="<?php echo $id; ?>">
                <i class="fa fa-undo"></i> Return
            </button>
        <?php else: ?>
            <button class="btn btn-secondary btn-sm" disabled>
                <i class="fa fa-clock-o"></i> <?php echo ($row['borrowed_status'] === 'pending_return') ? 'Pending Return' : 'Returned'; ?>
            </button>
        <?php endif; ?>
    </td>
</tr>


                            <?php
                            // Build modal HTML for each borrowed book entry
                            $modals .= '
                            <div class="modal fade" id="viewModal'.$id.'" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel'.$id.'" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="viewModalLabel'.$id.'"><i class="fa fa-book"></i> Borrowed Book Details</h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="'.$imgSrc.'" alt="Book Image" class="img-thumbnail mb-3" width="120">

                                            <table class="table table-borderless text-left mx-auto w-75">
                                                <tbody>
                                                    <tr><th>Book Title:</th><td>'.htmlspecialchars($row['book_title']).'</td></tr>
                                                    <tr><th>Date Borrowed:</th><td>'.$date_borrowed.'</td></tr>
                                                    <tr><th>Due Date:</th><td>'.$due_date.'</td></tr>
                                                    <tr><th>Date Returned:</th><td>'.$date_returned.'</td></tr>
                                                    <tr><th>Status:</th><td>'.$status_text.'</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>';

                            $modals .= '
<div class="modal fade" id="returnModal'.$id.'" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel'.$id.'" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="returnModalLabel'.$id.'"><i class="fa fa-undo"></i> Confirm Return</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <p>Are you sure you want to request return for <strong>'.htmlspecialchars($row['book_title']).'</strong>?</p>
        <input type="hidden" name="return_book_id" value="'.$id.'">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" name="request_return" class="btn btn-success">Yes, Request Return</button>
      </div>
    </form>
  </div>
</div>';

                            } // end while
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Output all modals -->
                <?php echo $modals; 
                ?>

            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on('click', '.btn-return', function() {
    let button = $(this);
    let borrowBookId = button.data('id');

    if (!confirm('Are you sure you want to request a return for this book?')) {
        return;
    }

    $.ajax({
        url: 'ajax_request_return.php',
        type: 'POST',
        data: { borrow_book_id: borrowBookId },
        success: function(response) {
            if (response.status === 'success') {
                button
                    .removeClass('btn-success')
                    .addClass('btn-secondary')
                    .prop('disabled', true)
                    .html('<i class="fa fa-clock-o"></i> Pending Return');
            } else {
                alert(response.message || 'Failed to request return.');
            }
        },
        error: function() {
            alert('Something went wrong. Please try again.');
        }
    });
});
</script>

<?php include('footer.php'); ?>

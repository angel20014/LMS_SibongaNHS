<?php include('header.php'); ?>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> Returned Books</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-book"></i> Returned Books Lists</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
    <button class="btn btn-danger" data-toggle="modal" data-target="#printReturnedModal">
        <i class="fa fa-print"></i> Print
    </button>
</li>


                </ul>
                <div class="clearfix"></div>

                
            </div>

            <div class="x_content">
                <div class="table-responsive">
                    <?php
                    $return_query = mysqli_query($con, "
    SELECT 
        GROUP_CONCAT(rb.return_book_id) AS return_ids,
        rb.student_id,
        rb.teacher_id,
        rb.admin_id,
        b.book_barcode,
        b.book_title,
        s.firstname AS student_firstname,
        s.middlename AS student_middlename,
        s.lastname AS student_lastname,
        t.firstname AS teacher_firstname,
        t.middlename AS teacher_middlename,
        t.lastname AS teacher_lastname,
        a.firstname AS admin_firstname,
        a.lastname AS admin_lastname,
        MIN(rb.date_borrowed) AS date_borrowed, 
        MIN(rb.due_date) AS due_date,
        MAX(rb.date_returned) AS date_returned,
        rb.return_status,
        COUNT(*) AS quantity
    FROM return_book rb
    LEFT JOIN book b ON rb.book_id = b.book_id
    LEFT JOIN students s ON rb.student_id = s.student_id
    LEFT JOIN teachers t ON rb.teacher_id = t.teacher_id
    LEFT JOIN admin a ON rb.admin_id = a.admin_id   -- ✅ join admin table
    GROUP BY rb.student_id, rb.teacher_id, b.book_id, rb.return_status, rb.admin_id
    ORDER BY date_returned DESC
") or die(mysqli_error($con));

                    $return_count = mysqli_num_rows($return_query);
                    ?>
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="example">
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>Borrower Name</th>
                                <th>Borrower Type</th>
                                <th>Title</th>
                                <th>Quantity</th>
                                <th>Date Borrowed</th>
                                <th>Due Date</th>
                                <th>Date Returned</th>
                                <th>Return Status</th>
                                <th>Processed By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($return_count > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($return_query)): ?>
                                    <?php
                                 
                                    if (!empty($row['student_id'])) {
                                        $middle = !empty($row['student_middlename']) ? ' ' . $row['student_middlename'] . ' ' : ' ';
                                        $borrower_name = $row['student_firstname'] . $middle . $row['student_lastname'];
                                        $borrower_type = 'Student';
                                    } elseif (!empty($row['teacher_id'])) {
                                        $middle = !empty($row['teacher_middlename']) ? ' ' . $row['teacher_middlename'] . ' ' : ' ';
                                        $borrower_name = $row['teacher_firstname'] . $middle . $row['teacher_lastname'];
                                        $borrower_type = 'Teacher';
                                    } else {
                                        $borrower_name = 'Unknown';
                                        $borrower_type = '-';
                                    }

                                    $borrower_name = trim(ucwords($borrower_name));

                                  
                                    $date_borrowed = !empty($row['date_borrowed']) ? date("M d, Y h:i A", strtotime($row['date_borrowed'])) : '-';
                                    $due_date = !empty($row['due_date']) ? date("M d, Y h:i A", strtotime($row['due_date'])) : '-';
                                    $date_returned = !empty($row['date_returned']) ? date("M d, Y h:i A", strtotime($row['date_returned'])) : '-';

                                    $return_status = !empty($row['return_status']) ? $row['return_status'] : 'Pending';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['book_barcode']); ?></td>
                                        <td style="text-transform: capitalize;"><?php echo htmlspecialchars($borrower_name); ?></td>
                                        <td><?php echo $borrower_type; ?></td>
                                        <td style="text-transform: capitalize;"><?php echo htmlspecialchars($row['book_title']); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td><?php echo $date_borrowed; ?></td>
                                        <td><?php echo $due_date; ?></td>
                                        <td><?php echo $date_returned; ?></td>
                                        <td><?php echo htmlspecialchars($return_status); ?></td>
                                        <td> <?php if (!empty($row['admin_firstname'])) {echo htmlspecialchars($row['admin_firstname'].' '.$row['admin_lastname']);} else { echo '-'; }?></td>

                                        <td class="text-center">
    <?php if (strtolower($return_status) === 'pending'): ?>
        <!-- Accept Icon -->
        <a href="update_return_status.php?return_ids=<?php echo urlencode($row['return_ids']); ?>&status=Accepted"
           title="Accept" 
           onclick="return confirm('Mark this return as Accepted?');">
            <i class="fa fa-check-circle text-success" style="font-size:20px;"></i>
        </a>

        <!-- Reject Icon -->
        <a href="update_return_status.php?return_ids=<?php echo urlencode($row['return_ids']); ?>&status=Rejected"
           title="Reject" 
           onclick="return confirm('Mark this return as Rejected?');">
            <i class="fa fa-times-circle text-danger" style="font-size:20px; margin-left:8px;"></i>
        </a>
    <?php else: ?>
        <?php if (strtolower($return_status) === 'accepted'): ?>
            <i class="fa fa-check-circle text-success" style="font-size:20px;" title="Accepted"></i>
        <?php elseif (strtolower($return_status) === 'rejected'): ?>
            <i class="fa fa-times-circle text-danger" style="font-size:20px;" title="Rejected"></i>
        <?php endif; ?>
    <?php endif; ?>
</td>

                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center alert alert-warning">No returned books found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Returned Books Modal -->
<div class="modal fade" id="printReturnedModal" tabindex="-1" role="dialog" aria-labelledby="printReturnedModalLabel">
  <div class="modal-dialog" role="document">
    <form method="POST" action="print_returned_book.php" target="_blank">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="printReturnedModalLabel">Print Returned Books</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label>Date From:</label>
            <input type="date" name="datefrom" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Date To:</label>
            <input type="date" name="dateto" class="form-control" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="print" class="btn btn-danger">
            <i class="fa fa-print"></i> Print
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>


<?php include('footer.php'); ?>

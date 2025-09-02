<?php include('header.php'); ?>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> Reports</h3>
    </div>
</div>

 <div class="title_right">
        <!-- Button to open modal -->
        <button class="btn btn-danger pull-right" data-toggle="modal" data-target="#printModal">
            <i class="fa fa-print"></i> Print Report
        </button>
    </div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-file"></i> Report Lists <div style="margin-bottom: 5px; margin-top: 5px;">
    <button class="btn btn-primary" onclick="filterTable('all')">Show All</button>
    <button class="btn btn-success" onclick="filterTable('Borrowed')">Borrowed</button>
    <button class="btn btn-warning" onclick="filterTable('Returned')">Returned</button>
</div>
</h2>
                <ul class="nav navbar-right panel_toolbox">
                  
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="table-responsive">
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="example">
                        <thead>
                            <tr>
                                <th>Borrower Name</th>
                                <th>Book Title</th>
                                <th>Task</th>
                                <th>Admin In Charge</th>
                                <th>Transaction Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $result = mysqli_query($con, "
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
                            ORDER BY bb.borrow_book_id DESC
                        ") or die(mysqli_error($con));

                        while ($row = mysqli_fetch_assoc($result)):
    // Borrower Name
    if (!empty($row['student_first'])) {
        $borrower_name = $row['student_first'].' '.$row['student_middle'].' '.$row['student_last'];
    } elseif (!empty($row['teacher_first'])) {
        $borrower_name = $row['teacher_first'].' '.$row['teacher_middle'].' '.$row['teacher_last'];
    } else {
        $borrower_name = 'N/A';
    }

    // Admin Name
    $admin_name = !empty($row['admin_first']) ? $row['admin_first'].' '.$row['admin_middle'].' '.$row['admin_last'] : 'N/A';

    // First row: Borrowed
?>
<tr>
    <td><?php echo htmlspecialchars($borrower_name); ?></td>
    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
    <td>Borrowed</td>
    <td><?php echo htmlspecialchars($admin_name); ?></td>
    <td><?php echo date("M d, Y h:i:s a", strtotime($row['date_borrowed'])); ?></td>
</tr>

<?php
    // Second row: Returned (only if return_status exists)
    if (!empty($row['return_status'])):
?>
<tr>
    <td><?php echo htmlspecialchars($borrower_name); ?></td>
    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
    <td>Returned</td>
    <td><?php echo htmlspecialchars($admin_name); ?></td>
    <td><?php echo date("M d, Y h:i:s a", strtotime($row['date_returned'])); ?></td>
</tr>
<?php
    endif;
endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for search results -->
<div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="searchModalLabel">Search Results</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="searchResults"></div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="printForm" method="POST" action="report_print.php" target="_blank">
        <div class="modal-header">
          <h4 class="modal-title" id="printModalLabel">Print Report Options</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <!-- Date range -->
          <div class="form-group">
            <label for="printDateFrom">Date From</label>
            <input type="date" name="datefrom" id="printDateFrom" class="form-control">
          </div>
          <div class="form-group">
            <label for="printDateTo">Date To</label>
            <input type="date" name="dateto" id="printDateTo" class="form-control">
          </div>

          <!-- Report type -->
          <div class="form-group">
            <label>Report Type</label><br>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="report_type" value="all" checked>
              <label class="form-check-label">All</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="report_type" value="borrowed">
              <label class="form-check-label">Borrowed Only</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="report_type" value="returned">
              <label class="form-check-label">Returned Only</label>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-danger"><i class="fa fa-print"></i> Print</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('searchForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('report_search.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => {
            document.getElementById('searchResults').innerHTML = data;
            $('#searchModal').modal('show');
        })
        .catch(error => console.error('Error:', error));
});
</script>
<script>
function filterTable(type) {
    const table = document.getElementById("example");
    const rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) { // skip header row
        const taskCell = rows[i].getElementsByTagName("td")[2]; // "Task" column
        if (!taskCell) continue;

        const task = taskCell.textContent || taskCell.innerText;

        if (type === "all") {
            rows[i].style.display = "";
        } else if (task.toLowerCase() === type.toLowerCase()) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
}
</script>


<?php include('footer.php'); ?>

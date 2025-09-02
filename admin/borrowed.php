<?php
include('header.php');
include('include/dbcon.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Date filter defaults
$datefrom = isset($_GET['datefrom']) ? $_GET['datefrom'] : date('Y-m-d');
$dateto = isset($_GET['dateto']) ? $_GET['dateto'] : date('Y-m-d');
$where = "";

// Only add date filter if 'search' button clicked
if (isset($_GET['search'])) {
    $datefrom = mysqli_real_escape_string($con, $datefrom);
    $dateto = mysqli_real_escape_string($con, $dateto);
    $where = " AND (DATE(borrow_book.date_borrowed) BETWEEN '$datefrom' AND '$dateto')";
}

$query = "
    SELECT 
        GROUP_CONCAT(bb.borrow_book_id) AS borrow_ids,
        bb.student_id,
        bb.teacher_id,
        bb.admin_id,
        b.book_title,
        COALESCE(s.firstname, t.firstname) AS borrower_firstname,
        COALESCE(s.middlename, t.middlename) AS borrower_middlename,
        COALESCE(s.lastname, t.lastname) AS borrower_lastname,
        CASE 
            WHEN s.student_id IS NOT NULL THEN 'Student'
            WHEN t.teacher_id IS NOT NULL THEN 'Teacher'
        END AS borrower_type,
        a.firstname AS admin_firstname,
        a.lastname AS admin_lastname,
        MIN(bb.date_borrowed) AS date_borrowed,
        MIN(bb.due_date) AS due_date,
        bb.borrowed_status,
        COUNT(*) AS quantity
    FROM borrow_book bb
    LEFT JOIN book b ON bb.book_id = b.book_id
    LEFT JOIN students s ON bb.student_id = s.student_id
    LEFT JOIN teachers t ON bb.teacher_id = t.teacher_id
    LEFT JOIN admin a ON bb.admin_id = a.admin_id  -- ✅ join admin
    WHERE 1=1 $where
    GROUP BY bb.student_id, bb.teacher_id, b.book_id, bb.borrowed_status, bb.admin_id
    ORDER BY date_borrowed DESC
";


$result = mysqli_query($con, $query) or die("Query failed: " . mysqli_error($con));
$count = mysqli_num_rows($result);
?>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> Borrowed Books</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-book"></i> Borrowed Books List</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
  <button class="btn btn-danger" data-toggle="modal" data-target="#printModal">
    <i class="fa fa-print"></i> Print
  </button>
</li>

                   
                </ul>
                <div class="clearfix"></div>

                
            </div>
            <div class="row mb-3">
  <div class="col-md-12 d-flex justify-content-end">
    <div style="width: 300px; margin-left: 740px;">
      <input type="text" id="tableSearch" class="form-control" placeholder="Search borrowed books...">
    </div>
  </div>
</div>

<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="example"></table>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="borrowedTable">
                        
                        <thead>
                            <tr>
    <th>Borrower Name</th>
    <th>Borrower Type</th>
    <th>Book Title</th>
    <th>Quantity</th>
    <th>Date Borrowed</th>
    <th>Due Date</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
<?php if ($count > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <?php
        // Full name
        $borrower_name = trim($row['borrower_firstname'] . ' ' . $row['borrower_middlename'] . ' ' . $row['borrower_lastname']);
        $borrower_type = $row['borrower_type'];

        // Format dates
        $date_borrowed = date("M d, Y h:i A", strtotime($row['date_borrowed']));
        $due_date = !empty($row['due_date'])
            ? date("M d, Y h:i A", strtotime($row['due_date']))
            : date("M d, Y h:i A", strtotime($row['date_borrowed'] . " +1 day"));

        // Check if overdue
        $now = new DateTime();
        $due = !empty($row['due_date']) ? new DateTime($row['due_date']) : new DateTime($row['date_borrowed'] . " +1 day");
        $isOverdue = $now > $due;

        // Row class for highlighting
        $rowClass = $isOverdue ? 'table-danger' : '';
        ?>
        <tr class="<?php echo $rowClass; ?>">
            <td style="text-transform: capitalize;"><?php echo htmlspecialchars($borrower_name); ?></td>
            <td><?php echo htmlspecialchars($borrower_type); ?></td>
            <td style="text-transform: capitalize;"><?php echo htmlspecialchars($row['book_title']); ?></td>
            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
            <td><?php echo $date_borrowed; ?></td>
            <td><?php echo $due_date; ?></td>
            <td><?php echo ucfirst($row['borrowed_status']); ?></td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="7" class="text-center alert alert-warning">No borrowed books found.</td>
    </tr>
<?php endif; ?>
</tbody>

</table>


                    <?php
                    // Debug output (uncomment if needed)
                    // echo '<pre>'; print_r(mysqli_fetch_all($result, MYSQLI_ASSOC)); echo '</pre>';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Print Modal -->
<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="printModalLabel">Select Date Range</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="printForm" method="get" action="print_borrowed_books.php" target="_blank">
        <div class="modal-body">
          <div class="form-group">
            <label for="datefrom">From:</label>
            <input type="date" class="form-control" name="datefrom" required>
          </div>
          <div class="form-group">
            <label for="dateto">To:</label>
            <input type="date" class="form-control" name="dateto" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" name="search" value="1" class="btn btn-danger">Print</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById("tableSearch").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#borrowedTable tbody tr");

    let found = false;
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        if (text.includes(value)) {
            row.style.display = "";
            found = true;
        } else {
            row.style.display = "none";
        }
    });

    // Handle "No results found"
    let noResultRow = document.getElementById("noResultRow");
    if (!found) {
        if (!noResultRow) {
            let tbody = document.querySelector("#borrowedTable tbody");
            let newRow = document.createElement("tr");
            newRow.id = "noResultRow";
            newRow.innerHTML = `<td colspan="7" class="text-center alert alert-warning">No matching records found.</td>`;
            tbody.appendChild(newRow);
        }
    } else {
        if (noResultRow) noResultRow.remove();
    }
});
</script>


<?php include('footer.php'); ?>

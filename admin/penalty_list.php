<?php
include('header.php');
include('include/dbcon.php');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

$now = date('Y-m-d H:i:s');

// Optional date filter
$datefrom = isset($_GET['datefrom']) ? mysqli_real_escape_string($con, $_GET['datefrom']) : '';
$dateto = isset($_GET['dateto']) ? mysqli_real_escape_string($con, $_GET['dateto']) : '';
$where = '';
if (!empty($datefrom) && !empty($dateto)) {
    $where = " AND DATE(bb.date_borrowed) BETWEEN '$datefrom' AND '$dateto'";
}


$query = "
    SELECT 
        bb.borrow_book_id,
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
        bb.date_borrowed,
        bb.due_date,
        bb.borrowed_status,
        p.message AS penalty_message,
        p.status AS penalty_status,
        p.created_at AS penalty_created
    FROM penalties p
    LEFT JOIN borrow_book bb ON p.borrow_book_id = bb.borrow_book_id
    LEFT JOIN book b ON bb.book_id = b.book_id
    LEFT JOIN students s ON bb.student_id = s.student_id
    LEFT JOIN teachers t ON bb.teacher_id = t.teacher_id
    LEFT JOIN admin a ON bb.admin_id = a.admin_id
    LEFT JOIN return_book rb ON bb.borrow_book_id = rb.borrow_book_id
    WHERE (rb.return_status IS NULL OR rb.return_status != 'Returned')
      AND bb.due_date <= '$now'
    ORDER BY bb.due_date ASC
";



$result = mysqli_query($con, $query) or die("Query failed: " . mysqli_error($con));
$count = mysqli_num_rows($result);
?>

<div class="x_content">
    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="penaltyTable">
            <thead>
                <tr>
                    <th>Borrower Name</th>
                    <th>Borrower Type</th>
                    <th>Book Title</th>
                    <th>Date Borrowed</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Penalty Message</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($count > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $borrower_name = trim($row['borrower_firstname'].' '.$row['borrower_middlename'].' '.$row['borrower_lastname']);
                    $date_borrowed = date("M d, Y h:i A", strtotime($row['date_borrowed']));
                    $due_date = date("M d, Y h:i A", strtotime($row['due_date']));
                ?>
                    <tr>
                        <td style="text-transform: capitalize;"><?php echo htmlspecialchars($borrower_name); ?></td>
                        <td><?php echo htmlspecialchars($row['borrower_type']); ?></td>
                        <td style="text-transform: capitalize;"><?php echo htmlspecialchars($row['book_title']); ?></td>
                        <td><?php echo $date_borrowed; ?></td>
                        <td><?php echo $due_date; ?></td>
                        <td><?php echo ucfirst($row['borrowed_status']); ?></td>
                        <td style="white-space: pre-line;"><?php echo htmlspecialchars($row['penalty_message']); ?></td>
                        <td class="text-center">
                            <?php if(!empty($row['penalty_message'])): ?>
                                <?php if($row['penalty_status'] == 'Approved'): ?>
                                    <span class="badge bg-success"><i class="fa fa-check"></i> Approved</span>
                                <?php elseif($row['penalty_status'] == 'Requesting Approval'): ?>
                                    <span class="badge bg-warning text-dark">Waiting for Approval</span>
                                    <button class="btn btn-sm btn-success approvePenaltyBtn mt-1"
                                        data-id="<?php echo $row['borrow_book_id']; ?>"
                                        title="Approve Penalty">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                <?php elseif($row['penalty_status'] == 'Pending'): ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-link sendPenaltyBtn"
                                    data-id="<?php echo $row['borrow_book_id']; ?>"
                                    data-name="<?php echo htmlspecialchars($borrower_name); ?>"
                                    data-book="<?php echo htmlspecialchars($row['book_title']); ?>"
                                    data-type="<?php echo htmlspecialchars($row['borrower_type']); ?>"
                                    data-due="<?php echo $row['due_date']; ?>"
                                    data-toggle="modal"
                                    data-target="#penaltyModal"
                                    title="Send Penalty">
                                    <i class="fa fa-envelope text-danger"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center alert alert-warning">No penalties found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Modal for Sending Penalty -->
<div class="modal fade" id="penaltyModal" tabindex="-1" aria-labelledby="penaltyModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="penaltyModalLabel">Send Penalty Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="penaltyForm">
        <div class="modal-body">
            <input type="hidden" name="borrow_id" id="penaltyBorrowId">
            <div class="mb-3">
                <label class="form-label">Borrower Name</label>
                <input type="text" class="form-control" id="penaltyUser" name="borrower_name" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Borrower Type</label>
                <input type="text" class="form-control" id="penaltyType" name="borrower_type" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Book Title</label>
                <input type="text" class="form-control" id="penaltyBook" name="book_title" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Due Date</label>
                <input type="text" class="form-control" id="penaltyDue" name="due_date" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Penalty Message</label>
                <textarea class="form-control" id="penaltyMessage" name="message" rows="5" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-danger">Send Penalty</button>
        </div>
      </form>
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
// Table search filter
document.getElementById("tableSearch").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    document.querySelectorAll("#penaltyTable tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
});

// Fill modal with data
document.querySelectorAll('.sendPenaltyBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('penaltyBorrowId').value = this.dataset.id;
        document.getElementById('penaltyUser').value = this.dataset.name;
        document.getElementById('penaltyType').value = this.dataset.type;
        document.getElementById('penaltyBook').value = this.dataset.book;
        document.getElementById('penaltyDue').value = this.dataset.due;
        document.getElementById('penaltyMessage').value = 
            `Dear ${this.dataset.name},\n\n` +
            `This is a reminder that your borrowed book "${this.dataset.book}" was due on ${this.dataset.due}.\n` +
            `Please return it immediately.\n\nPenalty: ______________________`;
    });
});

// Submit penalty form via AJAX
document.getElementById('penaltyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('send_penalty.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text()) // read raw text first
    .then(text => {
        console.log("Server response:", text); // debug
        try {
            const data = JSON.parse(text);
            if(data.success){
                alert('Penalty message sent!');
                var modal = bootstrap.Modal.getInstance(document.getElementById('penaltyModal'));
                modal.hide();
                location.reload(); // refresh to show approve button
            } else {
                alert('Failed: ' + (data.error || 'Unknown error'));
            }
        } catch (err) {
            alert('Invalid JSON response. Check console.');
            console.error(err);
        }
    })
    .catch(err => {
        alert('AJAX request failed: ' + err);
        console.error(err);
    });
});

// Approve penalty via AJAX
document.querySelectorAll('.approvePenaltyBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm("Approve this penalty?")) return;

        const borrowId = this.dataset.id;

        fetch('approve_penalty.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'borrow_id=' + encodeURIComponent(borrowId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Penalty approved!");
                location.reload();
            } else {
                alert("Failed: " + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            alert("AJAX error: " + err);
            console.error(err);
        });
    });
});


</script>

<?php include('footer.php'); ?>

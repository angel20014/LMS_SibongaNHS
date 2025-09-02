<?php 
include('header.php');
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['id'];

// ✅ Fetch penalties for this student using borrow_book → student_id
$query = "
    SELECT p.id, p.borrower_name, p.borrower_type, p.book_title, 
           p.due_date, p.message, p.created_at, p.status
    FROM penalties p
    INNER JOIN borrow_book br ON p.borrow_book_id = br.borrow_book_id
    WHERE br.student_id = '$student_id'
    ORDER BY p.created_at DESC
";
$result = mysqli_query($con, $query) or die(mysqli_error($con));
?>

<style>
.penalty-item {
    display: flex;
    align-items: flex-start;
    padding: 12px;
    border-bottom: 1px solid #eaeaea;
}
.penalty-item:last-child {
    border-bottom: none;
}
.penalty-icon {
    font-size: 22px;
    margin-right: 12px;
    color: #dc3545;
}
.penalty-content {
    flex: 1;
}
.penalty-time {
    font-size: 12px;
    color: #777;
}
.status-label {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: bold;
}
.status-pending { background: #f8d7da; color: #721c24; }
.status-requesting { background: #fff3cd; color: #856404; }
.status-approved { background: #d4edda; color: #155724; }
</style>

<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> Penalties</h3>
    </div>
</div>
<div class="clearfix"></div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <strong>Penalty Records</strong>
            </div>
            <div class="card-body p-0">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="penalty-item">
                            <i class="fa fa-exclamation-triangle penalty-icon"></i>
                            <div class="penalty-content">
                                <div><b><?= htmlspecialchars($row['book_title']) ?></b></div>
                                <div class="penalty-message"><?= htmlspecialchars($row['message']) ?></div>
                                <div class="penalty-time">Issued on <?= date("M d, Y h:i A", strtotime($row['created_at'])) ?></div>
                                
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <form method="POST" action="request_penalty.php" style="margin-top:6px;">
        <input type="hidden" name="penalty_id" value="<?= $row['id'] ?>">
        <textarea name="student_message" rows="2" class="form-control mb-2" 
            placeholder="Write a message to admin (e.g. I'm done with my penalty task)"></textarea>

                                        <button type="submit" class="btn btn-sm btn-warning">Request Approval</button>
                                    </form>
                                <?php elseif ($row['status'] === 'Requesting Approval'): ?>
                                    <span class="status-label status-requesting">Waiting for Admin Approval</span>
                                <?php elseif ($row['status'] === 'Approved'): ?>
                                    <span class="status-label status-approved">Approved ✅</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-3 text-center">No penalties found 🎉</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

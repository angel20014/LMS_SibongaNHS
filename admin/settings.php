<?php 
include('header.php');
include('include/dbcon.php');

if (isset($_POST['save_settings'])) {
    // Sanitize and validate inputs
    $allowed_days = intval($_POST['allowed_days']);
    $allowed_books = intval($_POST['allowed_books']);
    $message = trim($_POST['message']); // changed from penalty to message

    if ($allowed_days > 0 && $allowed_books > 0 && !empty($message)) {
        // Allowed Days
        $res_days = $con->query("SELECT * FROM allowed_days LIMIT 1");
        if ($res_days && $res_days->num_rows > 0) {
            $con->query("UPDATE allowed_days SET no_of_days = $allowed_days WHERE allowed_days_id = 1");
        } else {
            $con->query("INSERT INTO allowed_days (allowed_days_id, no_of_days) VALUES (1, $allowed_days)");
        }

        // Allowed Books
        $res_books = $con->query("SELECT * FROM allowed_book LIMIT 1");
        if ($res_books && $res_books->num_rows > 0) {
            $con->query("UPDATE allowed_book SET qntty_books = $allowed_books WHERE allowed_book_id = 1");
        } else {
            $con->query("INSERT INTO allowed_book (allowed_book_id, qntty_books) VALUES (1, $allowed_books)");
        }

        // Message
        $res_message = $con->query("SELECT * FROM penalty LIMIT 1"); // reuse penalty table for messages
        if ($res_message && $res_message->num_rows > 0) {
            $con->query("UPDATE penalty SET penalty_amount = '$message' WHERE penalty_id = 1");
        } else {
            $con->query("INSERT INTO penalty (penalty_id, penalty_amount) VALUES (1, '$message')");
        }

        echo "<script>alert('Settings saved successfully!'); window.location.href=window.location.href;</script>";
        exit();
    } else {
        echo "<script>alert('Please enter valid values!');</script>";
    }
}
?>

<!-- Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" action="">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="settingsModalLabel">Set Settings</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="form-group">
            <label for="allowed_days">Allowed Days</label>
            <input type="number" class="form-control" id="allowed_days" name="allowed_days" required min="1">
          </div>
          <div class="form-group">
            <label for="allowed_books">Allowed Books</label>
            <input type="number" class="form-control" id="allowed_books" name="allowed_books" required min="1">
          </div>
          <div class="form-group">
            <label for="message">Message / Note</label>
            <input type="text" class="form-control" id="message" name="message" required placeholder="e.g. Need to clean the room">
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="submit" name="save_settings" class="btn btn-success">Save Settings</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>


<div class="row">

<?php include('allowed_qntty.php'); ?>
<?php include('penalty.php'); ?>
<?php include('allowed_days.php'); ?>

<div class="clearfix"></div>

</div>

<?php include('footer.php'); ?>

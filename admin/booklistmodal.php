<!-- Print Books Modal -->
<div class="modal fade" id="printBooksModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <form method="GET" action="book_print.php" target="_blank">
        <div class="modal-header">
          <h4 class="modal-title">Print Books</h4>
        </div>
        <div class="modal-body">
          <!-- Date Range -->
          <label>Date From:</label>
          <input type="date" name="date_from" class="form-control"><br>
          
          <label>Date To:</label>
          <input type="date" name="date_to" class="form-control"><br>

          <div class="row">
<!-- Category (left) -->
<div class="col-md-6">
  <label>Category:</label><br>
  <input type="radio" name="category" value="all" checked> All Books <br>
  
  <?php
  $cat_query = mysqli_query($con, "SELECT DISTINCT category_name FROM category ORDER BY category_name ASC") or die(mysqli_error($con));
  while ($cat_row = mysqli_fetch_assoc($cat_query)) {
      $category_name = $cat_row['category_name'];
      echo '<input type="radio" name="category" value="'.$category_name.'"> '.$category_name.' <br>';
  }
  ?>
</div>


          <!-- Status (right) -->
<div class="col-md-6">
  <label>Status:</label><br>
  <input type="radio" name="status_option" value="all" checked> All Status <br>
  
  <?php
  $status_query = mysqli_query($con, "SELECT DISTINCT status FROM book ORDER BY status ASC") or die(mysqli_error($con));
  while ($status_row = mysqli_fetch_assoc($status_query)) {
      $status_name = $status_row['status'];
      echo '<input type="radio" name="status_option" value="'.$status_name.'"> '.$status_name.' <br>';
  }
  ?>
</div>


          </div>
        </div>
        
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger"><i class="fa fa-print"></i> Print</button>
        </div>
      </form>
    </div>
  </div>
</div>
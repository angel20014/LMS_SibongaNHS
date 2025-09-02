<?php include ('header.php'); ?>

        <div class="page-title">
            <div class="title_left">
				<!-- Button to open modal -->
<style>
.print-btn {
  background: #d9534f; /* danger red */
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  float: right; /* align right */
  
}
.print-btn:hover {
  background: #c9302c;
}
</style>


                <h3>
					<small>Home /</small> Books
                </h3>
				
            </div>
        </div>
		
<button class="print-btn" data-toggle="modal" data-target="#printBooksModal">
  <i class="fa fa-print"></i> Print Books List
</button>
        <div class="clearfix"></div>
 
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
							
							
							
                    <div class="x_title">
                        <h2><i class="fa fa-book"></i> Book List</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li>
							<a href="add_book.php" style="background:none;">
							<button class="btn btn-primary"><i class="fa fa-plus"></i> Add Book</button>
							</a>
							</li>
                            
                        </ul>
                        <div class="clearfix"></div>
							<ul class="nav nav-pills">
								<li role="presentation" class="active"><a href="book.php">All</a></li>
								<li role="presentation" ><a href="new_books.php">New Books</a></li>
								<li role="presentation"><a href="old_books.php">Old Books</a></li>
								<li role="presentation"><a href="lost_books.php">Lost Books</a></li>
								<li role="presentation"><a href="damage_books.php">Damaged Books</a></li>
								<li role="presentation"><a href="sub_rep.php">Subject for Replacement Books</a></li>
								<li role="presentation"><a href="hard_bound.php">Hardbound Books</a></li>
							</ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <!-- content starts here -->

						<div class="table-responsive">
							<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="example">
								
							<thead>
								<tr>
									<th style="width:100px;">Book Image</th>
									<th>Barcode</th>
									<th>Title</th>
									<th>Category</th>
									<th>ISBN</th>
									<th>Author/s</th>
									<th>Copies</th>
									
									<th>Status</th>
									<th>Remarks</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
							
							<?php
							$result = mysqli_query($con, "
    SELECT book.*, category.category_name 
    FROM book 
    LEFT JOIN category ON book.category_id = category.category_id 
    ORDER BY book.book_id DESC
") or die (mysqli_error($con));

							while ($row= mysqli_fetch_array ($result) ){
							$id=$row['book_id'];
						
							?>
							<tr>
								<td>
								<?php if($row['book_image'] != ""): ?>
								<img src="upload/<?php echo $row['book_image']; ?>" class="img-thumbnail" width="75px" height="50px">
								<?php else: ?>
								<img src="images/book_image.jpg" class="img-thumbnail" width="75px" height="50px">
								<?php endif; ?>
								</td>  <!--- either this <td><a target="_blank" href="view_book_barcode.php?code=<?php // echo $row['book_barcode']; ?>"><?php // echo $row['book_barcode']; ?></a></td> -->
								<td><a target="_blank" href="print_barcode_individual1.php?code=<?php echo $row['book_barcode']; ?>"><?php echo $row['book_barcode']; ?></a></td>
								<td style="word-wrap: break-word; width: 10em;"><?php echo $row['book_title']; ?></td>
								<td style="word-wrap: break-word; width: 10em;"><?php echo $row['category_name']; ?></td>
								<td style="word-wrap: break-word; width: 10em;"><?php echo $row['isbn']; ?></td>
								<td style="word-wrap: break-word; width: 10em;"><?php echo $row['author']."<br />".$row['author_2']."<br />".$row['author_3']."<br />".$row['author_4']."<br />".$row['author_5']; ?></td>
								<td><?php echo $row['book_copies']; ?></td> 
						
								<td><?php echo $row['status']; ?></td> 
								<td><?php echo $row['remarks']; ?></td> 
								<td>
									<a class="btn btn-primary" for="ViewAdmin" href="view_book.php<?php echo '?book_id='.$id; ?>">
										<i class="fa fa-eye"></i>
									</a>
									<a class="btn btn-warning" for="ViewAdmin" href="edit_book.php<?php echo '?book_id='.$id; ?>">
									<i class="fa fa-edit"></i>
									</a>
								<!--	<a class="btn btn-danger" for="DeleteAdmin" href="#delete<?php //echo $id;?>" data-toggle="modal" data-target="#delete<?php //echo $id;?>">
										<i class="glyphicon glyphicon-trash icon-white"></i>
									</a>
								-->
			
									<!-- delete modal user -->
									<div class="modal fade" id="delete<?php  echo $id;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
									<div class="modal-dialog">
										<div class="modal-content">
										<div class="modal-header">
											<h4 class="modal-title" id="myModalLabel"><i class="glyphicon glyphicon-user"></i> User</h4>
										</div>
										<div class="modal-body">
												<div class="alert alert-danger">
													Are you sure you want to delete?
												</div>
												<div class="modal-footer">
												<button class="btn btn-inverse" data-dismiss="modal" aria-hidden="true"><i class="glyphicon glyphicon-remove icon-white"></i> No</button>
												<a href="delete_user.php<?php echo '?book_id='.$id; ?>" style="margin-bottom:5px;" class="btn btn-primary"><i class="glyphicon glyphicon-ok icon-white"></i> Yes</a>
												</div>
										</div>
										</div>
									</div>
									</div>
								</td> 
							</tr>
							<?php } ?>
							</tbody>
							</table>
						</div>
						
                        <!-- content ends here -->
                    </div>
                </div>
            </div>
        </div>

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


<?php include ('footer.php'); ?>
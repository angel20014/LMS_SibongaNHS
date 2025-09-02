<?php include('header.php'); ?>
<?php include('include/dbcon.php'); ?>


<div class="page-title">
    <div class="title_left">
        <h3><small>Home /</small> Category</h3>
    </div>
</div>
<div class="clearfix"></div>


<?php
// Initialize message variable
$message = '';
$message_class = '';

// Insert new category
if (isset($_POST['add_category'])) {
    $book_id = $_POST['book_id'];
    $category_name = mysqli_real_escape_string($con, $_POST['category_name']);

    $check = mysqli_query($con, "SELECT * FROM category WHERE book_id = '$book_id' AND category_name = '$category_name'");
    if (mysqli_num_rows($check) > 0) {
        $message = "Category already exists for this book.";
        $message_class = "alert alert-warning";
    } else {
        mysqli_query($con, "INSERT INTO category (book_id, category_name) VALUES ('$book_id', '$category_name')") or die(mysqli_error($con));
        $message = "Category added successfully!";
        $message_class = "alert alert-success";
    }
}

// Update category
if (isset($_POST['update_category'])) {
    $edit_category_id = $_POST['edit_category_id'];
    $book_id = $_POST['book_id'];
    $category_name = mysqli_real_escape_string($con, $_POST['category_name']);

    mysqli_query($con, "UPDATE category SET book_id = '$book_id', category_name = '$category_name' WHERE category_id = '$edit_category_id'") or die(mysqli_error($con));
    $message = "Category updated successfully!";
    $message_class = "alert alert-info";
    echo "<meta http-equiv='refresh' content='1;url=category.php'>";
}
?>


<!-- Add Category Button aligned to the right -->
<div class="d-flex justify-content-end mb-3" style="width: 200px;">
  <style>
  <style>
  .btn-success {
    margin-left: 950px; /* or remove entirely */
  }

  .search-form {
  display: flex;
  justify-content: flex-end; /* Align all contents to the right */
  align-items: center;       /* Vertically center items */
  gap: 0.5rem;               /* Space between input and buttons */

}

  
  
</style>


  </style>
  <button class="btn btn-success" data-toggle="modal" data-target="#addCategoryModal">
   <i class="fa fa-plus"></i> Add Category 
  </button>
</div>


<!-- Display message at the top -->
<?php if ($message != ''): ?>
<div class="<?= $message_class; ?>" role="alert" style="margin-bottom: 15px;">
    <?= $message; ?>
</div>
<?php endif; ?>


<!-- Category Table -->
<div class="x_panel">
  
    <div class="x_title">
      
        <h2><i class="fa fa-table"></i> Category List</h2>

       <form method="GET" action="category.php" class="form-inline mb-3 search-form">
  <input
    type="text"
    name="search"
    class="form-control mr-2"
    placeholder="Search category or book title"
    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
  >
  <button type="submit" class="btn btn-primary">Search</button>
  <?php if (isset($_GET['search']) && $_GET['search'] != ''): ?>
    <a href="category.php" class="btn btn-secondary ml-2">Clear Search</a>
  <?php endif; ?>
</form>


        <div class="clearfix"></div>
    </div>
    <div class="x_content">
      
<div class="mb-3">
    <?php

    $filterCategory = '';
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filterCategory = mysqli_real_escape_string($con, $_GET['category']);
}
$sql = "
    SELECT category.category_id, category.category_name, book.book_id, book.book_title 
    FROM category 
    JOIN book ON category.book_id = book.book_id 
";


    ?>

</div>

<div class="mb-3">
    <?php
    // Get distinct category names with count
    $categoryCounts = mysqli_query($con, "
        SELECT category_name, COUNT(*) AS total 
        FROM category 
        GROUP BY category_name
        ORDER BY category_name ASC
    ");

    while ($cat = mysqli_fetch_assoc($categoryCounts)) {
        // Create a button with a GET parameter to filter categories by name
        $catName = htmlspecialchars($cat['category_name']);
        $total = $cat['total'];
        echo "<a href='category.php?category=" . urlencode($catName) . "' class='btn btn-info mr-2 mb-2'>
                {$catName} <span class='badge badge-light'>{$total}</span>
              </a>";
    }

    
    ?>
</div>

<?php if ($filterCategory != ''): ?>
    <a href="category.php" class="btn btn-secondary mb-3">Clear Filter</a>
<?php endif; ?>



        <div class="table-responsive">
            <table id="categoryTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Category Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
<?php
$filterCategory = '';
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filterCategory = mysqli_real_escape_string($con, $_GET['category']);
    $categories = mysqli_query($con, "
        SELECT category.category_id, category.category_name, book.book_id, book.book_title 
        FROM category 
        JOIN book ON category.book_id = book.book_id 
        WHERE category.category_name = '$filterCategory'
        ORDER BY category.category_id DESC
    ");
} elseif (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    $categories = mysqli_query($con, "
        SELECT category.category_id, category.category_name, book.book_id, book.book_title
        FROM category 
        JOIN book ON category.book_id = book.book_id
        WHERE category.category_name LIKE '%$search%' OR book.book_title LIKE '%$search%'
        ORDER BY category.category_id DESC
    ");
} else {
    $categories = mysqli_query($con, "
        SELECT category.category_id, category.category_name, book.book_id, book.book_title 
        FROM category 
        JOIN book ON category.book_id = book.book_id 
        ORDER BY category.category_id DESC
    ");
}

if (mysqli_num_rows($categories) > 0) {
    while ($row = mysqli_fetch_assoc($categories)) {
        echo "<tr>
                <td>{$row['book_title']}</td>
                <td>{$row['category_name']}</td>
                <td>
                   <button 
  class='btn btn-primary btn-sm editBtn'
  data-id='{$row['category_id']}'
  data-bookid='{$row['book_id']}'
  data-category='{$row['category_name']}'
  data-toggle='modal' 
  data-target='#editModal'>
  <i class='fa fa-edit'></i>
</button>

                   <button 
                      class='btn btn-danger btn-sm deleteBtn'
                      data-id='{$row['category_id']}'
                      data-toggle='modal' 
                      data-target='#deleteModal'>
                      <i class='fa fa-trash'></i>
                   </button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3' class='text-center'>No results found.</td></tr>";
}
?>
</tbody>

            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" action="">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <div class="form-group">
            <label>Book Title:</label>
            <select name="book_id" class="form-control" required>
              <option value="">Select Book</option>
              <?php
              $books = mysqli_query($con, "SELECT book_id, book_title FROM book ORDER BY book_title ASC");
              while ($book = mysqli_fetch_assoc($books)) {
                  echo "<option value='{$book['book_id']}'>{$book['book_title']}</option>";
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Category Name:</label>
            <input type="text" name="category_name" class="form-control" required>
          </div>

        </div>
        <div class="modal-footer">
  <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
</div>

      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form method="POST" action="">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Category</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_category_id" id="edit_category_id">
          
          <div class="form-group">
            <label>Book Title:</label>
           <select name="book_id" class="form-control" required id="edit_book_id">
  <option value="">Select Book</option>
  <?php
  $books = mysqli_query($con, "SELECT book_id, book_title FROM book ORDER BY book_title ASC");
  while ($book = mysqli_fetch_assoc($books)) {
      echo "<option value='{$book['book_id']}'>{$book['book_title']}</option>";
  }
  ?>
</select>

          </div>

          <div class="form-group">
            <label>Category Name:</label>
            <input type="text" name="category_name" id="edit_category_name" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_category" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form method="GET" action="delete_category.php">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirm Delete</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this category?
          <input type="hidden" name="category_id" id="delete_category_id">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Delete</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php include('footer.php'); ?>

<script>

    $(document).ready(function () {
    $('.editBtn').click(function () {
        var id = $(this).data('id');
        var category = $(this).data('category');
        var bookId = $(this).data('bookid');
        $('#edit_category_id').val(id);
        $('#edit_category_name').val(category);
        $('#edit_book_id').val(bookId).trigger('change'); // This triggers update for select element
    });
});


    $('.deleteBtn').click(function() {
        var id = $(this).data('id');
        $('#delete_category_id').val(id);
    });

    // DataTables initialization
    $('#categoryTable').DataTable({
        "lengthMenu": [[8, 10, 25, 50, -1], [8, 10, 25, 50, "All"]],
        "pageLength": 8
    });
});

</script>

<?php
include ('include/dbcon.php');
session_start();

// Your session checks here
if(!isset($_SESSION['id'])){
    header("Location: index.php");
    exit();
}
$success_msg = "";
$error_msg = "";

// Get last barcode
$query = mysqli_query($con, "SELECT mid_barcode FROM `barcode` ORDER BY mid_barcode DESC LIMIT 1") or die(mysqli_error($con));
$fetch = mysqli_fetch_array($query);

$mid_barcode = ($fetch && isset($fetch['mid_barcode'])) ? $fetch['mid_barcode'] : 1000;
$new_barcode = $mid_barcode + 1;
$pre_barcode = "SNHS";
$suf_barcode = "LMS";
$generate_barcode = $pre_barcode . $new_barcode . $suf_barcode;

if (!isset($_FILES['image']['tmp_name'])) {
    echo "";
} else {
    $file = $_FILES['image']['tmp_name'];
    $image = $_FILES["image"]["name"];
    $image_name = addslashes($_FILES['image']['name']);
    $size = $_FILES["image"]["size"];
    $error = $_FILES["image"]["error"];

    if($size > 10000000) {
        die("Format is not allowed or file size is too big!");
    } else {
        move_uploaded_file($_FILES["image"]["tmp_name"], "upload/" . $_FILES["image"]["name"]);            
        $book_image = $_FILES["image"]["name"];
        
        // Get form inputs
        $book_title = trim($_POST['book_title']);
        $author = $_POST['author'];
        $author_2 = $_POST['author_2'];
        $author_3 = $_POST['author_3'];
        $author_4 = $_POST['author_4'];
        $author_5 = $_POST['author_5'];
        $book_copies = $_POST['book_copies'];
        $book_pub = $_POST['book_pub'];
        $publisher_name = $_POST['publisher_name'];
        $isbn = $_POST['isbn'];
        $copyright_year = $_POST['copyright_year'];
        $status = $_POST['status'];

        $pre = "SNHS";
        $mid = $_POST['new_barcode'];
        $suf = "LMS";
        $gen = $pre . $mid . $suf;

        if($status == 'Lost' || $status == 'Damaged'){
            $remark = 'Not Available';
        } else {
            $remark = 'Available';
        }

        // ✅ Check if book title already exists (with fuzzy matching)
$book_title = strtolower(trim($_POST['book_title']));

// Get all titles
$all_books = mysqli_query($con, "SELECT book_title FROM book") or die(mysqli_error($con));

$isDuplicate = false;
while($row_book = mysqli_fetch_assoc($all_books)){
    $existing_title = strtolower(trim($row_book['book_title']));
    
    // Compute similarity percentage
    similar_text($book_title, $existing_title, $percent);

    // Or levenshtein distance
    $distance = levenshtein($book_title, $existing_title);

    // Rule: if >80% similar OR ≤2 edits away → consider duplicate
    if($percent >= 80 || $distance <= 2){
        $isDuplicate = true;
        break;
    }
}

if($isDuplicate){
    $error_msg = "⚠️ Book title '<b>$book_title</b>' is too similar to an existing book!";
} else {
    
    $category_name = $_POST['category'];

    // Check if category already exists
    $cat_query = mysqli_query($con, "SELECT category_id FROM category WHERE category_name = '$category_name'") or die(mysqli_error($con));
    if(mysqli_num_rows($cat_query) > 0){
        $cat_row = mysqli_fetch_assoc($cat_query);
        $category_id = $cat_row['category_id'];
    } else {
        mysqli_query($con, "INSERT INTO category (category_name) VALUES ('$category_name')") or die(mysqli_error($con));
        $category_id = mysqli_insert_id($con);
    }

    // Insert book
    mysqli_query($con, "INSERT INTO book 
        (book_title, author, author_2, author_3, author_4, author_5, book_copies, book_pub, publisher_name, isbn, copyright_year, status, book_barcode, book_image, date_added, remarks, category_id)
        VALUES ('$book_title', '$author', '$author_2', '$author_3', '$author_4', '$author_5', '$book_copies', '$book_pub', '$publisher_name', '$isbn', '$copyright_year', '$status', '$gen', '$book_image', NOW(), '$remark', '$category_id')") 
    or die(mysqli_error($con));

    // Insert barcode
    mysqli_query($con, "INSERT INTO barcode (pre_barcode, mid_barcode, suf_barcode) 
        VALUES ('$pre', '$mid', '$suf')") or die(mysqli_error($con));

    // Store message in session
$_SESSION['success_msg'] = "✅ Book '<b>$book_title</b>' added successfully!";

// Redirect immediately to book.php
header("Location: book.php");
exit();
}
    }
}
?>



<?php include ('header.php'); ?>

        <div class="page-title">
            <div class="title_left">
                <h3>
					<small>Home / Books /</small> Add Book
                </h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><i class="fa fa-plus"></i> Add Book</h2>
                        <ul class="nav navbar-right panel_toolbox">
                           
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                       
                        <!-- content starts here -->

                            <form method="post" enctype="multipart/form-data" class="form-horizontal form-label-left">
							<input type="hidden" name="new_barcode" value="<?php echo $new_barcode; ?>">
							
                                <div class="form-group">
    <label class="control-label col-md-4">Title <span style="color:red;">*</span></label>
    <div class="col-md-4">
        <input type="text" id="book_title" name="book_title" required class="form-control col-md-7 col-xs-12">
        <small id="title-status"></small>
    </div>
</div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="first-name">Author 1 <span class="required" style="color:red;">*</span>
                                    </label>
                                    <div class="col-md-4">
                                        <input type="text" name="author" id="first-name2" required="required" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="first-name">Author 2
                                    </label>
                                    <div class="col-md-4">
                                        <input type="text" name="author_2" id="first-name2" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="first-name">Author 3
                                    </label>
                                    <div class="col-md-4">
                                        <input type="text" name="author_3" id="first-name2" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="first-name">Author 4
                                    </label>
                                    <div class="col-md-4">
                                        <input type="text" name="author_4" id="first-name2" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="first-name">Author 5
                                    </label>
                                    <div class="col-md-4">
                                        <input type="text" name="author_5" id="first-name2" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="last-name">Publication
                                    </label>
                                    <div class="col-md-4">
                                        <input type="text" name="book_pub" id="last-name2" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="last-name">Publisher
                                    </label>
                                    <div class="col-md-4">
                                        <input type="text" name="publisher_name" id="last-name2" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="last-name">ISBN <span class="required" style="color:red;">*</span>
                                    </label>
                                    <div class="col-md-4">
                                        <input type="text" name="isbn" id="last-name2" class="form-control col-md-7 col-xs-12" required="required">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="last-name">Copyright
                                    </label>
                                    <div class="col-md-4">
                                        <input type="text" name="copyright_year" id="last-name2" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="last-name">Copies <span class="required" style="color:red;">*</span>
                                    </label>
                                    <div class="col-md-1">
                                        <input type="number" name="book_copies" step="1" min="0" max="1000" required="required"  class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="last-name">Status <span class="required" style="color:red;">*</span>
                                    </label>
									<div class="col-md-4">
                                        <select name="status" class="select2_single form-control" tabindex="-1" required="required">
											<option value="New">New</option>
											<option value="Old">Old</option>
											<option value="Lost">Lost</option>
											<option value="Damaged">Damaged</option>
											<option value="Replacement">Replacement</option>
											<option value="Hardbound">Hardbound</option>
                                        </select>
                                    </div>
                                </div>
                              
                                
                            

                                <div class="form-group">
                                    <label class="control-label col-md-4" for="last-name">Book Image
                                    </label>
                                    <div class="col-md-4">
                                        <input type="file" style="height:44px;" name="image" id="last-name2" class="form-control col-md-7 col-xs-12">
                                    </div>
                                </div>
                               <div class="form-group">
    <label class="control-label col-md-4" for="category">Category</label>
    <div class="col-md-4">
        <select name="category" class="form-control" required>
            <option value="">-- Select Category --</option>
            <option value="LANGUAGE">LANGUAGE</option>
            <option value="ENGLISH">ENGLISH</option>
            <option value="FICTION">FICTION</option>
            <option value="NON-FICTION">NON-FICTION</option>
            <option value="SCIENCE">SCIENCE</option>
            <option value="MATHEMATICS">MATHEMATICS</option>
            <option value="HISTORY">HISTORY</option>
            <option value="MAGAZINE">MAGAZINE</option>
            <!-- Add more categories as needed -->
        </select>
    </div>
</div>



                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                                        <a href="book.php"><button type="button" class="btn btn-primary"><i class="fa fa-times-circle-o"></i> Cancel</button></a>
                                        <button type="submit" name="submit" class="btn btn-success"><i class="fa fa-plus-square"></i> ADD</button>
                                    </div>
                                </div>
                            </form>
							
            
						
 

                        <!-- content ends here -->
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $("#book_title").on("keyup blur", function(){
        var title = $(this).val().trim();
        if(title.length > 0){
            $.ajax({
                url: "check_book.php",
                method: "POST",
                data: { book_title: title },
                success: function(response){
                    if(response === "exists"){
                        $("#title-status").text("⚠️ Book title already exists!")
                                         .css("color","red");
                        $("button[name='submit']").prop("disabled", true);
                    } else {
                        $("#title-status").text("✅ Available")
                                         .css("color","green");
                        $("button[name='submit']").prop("disabled", false);
                    }
                }
            });
        } else {
            $("#title-status").text("");
            $("button[name='submit']").prop("disabled", false);
        }
    });
});
</script>

<?php include ('footer.php'); ?>


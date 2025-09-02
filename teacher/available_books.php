<?php
include('header.php');
include('include/dbcon.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../index.php");
    exit();
}

// ✅ Get categories
$category_query = mysqli_query($con, "
    SELECT DISTINCT c.category_name
    FROM category c
    INNER JOIN book b ON b.category_id = c.category_id
    ORDER BY c.category_name ASC
") or die(mysqli_error($con));

$category_filter = " WHERE 1=1 ";

if (isset($_GET['category']) && $_GET['category'] !== "all") {
    $category_name = mysqli_real_escape_string($con, $_GET['category']);
    $category_filter .= " AND c.category_name = '$category_name' ";
}

// ✅ Get books
$book_query = mysqli_query($con, "
    SELECT 
        b.book_id, 
        b.book_title, 
        b.book_copies, 
        b.book_image,
        b.category_id,
        c.category_name
    FROM book b
    LEFT JOIN category c ON b.category_id = c.category_id
    $category_filter
    AND (b.remarks IS NULL OR b.remarks = '' OR b.remarks = 'available')
    ORDER BY b.date_added DESC
") or die(mysqli_error($con));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Books (Teacher)</title>
    <style>
        /* Disable page scroll */
        body {
            overflow-y: hidden;
        }

        /* Scrollable books container */
        .books-container {
            max-height: 600px; /* adjust as needed */
            overflow-y: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding-right: 10px;
        }

        /* Individual book card */
        .book-card-inner {
            margin-bottom: 20px;
            transition: transform 0.2s;
            flex: 0 0 calc(16.66% - 10px); /* 6 cards per row on large screens */
            display: flex;
            flex-direction: column;
        }

        .book-card-inner:hover {
            transform: scale(1.02);
        }

        .book-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1rem;
            font-weight: bold;
            min-height: 30px;
        }

        .availability {
            color: #28a745;
            font-weight: bold;
            margin: 5px 0;
        }

        .btn-block {
            width: 100%;
        }

        .category-buttons {
            margin-bottom: 20px;
        }

        .category-buttons a {
            margin-right: 5px;
        }
    </style>
</head>
<body>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success" style="margin:20px;">
        <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger" style="margin:20px;">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

<div class="flex items-center justify-between mb-3">
    <h2 class="mb-0 text-xl font-semibold">Available Books</h2>
    <div><input type="text" class="form-control" id="searchInput" placeholder="Search..." style="width: 280px; margin-left: 800px;"></div>
</div>

<div class="category-buttons mb-4">
    <a href="?category=all" class="btn btn-outline-primary btn-sm me-2 mb-2">ALL</a>
    <?php while ($cat = mysqli_fetch_assoc($category_query)): ?>
        <a href="?category=<?php echo urlencode($cat['category_name']); ?>" 
           class="btn btn-outline-primary btn-sm me-2 mb-2">
            <?php echo htmlspecialchars($cat['category_name']); ?>
        </a>
    <?php endwhile; ?>
</div>

<!-- Scrollable Book Cards Container -->
<div class="books-container">
    <?php while ($row = mysqli_fetch_assoc($book_query)): ?>
        <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
            <div class="card book-card-inner">
                <?php
                $imgPath = "upload/" . $row['book_image'];
                $imgSrc = (!empty($row['book_image']) && file_exists($imgPath)) ? $imgPath : "images/no-image.png";
                ?>
                <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="card-img-top book-image" alt="Book Image">
                <div class="card-body text-center">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['book_title']); ?></h5>
                    <p class="availability">Available: <?php echo (int)$row['book_copies']; ?></p>

                    <?php if ((int)$row['book_copies'] > 0): ?>
                        <button class="btn btn-primary btn-block borrow-btn" 
                                data-book-id="<?php echo $row['book_id']; ?>" 
                                data-book-title="<?php echo htmlspecialchars($row['book_title']); ?>" 
                                data-book-available="<?php echo (int)$row['book_copies']; ?>">Borrow</button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-block" disabled>Not Available</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- Borrow Confirmation Modal -->
<div id="borrowModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
  <div style="background:#fff; padding:20px; border-radius:8px; max-width:400px; margin:100px auto; position:relative;">
    <span id="modalClose" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px;">&times;</span>
    <h4>Confirm Borrow</h4>
    <p>Are you sure you want to borrow the book:</p>
    <p><strong id="modalBookTitle"></strong>?</p>
    <form id="borrowForm" method="GET" action="borrow.php" novalidate>
    <input type="hidden" name="book_id" id="modalBookId" value="">
    <label for="borrowQuantity">Quantity to borrow:</label><br>
    <input type="number" name="qty"   id="borrowQuantity" value="1" requiredstyle="width:60px; margin-bottom:15px;">

    <br>
    <button type="button" id="modalCancel" style="margin-right:10px;">Cancel</button>
    <button type="submit" class="btn btn-primary">Confirm Borrow</button>
</form>

  </div>
</div>

</body>
</html>

<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
  const filter = this.value.toLowerCase();

  document.querySelectorAll('.book-card').forEach(card => {
    const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
    card.style.display = title.includes(filter) ? '' : 'none';
  });
});

// Modal functions
function openBorrowModal(bookId, bookTitle, available) {
    document.getElementById('modalBookId').value = bookId;
    document.getElementById('modalBookTitle').textContent = bookTitle;
    const quantityInput = document.getElementById('borrowQuantity');
    quantityInput.removeAttribute("max");  
    quantityInput.value = 1;
    document.getElementById('borrowModal').style.display = 'block';
}

document.getElementById('modalClose').addEventListener('click', () => {
    document.getElementById('borrowModal').style.display = 'none';
});
document.getElementById('modalCancel').addEventListener('click', () => {
    document.getElementById('borrowModal').style.display = 'none';
});

// Attach button click
document.querySelectorAll('.borrow-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const bookId = btn.getAttribute('data-book-id');
      const bookTitle = btn.getAttribute('data-book-title');
      const available = btn.getAttribute('data-book-available');
      openBorrowModal(bookId, bookTitle, available);
    });
});

</script>

<?php include('footer.php'); ?>

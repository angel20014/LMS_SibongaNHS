<?php include ('header.php'); ?>
<style>
    body {
        overflow-y: auto; /* allow scrolling */
    }
    .tile-container {
        background-color: #f2f2f2;
        border-radius: 10px;
        padding: 15px;
        margin: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }
    .count {
        font-size: 50px;
        font-weight: bold;
        color: green;
        margin-top: 10px;
    }
    .count_top {
        font-size: 20px;
        font-weight: bold;
    }
    .tile_row {
        display: flex;
        flex-wrap: nowrap;
        padding: 10px;
    }
    .tile_stats_count {
        min-width: 160px;
        flex: 0 0 auto;
    }
    .recent-table {
        background: white;
        padding: 15px;
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .recent-table h4 {
        margin-bottom: 15px;
    }
</style>

<div class="tile_row">
    <!-- Admin -->
    <div class="tile_stats_count tile-container">
        <?php $result = mysqli_query($con, "SELECT * FROM admin"); ?>
        <a href="admin.php">
            <span class="count_top"><i class="fa fa-users"></i> Admin</span>
        </a>
        <div class="count"><?php echo mysqli_num_rows($result); ?></div>
    </div>

    <!-- Students -->
    <div class="tile_stats_count tile-container">
        <?php $student_result = mysqli_query($con, "SELECT * FROM students"); ?>
        <a href="user.php">
            <span class="count_top"><i class="fa fa-male"></i> Students</span>
        </a>
        <div class="count"><?php echo mysqli_num_rows($student_result); ?></div>
    </div>

    <!-- Teachers -->
    <div class="tile_stats_count tile-container">
        <?php $teacher_result = mysqli_query($con, "SELECT * FROM teachers"); ?>
        <a href="user.php">
            <span class="count_top"><i class="fa fa-female"></i> Teachers</span>
        </a>
        <div class="count"><?php echo mysqli_num_rows($teacher_result); ?></div>
    </div>

    <!-- Books -->
    <div class="tile_stats_count tile-container">
        <?php $result = mysqli_query($con, "SELECT * FROM book"); ?>
        <a href="book.php">
            <span class="count_top"><i class="fa fa-book"></i> Books</span>
        </a>
        <div class="count"><?php echo mysqli_num_rows($result); ?></div>
    </div>

    <!-- Borrowed -->
    <div class="tile_stats_count tile-container">
        <?php $result = mysqli_query($con, "SELECT * FROM borrow_book"); ?>
        <a href="borrowed.php">
            <span class="count_top"><i class="fa fa-book"></i> Borrowed</span>
        </a>
        <div class="count"><?php echo mysqli_num_rows($result); ?></div>
    </div>

    <!-- Returned -->
    <div class="tile_stats_count tile-container">
        <?php $result = mysqli_query($con, "SELECT * FROM return_book WHERE return_status = 'Accepted'"); ?>
        <a href="returned_book.php">
            <span class="count_top"><i class="fa fa-book"></i> Returned</span>
        </a>
        <div class="count"><?php echo mysqli_num_rows($result); ?></div>
    </div>
</div>

<!-- Recently Added & Activity Tables -->
<div class="row">
    <!-- Recently Added Books -->
    <div class="col-md-4">
    <div class="recent-table">
        <h4>📚 Books Added Today</h4>
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author(s)</th>
                    <th>Date Added</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $today = date('Y-m-d');
                $book_query = mysqli_query($con, "
                    SELECT book_title, author, author_2, author_3, author_4, author_5, date_added 
                    FROM book 
                    WHERE DATE(date_added) = '$today' 
                    ORDER BY date_added DESC
                ");
                if (mysqli_num_rows($book_query) == 0) {
                    echo '<tr><td colspan="3" class="text-center">No books added today.</td></tr>';
                } else {
                    while ($book = mysqli_fetch_assoc($book_query)) {
                        $authors = array_filter([
                            $book['author'],
                            $book['author_2'],
                            $book['author_3'],
                            $book['author_4'],
                            $book['author_5']
                        ]);
                        $author_list = implode(', ', $authors);

                        echo "<tr>
                            <td>".htmlspecialchars($book['book_title'])."</td>
                            <td>".htmlspecialchars($author_list)."</td>
                            <td>".htmlspecialchars($book['date_added'])."</td>
                        </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


   <!-- Recently Registered Users (students + teachers, registered today only) -->
<div class="col-md-4">
    <div class="recent-table">
        <h4>🧑 Users Registered Today</h4>
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $user_query = mysqli_query($con, "
                    SELECT firstname, lastname, date_registered
                    FROM students
                    WHERE DATE(date_registered) = CURDATE()
                    UNION ALL
                    SELECT firstname, lastname, date_registered
                    FROM teachers
                    WHERE DATE(date_registered) = CURDATE()
                    ORDER BY date_registered DESC
                    LIMIT 5
                ");
                if (mysqli_num_rows($user_query) == 0) {
                    echo '<tr><td colspan="2" class="text-center">No users registered today.</td></tr>';
                } else {
                    while ($user = mysqli_fetch_assoc($user_query)) {
                        echo "<tr>
                            <td>".htmlspecialchars($user['firstname'])." ".htmlspecialchars($user['lastname'])."</td>
                            <td>".htmlspecialchars($user['date_registered'])."</td>
                        </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Recently Borrowed Books -->
<div class="col-md-4">
    <div class="recent-table">
        <h4>📖 Books Borrowed Today</h4>
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Borrower</th>
                    <th>Title</th>
                    <th>Date Borrowed</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $today = date('Y-m-d');
                $borrow_query = mysqli_query($con, "
    SELECT 
        COALESCE(s.firstname, t.firstname) AS firstname, 
        COALESCE(s.lastname, t.lastname) AS lastname,
        b.book_title AS title,
        br.date_borrowed
    FROM borrow_book br
    JOIN book b ON br.book_id = b.book_id
    LEFT JOIN students s ON br.student_id = s.student_id
    LEFT JOIN teachers t ON br.teacher_id = t.teacher_id
    WHERE DATE(br.date_borrowed) = '$today'
    ORDER BY br.date_borrowed DESC
");

                if (mysqli_num_rows($borrow_query) == 0) {
                    echo '<tr><td colspan="3" class="text-center">No books borrowed today.</td></tr>';
                } else {
                    while ($borrow = mysqli_fetch_assoc($borrow_query)) {
                        $borrowerFullName = htmlspecialchars($borrow['firstname'] . ' ' . $borrow['lastname']);
                        $bookTitle = htmlspecialchars($borrow['title']);
                        $dateBorrowed = htmlspecialchars($borrow['date_borrowed']);
                        echo "<tr>
                            <td>{$borrowerFullName}</td>
                            <td>{$bookTitle}</td>
                            <td>{$dateBorrowed}</td>
                        </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>

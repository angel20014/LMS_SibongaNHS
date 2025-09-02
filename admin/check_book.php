<?php
include ('include/dbcon.php');

if(isset($_POST['book_title'])){
    $book_title = strtolower(trim($_POST['book_title']));
    $all_books = mysqli_query($con, "SELECT book_title FROM book");
    $isDuplicate = false;

    while($row = mysqli_fetch_assoc($all_books)){
        $existing = strtolower(trim($row['book_title']));
        similar_text($book_title, $existing, $percent);
        $distance = levenshtein($book_title, $existing);

        if($percent >= 80 || $distance <= 2){
            $isDuplicate = true;
            break;
        }
    }

    if($isDuplicate){
        echo "exists";
    } else {
        echo "available";
    }
}
?>

<?php
session_start();
require_once 'db_connect.php';

// Ensure user is a standard user
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'Librarian')) {
    header("Location: login.php");
    exit();
}
// Get action, book ID, and current page from the URL.
$action = $_GET['action'] ?? '';
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
$user_id = $_SESSION['user_id']; // Get the current user's ID
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

if ($book_id > 0 && $user_id > 0) {
    if ($action === 'borrow') {
        // Update the book's status to 'Borrowed'
        $sql_book = "UPDATE books SET status = 'Borrowed' WHERE book_id = ? AND status = 'Available'";
        $stmt_book = $mysqli->prepare($sql_book);
        $stmt_book->bind_param("i", $book_id);
        $stmt_book->execute();

        //  Create a new record in borrow_records
        if ($stmt_book->affected_rows > 0) {
            $sql_record = "INSERT INTO borrow_records (book_id, user_id, borrow_date) VALUES (?, ?, NOW())";
            $stmt_record = $mysqli->prepare($sql_record);
            $stmt_record->bind_param("ii", $book_id, $user_id);
            $stmt_record->execute();
            $stmt_record->close();
        }
        $stmt_book->close();

    } elseif ($action === 'return') {
        //  Update the book's status to 'Available'
        $sql_book = "UPDATE books SET status = 'Available' WHERE book_id = ?";
        $stmt_book = $mysqli->prepare($sql_book);
        $stmt_book->bind_param("i", $book_id);
        $stmt_book->execute();

        //  Update the borrow record by setting the return_date
        if ($stmt_book->affected_rows > 0) {
            // finds the open-ended borrow record for this book and user and closes it.
            $sql_record = "UPDATE borrow_records SET return_date = NOW() WHERE book_id = ? AND user_id = ? AND return_date IS NULL";
            $stmt_record = $mysqli->prepare($sql_record);
            $stmt_record->bind_param("ii", $book_id, $user_id);
            $stmt_record->execute();
            $stmt_record->close();
        }
        $stmt_book->close();
    }
}

// Redirect back to the catalog page
header("Location: catalog.php?page=" . $page);
exit();
?>
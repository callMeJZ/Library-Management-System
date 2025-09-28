<?php
session_start();

require_once 'db_connect.php';

// Ensure user is a standard user
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'Librarian')) {
    header("Location: login.php");
    exit();
}

$action = $_GET['action'] ?? '';
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
$user_id = $_SESSION['user_id']; // Get the current user's ID
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

if ($book_id > 0 && $user_id > 0) {
    if ($action === 'borrow') {
        // 1. Update the book's status to 'Borrowed'
        $sql_book = "UPDATE books SET status = 'Borrowed' WHERE book_id = ? AND status = 'Available'";
        $stmt_book = $mysqli->prepare($sql_book);
        $stmt_book->bind_param("i", $book_id);
        $stmt_book->execute();

        // 2. Create a new record in borrow_records
        if ($stmt_book->affected_rows > 0) {
            $sql_record = "INSERT INTO borrow_records (book_id, user_id, borrow_date) VALUES (?, ?, NOW())";
            $stmt_record = $mysqli->prepare($sql_record);
            $stmt_record->bind_param("ii", $book_id, $user_id);
            $stmt_record->execute();
            $stmt_record->close();
        }
        $stmt_book->close();

    } elseif ($action === 'return') {
        // 1. Update the book's status to 'Available'
        $sql_book = "UPDATE books SET status = 'Available' WHERE book_id = ?";
        $stmt_book = $mysqli->prepare($sql_book);
        $stmt_book->bind_param("i", $book_id);
        $stmt_book->execute();

        // 2. Update the borrow record by setting the return_date
        if ($stmt_book->affected_rows > 0) {
            // This finds the open-ended borrow record for this book and user and closes it.
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
if ($_SESSION['role'] != "User") {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Borrow book
if (isset($_GET['borrow'])) {
    $book_id = $_GET['borrow'];
    $conn->query("INSERT INTO borrow_records (book_id, user_id) VALUES ($book_id, $user_id)");
    $conn->query("UPDATE books SET status='Borrowed' WHERE book_id=$book_id");
    header("Location: borrow_return.php");
    exit();
}

// Return book
if (isset($_GET['return'])) {
    $book_id = $_GET['return'];
    $conn->query("UPDATE borrow_records 
                  SET return_date=NOW() 
                  WHERE book_id=$book_id AND user_id=$user_id AND return_date IS NULL");
    $conn->query("UPDATE books SET status='Available' WHERE book_id=$book_id");
    header("Location: borrow_return.php");
    exit();
}

// Show all books
$result = $conn->query("SELECT * FROM books");
?>

<h2>Borrow / Return Books</h2>
<table border="1" cellpadding="5">
<tr>
    <th>ID</th><th>Title</th><th>Status</th><th>Action</th>
</tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['book_id'] ?></td>
    <td><?= $row['title'] ?></td>
    <td><?= $row['status'] ?></td>
    <td>
        <?php if ($row['status'] == "Available"): ?>
            <a href="borrow_return.php?borrow=<?= $row['book_id'] ?>">Borrow</a>
        <?php else: ?>
            <a href="borrow_return.php?return=<?= $row['book_id'] ?>">Return</a>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

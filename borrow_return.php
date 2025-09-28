<?php
session_start();
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

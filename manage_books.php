<?php 
include 'db_connect.php';
session_start();

// Check if user is logged in and is a librarian
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Librarian') {
    header("Location: login.php");
    exit();
}

$message = "";
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && $book_id > 0) {
    $delete_sql = "DELETE FROM books WHERE book_id = ?";
    $stmt = $mysqli->prepare($delete_sql);
    $stmt->bind_param("i", $book_id);
    
    if ($stmt->execute()) {
        header("Location: librarian_dashboard.php");
        exit();
    } else {
        $message = "<p style='color: red;'>Error deleting book: " . $mysqli->error . "</p>";
    }
    $stmt->close();
}

// Handle update form submission
if (isset($_POST['update_book']) && $book_id > 0) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $publication_year = $_POST['publication_year'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    
    $update_sql = "UPDATE books SET title = ?, author = ?, isbn = ?, publication_year = ?, category = ?, status = ? 
                   WHERE book_id = ?";
    
    $stmt = $mysqli->prepare($update_sql);
    $stmt->bind_param("ssssssi", $title, $author, $isbn, $publication_year, $category, $status, $book_id);
    
    if ($stmt->execute()) {
        $message = "<p style='color: green;'>Book updated successfully!</p>";
        echo "<script>setTimeout(function() { window.location.href = 'librarian_dashboard.php'; }, 2000);</script>";
    } else {
        $message = "<p style='color: red;'>Error updating book: " . $mysqli->error . "</p>";
    }
    
    $stmt->close();
}

// Fetch book details
if ($book_id > 0) {
    $sql = "SELECT * FROM books WHERE book_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
    } else {
        header("Location: librarian_dashboard.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: librarian_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Book</title>
</head>
<body>
    <div>
        <h1>Manage Book</h1>
        <div style="text-align: right; margin-bottom: 20px;">
            <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
            <a href="logout.php"><button>Logout</button></a>
        </div>
        
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <table>
                <tr>
                    <td><label for="title">Title:</label></td>
                    <td><input type="text" name="title" id="title" value="<?php echo htmlspecialchars($book['title']); ?>" required></td>
                </tr>
                <tr>
                    <td><label for="author">Author:</label></td>
                    <td><input type="text" name="author" id="author" value="<?php echo htmlspecialchars($book['author']); ?>" required></td>
                </tr>
                <tr>
                    <td><label for="isbn">ISBN:</label></td>
                    <td><input type="text" name="isbn" id="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" required></td>
                </tr>
                <tr>
                    <td><label for="publication_year">Publication Year:</label></td>
                    <td><input type="number" name="publication_year" id="publication_year" min="1000" max="2024" 
                               value="<?php echo htmlspecialchars($book['publication_year']); ?>" required></td>
                </tr>
                <tr>
                    <td><label for="category">Category:</label></td>
                    <td><input type="text" name="category" id="category" value="<?php echo htmlspecialchars($book['category']); ?>" required></td>
                </tr>
                <tr>
                    <td><label for="status">Status:</label></td>
                    <td>
                        <select name="status" id="status" required>
                            <option value="Available" <?php echo ($book['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                            <option value="Borrowed" <?php echo ($book['status'] == 'Borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" name="update_book" value="Update Book">
                        <a href="?book_id=<?php echo $book_id; ?>&action=delete" 
                           onclick="return confirm('Are you sure you want to delete this book?')">
                            <button type="button">Delete Book</button>
                        </a>
                        <a href="librarian_dashboard.php"><button type="button">Cancel</button></a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>
<?php 
include 'db_connect.php';
session_start();

// Check if user is logged in and is a librarian
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Librarian') {
    header("Location: login.php");
    exit();
}

// Fetch top 5 books from database
$sql = "SELECT * FROM books ORDER BY book_id DESC LIMIT 5";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard</title>
</head>
<body>
    <div>
        <h1>Librarian Dashboard</h1>
        <div style="text-align: right; margin-bottom: 20px;">
            <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
            <a href="logout.php"><button>Logout</button></a>
        </div>
        
        <div style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="add_book.php"><button>+ Add Book</button></a>
                <a href="catalog.php"><button>View All Books</button></a>
            </div>
        </div>

        <h2>Recent Books</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Year</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                            <td><?php echo htmlspecialchars($book['publication_year']); ?></td>
                            <td><?php echo htmlspecialchars($book['category']); ?></td>
                            <td><?php echo htmlspecialchars($book['status']); ?></td>
                            <td>
                                <a href="manage_books.php?book_id=<?php echo $book['book_id']; ?>"><button>Edit</button></a>
                                <a href="manage_books.php?book_id=<?php echo $book['book_id']; ?>&action=delete" 
                                   onclick="return confirm('Are you sure you want to delete this book?')"><button>Delete</button></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No books found in the library.</p>
        <?php endif; ?>
    </div>
</body>
</html>
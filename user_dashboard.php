<?php 

include 'db_connect.php';

?><?php 
include 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
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
    <title>User Dashboard</title>
</head>
<body>
    <div>
        <h1>User Dashboard</h1>
        <div style="text-align: right; margin-bottom: 20px;">
            <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
            <a href="logout.php"><button>Logout</button></a>
        </div>
        
        <div style="margin-bottom: 20px;">
            <div style="text-align: right;">
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
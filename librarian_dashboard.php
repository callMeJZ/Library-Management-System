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
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #ffffffff;
        }

        h1 { 
            color: #333; 
            margin-bottom: 20px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-top: 30px;
            margin-bottom: 8px;
            text-align: center;
        }


        .header-section {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
        }

        .welcome-section{
           margin-top: 10px
        
        }

        .welcome-section p{
           color: #0a0909ff;
           font-size: 20px;
           font-weight: bold;
        }
        button, input[type="submit"] {
            padding: 10px 20px;
            background-color: #4973c0;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            transition: background-color 0.3s;
        }

        button:hover, input[type="submit"]:hover {
            background-color: #43629bff;
        }
        .add-book-btn {
            background-color: #50589C;
            font-weight: bold;
        }

        .add-book-btn:hover {
            background-color: #444979ff;
        }

        .view-all-btn {
            background-color: #4973c0;
        }

        .view-all-btn:hover {
            background-color: #456299ff;
        }

        .edit-btn {
            background-color: #3d8bf0ff;
            color: #212529;
            padding: 6px 12px;
            font-size: 12px;
        }

        .edit-btn:hover {
            background-color: #1c6dafff;
        }

        .delete-btn {
            background-color: #d46772ff;
            padding: 6px 12px;
            font-size: 12px;
        }

        .delete-btn:hover {
            background-color: #db4150ff;
        }

        .logout-btn {
            background-color: #102231ff;
            padding: 8px 16px;
            margin-top: 10px
        }

        .logout-btn:hover {
            background-color: #04111bff;
        }

        /* Action buttons section */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #ffffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* Table styles */
        .book-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.5);
            border-radius: 8px;
            overflow: hidden;
        }

        .book-table th, .book-table td { 
            border: 1px solid #dee2e6; 
            padding: 12px; 
            text-align: left; 
        }

        .book-table th { 
            background-color: #f8f9fa; 
            font-weight: bold;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        .book-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Status colors */
        .status-Available { 
            color: #28a745; 
            font-weight: bold; 
        }

        .status-Borrowed { 
            color: #dc3545; 
            font-weight: bold; 
        }

        /* Actions column */
        .actions-cell {
            white-space: nowrap;
        }

        .actions-cell button {
            margin-right: 5px;
        }

        /* No books message */
        .no-books {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        a {
            text-decoration: none;
        }
        a:hover {
            text-decoration: none;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header-section">
            <h1>Librarian Dashboard</h1>
            <div class="welcome-section">
                <p class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                <a href="logout.php"><button class="logout-btn">Logout</button></a>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="add_book.php"><button class="add-book-btn">+ Add Book</button></a>
            <a href="catalog.php"><button class="view-all-btn">View All Books</button></a>
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
            <table class="book-table">

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
                            <td class="status-<?php echo htmlspecialchars($book['status']); ?>">
                                <?php echo htmlspecialchars($book['status']); ?>
                            </td>
                            <td class="actions-cell">
                                <a href="manage_books.php?book_id=<?php echo $book['book_id']; ?>">
                                    <button class="edit-btn">Edit</button>
                                </a>
                                <a href="manage_books.php?book_id=<?php echo $book['book_id']; ?>&action=delete" 
                                   onclick="return confirm('Are you sure you want to delete this book?')">
                                    <button class="delete-btn">Delete</button>
                                </a>
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
            <div class="no-books">
                <p>No books found in the library.</p>
            </div>
            <p>No books found in the library.</p>
        <?php endif; ?>
    </div>
</body>
</html>
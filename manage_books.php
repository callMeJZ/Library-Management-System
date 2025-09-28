<?php 
include 'db_connect.php';
session_start();

// Check if user is logged in and is a librarian
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Librarian') {
    header("Location: login.php");
    exit();
}

$message = "";
// Ensure book_id is a valid integer
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && $book_id > 0) {
    $delete_sql = "DELETE FROM books WHERE book_id = ?";
    $stmt = $mysqli->prepare($delete_sql);
    $stmt->bind_param("i", $book_id);
    
    if ($stmt->execute()) {
        // Redirect to prevent resubmission and show result on the dashboard
        header("Location: librarian_dashboard.php?status=deleted");
        exit();
    } else {
        $message = "<div class='error-message'>Error deleting book: " . htmlspecialchars($mysqli->error) . "</div>";
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
    
    $update_sql = "UPDATE books SET title = ?, author = ?, isbn = ?, publication_year = ?, category = ?, status = ? WHERE book_id = ?";
    
    $stmt = $mysqli->prepare($update_sql);
    $stmt->bind_param("ssssssi", $title, $author, $isbn, $publication_year, $category, $status, $book_id);
    
    if ($stmt->execute()) {
        $message = "<div class='success-message'>Book updated successfully! Redirecting...</div>";
        echo "<script>setTimeout(function() { window.location.href = 'librarian_dashboard.php'; }, 2000);</script>";
    } else {
        $message = "<div class='error-message'>Error updating book: " . htmlspecialchars($mysqli->error) . "</div>";
    }
    
    $stmt->close();
}

// Fetch book details for the form
if ($book_id > 0) {
    $sql = "SELECT * FROM books WHERE book_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
    } else {
        // If book not found, redirect
        header("Location: librarian_dashboard.php");
        exit();
    }
    $stmt->close();
} else {
    // If no book_id is provided, redirect
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
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #ffffffff;
        }
        h1 { 
            color: #333; 
            margin-bottom: 30px;
            text-align: center;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header-section {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
        }
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            margin-bottom: 30px;
            background-color: #366b99ff; 
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #0d395cff;
        }
        .form-section {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .form-table td {
            padding: 15px 10px;
            vertical-align: top;
        }
        .form-table td:first-child {
            width: 200px;
            font-weight: bold;
            color: #495057;
        }
        input[type="text"], input[type="number"], select {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input[type="text"]:focus, input[type="number"]:focus, select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        .button-container {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .button-container a, .button-container button, .button-container input {
            text-decoration: none;
        }
        button, input[type="submit"] {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s;
            color: white;
        }
        .update-btn { background-color: #3d8bf0ff; }
        .update-btn:hover { background-color: #1c6dafff; }
        .delete-btn { background-color: #dc3545; }
        .delete-btn:hover { background-color: #c82333; }
        .cancel-btn { background-color: #6c757d; }
        .cancel-btn:hover { background-color: #5a6268; }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <a href="librarian_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        <div class="header-section">
            <h1>Manage Book Details</h1>
        </div>
        
        <?php echo $message; ?>
        
        <div class="form-section">
            <form method="POST" action="">
                <table class="form-table">
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
                        <td><input type="number" name="publication_year" id="publication_year" min="1000" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($book['publication_year']); ?>" required></td>
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
                </table>

                <div class="button-container">
                    <input type="submit" name="update_book" value="Update Book" class="update-btn">
                    <a href="?book_id=<?php echo $book_id; ?>&action=delete" 
                       onclick="return confirm('Are you sure you want to delete this book?')">
                        <button type="button" class="delete-btn">Delete Book</button>
                    </a>
                    <a href="librarian_dashboard.php">
                        <button type="button" class="cancel-btn">Cancel</button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
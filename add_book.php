<?php 
include 'db_connect.php';
session_start();

// Check if user is logged in and is a librarian
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Librarian') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle form submission
if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $publication_year = $_POST['publication_year'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    
    // Insert new book into database
    $sql = "INSERT INTO books (title, author, isbn, publication_year, category, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssssss", $title, $author, $isbn, $publication_year, $category, $status);
    
    if ($stmt->execute()) {
        $message = "<p style='color: green;'>Book added successfully!</p>";
        // Redirect after 2 seconds
        echo "<script>setTimeout(function() { window.location.href = 'librarian_dashboard.php'; }, 2000);</script>";
    } else {
        $message = "<p style='color: red;'>Error adding book: " . $mysqli->error . "</p>";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
</head>
<body>
    <div>
        <h1>Add New Book</h1>
        <div style="text-align: right; margin-bottom: 20px;">
            <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
            <a href="logout.php"><button>Logout</button></a>
        </div>
        
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <table>
                <tr>
                    <td><label for="title">Title:</label></td>
                    <td><input type="text" name="title" id="title" required></td>
                </tr>
                <tr>
                    <td><label for="author">Author:</label></td>
                    <td><input type="text" name="author" id="author" required></td>
                </tr>
                <tr>
                    <td><label for="isbn">ISBN:</label></td>
                    <td><input type="text" name="isbn" id="isbn" required></td>
                </tr>
                <tr>
                    <td><label for="publication_year">Publication Year:</label></td>
                    <td><input type="number" name="publication_year" id="publication_year" min="1000" max="2024" required></td>
                </tr>
                <tr>
                    <td><label for="category">Category:</label></td>
                    <td><input type="text" name="category" id="category" required></td>
                </tr>
                <tr>
                    <td><label for="status">Status:</label></td>
                    <td>
                        <select name="status" id="status" required>
                            <option value="Available">Available</option>
                            <option value="Borrowed">Borrowed</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" name="add_book" value="Add Book">
                        <a href="librarian_dashboard.php"><button type="button">Cancel</button></a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>
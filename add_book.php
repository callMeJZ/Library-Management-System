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
        $message = "<div class='success-message'>Book added successfully!</div>";
        // Redirect to the dashboard after 2 seconds
        echo "<script>setTimeout(function() { window.location.href = 'librarian_dashboard.php'; }, 2000);</script>";
    } else {
        $message = "<div class='error-message'>Error adding book: " . htmlspecialchars($mysqli->error) . "</div>";
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
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f8f9fa;
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
        .logout-section {
            margin-top: 10px;
        }
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            margin-bottom: 30px;
            background-color: #6c757d; 
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #5a6268;
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
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input[type="text"]:focus, input[type="number"]:focus, select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        button, input[type="submit"] {
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin: 5px 10px 5px 0;
            transition: background-color 0.3s;
        }
        button:hover, input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .add-btn {
            background-color: #28a745;
        }
        .add-btn:hover {
            background-color: #218838;
        }
        .cancel-btn {
            background-color: #6c757d;
        }
        .cancel-btn:hover {
            background-color: #5a6268;
        }
        .button-container {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            margin-top: 20px;
        }
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
        a {
            text-decoration: none;
        }

        a:hover {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Back Button -->
        <a href="librarian_dashboard.php" class="back-link">
            &larr; Back to Dashboard
        </a>
        <div class="header-section">
            <h1>Add New Book</h1>
        </div>
        <?php echo $message; ?>
        <div class="form-section">
            <form method="POST" action="">
                <table class="form-table">
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
                        <td><input type="number" name="publication_year" id="publication_year" min="1000" max="2025" required></td>
                    </tr>
                    <tr>
                        <td><label for="category">Category:</label></td>
                        <td><input type="text" name="category" id="category" required></td>
                    </tr>
                    <tr>
                        <td><label for="status">Status:</label></td>
                        <td>
                            <select name="status" id="status" required>
                                <option value="">Select Status</option>
                                <option value="Available">Available</option>
                                <option value="Borrowed">Borrowed</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <div class="button-container">
                    <input type="submit" name="add_book" value="Add Book" class="add-btn">
                    <a href="librarian_dashboard.php"><button type="button" class="cancel-btn">Cancel</button></a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
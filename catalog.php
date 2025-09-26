<?php
// Include database connection. Assumes $mysqli object is defined here.
require_once 'db_connect.php';

$books = [];
$error_message = null;

// --- PAGINATION SETUP ---
$records_per_page = 10; // Number of books to display per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure page is at least 1
$offset = ($current_page - 1) * $records_per_page;

$total_books = 0;
$total_pages = 1;

// Check if the database connection succeeded
if ($mysqli && !$mysqli->connect_error) {
    // 1. Get the total number of records for pagination
    $count_sql = "SELECT COUNT(*) AS total FROM books";
    $count_result = $mysqli->query($count_sql);
    
    if ($count_result && $count_row = $count_result->fetch_assoc()) {
        $total_books = (int)$count_row['total'];
        $total_pages = ceil($total_books / $records_per_page);
        $current_page = min($current_page, $total_pages); // Ensure current page doesn't exceed total pages
        $offset = ($current_page - 1) * $records_per_page; // Recalculate offset based on adjusted page
    }
    $count_result->free();


    // 2. Fetch records for the current page using LIMIT and OFFSET
    $sql = "
        SELECT 
            book_id, 
            title, 
            author, 
            isbn, 
            publication_year, 
            category,
            status
        FROM 
            books
        ORDER BY 
            title ASC
        LIMIT {$records_per_page} OFFSET {$offset};
    ";

    $result = $mysqli->query($sql);

    if ($result === FALSE) {
        $error_message = "Database Query Error: " . $mysqli->error;
    } elseif ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }

    $mysqli->close();
} else {
    $error_message = "Database connection failed.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Catalog</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .book-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .book-table th, .book-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .book-table th { background-color: #f2f2f2; font-weight: bold; }
        /* Styling based on the status field from the books table */
        .status-Available { color: green; font-weight: bold; }
        .status-Borrowed { color: red; font-weight: bold; }
        .error { color: red; font-weight: bold; padding: 10px; }
        /* Pagination Styling */
        .pagination { 
            margin-top: 20px; 
            text-align: center; 
            padding: 10px;
            border-top: 1px solid #ccc;
        }
        .pagination a {
            color: #333;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
        .pagination a:hover:not(.active) {background-color: #f2f2f2;}
    </style>
</head>
<body>

    <h1>Library Book Catalog (Page <?= $current_page ?> of <?= $total_pages ?>)</h1>

    <?php if ($error_message): ?>
        <p class="error">Error: <?= $error_message ?></p>
    <?php elseif (empty($books) && $current_page == 1): ?>
        <p>No books found in the catalog.</p>
    <?php elseif (empty($books) && $current_page > 1): ?>
        <p>No books found on this page. Please return to the first page.</p>
    <?php else: ?>
        <table class="book-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Category</th>
                    <th>Year</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                        <!-- Displaying category directly from the books table -->
                        <td><?= htmlspecialchars($book['category'] ?? 'N/A') ?></td> 
                        <td><?= htmlspecialchars($book['publication_year']) ?></td>
                        <!-- Applying style based on the status field (e.g., status-Available) -->
                        <td class="status-<?= htmlspecialchars($book['status']) ?>">
                            <?= htmlspecialchars($book['status']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- PAGINATION CONTROLS -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?= $current_page - 1 ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= ($i == $current_page) ? 'active' : ''; ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?= $current_page + 1 ?>">Next</a>
                <?php endif; ?>
                
            </div>
        <?php endif; ?>

    <?php endif; ?>

</body>
</html>

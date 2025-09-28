<?php
// Include database connection 
require_once 'db_connect.php';
session_start(); // Start the session to access the user's role

// Determine the correct dashboard link based on the session role
$dashboard_url = 'user_dashboard.php'; // Default for users
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Librarian') {
    $dashboard_url = 'librarian_dashboard.php';
}

$books = [];
$error_message = null;
$records_per_page = 6; 

// Get and sanitize search term and current page number
$search_term = trim($_GET['search'] ?? '');
$current_page = max(1, (int)($_GET['page'] ?? 1)); 

$where_clause = '';
if ($search_term) {
    // Build WHERE clause for Title, Author, ISBN, or Category
    $safe_term = "%" . $mysqli->real_escape_string($search_term) . "%";
    $where_clause = " 
        WHERE 
            title LIKE '{$safe_term}' OR 
            author LIKE '{$safe_term}' OR 
            isbn LIKE '{$safe_term}' OR 
            category LIKE '{$safe_term}'
    ";
}

// 1. Get total count of books (for pagination)
$count_sql = "SELECT COUNT(*) AS total FROM books {$where_clause}";
$count_result = $mysqli->query($count_sql);

$total_books = 0;
$total_pages = 1;

if ($count_result === FALSE) {
    $error_message = "Database Query Error: " . $mysqli->error;
} elseif ($count_row = $count_result->fetch_assoc()) {
    $total_books = (int)$count_row['total'];
    $total_pages = ceil($total_books / $records_per_page);
    
    // Calculate offset for current page
    $current_page = min($current_page, $total_pages);
    $current_page = max(1, $current_page); 
    $offset = ($current_page - 1) * $records_per_page;
}

// 2. Fetch paginated book data
if (!$error_message) { 
    $sql = "
        SELECT
            b.book_id, b.title, b.author, b.isbn, b.publication_year, b.category, b.status,
            br.user_id AS borrower_id
        FROM
            books b
        LEFT JOIN
            borrow_records br ON b.book_id = br.book_id AND br.return_date IS NULL
        {$where_clause}
        ORDER BY
            b.title ASC
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
}

// Close resources
if (isset($count_result) && $count_result instanceof mysqli_result) { $count_result->free(); }
if (isset($result) && $result instanceof mysqli_result) { $result->free(); }
if (isset($mysqli)) { $mysqli->close(); }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Catalog</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f8f9fa;
        }
        h1 { 
            color: #333; 
            text-align: center;
            margin-bottom: 30px;
        }
        .catalog-container {
            max-width: 1200px;
            margin: 0 auto;
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
        .search-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .search-form { 
            display: flex; 
            gap: 10px; 
            align-items: center;
            flex-wrap: wrap;
        }
        .search-form input[type="text"] { 
            padding: 10px; 
            width: 350px; 
            border: 1px solid #ccc; 
            border-radius: 4px;
            font-size: 14px;
        }
        .search-form button { 
            padding: 10px 20px; 
            background-color: #007bff; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .search-form button:hover { 
            background-color: #0056b3; 
        }
        .clear-search-link {
            padding: 10px 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            background-color: #f8f9fa;
            transition: background-color 0.3s;
        }
        .clear-search-link:hover {
            background-color: #e9ecef;
        }
        .results-info {
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            color: #666;
        }
        .book-table { 
            width: 100%; 
            border-collapse: collapse; 
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
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
        .status-Available { 
            color: #28a745; 
            font-weight: bold; 
        }
        .status-Borrowed { 
            color: #dc3545; 
            font-weight: bold; 
        }
        .error { 
            color: #dc3545; 
            font-weight: bold; 
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #dc3545;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .pagination { 
            margin-top: 30px; 
            text-align: center; 
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .pagination a {
            color: #333;
            padding: 10px 16px;
            text-decoration: none;
            border: 1px solid #dee2e6;
            margin: 0 4px;
            border-radius: 4px;
            display: inline-block;
            transition: all 0.3s;
        }
        .pagination a.active {
            background-color: #007bff; 
            color: white;
            border: 1px solid #007bff;
        }
        .pagination a:hover:not(.active) {
            background-color: #f8f9fa;
            border-color: #007bff;
        }
        .actions-cell {
            white-space: nowrap;
        }
        .actions-cell button, .actions-cell a {
            margin-right: 5px;
        }
        .edit-btn {
            background-color: #3d8bf0ff;
            color: #212529;
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .edit-btn:hover {
            background-color: #1c6dafff;
        }
        .delete-btn {
            background-color: #d46772ff;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .delete-btn:hover {
            background-color: #db4150ff;
        }
        .borrow-btn, .return-btn {
            display: inline-block;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .borrow-btn {
            background-color: #246632ff  ; 
        }
        .borrow-btn:hover {
            background-color: #143a1dff;
        }
        .return-btn {
            background-color: #3dacbdff; 
        }
        .return-btn:hover {
            background-color: #278d9cff;
        }
    </style>

</head>
<body>
    <div class="catalog-container">
        <a href="<?= htmlspecialchars($dashboard_url) ?>" class="back-link">
            &larr; Back to Dashboard
        </a>

        <h1>Library Book Catalog</h1>

        <div class="search-section">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search by Title, Author, ISBN, or Category" 
                       value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit">Search</button>
                <?php if (!empty($search_term)): ?>
                    <a href="catalog.php?page=<?= $current_page ?>" class="clear-search-link">Clear Search</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($error_message): ?>
            <div class="error">Error: <?= htmlspecialchars($error_message) ?></div>
        <?php elseif (empty($books) && !empty($search_term)): ?>
            <div class="no-results">
                <p>No books found matching "<?= htmlspecialchars($search_term) ?>".</p>
            </div>
        <?php elseif (empty($books) && $total_books == 0): ?>
            <div class="no-results">
                <p>No books found in the catalog.</p>
            </div>
        <?php else: ?>

            <div class="results-info">
                <?php if (!empty($search_term)): ?>
                    Search results for "<?= htmlspecialchars($search_term) ?>": 
                <?php endif; ?>
                Showing 
                <?= $total_books > 0 ? (($current_page - 1) * $records_per_page) + 1 : 0 ?> 
                to 
                <?= (($current_page - 1) * $records_per_page) + count($books) ?> 
                of 
                <?= $total_books ?> books total.
            </div>

            <table class="book-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Category</th>
                        <th>Year</th>
                        <th>Status</th>
                        <?php // MODIFICATION: Show Actions column for ANY logged-in user ?>
                        <?php if (isset($_SESSION['role'])): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['isbn']) ?></td>
                            <td><?= htmlspecialchars($book['category'] ?? 'N/A') ?></td> 
                            <td><?= htmlspecialchars($book['publication_year']) ?></td>
                            <td class="status-<?= htmlspecialchars($book['status']) ?>">
                                <?= htmlspecialchars($book['status']) ?>
                            </td>
                            <?php // Display actions based on user role and book status ?>
                            <?php if (isset($_SESSION['role'])): ?>
                                <td class="actions-cell">
                                    <?php if ($_SESSION['role'] === 'Librarian'): ?>
                                        <a href="manage_books.php?book_id=<?= $book['book_id'] ?>">
                                            <button class="edit-btn">Edit</button>
                                        </a>
                                        <a href="manage_books.php?book_id=<?= $book['book_id'] ?>&action=delete" 
                                           onclick="return confirm('Are you sure you want to delete this book?')">
                                            <button class="delete-btn">Delete</button>
                                        </a>
                                    <?php else: //Standard user ?>
                                        <?php // MODIFICATION STARTS HERE ?>
                                            <?php if ($book['status'] === 'Available'): ?>
                                                <a href="borrow_return.php?action=borrow&book_id=<?= $book['book_id'] ?>&page=<?= $current_page ?>" class="borrow-btn">Borrow</a>
                                            
                                            <?php // This is the key change: check if the borrower_id matches the session user_id ?>
                                            <?php elseif ($book['status'] === 'Borrowed' && $book['borrower_id'] == $_SESSION['user_id']): ?>
                                                <a href="borrow_return.php?action=return&book_id=<?= $book['book_id'] ?>&page=<?= $current_page ?>" class="return-btn">Return</a>
                                            
                                            <?php endif; ?>
                                            <?php // MODIFICATION ENDS HERE ?>
                                </td>
                                        <?php endif; ?>
                                         <?php endif; ?>
                        </tr>
                                 <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php 
                    // Build base URL for pagination links, keeping search term
                    $base_query = http_build_query(array_filter(['search' => $search_term]));
                    $base_url = "catalog.php?" . $base_query . (empty($base_query) ? '' : '&');
                    ?>

                    <?php if ($current_page > 1): ?>
                        <a href="<?= $base_url ?>page=<?= $current_page - 1 ?>">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="<?= $base_url ?>page=<?= $i ?>" class="<?= ($i == $current_page) ? 'active' : ''; ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?= $base_url ?>page=<?= $current_page + 1 ?>">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
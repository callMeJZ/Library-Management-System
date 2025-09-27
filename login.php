<?php 
include 'db_connect.php'; 
session_start();

    if(isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $sql = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
        $result = $mysqli->query($sql);
        
        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            if($user['role'] == 'Librarian') {
                header("Location: librarian_dashboard.php");
                exit();
            } else {
                header("Location: user_dashboard.php");
                exit();
            }
        } else {
            echo "<p style='color: red;'>Invalid username or password!</p>";
        }
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Login</title>
</head>
<body>
    <section class="header">
        <div class="head">
            <h1>Library Management System</h1>
        </div>
    </section>
    <section class="container">
        <div class="form">
            <h2>Log In</h2>
            <div class="log-in">
                <form method="POST" action="">
                    <div class="input-field">
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="input-field">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="button">
                        <input type="submit" name="login" value="Login">
                    </div>
                </form>
            </div>
        </div>
    </section>

    
</body>
</html>

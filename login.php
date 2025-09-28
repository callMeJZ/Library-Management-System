<?php 
include 'db_connect.php'; 
session_start();

$error_message = ''; // Variable to hold login error messages

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $username = $mysqli->real_escape_string($username);
    $password = $mysqli->real_escape_string($password);
    
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
        $error_message = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
           background: linear-gradient(135deg, #cfe4ff 0%, #7fadccff 50%, #496bbeff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            padding: 30px 40px; 
            background-color: #eef8faff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        h2 {
            color: #201919ff;
            margin-bottom: 30px;
            font-size: 18px;
            font-weight: 400;
        }
        .input-field {
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box; 
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #2c368fff; 
        }
        .button input[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: #313291ff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .button input[type="submit"]:hover {
            background-color: #4973c0; 
            color: #0c060bff
            border: 1px solid #32366eff;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Library Management System</h1>
        <h2>Log In</h2>

        <?php 
        // Display error message if it exists
        if (!empty($error_message)) {
            echo "<div class='error-message'>$error_message</div>";
        }
        ?>
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
</body>
</html>
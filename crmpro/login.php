<?php
session_start();
include 'includes/db_connect.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE email = ? AND password = ? AND role = ?");
    $stmt->bind_param("sss", $email, $password, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id']; 

        if($role == "admin"){
            header('Location: admin/dashboard.php');
        }
        else if($role == "User"){
            header('Location: user/dashboard.php');
        }
        else if($role == "Team Leader"){
            header('Location: teamleader/dashboard.php');
        }
    } else {
        echo "<script>alert('Invalid credentials!');</script>";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background: url('https://media.istockphoto.com/id/1494319207/photo/clouds-on-the-sky-sunset-weather.webp?b=1&s=170667a&w=0&k=20&c=vIIbJwXOEChf3QHgpgxbNab_kvtrfcdjlOFJPAyuvEo=') no-repeat center center fixed; 
            background-size: cover;
            color: white;
        }

        .quote-container {
            position: absolute;
            left: 500px;
            font-size: 50px;
            max-width: 500px;
            color:black;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            margin-right: 50px;
            color: black;
        }

        .login-container h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .login-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .login-container input[type="email"],
        .login-container input[type="password"],
        .login-container select {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
        }

        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: lightpink;
            color: black;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .login-container button:hover {
            background-color: lightblue;
        }
    </style>
</head>
<body>
    <div class="quote-container">
        <p><b>"The only way to do great work is to love what you do." - Steve Jobs</b></p>
    </div>
    <div class="login-container">
        <form action="login.php" method="POST">
            <h2>Login</h2>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <label for="role">Role:</label>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="User">User</option>
                <option value="Team Leader">Team Leader</option>
            </select>
            <button type="submit" name="login"><b>Login</b></button>
        </form>
    </div>
</body>
</html>

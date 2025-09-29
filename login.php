<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['type'] = $user['type'];
        echo "<script>alert('Login successful!'); redirectTo('index.php');</script>";
    } else {
        echo "<script>alert('Invalid credentials');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(to bottom, #f0f4f8, #ffffff); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        form { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 300px; animation: fadeIn 1s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #ff6200; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #e55a00; }
        @media (max-width: 768px) { form { width: 90%; } }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Login</h2>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>

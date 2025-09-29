<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>redirectTo('login.php');</script>";
    exit;
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = $_POST['company_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE users SET company_name = ?, address = ?, phone = ? WHERE id = ?");
    $stmt->execute([$company_name, $address, $phone, $user_id]);
    echo "<script>alert('Profile updated!'); redirectTo('profile.php');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(to bottom, #f0f4f8, #ffffff); padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: fadeIn 1s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #ff6200; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #e55a00; }
        @media (max-width: 768px) { .container { width: 100%; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Profile Management</h2>
        <form method="POST">
            <input type="text" name="company_name" placeholder="Company Name" value="<?php echo $user['company_name']; ?>">
            <input type="text" name="address" placeholder="Address" value="<?php echo $user['address']; ?>">
            <input type="text" name="phone" placeholder="Phone" value="<?php echo $user['phone']; ?>">
            <button type="submit">Update Profile</button>
        </form>
        <?php if ($_SESSION['type'] == 'seller'): ?>
            <button onclick="redirectTo('add_product.php')">Add Product</button>
        <?php endif; ?>
        <button onclick="redirectTo('index.php')">Back to Home</button>
    </div>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>

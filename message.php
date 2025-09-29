<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['to'])) {
    echo "<script>redirectTo('login.php');</script>";
    exit;
}
include 'db.php';

$to_id = $_GET['to'];
$from_id = $_SESSION['user_id'];

// Fetch messages
$stmt = $conn->prepare("SELECT * FROM messages WHERE (from_id = ? AND to_id = ?) OR (from_id = ? AND to_id = ?) ORDER BY timestamp");
$stmt->execute([$from_id, $to_id, $to_id, $from_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send message
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $stmt = $conn->prepare("INSERT INTO messages (from_id, to_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$from_id, $to_id, $message]);
    echo "<script>redirectTo('message.php?to=$to_id');</script>";
}

// Mark as read
$stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE to_id = ? AND from_id = ?");
$stmt->execute([$from_id, $to_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(to bottom, #f0f4f8, #ffffff); padding: 20px; }
        .chat { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .message { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .sent { background: #ff6200; color: white; text-align: right; }
        .received { background: #ddd; text-align: left; }
        form { margin-top: 20px; }
        textarea { width: 100%; padding: 10px; }
        button { background: #ff6200; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; }
        @media (max-width: 768px) { .chat { width: 100%; } }
    </style>
</head>
<body>
    <div class="chat">
        <h2>Chat</h2>
        <?php foreach ($messages as $msg): ?>
            <div class="message <?php echo $msg['from_id'] == $from_id ? 'sent' : 'received'; ?>">
                <p><?php echo $msg['message']; ?></p>
                <small><?php echo $msg['timestamp']; ?></small>
            </div>
        <?php endforeach; ?>
        <form method="POST">
            <textarea name="message" placeholder="Type message..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>

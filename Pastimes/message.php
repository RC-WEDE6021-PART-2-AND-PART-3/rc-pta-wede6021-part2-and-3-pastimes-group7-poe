<?php
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Create messages table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tblmessages (
    MessageID INT PRIMARY KEY AUTO_INCREMENT,
    SenderID INT NOT NULL,
    ReceiverID INT NOT NULL,
    Message TEXT NOT NULL,
    IsRead TINYINT DEFAULT 0,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (SenderID) REFERENCES tbluser(UserID),
    FOREIGN KEY (ReceiverID) REFERENCES tbluser(UserID)
)");

// Send message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send'])) {
    $receiver = trim($_POST['receiver']);
    $message = trim($_POST['message']);
    
    if (empty($receiver) || empty($message)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if receiver exists
        $check = mysqli_query($conn, "SELECT UserID, Username FROM tbluser WHERE Username='$receiver' OR Email='$receiver'");
        if (mysqli_num_rows($check) > 0) {
            $receiver_data = mysqli_fetch_assoc($check);
            $receiver_id = $receiver_data['UserID'];
            
            mysqli_query($conn, "INSERT INTO tblmessages (SenderID, ReceiverID, Message, Timestamp) 
                                 VALUES ('$user_id', '$receiver_id', '$message', NOW())");
            $success = "Message sent successfully!";
        } else {
            $error = "User not found!";
        }
    }
}

// Mark as read
if (isset($_GET['read'])) {
    $msg_id = (int)$_GET['read'];
    mysqli_query($conn, "UPDATE tblmessages SET IsRead=1 WHERE MessageID=$msg_id AND ReceiverID=$user_id");
    header("Location: message.php");
    exit();
}

// Get conversations (messages sent and received)
$messages = mysqli_query($conn, "SELECT m.*, 
    u1.Username as SenderName, 
    u2.Username as ReceiverName 
    FROM tblmessages m
    JOIN tbluser u1 ON m.SenderID = u1.UserID
    JOIN tbluser u2 ON m.ReceiverID = u2.UserID
    WHERE m.SenderID = $user_id OR m.ReceiverID = $user_id
    ORDER BY m.Timestamp DESC");

// Get unread count
$unread_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tblmessages WHERE ReceiverID=$user_id AND IsRead=0"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages - Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .message-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .message-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
        }
        .card {
            background: white;
            border: 1px solid #e5e0d5;
            border-radius: 10px;
            overflow: hidden;
        }
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e5e0d5;
            background: #faf9f7;
        }
        .card-header h3 {
            margin: 0;
        }
        .card-body {
            padding: 20px;
        }
        .message-item {
            padding: 15px;
            border-bottom: 1px solid #e5e0d5;
            transition: background 0.3s;
        }
        .message-item:hover {
            background: #faf9f7;
        }
        .message-item.unread {
            background: #e8f4f8;
            border-left: 3px solid #e67e22;
        }
        .message-sender {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .message-text {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .message-time {
            font-size: 0.7rem;
            color: #999;
        }
        .badge {
            background: #e67e22;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            margin-left: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #e5e0d5;
            border-radius: 5px;
        }
        button {
            background: #2c3e50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #e67e22;
        }
    </style>
</head>
<body>
<?php $active = 'message'; include 'navbar.php'; ?>
<div class="message-container">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1>Messages</h1>
        <p>Communicate with other collectors</p>
        <?php if ($unread_count > 0): ?>
            <span class="badge"><?php echo $unread_count; ?> unread</span>
        <?php endif; ?>
    </div>
    
    <div class="message-layout">
        <!-- Send Message Form -->
        <div class="card">
            <div class="card-header">
                <h3>Send a Message</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="receiver" placeholder="Username or Email" required>
                    <textarea name="message" placeholder="Your message..." rows="5" required></textarea>
                    <button type="submit" name="send">Send Message →</button>
                </form>
            </div>
        </div>
        
        <!-- Inbox -->
        <div class="card">
            <div class="card-header">
                <h3>Conversations</h3>
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <?php if (mysqli_num_rows($messages) == 0): ?>
                    <p style="text-align: center; padding: 40px;">No messages yet.</p>
                <?php else: ?>
                    <?php while($msg = mysqli_fetch_assoc($messages)): 
                        $is_unread = ($msg['IsRead'] == 0 && $msg['ReceiverID'] == $user_id);
                    ?>
                        <div class="message-item <?php echo $is_unread ? 'unread' : ''; ?>">
                            <div class="message-sender">
                                <?php if ($msg['SenderID'] == $user_id): ?>
                                    To: <?php echo htmlspecialchars($msg['ReceiverName']); ?>
                                <?php else: ?>
                                    From: <?php echo htmlspecialchars($msg['SenderName']); ?>
                                <?php endif; ?>
                                <?php if ($is_unread): ?>
                                    <a href="?read=<?php echo $msg['MessageID']; ?>" style="float: right; font-size: 0.7rem; color: #e67e22;">Mark read</a>
                                <?php endif; ?>
                            </div>
                            <div class="message-text"><?php echo htmlspecialchars($msg['Message']); ?></div>
                            <div class="message-time"><?php echo date('M j, g:i a', strtotime($msg['Timestamp'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
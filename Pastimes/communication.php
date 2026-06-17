<?php
session_start();
include 'DBConn.php';

// Check if logged in (admin or user)
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$is_admin = isset($_SESSION['admin_id']);
$my_id = $is_admin ? $_SESSION['admin_id'] : $_SESSION['user_id'];
$my_name = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];

// Create communication table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tblcommunication (
    CommID INT PRIMARY KEY AUTO_INCREMENT,
    SenderID INT NOT NULL,
    ReceiverID INT NOT NULL,
    SenderType VARCHAR(20),
    Message TEXT NOT NULL,
    IsRead TINYINT DEFAULT 0,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (SenderID) REFERENCES tbluser(UserID),
    FOREIGN KEY (ReceiverID) REFERENCES tbluser(UserID)
)");

// Send message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $message = trim($_POST['message']);
    $sender_type = $is_admin ? 'admin' : 'user';
    
    if (!empty($message)) {
        mysqli_query($conn, "INSERT INTO tblcommunication (SenderID, ReceiverID, SenderType, Message) 
                             VALUES ('$my_id', '$receiver_id', '$sender_type', '$message')");
        header("Location: communication.php?chat=$receiver_id");
        exit();
    }
}

// Get selected user for chat
$selected_user = isset($_GET['chat']) ? (int)$_GET['chat'] : 0;

// Get all users for admin to communicate with
if ($is_admin) {
    $users = mysqli_query($conn, "SELECT UserID, Username, Email FROM tbluser ORDER BY Username");
} else {
    // For regular users, only show admins
    $users = mysqli_query($conn, "SELECT AdminID as UserID, Username, Email FROM tbladmin");
}

// Get conversation with selected user
$conversation = [];
if ($selected_user > 0) {
    $conversation = mysqli_query($conn, "SELECT c.*, 
        CASE WHEN c.SenderID = $my_id THEN 'Me' ELSE (SELECT Username FROM tbluser WHERE UserID = c.SenderID) END as SenderName,
        CASE WHEN c.ReceiverID = $my_id THEN 'Me' ELSE (SELECT Username FROM tbluser WHERE UserID = c.ReceiverID) END as ReceiverName
        FROM tblcommunication c 
        WHERE (c.SenderID = $my_id AND c.ReceiverID = $selected_user) 
           OR (c.SenderID = $selected_user AND c.ReceiverID = $my_id)
        ORDER BY c.Timestamp ASC");
    
    // Mark messages as read
    mysqli_query($conn, "UPDATE tblcommunication SET IsRead=1 WHERE SenderID=$selected_user AND ReceiverID=$my_id");
}

// Get unread count
$unread_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tblcommunication WHERE ReceiverID=$my_id AND IsRead=0"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Communications - Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .comm-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .comm-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            background: white;
            border: 1px solid #e5e0d5;
            border-radius: 10px;
            overflow: hidden;
            min-height: 500px;
        }
        .user-list {
            background: #faf9f7;
            border-right: 1px solid #e5e0d5;
            padding: 20px;
        }
        .user-list h3 {
            margin-bottom: 20px;
        }
        .user-item {
            padding: 12px;
            border-bottom: 1px solid #e5e0d5;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
            display: block;
            color: #333;
        }
        .user-item:hover, .user-item.active {
            background: #e8dbbc;
        }
        .chat-area {
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #e5e0d5;
            background: #faf9f7;
        }
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            margin-bottom: 15px;
            display: flex;
        }
        .message.sent {
            justify-content: flex-end;
        }
        .message.received {
            justify-content: flex-start;
        }
        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
        }
        .message.sent .message-bubble {
            background: #e67e22;
            color: white;
        }
        .message.received .message-bubble {
            background: #f0f0f0;
            color: #333;
        }
        .message-time {
            font-size: 0.7rem;
            margin-top: 5px;
            opacity: 0.7;
        }
        .chat-input {
            padding: 20px;
            border-top: 1px solid #e5e0d5;
            display: flex;
            gap: 10px;
        }
        .chat-input textarea {
            flex: 1;
            padding: 12px;
            border: 1px solid #e5e0d5;
            border-radius: 5px;
            resize: none;
        }
        .chat-input button {
            background: #e67e22;
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .badge-unread {
            background: #e67e22;
            color: white;
            padding: 2px 6px;
            border-radius: 50%;
            font-size: 0.7rem;
            float: right;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="comm-container">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1>Communications</h1>
        <p><?php echo $is_admin ? 'Communicate with customers' : 'Contact support'; ?></p>
        <?php if ($unread_count > 0): ?>
            <span class="badge" style="background: #e67e22;"><?php echo $unread_count; ?> unread</span>
        <?php endif; ?>
    </div>
    
    <div class="comm-layout">
        <!-- User List Sidebar -->
        <div class="user-list">
            <h3><?php echo $is_admin ? 'Customers' : 'Support Team'; ?></h3>
            <?php while($user = mysqli_fetch_assoc($users)): ?>
                <?php 
                $user_id = $user['UserID'];
                $is_active = ($selected_user == $user_id);
                // Get unread count from this user
                $user_unread = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tblcommunication WHERE SenderID=$user_id AND ReceiverID=$my_id AND IsRead=0"));
                ?>
                <a href="?chat=<?php echo $user_id; ?>" class="user-item <?php echo $is_active ? 'active' : ''; ?>">
                    <strong><?php echo htmlspecialchars($user['Username']); ?></strong>
                    <br><small><?php echo htmlspecialchars($user['Email']); ?></small>
                    <?php if ($user_unread['cnt'] > 0): ?>
                        <span class="badge-unread"><?php echo $user_unread['cnt']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endwhile; ?>
        </div>
        
        <!-- Chat Area -->
        <div class="chat-area">
            <?php if ($selected_user > 0): 
                $chat_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Username FROM tbluser WHERE UserID=$selected_user"));
            ?>
                <div class="chat-header">
                    <h3>Chatting with: <?php echo htmlspecialchars($chat_user['Username']); ?></h3>
                </div>
                
                <div class="messages-area" id="messages-area">
                    <?php while($msg = mysqli_fetch_assoc($conversation)): ?>
                        <div class="message <?php echo $msg['SenderName'] == 'Me' ? 'sent' : 'received'; ?>">
                            <div class="message-bubble">
                                <div><?php echo nl2br(htmlspecialchars($msg['Message'])); ?></div>
                                <div class="message-time"><?php echo date('M j, g:i a', strtotime($msg['Timestamp'])); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <form method="POST" class="chat-input">
                    <input type="hidden" name="receiver_id" value="<?php echo $selected_user; ?>">
                    <textarea name="message" placeholder="Type your message..." rows="2" required></textarea>
                    <button type="submit" name="send_message">Send →</button>
                </form>
                
                <script>
                    // Auto-scroll to bottom
                    var messagesArea = document.getElementById('messages-area');
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                </script>
                
            <?php else: ?>
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999;">
                    <p>Select a user to start chatting</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
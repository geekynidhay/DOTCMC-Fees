<?php
include 'db_connect.php';

$sql1 = "CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    course VARCHAR(100) NOT NULL,
    fees VARCHAR(50) NOT NULL,
    session VARCHAR(50) NOT NULL,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$sql2 = "ALTER TABLE whatsapp_queue
ADD COLUMN media_url1 VARCHAR(500) DEFAULT NULL AFTER message,
ADD COLUMN media_url2 VARCHAR(500) DEFAULT NULL AFTER media_url1";

if($conn->query($sql1)){
    echo "Table inquiries created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

if($conn->query($sql2)){
    echo "Added media columns successfully.\n";
} else {
    echo "Error adding columns (might already exist): " . $conn->error . "\n";
}
?>

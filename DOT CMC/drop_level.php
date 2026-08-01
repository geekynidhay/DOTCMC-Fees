<?php
include 'db_connect.php';

echo "<h2>Database Migration: Drop Level Column</h2>";

// Check if column exists before dropping
$result = $conn->query("SHOW COLUMNS FROM `courses` LIKE 'level'");
if($result && $result->num_rows > 0){
    $drop = $conn->query("ALTER TABLE `courses` DROP COLUMN `level`");
    if($drop){
        echo "<p style='color:green;'>SUCCESS: The 'level' column has been successfully deleted from the 'courses' table.</p>";
    } else {
        echo "<p style='color:red;'>ERROR: Failed to delete the 'level' column. Error: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:orange;'>INFO: The 'level' column does not exist in the 'courses' table. It may have already been deleted.</p>";
}

echo "<br><a href='index.php'>Go back to Home</a>";
?>

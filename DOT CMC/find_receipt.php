<?php
include 'db_connect.php';
if(isset($_POST['receipt_no'])){
    $stmt = $conn->prepare("SELECT id, ef_id FROM payments WHERE remarks = ? LIMIT 1");
    $stmt->bind_param("s", $_POST['receipt_no']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['status' => 1, 'pid' => $row['id'], 'ef_id' => $row['ef_id']]);
    } else {
        echo json_encode(['status' => 0]);
    }
}
?>

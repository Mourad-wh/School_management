<?php
    include '../includes/db.php';

    if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $sql = "INSERT INTO Users (Name, Email, PasswordHash, Role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $name, $email, $passwordHash, $role);
    }

    if ($stmt->execute()) {
        echo json_encode(['message' => 'User Added successfully', 'userId' => $stmt->insert_id]);
    }
    else {
        echo json_encode(['error' => 'Failed to add the user']);
    }
?>
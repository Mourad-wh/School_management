<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['absence_id'])) {
    $id = intval($_POST['absence_id']);
    $sql = "UPDATE absence SET is_validated = 1 WHERE id = $id";
    $conn->query($sql);
    header("Location: admin_absences.php"); // Redirection vers le tableau
    exit;
}

if (isset($_GET['file'])) {
    $file = $_GET['file'];

    // Security: Only allow specific folder (e.g., justification uploads)
    $filePath = '../uploads/justifications/' . basename($file);

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
    }
}

if (isset($_POST['validate'])) {
    $id = (int) $_POST['validate_id'];
    $file = basename($_POST['justification_file']);  // Secure the filename
    $filePath = "../uploads/justifications/" . $file;

    // Delete file if it exists
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Delete the record from the DB
    $conn->query("DELETE FROM absence WHERE id = $id");
}

//absence alert is sent to the DB
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alert_id'])) {
    $id = (int) $_POST['alert_id'];
    $stmt = $conn->prepare("UPDATE absence SET alert_sent = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch data
$sql = "
SELECT 
    a.*, 
    s.full_name, 
    c.name AS class_name
FROM 
    absence a
JOIN 
    students s ON a.student_id = s.id
JOIN 
    classes c ON a.class_id = c.id
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Absence Management</title>
</head>
<body>

    <nav>
        <div class="logo-container">
            <img src="../images/logo.png" alt="School Logo">
        </div>
        <div class="nav-links">
            <ul>
                <li><a href="classes.php">Classes</a></li>
                <li><a href="teacher.php">Profs</a></li>
                <li><a href="students.php">Etudiants</a></li>
                <li><a href="admin_absences.php" style="color: rgb(150, 20, 255)">L'absence</a></li>
                <li><a href="exams.php">Exams</a></li>
                <li><a href="notes.php">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <main class="students-section">
        <div class="students-header">
            <h1 style="color: aliceblue;">Absence Management</h1>
        </div>
        
        <div class="students-container">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Hours of Absence</th>
                    <th>Justification</th>
                    <th>Action</th>
                    <th>Alert</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                    <td><?= $row['hours_absent'] ?></td>
                    <td>
                        <?php if ($row['justification_file']): ?>
                            <a href="?file=<?= urlencode($row['justification_file']) ?>">Download</a>
                        <?php else: ?>
                            <em>No justification</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['justification_file']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="validate_id" value="<?= $row['id'] ?>">
                                <button class="validation" type="submit" name="validate" onclick="return confirm('Confirm validation?');">Validate</button>
                            </form>
                        <?php else: ?>
                            <em>---</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['hours_absent'] > 9 && !$row['alert_sent']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="alert_id" value="<?= $row['id'] ?>">
                                <button class="alert-btn" type="submit" onclick="return confirm('Send alert to student?')">Alert</button>
                            </form>
                        <?php elseif ($row['alert_sent']): ?>
                            <em class="alertsent">Alert sent</em>
                        <?php else: ?>
                            <em>---</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </main>
</body>
</html>
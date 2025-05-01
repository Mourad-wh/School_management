<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "school_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['absence_id'])) {
    $id = intval($_POST['absence_id']);
    $sql = "UPDATE absences SET is_validated = 1 WHERE id = $id";
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
    $conn->query("DELETE FROM absences WHERE id = $id");
}

//absence alert is sent to the DB
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alert_id'])) {
    $id = (int) $_POST['alert_id'];
    $stmt = $conn->prepare("UPDATE absences SET alert_sent = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch data
$sql = "SELECT * FROM absences";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2&family=Fredoka+One&display=swap" rel="stylesheet">
    <title>Abscence</title>
    <style>
        body {
            background-color: #f9faff;
            font-family: 'Baloo 2', cursive;
        }

        
        .navbar {
        background-color: #9fc3f8;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        border-bottom: 4px solid #6c5ce7;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar .logo {
        font-family: 'Fredoka One', cursive;
        font-size: 1.8rem;
        color: white;
        }

        .navbar ul {
        list-style: none;
        display: flex;
        gap: 1.5rem;
        margin: 0;
        padding: 0;
        }

        .navbar li a {
        text-decoration: none;
        color: rgb(74, 74, 74);
        font-weight: bold;
        transition: color 0.3s;
        }

        .navbar li a:hover {
        color: #ffd6e8;
        }

        table {
        width:100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 30px;
        margin-top: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        overflow: hidden;
        font-family: 'Baloo 2', cursive;
        background-color: #fff;
        }

        th {
        background-color: #9fc3f8;
        color: #ffffff;
        font-weight: bold;
        padding: 12px 15px;
        text-align: center;
        font-size: 1rem;
        }

        td {
        padding: 12px 15px;
        text-align: center;
        border-top: 1px solid #eee;
        font-size: 0.95rem;
        }

        tr:nth-child(even) {
        background-color: #f9f9ff;
        }

        .principal {
            display: flex;
            flex-direction: row; /* default, but we declare it for clarity */
            justify-content: center;
            align-items: flex-start;
            gap: 2rem;
            padding: 2rem;
        }


        .video-container video {
            max-width: 100%;
        }

        .table-container {
            flex: 1;
            min-width: 75%;
        }

        #bg-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .validation{
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border: none;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .alert-btn{
            background-color:rgb(255, 0, 60);
            color: white;
            cursor: pointer;
            border: none;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .alertsent{
            background-color:rgb(0, 255, 47);
            color: black;
            cursor: pointer;
            border: none;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
</style>
</head>
<body>
    
    <nav class="navbar">
        <div class="logo">Educa</div>
        <ul>
          <li><a href="#">Classes</a></li>
          <li><a href="teacher.php">Teachers</a></li>
          <li><a href="#" style="color:rgb(255, 255, 255)">Abscence</a></li>
          <li><a href="#">Exams</a></li>
          <li><a href="#">Timetables</a></li>
        </ul>
    </nav>

    <div class="principal">
        <div class="video-container">
            <video autoplay loop muted playsinline id="bg-video">
                <source src="../images/absence.mp4" type="video/mp4">
            </video>
        </div>

        <div class="table-container">
        <table>
            <tr>
                <th>Name</th>
                <th>Class</th>
                <th>Hours of Absence</th>
                <th>Justification</th>
                <th>Action</th>
                <th>Alert</th> <!-- New column -->
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['class']) ?></td>
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
    </div>

</body>
</html>

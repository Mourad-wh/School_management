<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "school_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "SELECT * FROM teachers";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2&family=Fredoka+One&display=swap" rel="stylesheet">
    <title>Teachers</title>
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
            width: 150%;
            height: 150%;
            object-fit: cover;
        }


        .addteacher {
            background-color:rgb(176, 190, 193);
            width: 100%;
            color: black;
            cursor: pointer;
            border: none;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .addteacher:hover {
            background-color:rgb(0, 208, 255);
        }

</style>
</head>
<body>
    
    <nav class="navbar">
        <div class="logo" href="welcome.html">Educa</div>
        <ul>
          <li><a href="#">Classes</a></li>
          <li><a href="teacher.php" style="color:rgb(255, 255, 255)">Teachers</a></li>
          <li><a href="#">Abscence</a></li>
          <li><a href="#">Exams</a></li>
          <li><a href="#">Timetables</a></li>
        </ul>
    </nav>

    <div class="principal">
        <div class="video-container">
            <video autoplay loop muted playsinline id="bg-video">
                <source src="../images/teachers.mp4" type="video/mp4">
            </video>
            <button class="addteacher" onclick="window.location.href='add_teacher.html';">⚙️Settings</button>            </div>

        <div class="table-container">
        <table>
            <tr>
                <th>Name</th>
                <th>Classes</th>
                <th>Subjects</th>
                <th>Requests</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                <td><?= htmlspecialchars($row['classes']) ?></td>
                <td><?= htmlspecialchars($row['subjects']) ?></td>
                <td>
                    <?php if (!empty($row['request_file'])): ?>
                        <a href="?file=<?= urlencode($row['request_file']) ?>">Download</a>
                    <?php else: ?>
                        <em>No request</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        </div>
    </div>

</body>
</html>

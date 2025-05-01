<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "school_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "SELECT * FROM teachers";
$result = $conn->query($sql);

// teacher's informations edit.
if (isset($_POST['save'])) {
    $id = $_POST['teacher_id'];
    $name = $_POST['teacher_name'];
    $classes = $_POST['classes'];
    $subjects = $_POST['subjects'];

    $stmt = $conn->prepare("UPDATE teachers SET teacher_name=?, classes=?, subjects=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $classes, $subjects, $id);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2&family=Fredoka+One&display=swap" rel="stylesheet">
    <title>Add Teacher</title>
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

        .authentification {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
            overflow: hidden;
            margin-bottom: auto;
        }

        .formsection {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            z-index: 1;
            background-color: #f9faff; /* semi-transparent white */
        }

        .formsection h1 {
            margin-bottom: 20px;
            font: bold;
        }

        .formsection form {
            display: flex;
            flex-direction: column;
        }

        .formsection label {
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .formsection input,
        .formsection button {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .formsection button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border: none;
        }

        #bg-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            flex-direction: column; /* default, but we declare it for clarity */
            justify-content: center;
            align-items: flex-start;
            gap: 2rem;
            padding: 2rem;
            margin-top: auto;
        }

        .editbutton{
            background-color: #9fc3f8;
            width: 100%;
            color: black;
            cursor: pointer;
            border: none;
            padding: 10px;
            margin-bottom: 15px;
            justify-content: right;
            border-radius: 5px;
            border: 1px solid #ccc;
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

    <div class="authentification">
        <video autoplay loop muted playsinline id="bg-video">
            <source src="../images/authentificaton.mp4" type="video/mp4">
        </video>
        <div class="formsection">
          <h1>ADD TEACHER</h1>     
          <form id="addTeacherForm">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" placeholder="Enter teacher's name" required>
      
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="example@uca.ac.ma" required>
      
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>

            <label for="text">Classes</label>
            <input type="text" id="classes" name="classes" placeholder="GCDSTE3" required>

            <label for="subjects">Subjects</label>
            <input type="subjects" id="subjects" name="subjects" placeholder="Web Engineering" required>
      
            <input type="hidden" name="role" value="Teacher">
            <button type="submit">Add Teacher</button>
          </form>
        </div>
      
    </div>

    <div class="principal">
        <table>
            <tr>
                <th>Name</th>
                <th>Classes</th>
                <th>Subjects</th>
                <th>Edit</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr id="row-<?= $row['id'] ?>">
                <form method="POST" action="">
                    <td>
                        <input type="text" name="teacher_name" value="<?= htmlspecialchars($row['teacher_name']) ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="classes" value="<?= htmlspecialchars($row['classes']) ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="subjects" value="<?= htmlspecialchars($row['subjects']) ?>" readonly>
                    </td>
                    <td>
                        <input type="hidden" name="teacher_id" value="<?= $row['id'] ?>">
                        <button type="button" class="editbutton" onclick="enableEdit(<?= $row['id'] ?>)">🖋 Edit</button>
                        <button type="submit" class="editbutton" name="save" style="display:none;">✅ Save</button>
                    </td>
                </form>
            </tr>

            <?php endwhile; ?>
        </table>
    </div>


    <script>
        // to link the inserted information with the DB
        document.getElementById('addTeacherForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const response = await fetch('../../includes/db.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            alert(result.message || result.error);
        });

        // to let the admin change the teacher's informations.
        function enableEdit(id) {
            const row = document.querySelector(`#row-${id}`);
            const inputs = row.querySelectorAll('input[type="text"]');
            const saveBtn = row.querySelector('button[type="submit"]');

            inputs.forEach(input => input.removeAttribute('readonly'));
            saveBtn.style.display = 'inline-block';
        }
    </script>
</body>
</html>
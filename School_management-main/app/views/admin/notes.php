<?php
// Database connection
try {
    $conn = new mysqli("localhost", "root", "", "school_db");
    if ($conn->connect_error) {
        throw new Exception("Database connection error");
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("System temporarily unavailable. Please try again later.");
}

// Get all classes
$classes = $conn->query("SELECT id, name FROM classes ORDER BY name");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'])) {
    $class_id = $conn->real_escape_string($_POST['class_id']);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Get all students in the selected class
        $students = $conn->query("
            SELECT s.id, s.full_name 
            FROM students s
            JOIN student_class sc ON s.id = sc.student_id
            WHERE sc.class_id = $class_id
            ORDER BY s.full_name
        ");

        foreach ($students as $student) {
            $student_id = $student['id'];
            $full_name = $student['full_name'];

            // Get subject averages for this student
            $avg_query = $conn->query("
                SELECT average 
                FROM average 
                WHERE student_id = $student_id AND average IS NOT NULL
            ");

            $total = 0;
            $count = 0;
            $subjects_below_12 = 0;
            $has_eliminatory = false;

            while ($row = $avg_query->fetch_assoc()) {
                $subject_avg = floatval($row['average']);
                $total += $subject_avg;
                $count++;

                if ($subject_avg < 12) {
                    $subjects_below_12++;
                }
                if ($subject_avg <= 8) {
                    $has_eliminatory = true;
                }
            }

            $overall_avg = $count > 0 ? round($total / $count, 2) : 0;
            $result = ($overall_avg >= 12 && $subjects_below_12 < 4 && !$has_eliminatory) ? 'passed' : 'failed';

            // Insert or update overall average
            $stmt = $conn->prepare("
                INSERT INTO student_overall_average (student_id, class_id, overall_average, result)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    overall_average = ?, 
                    result = ?, 
                    calculation_date = CURRENT_TIMESTAMP
            ");
            $stmt->bind_param("iidssd", 
                $student_id, $class_id, $overall_avg, $result,
                $overall_avg, $result
            );
            $stmt->execute();

            $results[$student_id] = [
                'name' => $full_name,
                'average' => $overall_avg,
                'passed' => $result === 'passed',
                'subjects_below_12' => $subjects_below_12,
                'has_eliminatory' => $has_eliminatory
            ];
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Error processing evaluation: " . $e->getMessage());
    }
}

// Get previously calculated results if available
if (isset($class_id)) {
    $saved_results = $conn->query("
        SELECT s.id, s.full_name, soa.overall_average, soa.result, soa.calculation_date
        FROM student_overall_average soa
        JOIN students s ON soa.student_id = s.id
        WHERE soa.class_id = $class_id
        ORDER BY s.full_name
    ");
    
    // If we have saved results but didn't just calculate new ones
    if (!isset($results) && $saved_results->num_rows > 0) {
        while ($row = $saved_results->fetch_assoc()) {
            $results[$row['id']] = [
                'name' => $row['full_name'],
                'average' => $row['overall_average'],
                'passed' => $row['result'] === 'passed',
                'calculation_date' => $row['calculation_date']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Evaluation</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .form-container {
            width: 90%;
            margin: 2rem auto;
            background-color: #fff;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        select, button {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        button {
            background-color: #6c5ce7;
            color: white;
            border: none;
            cursor: pointer;
            margin-left: 1rem;
        }
        
        button:hover {
            background-color: #5c4ce7;
        }
        
        .passed {
            color: #28a745;
            font-weight: bold;
        }
        
        .failed {
            color: #dc3545;
            font-weight: bold;
        }
        
        .details-btn {
            background-color: #17a2b8;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        
        .details-btn:hover {
            background-color: #138496;
        }
        .calculation-info {
            text-align: center;
            margin: 1rem 0;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav>
        <div class="logo-container">
            <img src="../images/logo.png" alt="School Logo">
        </div>
        <div class="nav-links">
            <ul>
                <li><a href="classes.php">Classes</a></li>
                <li><a href="teacher.php">Profs</a></li>
                <li><a href="students.php">Etudiants</a></li>
                <li><a href="admin_absences.php">L'absence</a></li>
                <li><a href="exams.php">Exams</a></li>
                <li><a href="notes.php" style="color: rgb(150, 20, 255)">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <main class="students-section">
        <div class="students-header">
            <h1 style="color: aliceblue;">Student Evaluation</h1>
        </div>
        
        <div class="form-container">
            <form method="POST">
                <select name="class_id" required>
                    <option value="">Select a Class</option>
                    <?php while ($class = $classes->fetch_assoc()): ?>
                        <option value="<?= $class['id'] ?>" <?= isset($_POST['class_id']) && $_POST['class_id'] == $class['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Evaluate</button>
            </form>
        </div>

        <?php if (isset($results) && !empty($results)): ?>
            <?php if (isset($calculation_date)): ?>
                <div class="calculation-info">
                    Results calculated on: <?= date('Y-m-d H:i:s', strtotime($calculation_date)) ?>
                </div>
            <?php endif; ?>
            
            <div class="students-container">
                <table>
                    <tr>
                        <th>Student Name</th>
                        <th>Overall Average</th>
                        <th>Subjects Below 12</th>
                        <th>Has Eliminatory</th>
                        <th>Result</th>
                        <th>Details</th>
                    </tr>
                    <?php foreach ($results as $student_id => $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result['name']) ?></td>
                        <td><?= $result['average'] ?></td>
                        <td><?= $result['subjects_below_12'] ?? 'N/A' ?></td>
                        <td><?= isset($result['has_eliminatory']) ? ($result['has_eliminatory'] ? 'Yes' : 'No') : 'N/A' ?></td>
                        <td class="<?= $result['passed'] ? 'passed' : 'failed' ?>">
                            <?= $result['passed'] ? 'Passed' : 'Failed' ?>
                        </td>
                        <td>
                            <a href="student_details.php?student_id=<?= $student_id ?>&class_id=<?= $class_id ?>" class="details-btn">
                                View Details
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p style="text-align: center; margin: 2rem;">No evaluation data found for this class.</p>
        <?php endif; ?>
    </main>
</body>
</html>
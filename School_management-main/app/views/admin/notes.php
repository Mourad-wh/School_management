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

// Initialize results array
$results = [];

$sort_by = $_POST['sort_by'] ?? 'name'; // Default sort

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

            // Get all grades per subject
            $grades_query = $conn->query("
                SELECT subject_id, grade, qcm, participation
                FROM notes
                WHERE student_id = $student_id
            ");

            $grades_by_subject = [];

            while ($row = $grades_query->fetch_assoc()) {
                $subject_id = $row['subject_id'];
                $grades_by_subject[$subject_id]['grades'][] = $row;
            }

            // Calculate averages
            $total_average = 0;
            $subject_count = 0;
            $subjects_below_12 = 0;
            $has_eliminatory = false;

            foreach ($grades_by_subject as $subject_id => $data) {
                $subject_sum = 0;
                $entry_count = 0;

                foreach ($data['grades'] as $grade) {
                    $entry_sum = 0;
                    $entry_parts = 0;

                    if (!is_null($grade['grade'])) {
                        $entry_sum += $grade['grade'];
                        $entry_parts++;
                    }
                    if (!is_null($grade['qcm'])) {
                        $entry_sum += $grade['qcm'];
                        $entry_parts++;
                    }
                    if (!is_null($grade['participation'])) {
                        $entry_sum += $grade['participation'];
                        $entry_parts++;
                    }

                    if ($entry_parts > 0) {
                        $subject_sum += ($entry_sum / $entry_parts);
                        $entry_count++;
                    }
                }

                if ($entry_count > 0) {
                    $subject_avg = $subject_sum / $entry_count;
                    $total_average += $subject_avg;
                    $subject_count++;

                    if ($subject_avg < 12) {
                        $subjects_below_12++;
                    }
                    if ($subject_avg <= 8) {
                        $has_eliminatory = true;
                    }
                }
            }

            $overall_average = $subject_count > 0 ? round($total_average / $subject_count, 2) : 0;
            $result = ($overall_average >= 12 && $subjects_below_12 < 4 && !$has_eliminatory) ? 'Passed' : 'Failed';
            $has_eliminatory_text = $has_eliminatory ? 'Yes' : 'No';

            $results[] = [
                'id' => $student_id,
                'name' => $full_name,
                'average' => $overall_average,
                'below_12' => $subjects_below_12,
                'eliminatory' => $has_eliminatory_text,
                'result' => $result
            ];
        }

        $conn->commit();

        // Sort results based on admin choice
        usort($results, function($a, $b) use ($sort_by) {
            if ($sort_by === 'average') {
                return $b['average'] <=> $a['average']; // Descending
            } elseif ($sort_by === 'result') {
                return strcmp($a['result'], $b['result']); // Ascending: Failed then Passed
            } else {
                return strcmp($a['name'], $b['name']); // Default: name ascending
            }
        });

    } catch (Exception $e) {
        $conn->rollback();
        die("Error processing evaluation: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
            margin-right: 1rem;
        }
        button {
            background-color: #6c5ce7;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #5c4ce7;
        }
        .passed { color: #28a745; font-weight: bold; }
        .failed { color: #dc3545; font-weight: bold; }
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
        table {
            width: 90%;
            margin: 1rem auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 0.75rem;
            text-align: center;
        }
    </style>
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

                <select name="sort_by">
                    <option value="name" <?= $sort_by === 'name' ? 'selected' : '' ?>>Sort by Name</option>
                    <option value="average" <?= $sort_by === 'average' ? 'selected' : '' ?>>Sort by Average</option>
                    <option value="result" <?= $sort_by === 'result' ? 'selected' : '' ?>>Sort by Result</option>
                </select>

                <button type="submit">Evaluate</button>
            </form>
        </div>

        <?php if (!empty($results)): ?>
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
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars($result['name']) ?></td>
                            <td><?= $result['average'] ?></td>
                            <td><?= $result['below_12'] ?></td>
                            <td><?= $result['eliminatory'] ?></td>
                            <td class="<?= strtolower($result['result']) ?>"><?= $result['result'] ?></td>
                            <td>
                                <a href="student_details.php?student_id=<?= $result['id'] ?>&class_id=<?= $class_id ?>" class="details-btn">
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

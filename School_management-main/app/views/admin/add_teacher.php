<?php
session_start();

// Database connection with error handling
try {
    $conn = new mysqli("localhost", "root", "", "school_db");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("System error. Please try again later.");
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate password strength
function isStrongPassword($password) {
    return strlen($password) >= 8;
}

// Add Teacher Functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid request");
        }

        $conn->begin_transaction();
        
        // Get and validate form data
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
        $classes = isset($_POST['classes']) ? $_POST['classes'] : [];
        $school_id = "2202387";

        // Validation
        if (empty($name) || empty($email) || empty($password) || empty($username)) {
            throw new Exception("All fields are required");
        }

        if (!isValidEmail($email)) {
            throw new Exception("Invalid email format");
        }

        if (!isStrongPassword($password)) {
            throw new Exception("Password must be at least 8 characters");
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_ARGON2I);

        // Insert into teachers table
        $stmt = $conn->prepare("INSERT INTO teachers (full_name, email, password, school_id, username) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashedPassword, $school_id, $username);
        $stmt->execute();
        $teacher_id = $stmt->insert_id;
        $stmt->close();

        // Insert subjects
        if (!empty($subjects)) {
            $stmt = $conn->prepare("INSERT INTO teacher_subject (teacher_id, subject_id) VALUES (?, ?)");
            foreach ($subjects as $subject_id) {
                $subject_id = (int)$subject_id;
                if ($subject_id > 0) {
                    $stmt->bind_param("ii", $teacher_id, $subject_id);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        // Insert classes
        if (!empty($classes)) {
            $stmt = $conn->prepare("INSERT INTO teacher_class (teacher_id, class_id) VALUES (?, ?)");
            foreach ($classes as $class_id) {
                $class_id = (int)$class_id;
                if ($class_id > 0) {
                    $stmt->bind_param("ii", $teacher_id, $class_id);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        $conn->commit();
        
        $_SESSION['success_message'] = "Teacher added successfully";
        header("Location: " . $_SERVER['PHP_SELF'], true, 303);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error adding teacher: " . $e->getMessage());
        $error = "Error adding teacher: " . $e->getMessage();
    }
}

// UPDATE teacher info
if (isset($_POST['save'])) {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid request");
        }

        $conn->begin_transaction();
        
        $teacher_id = (int)$_POST['teacher_id'];
        $name = trim($_POST['teacher_name']);
        $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
        $classes = isset($_POST['classes']) ? $_POST['classes'] : [];

        // Validate input
        if (empty($name)) {
            throw new Exception("Name cannot be empty");
        }

        // Update teacher's name
        $stmt = $conn->prepare("UPDATE teachers SET full_name=? WHERE id=?");
        $stmt->bind_param("si", $name, $teacher_id);
        $stmt->execute();
        $stmt->close();

        // --- Update subjects ---
        // Delete existing subject relations
        $stmt = $conn->prepare("DELETE FROM teacher_subject WHERE teacher_id = ?");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $stmt->close();

        // Insert new subject relations
        if (!empty($subjects)) {
            $stmt = $conn->prepare("INSERT INTO teacher_subject (teacher_id, subject_id) VALUES (?, ?)");
            foreach ($subjects as $subject_id) {
                $subject_id = (int)$subject_id;
                if ($subject_id > 0) {
                    $stmt->bind_param("ii", $teacher_id, $subject_id);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        // --- Update classes ---
        // Delete existing class relations
        $stmt = $conn->prepare("DELETE FROM teacher_class WHERE teacher_id = ?");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $stmt->close();

        // Insert new class relations
        if (!empty($classes)) {
            $stmt = $conn->prepare("INSERT INTO teacher_class (teacher_id, class_id) VALUES (?, ?)");
            foreach ($classes as $class_id) {
                $class_id = (int)$class_id;
                if ($class_id > 0) {
                    $stmt->bind_param("ii", $teacher_id, $class_id);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        $conn->commit();
        
        $_SESSION['success_message'] = "Teacher updated successfully";
        header("Location: " . $_SERVER['PHP_SELF'], true, 303);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating teacher: " . $e->getMessage());
        $error = "Error updating teacher: " . $e->getMessage();
    }
}

// REMOVE teacher functionality
if (isset($_POST['remove'])) {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid request");
        }

        $conn->begin_transaction();
        
        $teacher_id = (int)$_POST['teacher_id'];

        // 1. Delete from teacher_subject
        $stmt = $conn->prepare("DELETE FROM teacher_subject WHERE teacher_id = ?");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $stmt->close();

        // 2. Delete from teacher_class
        $stmt = $conn->prepare("DELETE FROM teacher_class WHERE teacher_id = ?");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $stmt->close();

        // 3. Delete from teachers
        $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        
        $_SESSION['success_message'] = "Teacher removed successfully";
        header("Location: " . $_SERVER['PHP_SELF'], true, 303);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error removing teacher: " . $e->getMessage());
        $error = "Error removing teacher: " . $e->getMessage();
    }
}

// Get all teachers with their subjects and classes
$sql = "
    SELECT 
        t.id,
        t.full_name,
        t.email,
        GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS subjects,
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS classes,
        GROUP_CONCAT(DISTINCT s.id ORDER BY s.name SEPARATOR ',') AS subject_ids,
        GROUP_CONCAT(DISTINCT c.id ORDER BY c.name SEPARATOR ',') AS class_ids
    FROM 
        teachers t
    LEFT JOIN 
        teacher_subject ts ON t.id = ts.teacher_id
    LEFT JOIN 
        subjects s ON ts.subject_id = s.id
    LEFT JOIN 
        teacher_class ct ON t.id = ct.teacher_id
    LEFT JOIN 
        classes c ON ct.class_id = c.id
    GROUP BY 
        t.id, t.full_name, t.email
";

$result = $conn->query($sql);
$subjectsResult = $conn->query("SELECT id, name FROM subjects ORDER BY name");
$classesResult = $conn->query("SELECT id, name FROM classes ORDER BY name");

// Display success message if exists
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2&family=Fredoka+One&display=swap" rel="stylesheet">
    <title>Teacher Management</title>
</head>
<body>
    <nav>
        <div class="logo-container">
            <img src="../images/logo.png" alt="School Logo">
        </div>
        <div class="nav-links">
            <ul>
                <li><a href="classes.php">Classes</a></li>
                <li><a href="teacher.php" style="color: rgb(150, 20, 255)">Profs</a></li>
                <li><a href="students.php">Etudiants</a></li>
                <li><a href="admin_absences.php">L'absence</a></li>
                <li><a href="exams.php">Exams</a></li>
                <li><a href="notes.php">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <main class="teachers-management">
        <div class="teachers-header">
            <h1 style="color: aliceblue;">Gestion des profs</h1>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="teachers-container">
            <div class="add-teacher-form">
                <h2><i class="fas fa-user-plus"></i> Ajouter un nouveau prof</h2>
                <form method="POST" id="addTeacherForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="form-group">
                        <label for="name">Nom complet</label>
                        <input type="text" id="name" name="name" placeholder="Enter teacher's full name" required>
                    </div>

                    <div class="form-group">
                        <label for="usernamename">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" placeholder="Enter teacher's username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Address Email</label>
                        <input type="email" id="email" name="email" placeholder="example@school.edu" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe (min 8 characters)</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required minlength="8">
                        <div id="password-strength" style="font-size: 0.9rem; margin-top: 5px;"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subjects">Modules (Hold Ctrl for multiple selection)</label>
                        <select id="subjects" name="subjects[]" multiple required>
                            <?php while ($subject = $subjectsResult->fetch_assoc()): ?>
                                <option value="<?= (int)$subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="classes">Classes (Hold Ctrl for multiple selection)</label>
                        <select id="classes" name="classes[]" multiple required>
                            <?php while ($class = $classesResult->fetch_assoc()): ?>
                                <option value="<?= (int)$class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <input type="hidden" name="role" value="Teacher">
                    <button type="submit" name="add_teacher" class="submit-btn">Ajouter le prof</button>
                </form>
            </div>
            
            <h2 style="margin-top: 40px; color: #04207b;"><i class="fas fa-chalkboard-teacher"></i>La liste des profs</h2>
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Classes</th>
                    <th>Modules</th>
                    <th>Actions</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): 
                    $current_subject_ids = isset($row['subject_ids']) ? explode(',', $row['subject_ids']) : [];
                    $current_class_ids = isset($row['class_ids']) ? explode(',', $row['class_ids']) : [];
                ?>
                <tr id="row-<?= (int)$row['id'] ?>">
                    <form method="POST" class="edit-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="teacher_id" value="<?= (int)$row['id'] ?>">
                        
                        <td>
                            <input type="text" name="teacher_name" value="<?= htmlspecialchars($row['full_name']) ?>" required>
                        </td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <select name="classes[]" multiple class="edit-select">
                                <?php 
                                $classesResult->data_seek(0);
                                while ($class = $classesResult->fetch_assoc()): 
                                    $selected = in_array($class['id'], $current_class_ids) ? 'selected' : '';
                                ?>
                                    <option value="<?= (int)$class['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($class['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                        <td>
                            <select name="subjects[]" multiple class="edit-select">
                                <?php 
                                $subjectsResult->data_seek(0);
                                while ($subject = $subjectsResult->fetch_assoc()): 
                                    $selected = in_array($subject['id'], $current_subject_ids) ? 'selected' : '';
                                ?>
                                    <option value="<?= (int)$subject['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($subject['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                        <td>
                            <button type="submit" name="save" class="action-btn save-btn">Enregistrer</button>
                            <button type="submit" name="remove" class="action-btn remove-btn" onclick="return confirm('Are you sure you want to remove this teacher?')">Supprimer</button>
                            <button type="button" class="action-btn cancel-btn" onclick="cancelEdit(<?= (int)$row['id'] ?>)">Ignorer</button>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="../images/logo.png" alt="School Logo">
                <p>Donner du pouvoir aux futures générations grâce à une éducation de qualité</p>
            </div>
            <div class="footer-links">
                <h4>Liens rapides</h4>
                <a href="#">Home</a>
                <a href="#">About</a>
                <a href="#">Admissions</a>
                <a href="#">Contact</a>
            </div>
            <div class="footer-contact">
                <h4>Contacter Nous</h4>
                <p><i class="fas fa-map-marker-alt"></i> 123 School Street, City</p>
                <p><i class="fas fa-phone"></i> +123 456 7890</p>
                <p><i class="fas fa-envelope"></i> info@school.edu</p>
            </div>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="footer-copyright">
            <p>&copy; <?= date('Y') ?> School Name. All rights reserved.</p>
        </div>
    </footer>


    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const strengthIndicator = document.getElementById('password-strength');
            
            if (this.value.length === 0) {
                strengthIndicator.textContent = '';
            } else if (this.value.length < 8) {
                strengthIndicator.textContent = 'Weak (min 8 characters)';
                strengthIndicator.style.color = '#dc3545';
            } else {
                strengthIndicator.textContent = 'Strong enough';
                strengthIndicator.style.color = '#28a745';
            }
        });

        // Form submission handling
        document.getElementById('addTeacherForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
            }
        });

        // Enable edit mode for a teacher row
        function enableEdit(id) {
            const row = document.querySelector(`#row-${id}`);
            row.classList.add('edit-mode');
        }

        // Cancel edit mode
        function cancelEdit(id) {
            const row = document.querySelector(`#row-${id}`);
            row.classList.remove('edit-mode');
        }
    </script>
</body>
</html>

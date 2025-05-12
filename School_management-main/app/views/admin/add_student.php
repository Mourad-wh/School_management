<?php
session_start();

// Database connection with enhanced error handling
try {
    $conn = new mysqli("localhost", "root", "", "school_db", 3306);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log($e->getMessage());
    die("System error. Please try again later.");
}

// Generate CSRF token if not already present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// email validation
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// phone validation
function sanitizePhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return (strlen($phone) >= 9) ? $phone : false;
}

// Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_student'])) {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "Invalid request";
        } else {
            try {
                // Validate and sanitize input
                $name = htmlspecialchars(trim($_POST['name']));
                $email = htmlspecialchars(trim($_POST['email']));
                $phone = sanitizePhone($_POST['phone']);
                $password = trim($_POST['password']);
                $confirm_password = $_POST['confirm_password'] ?? '';
                $school_id = (int)$_POST['school_id'];
                $role = 'Student';

                if (empty($name) || strlen($name) < 2) {
                    throw new Exception("Please enter a valid name");
                }

                if (!isValidEmail($email)) {
                    throw new Exception("Invalid email format");
                }

                if (!$phone) {
                    throw new Exception("Invalid phone number");
                }

                if ($password !== $confirm_password) {
                    throw new Exception("Passwords don't match");
                }

                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    throw new Exception("Email already exists");
                }
                $stmt->close();

                // Verify school exists
                $stmt = $conn->prepare("SELECT id FROM school WHERE id = ?");
                $stmt->bind_param("i", $school_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows === 0) {
                    throw new Exception("Invalid school selected");
                }
                $stmt->close();

                // Begin transaction
                $conn->begin_transaction();

                // Insert student
                $stmt = $conn->prepare("INSERT INTO students (full_name, email, phone, password, school_id) VALUES (?, ?, ?, ?, ?)");
                $hashedPassword = password_hash($password, PASSWORD_ARGON2I);
                $stmt->bind_param("ssssi", $name, $email, $phone, $hashedPassword, $school_id);
                $stmt->execute();
                $student_id = $stmt->insert_id;
                $stmt->close();

                // Now add to student_class if class was selected
                if (isset($_POST['class_id']) && !empty($_POST['class_id'])) {
                    $class_id = (int)$_POST['class_id'];
                    // Verify class exists
                    $stmt = $conn->prepare("SELECT id FROM classes WHERE id = ? LIMIT 1");
                    $stmt->bind_param("i", $class_id);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows === 0) {
                        throw new Exception("Invalid class selected");
                    }
                    $stmt->close();
                }

                // Commit transaction
                $conn->commit();
                $_SESSION['success_message'] = "Student added successfully";
                header("Location: ".$_SERVER['PHP_SELF']."?success=1", true, 303);
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                error_log("Error adding student: " . $e->getMessage());
                $error = $e->getMessage();
            }
        }
    }
}

// UPDATE student info
if (isset($_POST['save'])) {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid request");
        }

        $conn->begin_transaction();
        
        $student_id = (int)$_POST['student_id'];
        $name = trim($_POST['student_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $school_id = (int)$_POST['school_id'];
        $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : null;
        
        // Validate input
        if (empty($name)) {
            throw new Exception("Name cannot be empty");
        }

        if (!isValidEmail($email)) {
            throw new Exception("Invalid email format");
        }

        if (!sanitizePhone($phone)) {
            throw new Exception("Invalid phone number");
        }

        // Check if new email already exists for another student
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $student_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already exists for another student");
        }
        $stmt->close();

        // Verify school exists
        $stmt = $conn->prepare("SELECT id FROM school WHERE id = ?");
        $stmt->bind_param("i", $school_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Invalid school selected");
        }
        $stmt->close();

        // Update student info
        $stmt = $conn->prepare("UPDATE students SET full_name=?, email=?, phone=?, school_id=? WHERE id=?");
        $stmt->bind_param("sssii", $name, $email, $phone, $school_id, $student_id);
        $stmt->execute();
        $stmt->close();

        // Handle class update if provided
        if ($class_id) {
            // Verify class exists
            $stmt = $conn->prepare("SELECT id FROM classes WHERE id = ?");
            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                throw new Exception("Invalid class selected");
            }
            $stmt->close();

            // Delete existing class relationships
            $stmt = $conn->prepare("DELETE FROM student_class WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $stmt->close();

            // Add new class relationship
            $stmt = $conn->prepare("INSERT INTO student_class (student_id, class_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $student_id, $class_id);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        
        $_SESSION['success_message'] = "Student updated successfully";
        header("Location: ".$_SERVER['PHP_SELF'], true, 303);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating student: " . $e->getMessage());
        $error = "Error updating student: " . $e->getMessage();
    }
}

// REMOVE student functionality
if (isset($_POST['remove'])) {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid request");
        }

        $conn->begin_transaction();
        
        $student_id = (int)$_POST['student_id'];

        // 1. Delete from student_class
        $stmt = $conn->prepare("DELETE FROM student_class WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();

        // 2. Delete from students
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        
        $_SESSION['success_message'] = "Student removed successfully";
        header("Location: " . $_SERVER['PHP_SELF'], true, 303);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error removing student: " . $e->getMessage());
        $error = "Error removing student: " . $e->getMessage();
    }
}

// Get all students with their classes
$sql = "
    SELECT 
        s.id,
        s.full_name,
        s.email,
        s.phone,
        sc.name as school_name,
        sc.id as school_id,
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS classes,
        GROUP_CONCAT(DISTINCT c.id ORDER BY c.name SEPARATOR ',') AS class_ids
    FROM 
        students s
    LEFT JOIN 
        school sc ON s.school_id = sc.id
    LEFT JOIN 
        student_class stc ON s.id = stc.student_id
    LEFT JOIN 
        classes c ON stc.class_id = c.id
    GROUP BY 
        s.id, s.full_name, s.email, s.phone, sc.name, sc.id
    ORDER BY 
        s.full_name
";

$result = $conn->query($sql);
$schoolsResult = $conn->query("SELECT id, name FROM school ORDER BY name");
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
    <title>Student Management</title>
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
                <li><a href="students.php" style="color: rgb(150, 20, 255)">Etudiants</a></li>
                <li><a href="admin_absences.php">L'absence</a></li>
                <li><a href="exams.php">Exams</a></li>
                <li><a href="notes.php">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <main class="student-management">
        <div class="student-header">
            <h1 style="color: aliceblue;">Student Management</h1>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="student-container">
            <div class="add-student-form">
                <h2><i class="fas fa-user-plus"></i> Ajouter un étudiant</h2>
                <form id="addStudentForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="form-group">
                        <label for="name">Nom Complet</label>
                        <input type="text" id="name" name="name" placeholder="Enter student's name" required 
                            value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                        <span id="name-error" class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="example@gmail.com" required
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        <span id="email-error" class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="text" id="phone" name="phone" placeholder="+212612345678" required
                            value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                        <span id="phone-error" class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                        <span id="password-error" class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de pass</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="school_id">School</label>
                        <select id="school_id" name="school_id" required>
                            <option value="">Ecole</option>
                            <?php while ($school = $schoolsResult->fetch_assoc()): ?>
                                <option value="<?= (int)$school['id'] ?>" 
                                    <?= (isset($_POST['school_id']) && $_POST['school_id'] == $school['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($school['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <span id="school_id-error" class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select id="class_id" name="class_id" required>
                            <option value="">Selectinoer la class</option>
                            <?php while ($class = $classesResult->fetch_assoc()): ?>
                                <option value="<?= (int)$class['id'] ?>" 
                                    <?= (isset($_POST['class_id']) && $_POST['class_id'] == $class['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <span id="class_id-error" class="error-message"></span>
                    </div>
                    
                    <button type="submit" name="add_student" class="submit-btn">Ajouter l'étudiant</button>
                </form>
            </div>
            
            <h2 style="margin-top: 40px; color: #04207b;"><i class="fas fa-users"></i> Listes des étudiants</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Ecole</th>
                        <th>Class</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): 
                        $current_class_ids = isset($row['class_ids']) ? explode(',', $row['class_ids']) : [];
                    ?>
                    <tr id="row-<?= (int)$row['id'] ?>">
                        <form method="POST" class="edit-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="student_id" value="<?= (int)$row['id'] ?>">
                            
                            <td>
                                <input type="text" name="student_name" value="<?= htmlspecialchars($row['full_name']) ?>" required>
                            </td>
                            <td>
                                <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
                            </td>
                            <td>
                                <input type="text" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" required>
                            </td>
                            <td>
                                <select name="school_id" required>
                                    <?php 
                                    $schoolsResult->data_seek(0);
                                    while ($school = $schoolsResult->fetch_assoc()): 
                                        $selected = ($school['id'] == $row['school_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= (int)$school['id'] ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($school['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </td>
                            <td>
                                <select name="class_id">
                                    <option value="">Class</option>
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
                                <button type="submit" name="save" class="action-btn save-btn">Enregistrer</button>
                                <button type="submit" name="remove" class="action-btn remove-btn" onclick="return confirm('Are you sure you want to remove this student?')">Supprimer</button>
                                <button type="button" class="action-btn cancel-btn" onclick="cancelEdit(<?= (int)$row['id'] ?>)">Ignorer</button>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
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
        // Enable edit mode for a student row
        function enableEdit(id) {
            const row = document.querySelector(`#row-${id}`);
            row.classList.add('edit-mode');
        }

        // Cancel edit mode
        function cancelEdit(id) {
            const row = document.querySelector(`#row-${id}`);
            row.classList.remove('edit-mode');
        }

        //form data validation
        document.getElementById('addStudentForm').addEventListener('submit', function(e) {
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            
            let isValid = true;

            // Check password match
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                document.getElementById('password-error').textContent = 'Passwords do not match';
                isValid = false;
            }

            // Check password strength
            if (password.length < 8) {
                document.getElementById('password-error').textContent = 'Password must be at least 8 characters';
                isValid = false;
            }

            // Check required fields
            const requiredFields = [
                {id: 'name', name: 'Name'},
                {id: 'email', name: 'Email'},
                {id: 'phone', name: 'Phone'},
                {id: 'school_id', name: 'School'},
                {id: 'class_id', name: 'Class'}
            ];

            requiredFields.forEach(field => {
                const element = document.getElementById(field.id);
                let value = element.value;
                
                // For select elements, we just check the value
                if (element.tagName === 'SELECT') {
                    if (value === "") {
                        document.getElementById(`${field.id}-error`).textContent = `${field.name} is required`;
                        isValid = false;
                    }
                } 
                // For input/textarea elements, we trim the value
                else {
                    if (!value.trim()) {
                        document.getElementById(`${field.id}-error`).textContent = `${field.name} is required`;
                        isValid = false;
                    }
                }
            });

            // Validate email format
            const email = document.getElementById('email').value;
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('email-error').textContent = 'Invalid email format';
                isValid = false;
            }

            // Validate phone number
            const phone = document.getElementById('phone').value;
            if (!/^\+?\d{9,}$/.test(phone)) {
                document.getElementById('phone-error').textContent = 'Invalid phone number';
                isValid = false;
            }

            // Prevent submission if not valid
            if (!isValid) {
                e.preventDefault();
            }
            // If valid, allow default form submission
        });
    </script>

</body>
</html>

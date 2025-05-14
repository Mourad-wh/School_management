<?php 
session_start();

if (isset($_POST['username']) &&
    isset($_POST['password']) &&
    isset($_POST['role'])) {

    include "../DB_connection.php";
    
    $uname = $_POST['username'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    if (empty($uname)) {
        $em  = "Username is required";
        header("Location: ../login.php?error=$em");
        exit;
    } else if (empty($pass)) {
        $em  = "Password is required";
        header("Location: ../login.php?error=$em");
        exit;
    } else if (empty($role)) {
        $em  = "An error Occurred";
        header("Location: ../login.php?error=$em");
        exit;
    } else {
        
        if($role == '1'){
            $sql = "SELECT * FROM admin WHERE username = ?";
            $role = "Admin";
        } else if($role == '2'){
            $sql = "SELECT * FROM teachers WHERE username = ?";
            $role = "Professeur";
        } else if($role == '3'){
            $sql = "SELECT * FROM students WHERE username = ?";
            $role = "Etudiant";
        } else {
            $em  = "An error occurred";
            header("Location: ../login.php?error=$em");
            exit;
        }

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$uname]);

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                $username = $user['username'];
                $password = $user['password'];
                
                if ($username == $uname) {
                    if (password_verify($pass, $password)) {
                        $_SESSION['role'] = $role;
                        
                        if ($role == 'Admin') {
                            $id = $user['admin_id'];
                            $_SESSION['admin_id'] = $id;
                            header("Location: ../../admin/dashboard.php");
                            exit;
                        } else if ($role == 'Etudiant') {
                            $id = $user['id'];
                            $_SESSION['student_id'] = $id;
                            header("Location: ../../student/index.php");
                            exit;
                        } else if($role == 'Professeur') {
                            $id = $user['id'];
                            $_SESSION['teacher_id'] = $id;
                            header("Location: ../../teacher/Teacher/index.php");
                            exit;
                        }
                    } else {
                        $em  = "Incorrect Username or Password";
                        header("Location: ../login.php?error=$em");
                        exit;
                    }
                } else {
                    $em  = "Incorrect Username or Password";
                    header("Location: ../login.php?error=$em");
                    exit;
                }
            } else {
                $em  = "Incorrect Username or Password";
                header("Location: ../login.php?error=$em");
                exit;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
    }
} else {
    header("Location: ../login.php");
    exit;
}
?>

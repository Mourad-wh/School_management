<?php  

$sName = "localhost";
$uName = "root";
$pass  = "";
$db_name = "school_db";

try {
    $conn = new PDO("mysql:host=$sName;dbname=$db_name", $uName, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {  // Correction ici
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>

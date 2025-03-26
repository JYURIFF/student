<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_subject'])) {
    $id = $_POST['id'];
    $subject_code = $_POST['subject_code'];
    $name = $_POST['name'];
    $professor = $_POST['professor'];
    $credits = $_POST['credits'];
    $hours = $_POST['hours'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE subjects SET subject_code = ?, name = ?, professor = ?, credits = ?, hours = ?, description = ? WHERE id = ?");
    $stmt->bind_param("sssissi", $subject_code, $name, $professor, $credits, $hours, $description, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: subjects.php");
    exit;
}
?>

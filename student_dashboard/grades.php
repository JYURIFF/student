<?php
include 'db.php';

// ---------------------
// Handle Add Grade
// ---------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_grade'])) {
    $subject_code = $_POST['subject_code'];
    $subject = $_POST['subject'];
    $professor = $_POST['professor'];
    $grade = $_POST['grade'];         // numeric grade earned
    $max_grade = $_POST['max_grade']; // maximum grade value
    $type_of_activity = $_POST['type_of_activity'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO grades (subject_code, subject, professor, grade, max_grade, type_of_activity, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiiis", $subject_code, $subject, $professor, $grade, $max_grade, $type_of_activity, $date);
    $stmt->execute();
    $stmt->close();
    header("Location: grades.php");
    exit;
}

// ---------------------
// Handle Edit Grade
// ---------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_grade'])) {
    $id = $_POST['id'];
    $subject_code = $_POST['subject_code'];
    $subject = $_POST['subject'];
    $professor = $_POST['professor'];
    $grade = $_POST['grade'];
    $max_grade = $_POST['max_grade'];
    $type_of_activity = $_POST['type_of_activity'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("UPDATE grades SET subject_code = ?, subject = ?, professor = ?, grade = ?, max_grade = ?, type_of_activity = ?, date = ? WHERE id = ?");
    $stmt->bind_param("sssiiisi", $subject_code, $subject, $professor, $grade, $max_grade, $type_of_activity, $date, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: grades.php");
    exit;
}

// ---------------------
// Handle Soft Delete
// ---------------------
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE grades SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: grades.php");
    exit;
}

// ---------------------
// Handle Undo Soft Delete
// ---------------------
if (isset($_GET['undo'])) {
    $id = $_GET['undo'];
    $stmt = $conn->prepare("UPDATE grades SET is_deleted = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: grades.php");
    exit;
}

// ---------------------
// Handle Permanent Delete
// ---------------------
if (isset($_GET['delete_perm'])) {
    $id = $_GET['delete_perm'];
    $stmt = $conn->prepare("DELETE FROM grades WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: grades.php");
    exit;
}

// ---------------------
// Search Functionality
// ---------------------
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// ---------------------
// Query Active Grades
// ---------------------
$sqlActive = "SELECT * FROM grades WHERE is_deleted = 0";
if ($search != "") {
    $sqlActive .= " AND (subject_code LIKE '%$search%' 
                      OR subject LIKE '%$search%' 
                      OR professor LIKE '%$search%'
                      OR type_of_activity LIKE '%$search%')";
}
$activeResult = $conn->query($sqlActive);

// ---------------------
// Query Deleted Grades
// ---------------------
$sqlDeleted = "SELECT * FROM grades WHERE is_deleted = 1";
if ($search != "") {
    $sqlDeleted .= " AND (subject_code LIKE '%$search%' 
                      OR subject LIKE '%$search%' 
                      OR professor LIKE '%$search%'
                      OR type_of_activity LIKE '%$search%')";
}
$deletedResult = $conn->query($sqlDeleted);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Grades</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .modal {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
      display: none;
    }
    .modal:target {
      display: block;
    }
    .modal-content {
      background-color: #fff;
      margin: 5% auto;
      padding: 20px;
      width: 50%;
      position: relative;
    }
    .close {
      position: absolute;
      top: 10px; right: 20px;
      text-decoration: none;
      font-size: 20px;
    }
    .btn {
      background: #ff69b4; 
      color: #fff; 
      padding: 8px 15px; 
      text-decoration: none; 
      border-radius: 5px; 
      margin-left: 5px;
    }
  </style>
</head>
<body>
  <nav>
    <h2>📊 Grades</h2>
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="subjects.php">Subjects</a></li>
      <li><a href="grades.php">Grades</a></li>
      <li><a href="schedule.php">Schedule</a></li>
    </ul>
  </nav>
  
  <section class="container">
    <h2>Your Grades</h2>
    
    <!-- Search Form -->
    <form method="GET" action="grades.php" style="display:inline-block;">
      <input type="text" name="search" placeholder="🔍 Search..." value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit">Search</button>
    </form>
    
    <!-- Add Grade Modal Trigger -->
    <div style="float:right;">
      <a href="#addGradeModal" class="btn">Add Grade</a>
    </div>
    <div style="clear:both;"></div>
    <br>
    
    <!-- Active Grades -->
    <h3>Active Grades</h3>
    <div class="grades-grid">
      <?php while ($row = $activeResult->fetch_assoc()): ?>
        <div class="grade-card">
          <h3><?php echo $row['subject']; ?> (<?php echo $row['subject_code']; ?>)</h3>
          <p>Professor: <?php echo $row['professor']; ?></p>
          <p>Grade: <?php echo $row['grade']; ?> / <?php echo $row['max_grade']; ?></p>
          <p>Activity: <?php echo $row['type_of_activity']; ?></p>
          <p>Date: <?php echo $row['date']; ?></p>
          <a href="grades.php?delete=<?php echo $row['id']; ?>" class="details-btn">Delete</a>
          <a href="#editGradeModal<?php echo $row['id']; ?>" class="details-btn">Edit</a>
        </div>
        
        <!-- Modal for Editing Grade -->
        <div id="editGradeModal<?php echo $row['id']; ?>" class="modal">
          <div class="modal-content">
            <a href="grades.php" class="close">&times;</a>
            <h3>Edit Grade</h3>
            <form method="POST" action="grades.php">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <input type="text" name="subject_code" value="<?php echo $row['subject_code']; ?>" required>
              <input type="text" name="subject" value="<?php echo $row['subject']; ?>" required>
              <input type="text" name="professor" value="<?php echo $row['professor']; ?>" required>
              <label>Grade:</label>
              <input type="number" name="grade" value="<?php echo $row['grade']; ?>" required>
              <label>Max Grade:</label>
              <input type="number" name="max_grade" value="<?php echo $row['max_grade']; ?>" required>
              <input type="text" name="type_of_activity" value="<?php echo $row['type_of_activity']; ?>" placeholder="Type of Activity" required>
              <input type="date" name="date" value="<?php echo $row['date']; ?>" required>
              <button type="submit" name="edit_grade">Update</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    
    <!-- Deleted Grades -->
    <h3>Deleted Grades</h3>
    <div class="grades-grid">
      <?php while ($row = $deletedResult->fetch_assoc()): ?>
        <div class="grade-card">
          <h3><?php echo $row['subject']; ?> (<?php echo $row['subject_code']; ?>)</h3>
          <p>Professor: <?php echo $row['professor']; ?></p>
          <p>Grade: <?php echo $row['grade']; ?> / <?php echo $row['max_grade']; ?></p>
          <p>Activity: <?php echo $row['type_of_activity']; ?></p>
          <p>Date: <?php echo $row['date']; ?></p>
          <a href="grades.php?undo=<?php echo $row['id']; ?>" class="details-btn">Undo</a>
          <a href="grades.php?delete_perm=<?php echo $row['id']; ?>" class="details-btn">Delete Permanently</a>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
  
  <!-- Modal for Adding a Grade -->
  <div id="addGradeModal" class="modal">
    <div class="modal-content">
      <a href="grades.php" class="close">&times;</a>
      <h3>Add Grade</h3>
      <form method="POST" action="grades.php">
        <input type="text" name="subject_code" placeholder="Subject Code" required>
        <input type="text" name="subject" placeholder="Subject Name" required>
        <input type="text" name="professor" placeholder="Professor" required>
        <label>Grade:</label>
        <input type="number" name="grade" placeholder="Earned Grade" required>
        <label>Max Grade:</label>
        <input type="number" name="max_grade" placeholder="Max Grade" required>
        <input type="text" name="type_of_activity" placeholder="Type of Activity" required>
        <input type="date" name="date" required>
        <button type="submit" name="add_grade">Add Grade</button>
      </form>
    </div>
  </div>
</body>
</html>

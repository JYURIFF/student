<?php
include 'db.php';

// Handle adding a subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $subject_code = $_POST['subject_code'];
    $name = $_POST['name'];
    $professor = $_POST['professor'];
    $credits = $_POST['credits'];
    $hours = $_POST['hours'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, name, professor, credits, hours, description) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $subject_code, $name, $professor, $credits, $hours, $description);
    $stmt->execute();
    $stmt->close();
    header("Location: subjects.php");
    exit;
}

// Soft delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE subjects SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: subjects.php");
    exit;
}

// Undo soft delete
if (isset($_GET['undo'])) {
    $id = $_GET['undo'];
    $stmt = $conn->prepare("UPDATE subjects SET is_deleted = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: subjects.php");
    exit;
}

// Delete permanently
if (isset($_GET['delete_perm'])) {
    $id = $_GET['delete_perm'];
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: subjects.php");
    exit;
}

// Search functionality
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Query active subjects
$sqlActive = "SELECT * FROM subjects WHERE is_deleted = 0";
if ($search != "") {
    $sqlActive .= " AND (subject_code LIKE '%$search%' 
                    OR name LIKE '%$search%' 
                    OR professor LIKE '%$search%')";
}
$activeResult = $conn->query($sqlActive);

// Query deleted subjects
$sqlDeleted = "SELECT * FROM subjects WHERE is_deleted = 1";
if ($search != "") {
    $sqlDeleted .= " AND (subject_code LIKE '%$search%' 
                    OR name LIKE '%$search%' 
                    OR professor LIKE '%$search%')";
}
$deletedResult = $conn->query($sqlDeleted);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Subjects</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* CSS-only modal using :target */
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
    <h2>📘 Subjects</h2>
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="subjects.php">Subjects</a></li>
      <li><a href="grades.php">Grades</a></li>
      <li><a href="schedule.php">Schedule</a></li>
    </ul>
  </nav>

  <section class="container">
    <h2>Your Subjects</h2>
    
    <!-- Search Form -->
    <form method="GET" action="subjects.php" style="display:inline-block;">
      <input type="text" name="search" placeholder="🔍 Search..." value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit">Search</button>
    </form>
    
    <!-- Add Subject Modal Trigger -->
    <div style="float:right;">
      <a href="#addSubjectModal" class="btn">Add Subject</a>
    </div>
    <div style="clear:both;"></div>
    <br>
    
    <!-- Active Subjects -->
    <h3>Active Subjects</h3>
    <div class="subject-grid">
      <?php while ($row = $activeResult->fetch_assoc()): ?>
        <div class="subject-card">
          <h3><?php echo $row['name']; ?> (<?php echo $row['subject_code']; ?>)</h3>
          <p>Professor: <?php echo $row['professor']; ?></p>
          <p>Credits: <?php echo $row['credits']; ?></p>
          <p>Hours: <?php echo $row['hours']; ?></p>
          <p><?php echo $row['description']; ?></p>
          <a href="subjects.php?delete=<?php echo $row['id']; ?>" class="details-btn">Delete</a>
          <a href="#editSubjectModal<?php echo $row['id']; ?>" class="details-btn">Edit</a>
        </div>
        
        <!-- Modal for Editing -->
        <div id="editSubjectModal<?php echo $row['id']; ?>" class="modal">
          <div class="modal-content">
            <a href="subjects.php" class="close">&times;</a>
            <h3>Edit Subject</h3>
            <form method="POST" action="edit_subject.php">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <input type="text" name="subject_code" value="<?php echo $row['subject_code']; ?>" required>
              <input type="text" name="name" value="<?php echo $row['name']; ?>" required>
              <input type="text" name="professor" value="<?php echo $row['professor']; ?>" required>
              <input type="number" name="credits" value="<?php echo $row['credits']; ?>" required>
              <input type="number" name="hours" value="<?php echo $row['hours']; ?>" required>
              <textarea name="description" required><?php echo $row['description']; ?></textarea>
              <button type="submit" name="update_subject">Update</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    
    <!-- Deleted Subjects -->
    <h3>Deleted Subjects</h3>
    <div class="subject-grid">
      <?php while ($row = $deletedResult->fetch_assoc()): ?>
        <div class="subject-card">
          <h3><?php echo $row['name']; ?> (<?php echo $row['subject_code']; ?>)</h3>
          <p>Professor: <?php echo $row['professor']; ?></p>
          <p>Credits: <?php echo $row['credits']; ?></p>
          <p>Hours: <?php echo $row['hours']; ?></p>
          <p><?php echo $row['description']; ?></p>
          <a href="subjects.php?undo=<?php echo $row['id']; ?>" class="details-btn">Undo</a>
          <a href="subjects.php?delete_perm=<?php echo $row['id']; ?>" class="details-btn">Delete Permanently</a>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
  
  <!-- Modal for Adding a Subject -->
  <div id="addSubjectModal" class="modal">
    <div class="modal-content">
      <a href="subjects.php" class="close">&times;</a>
      <h3>Add Subject</h3>
      <form method="POST" action="subjects.php">
        <input type="text" name="subject_code" placeholder="Subject Code" required>
        <input type="text" name="name" placeholder="Subject Name" required>
        <input type="text" name="professor" placeholder="Professor" required>
        <input type="number" name="credits" placeholder="Credits" required>
        <input type="number" name="hours" placeholder="Hours" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <button type="submit" name="add_subject">Add Subject</button>
      </form>
    </div>
  </div>
</body>
</html>

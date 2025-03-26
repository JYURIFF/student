<?php
include 'db.php';

// Handle adding a schedule entry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_schedule'])) {
    $subject_code = $_POST['subject_code'];
    $subject = $_POST['subject'];
    $professor = $_POST['professor'];
    $day = $_POST['day'];
    $room = $_POST['room'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    $stmt = $conn->prepare("INSERT INTO schedule (subject_code, subject, professor, day, room, start_time, end_time)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $subject_code, $subject, $professor, $day, $room, $start_time, $end_time);
    $stmt->execute();
    $stmt->close();
    header("Location: schedule.php");
    exit;
}

// Soft delete (mark is_deleted=1)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE schedule SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: schedule.php");
    exit;
}

// Undo soft delete (mark is_deleted=0)
if (isset($_GET['undo'])) {
    $id = $_GET['undo'];
    $stmt = $conn->prepare("UPDATE schedule SET is_deleted = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: schedule.php");
    exit;
}

// Permanently delete
if (isset($_GET['delete_perm'])) {
    $id = $_GET['delete_perm'];
    $stmt = $conn->prepare("DELETE FROM schedule WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: schedule.php");
    exit;
}

// Handle editing a schedule entry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_schedule'])) {
    $id = $_POST['id'];
    $subject_code = $_POST['subject_code'];
    $subject = $_POST['subject'];
    $professor = $_POST['professor'];
    $day = $_POST['day'];
    $room = $_POST['room'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    $stmt = $conn->prepare("UPDATE schedule
                            SET subject_code = ?, subject = ?, professor = ?, day = ?, room = ?, start_time = ?, end_time = ?
                            WHERE id = ?");
    $stmt->bind_param("sssssssi", $subject_code, $subject, $professor, $day, $room, $start_time, $end_time, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: schedule.php");
    exit;
}

// Search functionality
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Query active schedules
$sqlActive = "SELECT * FROM schedule WHERE is_deleted = 0";
if ($search != "") {
    // Searching across multiple columns
    $sqlActive .= " AND (subject_code LIKE '%$search%'
                    OR subject LIKE '%$search%'
                    OR professor LIKE '%$search%'
                    OR day LIKE '%$search%'
                    OR room LIKE '%$search%')";
}
$activeResult = $conn->query($sqlActive);

// Query deleted schedules
$sqlDeleted = "SELECT * FROM schedule WHERE is_deleted = 1";
if ($search != "") {
    $sqlDeleted .= " AND (subject_code LIKE '%$search%'
                    OR subject LIKE '%$search%'
                    OR professor LIKE '%$search%'
                    OR day LIKE '%$search%'
                    OR room LIKE '%$search%')";
}
$deletedResult = $conn->query($sqlDeleted);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Schedule</title>
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
    <h2>📆 Schedule</h2>
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="subjects.php">Subjects</a></li>
      <li><a href="grades.php">Grades</a></li>
      <li><a href="schedule.php">Schedule</a></li>
    </ul>
  </nav>
  
  <section class="container">
    <h2>Your Schedule</h2>
    
    <!-- Search Form -->
    <form method="GET" action="schedule.php" style="display:inline-block;">
      <input type="text" name="search" placeholder="🔍 Search by subject, professor, day, room..." 
             value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit">Search</button>
    </form>
    
    <!-- Add Schedule Modal Trigger -->
    <div style="float:right;">
      <a href="#addScheduleModal" class="btn">Add Schedule</a>
    </div>
    <div style="clear:both;"></div>
    <br>
    
    <!-- Active Schedules -->
    <h3>Active Schedules</h3>
    <div class="schedule-grid">
      <?php while ($row = $activeResult->fetch_assoc()): ?>
        <div class="schedule-card">
          <h3><?php echo $row['subject']; ?> (<?php echo $row['subject_code']; ?>)</h3>
          <p>Professor: <?php echo $row['professor']; ?></p>
          <p>Day: <?php echo $row['day']; ?></p>
          <p>Room: <?php echo $row['room']; ?></p>
          <p>Time: <?php echo $row['start_time']; ?> - <?php echo $row['end_time']; ?></p>
          
          <!-- Soft Delete and Edit -->
          <a href="schedule.php?delete=<?php echo $row['id']; ?>" class="details-btn">Delete</a>
          <a href="#editScheduleModal<?php echo $row['id']; ?>" class="details-btn">Edit</a>
        </div>

        <!-- Modal for Editing Schedule -->
        <div id="editScheduleModal<?php echo $row['id']; ?>" class="modal">
          <div class="modal-content">
            <a href="schedule.php" class="close">&times;</a>
            <h3>Edit Schedule</h3>
            <form method="POST" action="schedule.php">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <input type="text" name="subject_code" value="<?php echo $row['subject_code']; ?>" required>
              <input type="text" name="subject" value="<?php echo $row['subject']; ?>" required>
              <input type="text" name="professor" value="<?php echo $row['professor']; ?>" required>
              <input type="text" name="day" value="<?php echo $row['day']; ?>" required>
              <input type="text" name="room" value="<?php echo $row['room']; ?>" required>
              <input type="time" name="start_time" value="<?php echo $row['start_time']; ?>" required>
              <input type="time" name="end_time" value="<?php echo $row['end_time']; ?>" required>
              <button type="submit" name="edit_schedule">Update</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    
    <!-- Deleted Schedules -->
    <h3>Deleted Schedules</h3>
    <div class="schedule-grid">
      <?php while ($row = $deletedResult->fetch_assoc()): ?>
        <div class="schedule-card">
          <h3><?php echo $row['subject']; ?> (<?php echo $row['subject_code']; ?>)</h3>
          <p>Professor: <?php echo $row['professor']; ?></p>
          <p>Day: <?php echo $row['day']; ?></p>
          <p>Room: <?php echo $row['room']; ?></p>
          <p>Time: <?php echo $row['start_time']; ?> - <?php echo $row['end_time']; ?></p>
          
          <!-- Undo soft delete or delete permanently -->
          <a href="schedule.php?undo=<?php echo $row['id']; ?>" class="details-btn">Undo</a>
          <a href="schedule.php?delete_perm=<?php echo $row['id']; ?>" class="details-btn">Delete Permanently</a>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
  
  <!-- Modal for Adding a Schedule -->
  <div id="addScheduleModal" class="modal">
    <div class="modal-content">
      <a href="schedule.php" class="close">&times;</a>
      <h3>Add Schedule</h3>
      <form method="POST" action="schedule.php">
        <input type="text" name="subject_code" placeholder="Subject Code" required>
        <input type="text" name="subject" placeholder="Subject Name" required>
        <input type="text" name="professor" placeholder="Professor" required>
        <input type="text" name="day" placeholder="Day" required>
        <input type="text" name="room" placeholder="Room" required>
        <input type="time" name="start_time" required>
        <input type="time" name="end_time" required>
        <button type="submit" name="add_schedule">Add Schedule</button>
      </form>
    </div>
  </div>
</body>
</html>

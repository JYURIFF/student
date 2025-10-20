<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <nav>
    <h2>🌸 Student Dashboard</h2>
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="subjects.php">Subjects</a></li>
      <li><a href="grades.php">Grades</a></li>
      <li><a href="schedule.php">Schedule</a></li>
    </ul>
  </nav>

  <!-- Main Container to Match Subjects Page Style -->
  <section class="container">
    <h2>Your Dashboard</h2>
    
    <div class="profile-card">
        <!-- Text content on the left -->
        <div class="profile-info">
          <h1>👤 JUSTIN YURI F. FABICON</h1>
          <p><strong>Student ID:</strong> 202311181</p>
          <p><strong>Course:</strong> Computer Science</p>
          <p><strong>Section:</strong> 2-2</p>
          <p><strong>Phone:</strong> 09456027316</p>
        </div>
      
        <!-- Profile image on the right -->
        <img src="pic.png" alt="Your Photo" class="profile-pic">
    </div>

    <!-- Dashboard Grid (Similar to Subject Grid) -->
    <div class="subject-grid">
      <!-- Card 1 -->
      <div class="subject-card">
        <h3>📊 Grades</h3>
        <p>View and track your latest grades.</p>
        <a href="grades.php" class="details-btn">View Grades</a>
      </div>

      <!-- Card 2 -->
      <div class="subject-card">
        <h3>📆 Schedule</h3>
        <p>Check your class schedule.</p>
        <a href="schedule.php" class="details-btn">View Schedule</a>
      </div>

      <!-- Card 3 -->
      <div class="subject-card">
        <h3>📚 Subjects</h3>
        <p>See your enrolled subjects.</p>
        <a href="subjects.php" class="details-btn">View Subjects</a>
      </div>
    </div>
  </section>
</body>
</html>

<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isStudent()) {
   redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

// Get available macs
$query = "SELECT mac_number FROM mac_computers ORDER BY mac_number";
$stmt = $db->prepare($query);
$stmt->execute();
$macs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $mac_number = sanitize($_POST['mac_number']);
   $subject = sanitize($_POST['subject']);
   $purpose = sanitize($_POST['purpose']);

   if (empty($mac_number) || empty($subject) || empty($purpose)) {
      $error = "All fields are required";
   } else {
      // CHECK FOR ANY ACTIVE SESSION (NOT TIMED OUT)
      $query = "SELECT id, subject FROM logbook_entries 
                  WHERE student_id = :student_id 
                  AND time_out IS NULL";
      $stmt = $db->prepare($query);
      $stmt->bindParam(':student_id', $_SESSION['student_id']);
      $stmt->execute();

      if ($stmt->rowCount() > 0) {
         $active_entry = $stmt->fetch(PDO::FETCH_ASSOC);
         $error = "You have an active session (Subject: " . htmlspecialchars($active_entry['subject']) . "). Please time out first before creating a new entry.";
      } else {
         // INSERT new entry
         $query = "INSERT INTO logbook_entries (student_id, full_name, course, year_level, mac_number, subject, purpose) 
                      VALUES (:student_id, :full_name, :course, :year_level, :mac_number, :subject, :purpose)";
         $stmt = $db->prepare($query);
         $stmt->bindParam(':student_id', $_SESSION['student_id']);
         $stmt->bindParam(':full_name', $_SESSION['full_name']);
         $stmt->bindParam(':course', $_SESSION['course']);
         $stmt->bindParam(':year_level', $_SESSION['year_level']);
         $stmt->bindParam(':mac_number', $mac_number);
         $stmt->bindParam(':subject', $subject);
         $stmt->bindParam(':purpose', $purpose);

         if ($stmt->execute()) {
            $success = "Entry logged successfully!";
            header("refresh:2;url=student_dashboard.php");
         } else {
            $error = "Failed to create entry";
         }
      }
   }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MacLog - New Entry</title>
   <link rel="stylesheet" href="css/style.css">
</head>

<body class="dashboard-body">
   <div class="dashboard-container">
      <aside class="sidebar">
         <div class="sidebar-header">
            <div class="logo-small">
               <img src="maclog-logo.png" alt="" class="mac-logo-dashboard">
            </div>
            <h3>MacLog</h3>
         </div>
         <nav class="sidebar-nav">
            <a href="student_dashboard.php" class="nav-item">
               <span>📊</span> Dashboard
            </a>
            <a href="new_entry.php" class="nav-item active">
               <span>➕</span> New Entry
            </a>
            <a href="history.php" class="nav-item">
               <span>📜</span> History
            </a>
            <a href="profile.php" class="nav-item">
               <span>👤</span> Profile
            </a>
         </nav>
         <div class="sidebar-footer">
            <a href="logout.php" class="nav-item logout">
               <span>🚪</span> Logout
            </a>
         </div>
      </aside>

      <main class="main-content">
         <header class="top-bar">
            <h1>Student Form</h1>
            <div class="user-info">
               <a href="profile.php" style="text-decoration: none;">
                  <div class="user-avatar" style="cursor: pointer;">
                     <?php if (isset($_SESSION['profile_picture']) && $_SESSION['profile_picture'] && file_exists($_SESSION['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                     <?php else: ?>
                        <?php echo substr($_SESSION['full_name'], 0, 1); ?>
                     <?php endif; ?>
                  </div>
               </a>
               <div>
                  <div class="user-name"><?php echo $_SESSION['full_name']; ?></div>
                  <div class="user-id"><?php echo $_SESSION['student_id']; ?></div>
               </div>
               <a href="logout.php" class="btn-logout">Logout</a>
            </div>
         </header>

         <div class="content-area">
            <?php
            // Check for active sessions and show warning
            $query = "SELECT id, subject, mac_number FROM logbook_entries 
              WHERE student_id = :student_id AND time_out IS NULL";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':student_id', $_SESSION['student_id']);
            $stmt->execute();
            $active_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($active_sessions) > 0):
            ?>
               <div class="alert-error">
                  <strong>⚠️ You have active session(s):</strong><br>
                  <?php foreach ($active_sessions as $session): ?>
                     Subject: <?php echo htmlspecialchars($session['subject']); ?> |
                     Mac: <?php echo htmlspecialchars($session['mac_number']); ?><br>
                  <?php endforeach; ?>
                  Please time out before creating a new entry.
               </div>
            <?php endif; ?>

            <?php if ($success): ?>
               <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="form-card">
               <form method="POST" action="">
                  <div class="form-row">
                     <div class="form-group">
                        <label>Student Number</label>
                        <input type="text" value="<?php echo $_SESSION['student_id']; ?>" disabled>
                     </div>
                     <div class="form-group">
                        <label>Mac Number</label>
                        <select name="mac_number" required>
                           <option value="">Select Mac</option>
                           <?php foreach ($macs as $mac): ?>
                              <option value="<?php echo $mac['mac_number']; ?>"><?php echo $mac['mac_number']; ?></option>
                           <?php endforeach; ?>
                        </select>
                     </div>
                  </div>

                  <div class="form-row">
                     <div class="form-group">
                        <label>Student Course</label>
                        <input type="text" value="<?php echo $_SESSION['course']; ?>" disabled>
                     </div>
                     <div class="form-group">
                        <label>Year</label>
                        <input type="text" value="<?php echo $_SESSION['year_level']; ?>" disabled>
                     </div>
                  </div>

                  <div class="form-group">
                     <label>Your full name (surname first)</label>
                     <input type="text" value="<?php echo $_SESSION['full_name']; ?>" disabled>
                  </div>

                  <div class="form-group">
                     <label>Subject</label>
                     <input type="text" name="subject" placeholder="e.g., ITE 031" required>
                  </div>

                  <div class="form-group">
                     <label>Purpose of use (Business Reason)</label>
                     <textarea name="purpose" rows="4" placeholder="Describe your purpose for using the Mac..." required></textarea>
                  </div>

                  <button type="submit" class="btn-submit">Submit</button>
               </form>
            </div>
         </div>
      </main>
   </div>
</body>

</html>
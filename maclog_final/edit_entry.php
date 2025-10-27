<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isStudent()) {
   redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$entry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get entry
$query = "SELECT * FROM logbook_entries WHERE id = :id AND student_id = :student_id AND time_out IS NULL";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $entry_id);
$stmt->bindParam(':student_id', $_SESSION['student_id']);
$stmt->execute();

if ($stmt->rowCount() == 0) {
   redirect('student_dashboard.php');
}

$entry = $stmt->fetch(PDO::FETCH_ASSOC);

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
      // Check if subject changed and there's another active entry with new subject
      if ($subject !== $entry['subject']) {
         $query = "SELECT id FROM logbook_entries 
                      WHERE student_id = :student_id 
                      AND subject = :subject 
                      AND time_out IS NULL 
                      AND id != :entry_id";
         $stmt = $db->prepare($query);
         $stmt->bindParam(':student_id', $_SESSION['student_id']);
         $stmt->bindParam(':subject', $subject);
         $stmt->bindParam(':entry_id', $entry_id);
         $stmt->execute();

         if ($stmt->rowCount() > 0) {
            $error = "You have an active session for this subject. Please time out that entry first.";
         }
      }

      if (empty($error)) {
         $query = "UPDATE logbook_entries 
                      SET mac_number = :mac_number, subject = :subject, purpose = :purpose 
                      WHERE id = :id AND student_id = :student_id";
         $stmt = $db->prepare($query);
         $stmt->bindParam(':mac_number', $mac_number);
         $stmt->bindParam(':subject', $subject);
         $stmt->bindParam(':purpose', $purpose);
         $stmt->bindParam(':id', $entry_id);
         $stmt->bindParam(':student_id', $_SESSION['student_id']);

         if ($stmt->execute()) {
            $success = "Entry updated successfully!";
            header("refresh:2;url=student_dashboard.php");
         } else {
            $error = "Failed to update entry";
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
   <title>MacLog - Edit Entry</title>
   <link rel="stylesheet" href="css/style.css">
</head>

<body class="dashboard-body">
   <div class="dashboard-container">
      <aside class="sidebar">
         <div class="sidebar-header">
            <div class="logo-small">
               <div class="logo-gradient-small">M</div>
            </div>
            <h3>MacLog</h3>
         </div>
         <nav class="sidebar-nav">
            <a href="student_dashboard.php" class="nav-item active">
               <span>📊</span> Dashboard
            </a>
            <a href="new_entry.php" class="nav-item">
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
            <h1>Edit Entry</h1>
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
               </div>
               <a href="logout.php" class="btn-logout">Logout</a>
            </div>
         </header>

         <div class="content-area">
            <?php if ($success): ?>
               <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
               <div class="alert-error"><?php echo $error; ?></div>
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
                           <?php foreach ($macs as $mac): ?>
                              <option value="<?php echo $mac['mac_number']; ?>" <?php echo $entry['mac_number'] == $mac['mac_number'] ? 'selected' : ''; ?>>
                                 <?php echo $mac['mac_number']; ?>
                              </option>
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
                     <label>Time In (Cannot be edited)</label>
                     <input type="text" value="<?php echo date('M d, Y h:i A', strtotime($entry['time_in'])); ?>" disabled>
                  </div>

                  <div class="form-group">
                     <label>Subject</label>
                     <input type="text" name="subject" value="<?php echo htmlspecialchars($entry['subject']); ?>" placeholder="e.g., ITE 3MILLION" required>
                  </div>

                  <div class="form-group">
                     <label>Purpose of use (Business Reason)</label>
                     <textarea name="purpose" rows="4" placeholder="Describe your purpose for using the Mac..." required><?php echo htmlspecialchars($entry['purpose']); ?></textarea>
                  </div>

                  <div style="display: flex; gap: 10px;">
                     <button type="submit" class="btn-submit">Update Entry</button>
                     <a href="student_dashboard.php" class="btn-submit" style="background: #718096; text-align: center; line-height: 50px; text-decoration: none;">Cancel</a>
                  </div>
               </form>
            </div>
         </div>
      </main>
   </div>
</body>

</html>
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
   redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
   $student_id = sanitize($_POST['student_id']);
   $new_password = $_POST['new_password'];

   if (strlen($new_password) < 6) {
      $error = "Password must be at least 6 characters";
   } else {
      $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
      $query = "UPDATE students SET password = :password WHERE student_id = :student_id";
      $stmt = $db->prepare($query);
      $stmt->bindParam(':password', $hashed_password);
      $stmt->bindParam(':student_id', $student_id);

      if ($stmt->execute()) {
         $success = "Password reset successful for student: $student_id";
      } else {
         $error = "Failed to reset password";
      }
   }
}

$query = "SELECT s.*, 
          (SELECT COUNT(*) FROM logbook_entries WHERE student_id = s.student_id) as entry_count
          FROM students s ORDER BY s.full_name";
$stmt = $db->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MacLog - Students Management</title>
   <link rel="stylesheet" href="css/style.css">
</head>

<body class="dashboard-body">
   <div class="dashboard-container">
      <aside class="sidebar admin-sidebar">
         <div class="sidebar-header">
            <div class="logo-small">
               <div class="logo-gradient-small">M</div>
            </div>
            <h3>MacLog Admin</h3>
         </div>
         <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="nav-item">
               <span>📊</span> Dashboard
            </a>
            <a href="admin_students.php" class="nav-item active">
               <span>👥</span> Students
            </a>
            <a href="admin_analytics.php" class="nav-item">
               <span>📈</span> Analytics
            </a>
            <a href="admin_reports.php" class="nav-item">
               <span>📄</span> Reports
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
            <h1>Student Management</h1>
            <div class="user-info">
               <div class="user-avatar">A</div>
               <div>
                  <div class="user-name"><?php echo $_SESSION['username']; ?></div>
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

            <div class="section">
               <h3>Registered Students (<?php echo count($students); ?>)</h3>
               <div class="entries-table">
                  <table>
                     <thead>
                        <tr>
                           <th>Student ID</th>
                           <th>Full Name</th>
                           <th>Email</th>
                           <th>Course</th>
                           <th>Year</th>
                           <th>Sessions</th>
                           <th>Registered</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php foreach ($students as $student): ?>
                           <tr>
                              <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                              <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                              <td><?php echo htmlspecialchars($student['email']); ?></td>
                              <td><?php echo htmlspecialchars($student['course']); ?></td>
                              <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                              <td><?php echo $student['entry_count']; ?></td>
                              <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                              <td>
                                 <button onclick="openResetModal('<?php echo $student['student_id']; ?>', '<?php echo htmlspecialchars($student['full_name']); ?>')" class="btn-small">Reset Password</button>
                              </td>
                           </tr>
                        <?php endforeach; ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </main>
   </div>

   <div id="resetModal" class="modal">
      <div class="modal-content">
         <span class="close" onclick="closeModal()">&times;</span>
         <h2>Reset Student Password</h2>
         <form method="POST" action="">
            <input type="hidden" name="student_id" id="reset_student_id">
            <div class="form-group">
               <label>Student: <strong id="reset_student_name"></strong></label>
            </div>
            <div class="form-group">
               <label>New Password</label>
               <input type="password" name="new_password" placeholder="At least 6 characters" required>
            </div>
            <button type="submit" name="reset_password" class="btn-submit">Reset Password</button>
         </form>
      </div>
   </div>

   <script>
      function openResetModal(studentId, studentName) {
         document.getElementById('reset_student_id').value = studentId;
         document.getElementById('reset_student_name').textContent = studentName;
         document.getElementById('resetModal').style.display = 'block';
      }

      function closeModal() {
         document.getElementById('resetModal').style.display = 'none';
      }

      window.onclick = function(event) {
         const modal = document.getElementById('resetModal');
         if (event.target == modal) {
            modal.style.display = 'none';
         }
      }
   </script>
</body>

</html>
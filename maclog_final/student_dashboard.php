<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isStudent()) {
   redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM logbook_entries WHERE student_id = :student_id ORDER BY time_in DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $_SESSION['student_id']);
$stmt->execute();
$my_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MacLog - Dashboard</title>
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
            <h1>Student Dashboard</h1>
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
            <div class="welcome-card">
               <h2>Welcome, <?php echo explode(',', $_SESSION['full_name'])[0]; ?>! 👋</h2>
               <p>Ready to log your Mac session?</p>
               <a href="new_entry.php" class="btn-primary">New Entry +</a>
            </div>

            <div class="stats-grid">
               <div class="stat-card">
                  <div class="stat-number"><?php echo count($my_entries); ?></div>
                  <div class="stat-label">Total Sessions</div>
               </div>
               <div class="stat-card">
                  <div class="stat-number">
                     <?php
                     $total_duration = array_sum(array_column($my_entries, 'duration'));
                     echo round($total_duration / 60, 1);
                     ?>
                  </div>
                  <div class="stat-label">Hours Logged</div>
               </div>
            </div>

            <div class="section">
               <h3>Recent Sessions</h3>
               <div class="entries-table">
                  <?php if (count($my_entries) > 0): ?>
                     <table>
                        <thead>
                           <tr>
                              <th>Mac Number</th>
                              <th>Subject</th>
                              <th>Purpose</th>
                              <th>Time In</th>
                              <th>Time Out</th>
                              <th>Action</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php foreach (array_slice($my_entries, 0, 10) as $entry): ?>
                              <tr>
                                 <td><span class="badge-mac"><?php echo htmlspecialchars($entry['mac_number']); ?></span></td>
                                 <td><?php echo htmlspecialchars($entry['subject']); ?></td>
                                 <td><?php echo htmlspecialchars(substr($entry['purpose'], 0, 50)) . '...'; ?></td>
                                 <td><?php echo date('M d, h:i A', strtotime($entry['time_in'])); ?></td>
                                 <td><?php echo $entry['time_out'] ? date('M d, h:i A', strtotime($entry['time_out'])) : '<span class="badge-active">Active</span>'; ?></td>
                                 <!-- ADD THIS NEW COLUMN -->
                                 <td>
                                    <?php if (!$entry['time_out']): ?>
                                       <button onclick="timeoutEntry(<?php echo $entry['id']; ?>)" class="btn-small btn-success">Time Out</button>
                                       <button onclick="window.location.href='edit_entry.php?id=<?php echo $entry['id']; ?>'" class="btn-small">Edit</button>
                                    <?php endif; ?>
                                 </td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  <?php else: ?>
                     <div class="empty-state">
                        <p>No sessions yet. Create your first entry!</p>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </main>
   </div>
   <script>
      function timeoutEntry(entryId) {
         if (confirm('Are you sure you want to time out this session?')) {
            window.location.href = 'timeout_entry.php?id=' + entryId;
         }
      }
   </script>
</body>

</html>
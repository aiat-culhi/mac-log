<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
   redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT COUNT(*) as total FROM students";
$stmt = $db->prepare($query);
$stmt->execute();
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM logbook_entries";
$stmt = $db->prepare($query);
$stmt->execute();
$total_entries = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM logbook_entries WHERE time_out IS NULL";
$stmt = $db->prepare($query);
$stmt->execute();
$active_sessions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT s.*, 
          (SELECT COUNT(*) FROM logbook_entries WHERE student_id = s.student_id) as entry_count
          FROM students s ORDER BY s.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT * FROM logbook_entries ORDER BY time_in DESC LIMIT 20";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT student_id, full_name, course, COUNT(*) as usage_count 
          FROM logbook_entries 
          GROUP BY student_id 
          ORDER BY usage_count DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$most_active = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT mac_number, COUNT(*) as usage_count 
          FROM logbook_entries 
          GROUP BY mac_number 
          ORDER BY usage_count DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$most_used_macs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MacLog - Admin Dashboard</title>
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
            <a href="admin_dashboard.php" class="nav-item active">
               <span>📊</span> Dashboard
            </a>
            <a href="admin_students.php" class="nav-item">
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
            <h1>Admin Dashboard</h1>
            <div class="user-info">
               <div class="user-avatar">A</div>
               <div>
                  <div class="user-name"><?php echo $_SESSION['username']; ?></div>
                  <div class="user-id">Administrator</div>
               </div>
               <a href="logout.php" class="btn-logout">Logout</a>
            </div>
         </header>

         <div class="content-area">
            <div class="stats-grid admin-stats">
               <div class="stat-card">
                  <div class="stat-icon">👥</div>
                  <div class="stat-number"><?php echo $total_students; ?></div>
                  <div class="stat-label">Total Students</div>
               </div>
               <div class="stat-card">
                  <div class="stat-icon">📝</div>
                  <div class="stat-number"><?php echo $total_entries; ?></div>
                  <div class="stat-label">Total Entries</div>
               </div>
               <div class="stat-card">
                  <div class="stat-icon">✅</div>
                  <div class="stat-number"><?php echo $active_sessions; ?></div>
                  <div class="stat-label">Active Sessions</div>
               </div>
               <div class="stat-card">
                  <div class="stat-icon">💻</div>
                  <div class="stat-number">10</div>
                  <div class="stat-label">Total Macs</div>
               </div>
            </div>

            <div class="analytics-row">
               <div class="analytics-card">
                  <h3>Most Active Users</h3>
                  <div class="analytics-list">
                     <?php if (!empty($most_active)): ?>
                        <?php foreach ($most_active as $user): ?>
                           <div class="analytics-item">
                              <div>
                                 <div class="analytics-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                 <div class="analytics-sub"><?php echo htmlspecialchars($user['course']); ?> - <?php echo htmlspecialchars($user['student_id']); ?></div>
                              </div>
                              <div class="analytics-value"><?php echo $user['usage_count']; ?> sessions</div>
                           </div>
                        <?php endforeach; ?>
                     <?php else: ?>
                        <p>No active user data available.</p>
                     <?php endif; ?>
                  </div>
               </div>

               <div class="analytics-card">
                  <h3>Most Used Macs</h3>
                  <div class="analytics-list">
                     <?php if (!empty($most_used_macs)): ?>
                        <?php foreach ($most_used_macs as $mac): ?>
                           <div class="analytics-item">
                              <div>
                                 <span class="badge-mac"><?php echo htmlspecialchars($mac['mac_number']); ?></span>
                              </div>
                              <div class="analytics-value"><?php echo $mac['usage_count']; ?> sessions</div>
                           </div>
                        <?php endforeach; ?>
                     <?php else: ?>
                        <p>No Mac usage data available.</p>
                     <?php endif; ?>
                  </div>
               </div>
            </div>

            <div class="section">
               <h3>Recent Logbook Entries</h3>
               <div class="entries-table">
                  <table>
                     <thead>
                        <tr>
                           <th>Student ID</th>
                           <th>Name</th>
                           <th>Course</th>
                           <th>Mac</th>
                           <th>Subject</th>
                           <th>Time In</th>
                           <th>Status</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php if (!empty($recent_entries)): ?>
                           <?php foreach ($recent_entries as $entry): ?>
                              <tr>
                                 <td><?php echo htmlspecialchars($entry['student_id']); ?></td>
                                 <td><?php echo htmlspecialchars($entry['full_name']); ?></td>
                                 <td><?php echo htmlspecialchars($entry['course']); ?></td>
                                 <td><span class="badge-mac"><?php echo htmlspecialchars($entry['mac_number']); ?></span></td>
                                 <td><?php echo htmlspecialchars($entry['subject']); ?></td>
                                 <td><?php echo date('M d, h:i A', strtotime($entry['time_in'])); ?></td>
                                 <td><?php echo $entry['time_out'] ? '<span class="badge-completed">Completed</span>' : '<span class="badge-active">Active</span>'; ?></td>
                              </tr>
                           <?php endforeach; ?>
                        <?php else: ?>
                           <tr>
                              <td colspan="7">No recent entries found.</td>
                           </tr>
                        <?php endif; ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </main>
   </div>
</body>

</html>
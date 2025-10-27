<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
   redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT mac_number, COUNT(*) as usage_count,
          COUNT(DISTINCT student_id) as unique_users,
          AVG(duration) as avg_duration
          FROM logbook_entries 
          GROUP BY mac_number 
          ORDER BY usage_count DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$mac_analytics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT student_id, full_name, course, year_level,
          COUNT(*) as usage_count,
          SUM(duration) as total_duration,
          COUNT(DISTINCT mac_number) as macs_used
          FROM logbook_entries 
          GROUP BY student_id 
          ORDER BY usage_count DESC 
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$top_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT DATE(time_in) as date, COUNT(*) as count 
          FROM logbook_entries 
          WHERE time_in >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          GROUP BY DATE(time_in) 
          ORDER BY date";
$stmt = $db->prepare($query);
$stmt->execute();
$daily_usage = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MacLog - Analytics</title>
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
            <a href="admin_students.php" class="nav-item">
               <span>👥</span> Students
            </a>
            <a href="admin_analytics.php" class="nav-item active">
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
            <h1>Admin Analytics</h1>
            <div class="user-info">
               <div class="user-avatar">A</div>
               <div>
                  <div class="user-name"><?php echo $_SESSION['username']; ?></div>
               </div>
               <a href="logout.php" class="btn-logout">Logout</a>
            </div>
         </header>

         <div class="content-area">
            <div class="section">
               <h3>Mac Usage Analytics</h3>
               <div class="entries-table">
                  <table>
                     <thead>
                        <tr>
                           <th>Mac Number</th>
                           <th>Total Sessions</th>
                           <th>Unique Users</th>
                           <th>Avg Duration (min)</th>
                           <th>Status</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php foreach ($mac_analytics as $mac): ?>
                           <tr>
                              <td><span class="badge-mac"><?php echo htmlspecialchars($mac['mac_number']); ?></span></td>
                              <td><?php echo $mac['usage_count']; ?></td>
                              <td><?php echo $mac['unique_users']; ?></td>
                              <td><?php echo $mac['avg_duration'] ? round($mac['avg_duration']) : 'N/A'; ?></td>
                              <td>
                                 <?php if ($mac['usage_count'] > 50): ?>
                                    <span class="badge-high">High Usage</span>
                                 <?php elseif ($mac['usage_count'] > 20): ?>
                                    <span class="badge-medium">Medium Usage</span>
                                 <?php else: ?>
                                    <span class="badge-low">Low Usage</span>
                                 <?php endif; ?>
                              </td>
                           </tr>
                        <?php endforeach; ?>
                     </tbody>
                  </table>
               </div>
            </div>

            <div class="section">
               <h3>Top 10 Most Active Users</h3>
               <div class="entries-table">
                  <table>
                     <thead>
                        <tr>
                           <th>Rank</th>
                           <th>Student ID</th>
                           <th>Name</th>
                           <th>Course</th>
                           <th>Year</th>
                           <th>Total Sessions</th>
                           <th>Total Hours</th>
                           <th>Macs Used</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php $rank = 1;
                        foreach ($top_users as $user): ?>
                           <tr>
                              <td><strong><?php echo $rank++; ?></strong></td>
                              <td><?php echo htmlspecialchars($user['student_id']); ?></td>
                              <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                              <td><?php echo htmlspecialchars($user['course']); ?></td>
                              <td><?php echo htmlspecialchars($user['year_level']); ?></td>
                              <td><?php echo $user['usage_count']; ?></td>
                              <td><?php echo $user['total_duration'] ? round($user['total_duration'] / 60, 1) : '0'; ?></td>
                              <td><?php echo $user['macs_used']; ?></td>
                           </tr>
                        <?php endforeach; ?>
                     </tbody>
                  </table>
               </div>
            </div>

            <div class="section">
               <h3>Usage Trend (Last 7 Days)</h3>
               <div class="chart-container">
                  <?php if (count($daily_usage) > 0): ?>
                     <div class="bar-chart">
                        <?php foreach ($daily_usage as $day): ?>
                           <div class="bar-item">
                              <div class="bar" style="height: <?php echo min(($day['count'] / max(array_column($daily_usage, 'count'))) * 200, 200); ?>px;">
                                 <span class="bar-value"><?php echo $day['count']; ?></span>
                              </div>
                              <div class="bar-label"><?php echo date('M d', strtotime($day['date'])); ?></div>
                           </div>
                        <?php endforeach; ?>
                     </div>
                  <?php else: ?>
                     <p>No data available for the last 7 days.</p>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </main>
   </div>
</body>

</html>
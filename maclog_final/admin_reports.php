<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
   redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT COUNT(*) as total FROM logbook_entries";
$stmt = $db->prepare($query);
$stmt->execute();
$total_entries = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(DISTINCT student_id) as total FROM logbook_entries";
$stmt = $db->prepare($query);
$stmt->execute();
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(DISTINCT mac_number) as total FROM logbook_entries";
$stmt = $db->prepare($query);
$stmt->execute();
$total_macs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MacLog - Generate Reports</title>
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
            <a href="admin_analytics.php" class="nav-item">
               <span>📈</span> Analytics
            </a>
            <a href="admin_reports.php" class="nav-item active">
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
            <h1>Generate PDF Reports</h1>
            <div class="user-info">
               <div class="user-avatar">A</div>
               <div>
                  <div class="user-name"><?php echo $_SESSION['username']; ?></div>
               </div>
               <a href="logout.php" class="btn-logout">Logout</a>
            </div>
         </header>

         <div class="content-area">
            <div class="stats-grid admin-stats" style="margin-bottom: 30px;">
               <div class="stat-card">
                  <div class="stat-icon">📝</div>
                  <div class="stat-number"><?php echo $total_entries; ?></div>
                  <div class="stat-label">Total Entries</div>
               </div>
               <div class="stat-card">
                  <div class="stat-icon">👥</div>
                  <div class="stat-number"><?php echo $total_students; ?></div>
                  <div class="stat-label">Active Students</div>
               </div>
               <div class="stat-card">
                  <div class="stat-icon">💻</div>
                  <div class="stat-number"><?php echo $total_macs; ?></div>
                  <div class="stat-label">Macs in Use</div>
               </div>
            </div>

            <div class="reports-grid">
               <div class="report-card">
                  <div class="report-icon">📋</div>
                  <h3>All Entries Report</h3>
                  <p>Complete logbook entries with student information, Mac numbers, and timestamps.</p>
                  <form action="generate_pdf.php" method="GET" target="_blank">
                     <input type="hidden" name="type" value="all_entries">
                     <div class="date-range">
                        <div class="form-group">
                           <label>From Date</label>
                           <input type="date" name="date_from" value="<?php echo date('Y-m-01'); ?>" required>
                        </div>
                        <div class="form-group">
                           <label>To Date</label>
                           <input type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                     </div>
                     <button type="submit" class="btn-report">
                        <span>📄</span> Generate PDF
                     </button>
                  </form>
               </div>

               <div class="report-card">
                  <div class="report-icon">👤</div>
                  <h3>Student Activity Report</h3>
                  <p>Detailed student usage statistics including sessions, hours logged, and Mac usage patterns.</p>
                  <form action="generate_pdf.php" method="GET" target="_blank">
                     <input type="hidden" name="type" value="student_activity">
                     <div class="date-range">
                        <div class="form-group">
                           <label>From Date</label>
                           <input type="date" name="date_from" value="<?php echo date('Y-m-01'); ?>" required>
                        </div>
                        <div class="form-group">
                           <label>To Date</label>
                           <input type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                     </div>
                     <button type="submit" class="btn-report">
                        <span>📄</span> Generate PDF
                     </button>
                  </form>
               </div>

               <div class="report-card">
                  <div class="report-icon">💻</div>
                  <h3>Mac Usage Report</h3>
                  <p>Mac utilization statistics showing usage frequency, unique users, and average session durations.</p>
                  <form action="generate_pdf.php" method="GET" target="_blank">
                     <input type="hidden" name="type" value="mac_usage">
                     <div class="date-range">
                        <div class="form-group">
                           <label>From Date</label>
                           <input type="date" name="date_from" value="<?php echo date('Y-m-01'); ?>" required>
                        </div>
                        <div class="form-group">
                           <label>To Date</label>
                           <input type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                     </div>
                     <button type="submit" class="btn-report">
                        <span>📄</span> Generate PDF
                     </button>
                  </form>
               </div>
            </div>

            <div class="section" style="margin-top: 30px;">
               <h3>Quick Actions</h3>
               <div class="quick-actions">
                  <a href="generate_pdf.php?type=all_entries&date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>" target="_blank" class="quick-action-btn">
                     <span>📅</span> Today's Entries
                  </a>
                  <a href="generate_pdf.php?type=all_entries&date_from=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&date_to=<?php echo date('Y-m-d'); ?>" target="_blank" class="quick-action-btn">
                     <span>📊</span> Last 7 Days
                  </a>
                  <a href="generate_pdf.php?type=student_activity&date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" target="_blank" class="quick-action-btn">
                     <span>📈</span> This Month Activity
                  </a>
                  <a href="generate_pdf.php?type=mac_usage&date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" target="_blank" class="quick-action-btn">
                     <span>💻</span> This Month Mac Usage
                  </a>
               </div>
            </div>

            <div class="info-box">
               <h4>📌 Report Information</h4>
               <ul>
                  <li><strong>All Entries Report:</strong> Complete list of all logbook entries with student details</li>
                  <li><strong>Student Activity Report:</strong> Ranked list of students by activity with detailed statistics</li>
                  <li><strong>Mac Usage Report:</strong> Utilization statistics for each Mac computer</li>
                  <li><strong>Date Range:</strong> Select custom date ranges to filter report data</li>
                  <li><strong>PDF Format:</strong> Reports are generated in printer-friendly PDF format</li>
               </ul>
            </div>
         </div>
      </main>
   </div>
</body>

</html>
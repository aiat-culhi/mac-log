<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isStudent()) {
   redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$mac_filter = isset($_GET['mac']) ? $_GET['mac'] : '';

$query = "SELECT * FROM logbook_entries WHERE 1=1";
if ($mac_filter) {
   $query .= " AND mac_number = :mac_number";
}
$query .= " ORDER BY time_in DESC";

$stmt = $db->prepare($query);
if ($mac_filter) {
   $stmt->bindParam(':mac_number', $mac_filter);
}
$stmt->execute();
$all_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT DISTINCT mac_number FROM logbook_entries ORDER BY mac_number";
$stmt = $db->prepare($query);
$stmt->execute();
$used_macs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MacLog - History</title>
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
            <a href="new_entry.php" class="nav-item">
               <span>➕</span> New Entry
            </a>
            <a href="history.php" class="nav-item active">
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
            <h1>Mac Usage History</h1>
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
            <div class="filter-section">
               <form method="GET" action="">
                  <label>Filter by Mac:</label>
                  <select name="mac" onchange="this.form.submit()">
                     <option value="">All Macs</option>
                     <?php foreach ($used_macs as $mac): ?>
                        <option value="<?php echo $mac['mac_number']; ?>" <?php echo $mac_filter === $mac['mac_number'] ? 'selected' : ''; ?>>
                           <?php echo $mac['mac_number']; ?>
                        </option>
                     <?php endforeach; ?>
                  </select>
               </form>
            </div>

            <div class="section">
               <h3>Who used this Mac before</h3>
               <div class="entries-table">
                  <?php if (count($all_entries) > 0): ?>
                     <table>
                        <thead>
                           <tr>
                              <th>Student ID</th>
                              <th>Name</th>
                              <th>Course</th>
                              <th>Mac</th>
                              <th>Subject</th>
                              <th>Purpose</th>
                              <th>Time In</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php foreach ($all_entries as $entry): ?>
                              <tr>
                                 <td><?php echo htmlspecialchars($entry['student_id']); ?></td>
                                 <td><?php echo htmlspecialchars($entry['full_name']); ?></td>
                                 <td><?php echo htmlspecialchars($entry['course']); ?></td>
                                 <td><span class="badge-mac"><?php echo htmlspecialchars($entry['mac_number']); ?></span></td>
                                 <td><?php echo htmlspecialchars($entry['subject']); ?></td>
                                 <td><?php echo htmlspecialchars(substr($entry['purpose'], 0, 40)) . '...'; ?></td>
                                 <td><?php echo date('M d, Y h:i A', strtotime($entry['time_in'])); ?></td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  <?php else: ?>
                     <div class="empty-state">
                        <p>No usage history found.</p>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </main>
   </div>
</body>

</html>
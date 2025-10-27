<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
   redirect('index.php');
}

$report_type = isset($_GET['type']) ? $_GET['type'] : 'all_entries';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

$database = new Database();
$db = $database->getConnection();

header('Content-Type: text/html; charset=utf-8');

class PDF
{
   private $content = '';

   public function addHeader($title)
   {
      $this->content .= "<h1 style='color: #667eea; text-align: center; margin-bottom: 10px;'>$title</h1>";
      $this->content .= "<p style='text-align: center; color: #718096; margin-bottom: 30px;'>Generated on " . date('F d, Y h:i A') . "</p>";
   }

   public function addSection($title, $content)
   {
      $this->content .= "<h2 style='color: #2D3748; margin-top: 30px; margin-bottom: 15px;'>$title</h2>";
      $this->content .= $content;
   }

   public function output()
   {
      $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                @media print {
                    @page { margin: 1cm; }
                }
                body { font-family: Arial, sans-serif; padding: 40px; background: white; color: #2D3748; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background: #667eea; color: white; padding: 12px; text-align: left; }
                td { padding: 10px; border-bottom: 1px solid #E2E8F0; }
                tr:nth-child(even) { background: #F7FAFC; }
                .badge { background: #667eea; color: white; padding: 4px 10px; border-radius: 10px; font-size: 12px; display: inline-block; }
                .stat-box { display: inline-block; padding: 20px; background: #F7FAFC; border-radius: 8px; margin: 10px; min-width: 150px; text-align: center; }
                .stat-number { font-size: 32px; font-weight: bold; color: #667eea; }
                .stat-label { color: #718096; font-size: 14px; margin-top: 5px; }
            </style>
        </head>
        <body>
            {$this->content}
            <div style='margin-top: 50px; padding-top: 20px; border-top: 2px solid #E2E8F0; text-align: center; color: #718096;'>
                <p><strong>MacLog System</strong> - Student Mac Lab Logbook</p>
                <p>Report Period: " . date('M d, Y', strtotime($GLOBALS['date_from'])) . " to " . date('M d, Y', strtotime($GLOBALS['date_to'])) . "</p>
            </div>
        </body>
        </html>";

      echo $html;
   }
}

$pdf = new PDF();

if ($report_type === 'all_entries') {
   $query = "SELECT * FROM logbook_entries 
              WHERE DATE(time_in) BETWEEN :date_from AND :date_to 
              ORDER BY time_in DESC";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':date_from', $date_from);
   $stmt->bindParam(':date_to', $date_to);
   $stmt->execute();
   $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

   $pdf->addHeader('MacLog - All Entries Report');

   $stats = "<div style='text-align: center;'>
        <div class='stat-box'>
            <div class='stat-number'>" . count($entries) . "</div>
            <div class='stat-label'>Total Entries</div>
        </div>
    </div>";
   $pdf->addSection('Summary Statistics', $stats);

   $table = "<table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Mac</th>
                <th>Subject</th>
                <th>Time In</th>
            </tr>
        </thead>
        <tbody>";

   foreach ($entries as $entry) {
      $table .= "<tr>
            <td>" . date('M d, Y', strtotime($entry['time_in'])) . "</td>
            <td>" . htmlspecialchars($entry['student_id']) . "</td>
            <td>" . htmlspecialchars($entry['full_name']) . "</td>
            <td>" . htmlspecialchars($entry['course']) . "</td>
            <td><span class='badge'>" . htmlspecialchars($entry['mac_number']) . "</span></td>
            <td>" . htmlspecialchars($entry['subject']) . "</td>
            <td>" . date('h:i A', strtotime($entry['time_in'])) . "</td>
        </tr>";
   }

   $table .= "</tbody></table>";
   $pdf->addSection('All Logbook Entries', $table);
} elseif ($report_type === 'student_activity') {
   $query = "SELECT student_id, full_name, course, year_level,
              COUNT(*) as total_sessions,
              SUM(duration) as total_duration,
              COUNT(DISTINCT mac_number) as macs_used,
              MIN(time_in) as first_session,
              MAX(time_in) as last_session
              FROM logbook_entries 
              WHERE DATE(time_in) BETWEEN :date_from AND :date_to 
              GROUP BY student_id 
              ORDER BY total_sessions DESC";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':date_from', $date_from);
   $stmt->bindParam(':date_to', $date_to);
   $stmt->execute();
   $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

   $pdf->addHeader('MacLog - Student Activity Report');

   $total_sessions = array_sum(array_column($students, 'total_sessions'));
   $total_students = count($students);

   $stats = "<div style='text-align: center;'>
        <div class='stat-box'>
            <div class='stat-number'>$total_students</div>
            <div class='stat-label'>Active Students</div>
        </div>
        <div class='stat-box'>
            <div class='stat-number'>$total_sessions</div>
            <div class='stat-label'>Total Sessions</div>
        </div>
    </div>";
   $pdf->addSection('Summary', $stats);

   $table = "<table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Sessions</th>
                <th>Hours</th>
                <th>Macs Used</th>
                <th>First Session</th>
            </tr>
        </thead>
        <tbody>";

   $rank = 1;
   foreach ($students as $student) {
      $hours = $student['total_duration'] ? round($student['total_duration'] / 60, 1) : 0;
      $table .= "<tr>
            <td><strong>$rank</strong></td>
            <td>" . htmlspecialchars($student['student_id']) . "</td>
            <td>" . htmlspecialchars($student['full_name']) . "</td>
            <td>" . htmlspecialchars($student['course']) . "</td>
            <td>" . $student['total_sessions'] . "</td>
            <td>" . $hours . " hrs</td>
            <td>" . $student['macs_used'] . "</td>
            <td>" . date('M d, Y', strtotime($student['first_session'])) . "</td>
        </tr>";
      $rank++;
   }

   $table .= "</tbody></table>";
   $pdf->addSection('Student Activity Details', $table);
} elseif ($report_type === 'mac_usage') {
   $query = "SELECT mac_number,
              COUNT(*) as total_sessions,
              COUNT(DISTINCT student_id) as unique_users,
              AVG(duration) as avg_duration,
              SUM(duration) as total_duration
              FROM logbook_entries 
              WHERE DATE(time_in) BETWEEN :date_from AND :date_to 
              GROUP BY mac_number 
              ORDER BY total_sessions DESC";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':date_from', $date_from);
   $stmt->bindParam(':date_to', $date_to);
   $stmt->execute();
   $macs = $stmt->fetchAll(PDO::FETCH_ASSOC);

   $pdf->addHeader('MacLog - Mac Usage Report');

   $total_macs = count($macs);
   $total_sessions = array_sum(array_column($macs, 'total_sessions'));

   $stats = "<div style='text-align: center;'>
        <div class='stat-box'>
            <div class='stat-number'>$total_macs</div>
            <div class='stat-label'>Active Macs</div>
        </div>
        <div class='stat-box'>
            <div class='stat-number'>$total_sessions</div>
            <div class='stat-label'>Total Sessions</div>
        </div>
    </div>";
   $pdf->addSection('Summary', $stats);

   $table = "<table>
        <thead>
            <tr>
                <th>Mac Number</th>
                <th>Total Sessions</th>
                <th>Unique Users</th>
                <th>Avg Duration</th>
                <th>Total Hours</th>
                <th>Utilization</th>
            </tr>
        </thead>
        <tbody>";

   foreach ($macs as $mac) {
      $avg_duration = $mac['avg_duration'] ? round($mac['avg_duration']) . ' min' : 'N/A';
      $total_hours = $mac['total_duration'] ? round($mac['total_duration'] / 60, 1) : 0;
      $utilization = $mac['total_sessions'] > 50 ? 'High' : ($mac['total_sessions'] > 20 ? 'Medium' : 'Low');

      $table .= "<tr>
            <td><span class='badge'>" . htmlspecialchars($mac['mac_number']) . "</span></td>
            <td>" . $mac['total_sessions'] . "</td>
            <td>" . $mac['unique_users'] . "</td>
            <td>" . $avg_duration . "</td>
            <td>" . $total_hours . " hrs</td>
            <td>" . $utilization . "</td>
        </tr>";
   }

   $table .= "</tbody></table>";
   $pdf->addSection('Mac Usage Details', $table);
}

$pdf->output();

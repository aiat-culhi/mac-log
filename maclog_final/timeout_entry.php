<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isStudent()) {
   redirect('index.php');
}

$entry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$database = new Database();
$db = $database->getConnection();

// Verify entry belongs to student
$query = "SELECT * FROM logbook_entries WHERE id = :id AND student_id = :student_id AND time_out IS NULL";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $entry_id);
$stmt->bindParam(':student_id', $_SESSION['student_id']);
$stmt->execute();

if ($stmt->rowCount() > 0) {
   $entry = $stmt->fetch(PDO::FETCH_ASSOC);

   // Calculate duration in minutes
   $time_in = new DateTime($entry['time_in']);
   $time_out = new DateTime();
   $interval = $time_in->diff($time_out);
   $duration = ($interval->h * 60) + $interval->i;

   // Update entry
   $query = "UPDATE logbook_entries SET time_out = NOW(), duration = :duration WHERE id = :id";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':duration', $duration);
   $stmt->bindParam(':id', $entry_id);
   $stmt->execute();
}

redirect('student_dashboard.php');

<?php
session_start();
require_once 'config/database.php';

// Auto timeout active sessions if student
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student') {
   $database = new Database();
   $db = $database->getConnection();

   // Get all active sessions
   $query = "SELECT id, time_in FROM logbook_entries WHERE student_id = :student_id AND time_out IS NULL";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':student_id', $_SESSION['student_id']);
   $stmt->execute();
   $active_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

   // Update each active entry
   foreach ($active_entries as $entry) {
      $time_in = new DateTime($entry['time_in']);
      $time_out = new DateTime();
      $interval = $time_in->diff($time_out);
      $duration = ($interval->h * 60) + $interval->i;

      $query = "UPDATE logbook_entries SET time_out = NOW(), duration = :duration WHERE id = :id";
      $stmt = $db->prepare($query);
      $stmt->bindParam(':duration', $duration);
      $stmt->bindParam(':id', $entry['id']);
      $stmt->execute();
   }
}

session_destroy();
header("Location: index.php");
exit();

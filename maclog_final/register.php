<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
   redirect(isAdmin() ? 'admin_dashboard.php' : 'student_dashboard.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $student_id = sanitize($_POST['student_id']);
   $full_name = sanitize($_POST['full_name']);
   $email = sanitize($_POST['email']);
   $course = sanitize($_POST['course']);
   $year_level = sanitize($_POST['year_level']);
   $password = $_POST['password'];
   $confirm_password = $_POST['confirm_password'];

   if (empty($student_id) || empty($full_name) || empty($email) || empty($course) || empty($year_level) || empty($password)) {
      $error = "All fields are required";
   } elseif (!validateStudentId($student_id)) {
      $error = "Invalid Student ID format (XX-XXXX-XXXXXX)";
   } elseif (!validateEmail($email)) {
      $error = "Invalid email format";
   } elseif ($password !== $confirm_password) {
      $error = "Passwords do not match";
   } elseif (strlen($password) < 6) {
      $error = "Password must be at least 6 characters";
   } else {
      $database = new Database();
      $db = $database->getConnection();

      $query = "SELECT id FROM students WHERE student_id = :student_id OR email = :email";
      $stmt = $db->prepare($query);
      $stmt->bindParam(':student_id', $student_id);
      $stmt->bindParam(':email', $email);
      $stmt->execute();

      if ($stmt->rowCount() > 0) {
         $error = "Student ID or Email already registered";
      } else {
         $hashed_password = password_hash($password, PASSWORD_DEFAULT);
         $query = "INSERT INTO students (student_id, full_name, email, course, year_level, password) 
                      VALUES (:student_id, :full_name, :email, :course, :year_level, :password)";
         $stmt = $db->prepare($query);
         $stmt->bindParam(':student_id', $student_id);
         $stmt->bindParam(':full_name', $full_name);
         $stmt->bindParam(':email', $email);
         $stmt->bindParam(':course', $course);
         $stmt->bindParam(':year_level', $year_level);
         $stmt->bindParam(':password', $hashed_password);

         if ($stmt->execute()) {
            $success = "Registration successful! Redirecting...";
            header("refresh:2;url=index.php");
         } else {
            $error = "Registration failed";
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
   <title>MacLog - Register</title>
   <link rel="stylesheet" href="css/style.css">
</head>

<body>
   <div class="login-container">
      <div class="login-left">
         <div class="logo-section">
            <div class="logo">
               <img src="maclog-logo.png" alt="" class="mac-logo">
               <p class="tagline">"Efficient, reliable, and organized &ndash; the future of Mac Lab monitoring"</p>
            </div>
         </div>
      </div>
      <div class="login-right">
         <div class="login-form-container">
            <h2>Hi there</h2>
            <p class="subtitle">Let's help you get started</p>

            <?php if ($success): ?>
               <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
               <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
               <div class="form-row">
                  <div class="form-group">
                     <label>Your student ID</label>
                     <input type="text" name="student_id" placeholder="XX-XXXX-XXXXXX" required value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>">
                  </div>
                  <div class="form-group">
                     <label>Your full name (surname first)</label>
                     <input type="text" name="full_name" placeholder="Dela Cruz, Juan" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                  </div>
               </div>
               <div class="form-row">
                  <div class="form-group">
                     <label>Your college email address</label>
                     <input type="email" name="email" placeholder="jt.delacruz.up@phinmaed.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                  </div>
                  <div class="form-group">
                     <label>Course</label>
                     <select name="course" required>
                        <option value="">Select Course</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSAR">BSAR</option>
                        <option value="BSA">BSA</option>
                     </select>
                  </div>
               </div>
               <div class="form-row">
                  <div class="form-group">
                     <label>Year Level</label>
                     <select name="year_level" required>
                        <option value="">Select Year</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                     </select>
                  </div>
                  <div class="form-group">
                     <label>Password</label>
                     <input type="password" name="password" placeholder="At least 6 characters" required>
                  </div>
               </div>
               <div class="form-group">
                  <label>Confirm Password</label>
                  <input type="password" name="confirm_password" placeholder="Re-enter password" required>
               </div>
               <button type="submit" class="btn-login">Sign up</button>
            </form>

            <p class="register-link">Already have an account? <a href="index.php">Sign in</a></p>
         </div>
      </div>
   </div>
</body>

</html>
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
   if (isAdmin()) {
      redirect('admin_dashboard.php');
   } else {
      redirect('student_dashboard.php');
   }
}

$error = '';
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'student';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
   $user_type = $_POST['user_type'];
   $database = new Database();
   $db = $database->getConnection();

   if ($user_type === 'admin') {
      $username = sanitize($_POST['username']);
      $password = $_POST['admin_password'];

      $query = "SELECT id, username, password FROM admins WHERE username = :username";
      $stmt = $db->prepare($query);
      $stmt->bindParam(':username', $username);
      $stmt->execute();

      if ($stmt->rowCount() == 1) {
         $admin = $stmt->fetch(PDO::FETCH_ASSOC);
         if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_type'] = 'admin';
            redirect('admin_dashboard.php');
         } else {
            $error = "Invalid admin credentials";
         }
      } else {
         $error = "Invalid admin credentials";
      }
   } else {
      $student_id = sanitize($_POST['student_id']);
      $password = $_POST['student_password'];

      $query = "SELECT * FROM students WHERE student_id = :student_id";
      $stmt = $db->prepare($query);
      $stmt->bindParam(':student_id', $student_id);
      $stmt->execute();

      if ($stmt->rowCount() == 1) {
         $student = $stmt->fetch(PDO::FETCH_ASSOC);
         if (password_verify($password, $student['password'])) {
            $_SESSION['user_id'] = $student['id'];
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['full_name'] = $student['full_name'];
            $_SESSION['email'] = $student['email'];
            $_SESSION['course'] = $student['course'];
            $_SESSION['year_level'] = $student['year_level'];
            $_SESSION['profile_picture'] = $student['profile_picture'];
            $_SESSION['user_type'] = 'student';
            redirect('student_dashboard.php');
         } else {
            $error = "Invalid student ID or password";
         }
      } else {
         $error = "Invalid student ID or password";
      }
   }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MacLog - Login</title>
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
            <h2>Welcome back</h2>

            <?php if ($error): ?>
               <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
               <div class="user-type-switch">
                  <input type="radio" name="user_type" value="student" id="student" <?php echo $user_type === 'student' ? 'checked' : ''; ?>>
                  <input type="radio" name="user_type" value="admin" id="admin" <?php echo $user_type === 'admin' ? 'checked' : ''; ?>>
                  <label for="student" class="student-label">Student</label>
                  <label for="admin" class="admin-label">Admin</label>
                  <div class="switch-slider"></div>
               </div>

               <div id="studentFields" style="display: <?php echo $user_type === 'student' ? 'block' : 'none'; ?>;">
                  <div class="form-group">
                     <label>Your student ID</label>
                     <input type="text" name="student_id" placeholder="XX-XXXX-XXXXXX">
                  </div>
                  <div class="form-group">
                     <label>Password</label>
                     <input type="password" name="student_password" placeholder="password">
                  </div>
                  <p class="forgot-link">Forgot password? Contact admin</p>
               </div>

               <div id="adminFields" style="display: <?php echo $user_type === 'admin' ? 'block' : 'none'; ?>;">
                  <div class="form-group">
                     <label>Username</label>
                     <input type="text" name="username" placeholder="Enter username">
                  </div>
                  <div class="form-group">
                     <label>Password</label>
                     <input type="password" name="admin_password" placeholder="password">
                  </div>
               </div>

               <button type="submit" name="login" class="btn-login">Sign In</button>
            </form>

            <p class="register-link">Don't have an account yet? <a href="register.php">Sign up</a></p>
         </div>
      </div>
   </div>

   <script>
      const studentRadio = document.getElementById('student');
      const adminRadio = document.getElementById('admin');
      const studentFields = document.getElementById('studentFields');
      const adminFields = document.getElementById('adminFields');

      function toggleFields() {
         if (studentRadio.checked) {
            studentFields.style.display = 'block';
            adminFields.style.display = 'none';
         } else {
            studentFields.style.display = 'none';
            adminFields.style.display = 'block';
         }
      }

      studentRadio.addEventListener('change', toggleFields);
      adminRadio.addEventListener('change', toggleFields);
   </script>
</body>

</html>
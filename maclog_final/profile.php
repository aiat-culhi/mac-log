<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isStudent()) {
   redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $full_name = sanitize($_POST['full_name']);
   $email = sanitize($_POST['email']);
   $course = sanitize($_POST['course']);
   $year_level = sanitize($_POST['year_level']);
   $current_password = $_POST['current_password'];
   $new_password = $_POST['new_password'];
   $confirm_password = $_POST['confirm_password'];

   // Get current student data first
   $query = "SELECT * FROM students WHERE student_id = :student_id";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':student_id', $_SESSION['student_id']);
   $stmt->execute();
   $student = $stmt->fetch(PDO::FETCH_ASSOC);

   // Handle profile picture upload
   $profile_picture = $student['profile_picture']; // Keep existing by default
   if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
      $allowed = ['jpg', 'jpeg', 'png', 'gif'];
      $filename = $_FILES['profile_picture']['name'];
      $filetype = pathinfo($filename, PATHINFO_EXTENSION);

      if (in_array(strtolower($filetype), $allowed)) {
         $new_filename = $_SESSION['student_id'] . '_' . time() . '.' . $filetype;
         $upload_path = 'uploads/profiles/' . $new_filename;

         // Create directory if not exists
         if (!file_exists('uploads/profiles')) {
            mkdir('uploads/profiles', 0777, true);
         }

         if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
            // Delete old picture if exists
            if ($student['profile_picture'] && file_exists($student['profile_picture'])) {
               unlink($student['profile_picture']);
            }
            $profile_picture = $upload_path;
         } else {
            $error = "Failed to upload profile picture";
         }
      } else {
         $error = "Invalid file type. Only JPG, PNG, and GIF allowed";
      }
   }

   if (empty($error)) {
      if (empty($full_name) || empty($email)) {
         $error = "Name and email are required";
      } elseif (!validateEmail($email)) {
         $error = "Invalid email format";
      } else {
         // Check if changing password
         if (!empty($new_password)) {
            if (empty($current_password)) {
               $error = "Current password is required to change password";
            } elseif (!password_verify($current_password, $student['password'])) {
               $error = "Current password is incorrect";
            } elseif ($new_password !== $confirm_password) {
               $error = "New passwords do not match";
            } elseif (strlen($new_password) < 6) {
               $error = "Password must be at least 6 characters";
            } else {
               // Update with new password
               $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
               $query = "UPDATE students SET full_name = :full_name, email = :email, 
                              course = :course, year_level = :year_level, password = :password,
                              profile_picture = :profile_picture 
                              WHERE student_id = :student_id";
               $stmt = $db->prepare($query);
               $stmt->bindParam(':full_name', $full_name);
               $stmt->bindParam(':email', $email);
               $stmt->bindParam(':course', $course);
               $stmt->bindParam(':year_level', $year_level);
               $stmt->bindParam(':password', $hashed_password);
               $stmt->bindParam(':profile_picture', $profile_picture);
               $stmt->bindParam(':student_id', $_SESSION['student_id']);

               if ($stmt->execute()) {
                  $_SESSION['full_name'] = $full_name;
                  $_SESSION['email'] = $email;
                  $_SESSION['course'] = $course;
                  $_SESSION['year_level'] = $year_level;
                  $_SESSION['profile_picture'] = $profile_picture;
                  $success = "Profile updated successfully!";

                  // Refresh student data
                  $query = "SELECT * FROM students WHERE student_id = :student_id";
                  $stmt = $db->prepare($query);
                  $stmt->bindParam(':student_id', $_SESSION['student_id']);
                  $stmt->execute();
                  $student = $stmt->fetch(PDO::FETCH_ASSOC);
               } else {
                  $error = "Failed to update profile";
               }
            }
         } else {
            // Update without password change
            $query = "UPDATE students SET full_name = :full_name, email = :email, 
                          course = :course, year_level = :year_level, profile_picture = :profile_picture 
                          WHERE student_id = :student_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':course', $course);
            $stmt->bindParam(':year_level', $year_level);
            $stmt->bindParam(':profile_picture', $profile_picture);
            $stmt->bindParam(':student_id', $_SESSION['student_id']);

            if ($stmt->execute()) {
               $_SESSION['full_name'] = $full_name;
               $_SESSION['email'] = $email;
               $_SESSION['course'] = $course;
               $_SESSION['year_level'] = $year_level;
               $_SESSION['profile_picture'] = $profile_picture;
               $success = "Profile updated successfully!";

               // Refresh student data
               $query = "SELECT * FROM students WHERE student_id = :student_id";
               $stmt = $db->prepare($query);
               $stmt->bindParam(':student_id', $_SESSION['student_id']);
               $stmt->execute();
               $student = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
               $error = "Failed to update profile";
            }
         }
      }
   }
}

// Get current student data if not already loaded
if (!isset($student)) {
   $query = "SELECT * FROM students WHERE student_id = :student_id";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':student_id', $_SESSION['student_id']);
   $stmt->execute();
   $student = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>MacLog - Profile</title>
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
            <a href="history.php" class="nav-item">
               <span>📜</span> History
            </a>
            <a href="profile.php" class="nav-item active">
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
            <h1>Profile Settings</h1>
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
            <?php if ($success): ?>
               <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
               <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="profile-container">
               <div class="profile-avatar-section">
                  <div class="profile-avatar-large">
                     <?php if ($student['profile_picture'] && file_exists($student['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($student['profile_picture']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                     <?php else: ?>
                        <?php echo substr($_SESSION['full_name'], 0, 1); ?>
                     <?php endif; ?>
                  </div>
                  <h2><?php echo htmlspecialchars($student['full_name']); ?></h2>
                  <p><?php echo htmlspecialchars($student['student_id']); ?></p>
               </div>

               <div class="form-card">
                  <h3>Personal Information</h3>
                  <form method="POST" action="" enctype="multipart/form-data">
                     <div class="form-group">
                        <label>Profile Picture</label>
                        <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(this)" class="file-input">
                        <div id="imagePreview" style="margin-top: 15px; text-align: center;">
                           <?php if ($student['profile_picture'] && file_exists($student['profile_picture'])): ?>
                              <img src="<?php echo htmlspecialchars($student['profile_picture']); ?>" style="max-width: 200px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                              <p style="margin-top: 10px; color: #718096; font-size: 14px;">Current profile picture</p>
                           <?php else: ?>
                              <p style="color: #718096; font-size: 14px;">No profile picture uploaded</p>
                           <?php endif; ?>
                        </div>
                     </div>

                     <div class="form-group">
                        <label>Student ID (Cannot be changed)</label>
                        <input type="text" value="<?php echo htmlspecialchars($student['student_id']); ?>" disabled>
                     </div>

                     <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                     </div>

                     <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                     </div>

                     <div class="form-row">
                        <div class="form-group">
                           <label>Course</label>
                           <select name="course" required>
                              <option value="BSIT" <?php echo $student['course'] == 'BSIT' ? 'selected' : ''; ?>>BSIT</option>
                              <option value="BSCS" <?php echo $student['course'] == 'BSCS' ? 'selected' : ''; ?>>BSCS</option>
                              <option value="BSCpE" <?php echo $student['course'] == 'BSCpE' ? 'selected' : ''; ?>>BSCpE</option>
                              <option value="BSIS" <?php echo $student['course'] == 'BSIS' ? 'selected' : ''; ?>>BSIS</option>
                           </select>
                        </div>
                        <div class="form-group">
                           <label>Year Level</label>
                           <select name="year_level" required>
                              <option value="1st Year" <?php echo $student['year_level'] == '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                              <option value="2nd Year" <?php echo $student['year_level'] == '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                              <option value="3rd Year" <?php echo $student['year_level'] == '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                              <option value="4th Year" <?php echo $student['year_level'] == '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                           </select>
                        </div>
                     </div>

                     <hr style="margin: 30px 0; border: none; border-top: 1px solid #E2E8F0;">

                     <h3 style="margin-bottom: 20px;">Change Password (Optional)</h3>
                     <p style="color: #718096; font-size: 14px; margin-bottom: 20px;">Leave blank if you don't want to change your password</p>

                     <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter current password">
                     </div>

                     <div class="form-row">
                        <div class="form-group">
                           <label>New Password</label>
                           <input type="password" name="new_password" placeholder="At least 6 characters">
                        </div>
                        <div class="form-group">
                           <label>Confirm New Password</label>
                           <input type="password" name="confirm_password" placeholder="Re-enter new password">
                        </div>
                     </div>

                     <button type="submit" class="btn-submit">Update Profile</button>
                  </form>
               </div>
            </div>
         </div>
      </main>
   </div>

   <script>
      function previewImage(input) {
         const preview = document.getElementById('imagePreview');
         if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
               preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"><p style="margin-top: 10px; color: #48BB78; font-size: 14px;">✓ New image selected - Click Update Profile to save</p>';
            }
            reader.readAsDataURL(input.files[0]);
         }
      }
   </script>
</body>

</html>
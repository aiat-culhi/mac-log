<?php
function sanitize($data)
{
   return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn()
{
   return isset($_SESSION['user_type']);
}

function isAdmin()
{
   return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isStudent()
{
   return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

function redirect($url)
{
   header("Location: $url");
   exit();
}

function validateStudentId($student_id)
{
   return preg_match('/^\d{2}-\d{4}-\d{8}$/', $student_id);
}

function validateEmail($email)
{
   return filter_var($email, FILTER_VALIDATE_EMAIL);
}

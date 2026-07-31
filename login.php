<?php
session_start();
include "database/db_connect.php";

$loginMsg= "";

if($_SERVER["REQUEST_METHOD"]=="POST") {
  
    $email= trim($_POST['email']);
    $password= $_POST['password'];

    $stmt= $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result= $stmt->get_result();

    if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();   

    if (password_verify($password, $row["password"])) {
        
      
      $_SESSION["user_id"]= $row["id"];
      $_SESSION["full_name"]= $row["full_name"];

      header("Location:land.php");
      exit();

      }else{
        $loginMsg="Incorrect password!";
      }
    }else{
      $loginMsg="No account found with this email. Please sign up first.";
    }
    $stmt->close();
  }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | Sweet Haven</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
  <div class="auth-shell">
  <div class="auth-brand">
    <img src="comfy.png" class="spring" alt="Cozy living room with an armchair and plant">
</div>

<div class="auth-form-side">
   <div class="auth-card">
   <h1>Welcome Back!</h1>
   <p class="subtitle">Please login to your account</p>

<div class="auth-message" id="formMessage"
     style="<?php echo $loginMsg ? 'display:block;' : 'display:none;'; ?>">
    <?php echo htmlspecialchars($loginMsg); ?>
</div>

<form id="loginForm" method="POST" action="" novalidate>
  <div class="field">
    <i class="fa-solid fa-user icon-left"></i>
    <input type="text" name="email" id="email" placeholder="Enter Email" required>
  </div>
  <div class="field">
    <i class="fa-solid fa-lock icon-left"></i>
    <input type="password" name="password" id="password" placeholder="Enter Password" required>
    <button type="button" class="toggle-eye" data-target="password"><i class="fa-solid fa-eye"></i></button>
  </div>
  <div class="field-row">
    <label><input type="checkbox" name="remember"> Remember me</label>
    <a href="#">Forgot Password?</a>
  </div>

         
<button type="submit" class="btn-auth" name="login">Login</button>
</form>

<p class="auth-switch">Don't have an account? <a href="signup.php">Sign up</a></p>
</div>
</div>
<!-- Right: feature sidebar -->
<div class="auth-features">
  <div class="feature-item">
    <span class="feature-icon"><i class="fa-solid fa-couch"></i></span>
    <div><strong>Stylish Living</strong><span>Handpicked just for you</span></div>
  </div>
  <div class="feature-item">
    <span class="feature-icon"><i class="fa-solid fa-rotate-left"></i></span>
    <div><strong>Easy Returns</strong><span>Hassle-free experience</span></div>
  </div>
  <div class="feature-item">
    <span class="feature-icon"><i class="fa-solid fa-truck-fast"></i></span>
    <div><strong>Fast Delivery</strong><span>Decor delivered with care</span></div>
  </div>
</div>

</div>

<script>
  document.querySelectorAll('.toggle-eye').forEach(function (btn) {
   btn.addEventListener('click', function () {
    var input = document.getElementById(btn.dataset.target);
    if (!input) return;
    var showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    btn.innerHTML = showing
     ? '<i class="fa-solid fa-eye"></i>'
     : '<i class="fa-solid fa-eye-slash"></i>';
   });
  });

document.getElementById('loginForm').addEventListener('submit', function (e) {
    
    var email = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value.trim();
    var msg = document.getElementById('formMessage');

  if (!email || !password) {
    e.preventDefault();
      msg.textContent = 'Please fill in both fields.';
      msg.style.display = 'block';
      return;
  }

 
});
    </script>
</body>
</html>
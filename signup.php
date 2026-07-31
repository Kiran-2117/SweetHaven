<?php
include "database/db_connect.php";
 
$error = "";
$success = "";
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // Get the values from the form fields
    $full_name = $_POST["full_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $terms = isset($_POST["terms"]) ? "yes" : "no";
 
    // Basic validation
    if ($full_name == "" || $email == "" || $password == "" || $confirm_password == "") {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif ($terms == "no") {
        $error = "You must agree to the Terms & Conditions.";
    } else {
 
       
        $check_query = "SELECT id FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
 
        if (mysqli_num_rows($check_result) > 0) {
            $error = "An account with this email already exists.";
        } else {
          
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
 
            // Insert the new user into the database
            $insert_query = "INSERT INTO users (full_name, email, password) 
                              VALUES ('$full_name', '$email', '$hashed_password')";
 
            if (mysqli_query($conn, $insert_query)) {

            header("Location: land.php");
             exit();

            } else { 
                    $error = "Something went wrong: " . mysqli_error($conn);
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
    <title>Create Account | Signup</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
     <link rel="stylesheet" href="signup.css">
</head>
<body>
    <div class="auth-shell">
       <div class="auth-brand">
           <img src="green login.png" alt="Sweet haven create account" class="spring">
       </div>
    <div class="auth-form-side">
      <div class="auth-card">

        <div class="brand-logo">
            <img src="sweet-haven-logo-2.png" alt="Logo" class="logo">
            <span>Sweet Haven</span>
        </div>

    <h1>Create Account</h1>
    <p class="subtitle">Your dream space starts here.</p>

    <div class="auth-message" id="formMessage" style="display: none;"></div>

    <?php if ($error != "") { ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php } ?>
 
    <?php if ($success != "") { ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php } ?>

<form id="signupForm" method="POST" action="" novalidate>
    <div class="field">
        <i class="fa-solid fa-user icon-left"></i>
        <input type="text" name="full_name" id="full_name" placeholder="Full Name" required>
    </div>

    <div class="field">
        <i class="fa-solid fa-envelope icon-left"></i>
        <input type="email" name="email" id="email" placeholder="Email Address" required>
    </div>

    <div class="field">
        <i class="fa-solid fa-lock icon-left"></i>
        <input type="password" name="password" id="password" placeholder="Password" required>

<button type="button" class="toggle-eye" data-target="password"><i class="fa-solid fa-eye"></i></button>
    </div>

    <div class="field">
        <i class="fa-solid fa-lock icon-left"></i>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
    <button type="button" class="toggle-eye" data-target="confirm_password"><i class="fa-solid fa-eye"></i></button>
    </div>

<div class="terms-row">
    <input type="checkbox" name="terms" id="terms" required>
    <label for="terms">I agree to the <a href="#">Terms &amp;Condition</a></label>
</div>

<button type="submit" class="btn-auth">Create Account</button>
</form>

<p class="auth-switch">Already have an account? <a href="login.php">Login</a></p>

      </div>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-eye').forEach(function (btn) {
     btn.addEventListener('click', function() {
       var input =document.getElementById(btn.dataset.target);
       if(!input) return;
       var showing = input.type === 'text';
       input.type= showing ? 'password' : 'text';
       btn.innerHTML= showing
         ? '<i class="fa-solid fa-eye"></i>': '<i class="fa-solid fa-eye-slash"></i>';
     });
    });

document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fullName= document.getElementById('full_name').value.trim();
    var email= document.getElementById('email').value.trim();
    var password= document.getElementById('password').value.trim();
    var confirm = document.getElementById('confirm_password').value.trim();
    var terms= document.getElementById('terms').checked;
    var msg= document.getElementById('formMessage');

    function showError(text) {
        msg.textContent= text;
        msg.style.display= 'block';
    }

    if(!fullName || !email || !password || !confirm) {
        showError('Please fill all the fields.');
        return;
    }
    if(password !== confirm) {
        showError("Password don't match. Please try again.");
        return;
    }
    if(password.length <8) {
        showError("Password must be atleast 8 characters.");
        return;
    }
    if(!terms) {
        showError("Please agree to the Terms & Conditions.");
        return;
    }

    this.submit();
    
});


</script>
</body>
</html>
<?php
session_start();

$host= "localhost";
$db_user="root";
$db_pass="";
$db_name="sweet_haven";

$conn= mysqli_connect($host, $db_user, $db_pass, $db_name);
if(!$conn){
    die("Database connection failed:" . mysqli_connect_error());
}

if(!isset($_SESSION['user_id'])){
    header("Location:login.php");
    
    exit();
}

$user_id= $_SESSION['user_id'];
$success_msg= "";
$error_msg= "";

$upload_dir = "uploads/profile_images/";
 

if(isset($_POST['upload_avatar']) && isset($_FILES['avatar'])){
 
    $file = $_FILES['avatar'];
 
    // basic error check
    if($file['error'] !== UPLOAD_ERR_OK){
        $error_msg = "Something went wrong while uploading the image.";
    }else{
 
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 2 * 1024 * 1024; 
 
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
 
        if(!in_array($mime_type, $allowed_types)){
            $error_msg = "Only JPG, PNG or WEBP images are allowed.";
        }else if($file['size'] > $max_size){
            $error_msg = "Image must be smaller than 2MB.";
        }else{
 
            // make the upload folder if it doesn't exist yet
            if(!is_dir($upload_dir)){
                mkdir($upload_dir, 0755, true);
            }
 
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
            $destination = $upload_dir . $new_filename;
 
            if(move_uploaded_file($file['tmp_name'], $destination)){
 
              
                $stmt = mysqli_prepare($conn, "SELECT profile_image FROM users WHERE id=?");
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $old_result = mysqli_stmt_get_result($stmt);
                $old_row = mysqli_fetch_assoc($old_result);
                mysqli_stmt_close($stmt);
 
                if(!empty($old_row['profile_image']) && file_exists($upload_dir . $old_row['profile_image'])){
                    unlink($upload_dir . $old_row['profile_image']);
                }
 
                // save just the filename in the database
                $stmt = mysqli_prepare($conn, "UPDATE users SET profile_image=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "si", $new_filename, $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
 
                $success_msg = "Profile picture updated.";
            }else{
                $error_msg = "Could not save the uploaded image.";

            }
        }
    }
}

// handle profile update(phone, gender, address)
if(isset($_POST['update_profile'])){
    $phone= trim($_POST['phone']);
    $gender= trim($_POST['gender']);
    $billing_address= trim($_POST['billing_address']);
    $shipping_address= trim($_POST['shipping_address']);

$sql= "UPDATE users SET phone=?, gender=?, billing_address=?, shipping_address=? WHERE id=? ";
$stmt= mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssi", $phone, $gender, $billing_address, $shipping_address, $user_id);

if(mysqli_stmt_execute($stmt)){
    $success_msg= "Profile updated successfully.";
}else{
    $error_msg= "Something went wrong. Please try again.";
}
mysqli_stmt_close($stmt);
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt= mysqli_prepare($conn, "SELECT password FROM users where id=?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if(!$row || !password_verify($current_password, $row['password'])){
        $error_msg= "Current password is incorrect.";
    }else if($new_password !== $confirm_password){
        $error_msg= "New password and confirm password do not match. ";
    }else if(strlen($new_password) <6) {
        $error_msg= "New password must be of 6 characters.";
    }else{
        $hashed_password= password_hash($new_password, PASSWORD_DEFAULT);
        $stmt= mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
        if(mysqli_stmt_execute($stmt)){
            $success_msg="Password changed successfully";
        }else{
            $error_msg= "Could not update password. Try again";
        }
        mysqli_stmt_close($stmt);
    }
}

$stmt = mysqli_prepare($conn, "SELECT full_name, email, phone, gender, billing_address, shipping_address,profile_image FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result= mysqli_stmt_get_result($stmt);
$user= mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$currentPage= basename($_SERVER['PHP_SELF']);
$avatar_path = "";
if(!empty($user['profile_image']) && file_exists($upload_dir . $user['profile_image'])){
    $avatar_path = $upload_dir . $user['profile_image'];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="profile.css">
    <title>Profile</title>
</head>
<body>
    <div class="profile-wrap">
        <div class="profile-header">

        <h1>My Profile</h1>
        <p>Manage your account details and delivery address</p>
        </div>

        <?php if ($success_msg): ?>
            <div class="msg success"><?= htmlspecialchars($success_msg)?></div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <divc class="msg_error"><?= htmlspecialchars($error_msg) ?></div>
                <?php endif; ?>

   <div class="summary-card">

    <form id="avatarForm"
          action="profile.php"
          method="POST"
          enctype="multipart/form-data">

        <label class="avatar-container" for="avatarInput">

            <?php if($avatar_path): ?>

                <img src="<?= htmlspecialchars($avatar_path) ?>"
                     class="avatar-image"
                     alt="Profile Picture">

            <?php else: ?>

                <div class="avatar-placeholder">
                    <i class="fa-solid fa-user"></i>
                </div>

            <?php endif; ?>

            <div class="camera-icon">
                <i class="fa-solid fa-camera"></i>
            </div>

        </label>

        <input
            type="file"
            name="avatar"
            id="avatarInput"
            accept=".jpg,.jpeg,.png,.webp"
            hidden>

        <input
            type="hidden"
            name="upload_avatar"
            value="1">

    </form>

    <div class="summary-info">
        <h2><?= htmlspecialchars($user['full_name']) ?></h2>
        <p><?= htmlspecialchars($user['email']) ?></p>
    </div>

</div>

    <!-- Profile form  -->
     <form action="profile.php" method="POST" class="section-card">
        <h3><i class="fa-solid fa-address-card"></i> Personal Details</h3>

        <div class="form-grid">
            <div class="field">
                <label>Full Name</label>
                <input type="text" value="<?= htmlspecialchars($user['full_name']) ?>" readonly>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
            </div>
            <div class="field">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="98XXXXXXXX" value="<?= htmlspecialchars($user['phone']) ?? '' ?>" >
            </div>
            <div class="field">
                <label>Gender</label>
                <select name="gender" id="" value="Select Gender">
                    
                    <option value="Female" <?=  (($user['gender'] ?? '')== 'Female')? 'selected': ''?>>Female</option>
                    <option value="Male" <?=  (($user['gender'] ?? '')== 'Male')? 'selected': ''?>>Male</option>
                    <option value="Other" <?=  (($user['gender'] ?? '')== 'Other')? 'selected': ''?>>Other</option>
                </select>
            </div>

        </div>

        <h3 style="margin-top:1.8rem;"><i class="fa-solid fa-location-dot"></i> Address</h3>    
    <p class="hint">You can add just one.</p>

    <div class="form-grid">
        <div class="field">
            <label>Billing Address</label>
            <textarea name="billing_address" placeholder="Street, City, District" id=""><?= htmlspecialchars($user['billing_address']?? '') ?></textarea>
        </div>
        <div class="field">
            <label>Shipping Address</label>
            <textarea name="shipping_address" placeholder="Street, City, District" id=""><?= htmlspecialchars($user['shipping_address']?? '') ?></textarea>
        </div>
    </div>

    <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
    </form>


    <!-- Change Password -->
     <form action="profile.php" method="POST" class="section-card">
        <h3><i class="fa-solid fa-lock"></i> Change Password</h3>

        <div class="form-grid single">
            <div class="field">
                <label>Current Password</label>
                <input type="password" name="current_password" id="" required>
            </div>
            <div class="field">
                <label>New Password</label>
                <input type="password" name="new_password" id="" required>
            </div>
            <div class="field">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>
        </div>

        <button type="submit" name="change_password" class="btn-save">Update Password</button>
     </form>
    </div>


<script>

document.getElementById("avatarInput").addEventListener("change", function(){

    if(this.files.length > 0){

        document.getElementById("avatarForm").submit();

    }

});

</script>
</body>
</html>
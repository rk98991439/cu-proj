<?php
require_once "config.php";

$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

if ($_SERVER['REQUEST_METHOD'] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Username cannot be blank";
    }
    else{
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if($stmt)
        {
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set the value of param username
            $param_username = trim($_POST['username']);

            // Try to execute this statement
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1)
                {
                    $username_err = "This username is already taken"; 
                }
                else{
                    $username = trim($_POST['username']);
                }
            }
            else{
                echo "Something went wrong";
            }
        }
    }

    mysqli_stmt_close($stmt);


// Check for password
if(empty(trim($_POST['password']))){
    $password_err = "Password cannot be blank";
}
elseif(strlen(trim($_POST['password'])) < 5){
    $password_err = "Password cannot be less than 5 characters";
}
else{
    $password = trim($_POST['password']);
}

// Check for confirm password field
if(trim($_POST['password']) !=  trim($_POST['confirm_password'])){
    $password_err = "Passwords should match";
}


// If there were no errors, go ahead and insert into the database
if(empty($username_err) && empty($password_err) && empty($confirm_password_err))
{
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt)
    {
        mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);

        // Set these parameters
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT);

        // Try to execute the query
        if (mysqli_stmt_execute($stmt))
        {
            header("location: login.php");
        }
        else{
            echo "Something went wrong... cannot redirect!";
        }
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <link rel="stylesheet" href="style.css">

</head>

<body>
  <section class="forms-section">
    <h1 class="section-title">CU Bus Pass System</h1>
    <div class="forms">
      <div class="form-wrapper is-active">
        <button type="button" class="switcher switcher-login">
          Login
          <span class="underline"></span>
        </button>
        <form class="form form-login" action="index.php" method="post">
          <fieldset>

            <img class="cu-logo" src="./cu-logo-white.webp" alt="cu-logo">

            <div class="input-block">
              <label for="login-email">Email Address</label>
              <input id="login-email" placeholder="UID@cuchd.in" type="email" required>
            </div>
            <div class="input-block">
              <label for="login-password">Password</label>
              <input id="login-password" placeholder="Password" type="password" name="password" required>
            </div>
            <div class="footer"><span>Signup</span><span>Forgot Password?</span></div>
            <h2 class="qrText">Login With QR Code 🤳🏻</h2>
            <form>
              <input type="file" accept="image/*" onchange="readQRCode(this.files)">
              <div class="qrText" id="result"><h1></h1></div>

          </fieldset>
          <button type="submit" class="btn-login">Login</button>
        </form>

      </div>

      <div class="form-wrapper">
        <button type="button" class="switcher switcher-signup">

          Sign Up
          <span class="underline"></span>
        </button>
        <form class="form form-signup" action="index.php" method="post">
          <fieldset>

            <img class="cu-logo" src="./cu-logo-white.webp" alt="cu-logo-white">

            <div class="input-block">
              <label for="signup-email">Email Address</label>
              <input id="signup-email" placeholder="UID@cuchd.in" type="email" required Id="qrcodeid"
                onchange="generateQRCode(this.value)">
            </div>
            <div class="input-block">
              <label for="signup-password">Password</label>
              <input id="signup-password" placeholder="Password" type="password" required Id="qrcodepass"
                onchange="generateQRCode(this.p)">
            </div>
            <div class="input-block">
              <label for="signup-password-confirm">Confirm password</label>
              <input id="signup-password-confirm" placeholder="Same Password" type="password" required
                onclick="generateQRCode()">
            </div>

            <img class="result" id="qrcode" />
            <a href="#" id="download-link" download="qrcode.png">⬇️</a>
          </fieldset>

          <button type="submit" class="btn-signup">Continue</button>
        </form>
      </div>
    </div>
  </section>

  <script src="script.js"></script>

</body>

</html>


<?php
//This script will handle login
session_start();

// check if the user is already logged in
if(isset($_SESSION['username']))
{
    header("location: welcome.php");
    exit;
}
require_once "config.php";

$username = $password = "";
$err = "";

// if request method is post
if ($_SERVER['REQUEST_METHOD'] == "POST"){
    if(empty(trim($_POST['username'])) || empty(trim($_POST['password'])))
    {
        $err = "Please enter username + password";
    }
    else{
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
    }


if(empty($err))
{
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $param_username);
    $param_username = $username;
    
    
    // Try to execute this statement
    if(mysqli_stmt_execute($stmt)){
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1)
                {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt))
                    {
                        if(password_verify($password, $hashed_password))
                        {
                            // this means the password is corrct. Allow user to login
                            session_start();
                            $_SESSION["username"] = $username;
                            $_SESSION["id"] = $id;
                            $_SESSION["loggedin"] = true;

                            //Redirect user to welcome page
                            header("location: welcome.php");
                            
                        }
                    }

                }

    }
}    


}


?>
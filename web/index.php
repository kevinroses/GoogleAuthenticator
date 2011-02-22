<?php
ob_start(); //i'm too lazy to check when is sent what ;)
//set session cookie to be read only via http and not by JavaScript
ini_set("session.cookie_httponly", 1);
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Google Authenticator in PHP demo</title>
</head>
<body>
<?php
include_once("../lib/GoogleAuthenticator.php");
include_once("Users.php");

$debug = true;

$users = new Users();
if ($username = $users->hasSession()) {
    $user = $users->loadUser($username);
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: ./");
    }
    if ($user->isLoggedIn()) {
        include("../tmpl/loggedin.php");
        if (isset($_GET['showqr'])) {
            $secret = $user->getSecret();
            include("../tmpl/show-qr.php");
        }
    } else if ($user->isOTP() && isset($_POST['otp'])) {
        $g = new GoogleAuthenticator();
        if ($g->checkCode($user->getSecret(),$_POST['otp'])) {
             $user->doLogin();
             if (isset($_POST['remember']) && $_POST['remember']) {
                 $user->setOTPCookie();
             }
             include("../tmpl/loggedin.php");   
        } else {
            session_destroy();
            include("../tmpl/login-error.php");
        }
        
    } else {
        session_destroy();
        include("../tmpl/login.php");
    }
    
    
                
   die();
} else if (isset($_POST['username'])) { 
    $user = $users->loadUser($_POST['username']);
    
    if ($user) {
        if ($user->auth($_POST['password'])) {
            $user->startSession();
            if ($user->hasValidOTPCookie()) {
                include("../tmpl/loggedin.php");
                $user->doLogin();
                
            } else if (!$user->getSecret()) {
                include("../tmpl/loggedin.php");
            
                $secret = $user->generateSecret();
                $users->storeData($user);
                $user->doLogin();
                include("../tmpl/show-qr.php");
               
            } else {
                $user->doOTP();
                include("../tmpl/ask-for-otp.php");
            }
            
            
            die();
        } 
    }
            session_destroy();
        
    include("../tmpl/login-error.php");
    die();
} 

include("../tmpl/login.php");


?>
</body>
</html>
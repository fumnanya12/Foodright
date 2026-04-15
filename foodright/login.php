<?php 
require('connect.php');
$errors="";
$result="";
session_start();
if(isset($_SESSION['registered'])){
    $msg = json_encode($_SESSION['registered']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['registered']);
    
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['username'])&&isset($_POST['password'])){
    $username=filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $trimed_username=trim($username);
    $password=$_POST['password'];
    $query = "SELECT username,user_id,useremail,role,password_hash FROM users WHERE username = :username LIMIT 1";
    $statement=$db->prepare($query);
    $statement->bindValue(':username',$trimed_username);
    $statement->execute();

    $users=$statement->fetch();
    if($users&& password_verify($password,$users['password_hash'])){
    $_SESSION['username']['username']=$users['username']; //creates a session with the username
    $_SESSION['username']['user_id']=$users['user_id'];
    $_SESSION['username']['useremail']=$users['useremail'];
    $_SESSION['username']['role']=$users['role'];
   // $result="You have logged in succesfully";
   $_SESSION['login']="Logged in successfully";
$redirect = $_SESSION['location'] ?? 'index.php';

    header("Location: $redirect");
            exit();
    }
        else{
            $errors="Invalid username or password! ";
        }
    }
    else{
    $errors="Please enter username and password";
}
    
}






?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="./mvp.css" />
   
</head>
<body>
     <main>
           <section>
    <div class="errors">
    <?php if($errors!=""): ?>    
        <p style="color: red;"> Error: <?= $errors ?></p>
    <?php endif?>
    </div>
    </section>
    <section>
   
    <form action="login.php" method="post">
            
                <header>
                <nav> <a href="index.php">home</a>
                    </nav>
                   
               
                    <div class="thumbnail">
                        <img src="./pictures/foodrightlogo.jpg" alt="">

                    </div>

                    <h2>Welcome back</h2>
                    <p>Please enter your details to sign in</p>
                </header>
                <label for="username">Name:</label>
                <input type="text" id="username" name="username" placeholder="Enter your username..." required>
                 <label for="userpassword">Password:</label>
                <input  type="password" id="password" name="password" required>
                <div id="runner">
                <div class="remember">
                <input type="checkbox" id="check" name="remember">
                <label for="check">Remember me</label>
                </div>
                <a href="register.php">Create a new account</a>

                <button type="submit">Submit</button>
            </div>
    </form>
   
    </section>
 

    </main>
</body>
</html>

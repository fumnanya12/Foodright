<?php 
require('connect.php');
session_start();

if (!isset($_SESSION['username']['user_id']) || $_SESSION['username']['role'] !== 'admin') {
    header("Location: $base/index.php");
    exit;
}
$errors=[];
function create_slug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['username'])&&isset($_POST['password'])){
    $username=trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
    $useremail=trim(filter_input(INPUT_POST,'useremail',FILTER_SANITIZE_EMAIL)??'');
    $role=trim(filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');

    $trimed_username=trim($username);
    $password=$_POST['password'] ?? '';
    $confirm_password=$_POST['confirm_password']?? '';
    $slug=create_slug($username);
    $allowed_roles=['admin','user'];

    if($username===''){
        $errors[]="Username is required.";
    }
      if($useremail===''){
        $errors[]="Email is required.";
    }

    if ($password === '' || $confirm_password === '') {
        $errors[] = "Both password fields are required.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if($role===''||!in_array($role,$allowed_roles,true)){
        $errors[] = "Please select a role.";

    }
    if(empty($errors)){
        $password_hash=password_hash($password,PASSWORD_DEFAULT);

        $query="INSERT INTO users (username,useremail,password_hash,role,slug) values (:username, :useremail, :password_hash,:role,:slug)";
        $statement=$db->prepare($query);
        $statement->bindValue(':username',$username);
        $statement->bindValue(':useremail',$useremail);
        $statement->bindValue(':password_hash',$password_hash);
        $statement->bindValue(':role',$role);

        $statement->bindValue(':slug',$slug);

        $statement->execute();
        $_SESSION['registered']="You haved Registered your account ";
        header("Location: admin.php");
            exit();
    }

    



    
  
}
}






?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="./mvp.css" />
   
</head>
<body>
     <main>
           <section>
    <div class="errors">
    <?php if (!empty($errors)): ?>
    <?php foreach($errors as $error): ?>
        <p style="color:red;">Error: <?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>
    <?php endif; ?>
    </div>
    </section>
    <section>
   
    <form action="register.php" method="post">
            
                <header>
                <nav> <a href="admin.php">back</a>
                    </nav>
                   
               
                    <div class="thumbnail">
                        <img src="./pictures/foodrightlogo.jpg" alt="">

                    </div>

                <h2>Create a user</h2>
                </header>
                <label for="username">Name:</label>
                <input type="text" id="username" name="username" placeholder="Enter your username..." required>
                <label for="useremail">Email:</label>
                <input type="email" id="useremail" name="useremail" placeholder="Enter your Email address..." required>
                <label for="role">Category</label>

                <select id="role" name="role" style="width: 180px;" required>
                <option value="user">user</option>
                <option value="admin">admin</option>

            </select>

                <label for="userpassword">Password:</label>
                <input  type="password" id="password" name="password" required>
                   <label for="confirm_password">Reenter Password:</label>
                <input  type="password" id="confirm_password" name="confirm_password" required>
                <div id="runner">
            

                <button type="submit">Join</button>
            </div>
    </form>
   
    </section>
 

    </main>
</body>
</html>

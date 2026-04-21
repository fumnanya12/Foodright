<?php
$base = '/Assignment/Finalproject/foodright';
session_start();
require('connect.php');
if (!isset($_SESSION['username']['user_id']) || $_SESSION['username']['role'] !== 'admin') {
    header("Location: $base/index.php");
    exit;
}
if(isset($_SESSION['login'])){
    $msg = json_encode($_SESSION['login']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['login']);
    
}
$errors=[];

$user=null;
if($_SERVER['REQUEST_METHOD'] === 'POST'){
$id  = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$username=trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
$useremail=trim(filter_input(INPUT_POST,'useremail',FILTER_SANITIZE_EMAIL)??'');
$role=trim(filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');

if($_POST['command']==='Update'){
    if ($username === '') {
        $errors[] = "Username is required.";
    }

    if ($useremail === '') {
        $errors[] = "useremail is required.";
    }

  
    if ($role === '') {
        $errors[] = "role is required.";
    }
    elseif($role!=='admin' && $role!=='user'){
         $errors[]="role must be admin or user: $role";
    }

    if(empty($errors)){
        $query="UPDATE users SET username= :username, useremail=:useremail, role=:role WHERE user_id = :id";
        $statement=$db->prepare($query);
        $statement->bindValue(':username',$username);
        $statement->bindValue(':useremail',$useremail);
        $statement->bindValue(':role',$role);
        $statement->bindValue(':id', (int)$id, PDO::PARAM_INT);

        $statement->execute();
          header("Location: $base/admin.php");
    exit();


    }
    $user=[
        'user_id' => $id,
        'username' => $username,
        'useremail' => $useremail,
        'role' => $role,

    ];
  

}
 elseif ($_POST['command'] === 'Delete'){
        $query="DELETE FROM users WHERE user_id = :id";
        $statement=$db->prepare($query);
        $statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $statement->execute();
        header("Location: $base/admin.php");



    }
    
}
elseif(isset($_GET['id'])){

$id=filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$slug=filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

$query = "SELECT * FROM users WHERE user_id = :id LIMIT 1";
$statement = $db->prepare($query);
$statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
//$statement->bindValue(':slug', $slug);
$statement->execute();
$user = $statement->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit('Recipe not found.');
}
}else{
    $id=false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit user </title>
    <link rel="stylesheet" href="./mvp.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&display=swap" rel="stylesheet">
   
</head>
<body>
     <header class="homepage">
        <nav class="navigation" >
            <div class="logo" >
            <p>FOOD <br>Right</p>
             <img src="./pictures/foodrightlogo.jpg" alt="">
             </div>
            <ul>
                <li><a href="index.php">Receipes</a></li>
                <li><a href="#">Calorie calculator</a></li>
                <?php if(isset($_SESSION['username']['user_id'])):
                     $_SESSION['location']="edituser.php"; ?>
                    <li><a href="createrecipe.php">CreateReceipe</a></li>

                    <li><a href="logout.php" onclick="return confirm('Are you sure you want to Logout?');" >Logout</a></li>
                <?php else :
                    $_SESSION['location']="edituser.php";?>
                <li><a href="login.php">Login</a></li>
                <?php endif?>
            </ul>
        </nav>
    </header>
    <main>
        <section class="receipe-details">
             
            <h2><?= $user['username'] ?> Profile </h2>
             <div class="edit-details">
                    <?php if (!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                    <p style="color:red;">Error: <?= htmlspecialchars($error)  ?></p>
                <?php endforeach; ?>
                <?php endif; ?>
                <form action="edituser.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $user['user_id'] ?>">
                <label for="username">Username:</label>    
                <input type="text" id="username" name="username" value="<?= $user['username'] ?>">
    
                <label for="useremail">Email:</label>
                <input type="email" id="useremail" name="useremail" value="<?= $user['useremail'] ?>">
                <label for="Role">Role:</label>
                <select id="role" name="role" style="width: 180px;" required>
                 <?php if($user['role'] =="user"):?>
                <option value="user">user</option>
                <option value="admin">admin</option>
                <?php else:?>
                <option value="admin">admin</option>
                <option value="user">user</option>
                <?php endif; ?>
            </select>

                        <input type="submit" name="command" value="Update">
                        <input type="submit" name="command" value="Delete"   onclick="return confirm('Are you sure you want to delete this recipe?');" >

                    </p>
            </form>
            
            </div>
           
    
        </section>

       
       
        
    </main>
    
</body>
</html>

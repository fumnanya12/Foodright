<?php
$base = '/Assignment/Finalproject/foodright';
session_start();
require('connect.php');
if (!isset($_SESSION['username']['user_id']) || $_SESSION['username']['role'] !== 'admin') {
    header("Location: $base/index.php");
    exit;
}
if(isset($_SESSION['registered'])){
    $msg = json_encode($_SESSION['registered']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['registered']);
    
}
$query="SELECT * FROM users LIMIT 20";
 // A PDO::Statement is prepared from the query.
     $statement = $db->prepare($query);

     // Execution on the DB server is delayed until we execute().
     $statement->execute(); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dasbhoard </title>
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
                <li><a href="index.php">Home</a></li>
                <li><a href="allrecipes.php">Receipes</a></li>
                <li><a href="#">Calorie calculator</a></li>
                <li><a href="createrecipe.php">CreateReceipe</a></li>
                <?php if(isset($_SESSION['username']['user_id'])):
                    $_SESSION['location']="$base/admin.php";
                    ?>
                    <li><a href="logout.php" onclick="return confirm('Are you sure you want to Logout?');" >Logout</a></li>
                <?php else :
                     $_SESSION['location']="$base/admin.php";
                    ?>
                <li><a href="login.php">Login</a></li>
                <?php endif?>
            </ul>
        </nav>
    </header>
    <main>
        <section class="usersdetails">
            <h2>Foodright users</h2>
            <aside>
             <?php while(($row=$statement->fetch())): ?>
            <div class="user-list">
                <img src="./pictures/user-regular.png" alt="">
                <p><?= $row['username'] ?></p>
                <a href="<?= $base?>/edituser.php?id=<?=$row['user_id'] ?>">edit</a>
            </div>
            
            <?php endwhile ?>

          
            </aside>
                <a href="<?= $base?>/createuser.php"><button>Add new user</button></a>

        </section>
        <hr>

       
       
        
    </main>
    
</body>
</html>

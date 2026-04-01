<?php
require('connect.php');
session_start();
if(isset($_SESSION['login'])){
    $msg = json_encode($_SESSION['login']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['login']);
    
}
$query="SELECT * FROM recipes LIMIT 20";
 // A PDO::Statement is prepared from the query.
     $statement = $db->prepare($query);

     // Execution on the DB server is delayed until we execute().
     $statement->execute(); 
$placeholder='/Assignment/Finalproject/foodright/pictures/placeholder.png';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodRight</title>
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
                <li><a href="#">Receipes</a></li>
                <li><a href="#">Calorie calculator</a></li>
                <li><a href="createrecipe.php">CreateReceipe</a></li>
                <?php if(isset($_SESSION['username']['user_id'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else :?>
                <li><a href="login.php">Login</a></li>
                <?php endif?>
            </ul>
        </nav>
    </header>
    <main>
        <section class="search-feature">
            <div class="search-wrapper">
            <div class="InputContainer">
            <input placeholder="Search.." id="input" class="input" name="text" type="text"> 
            </div>
               <div class="dropdownStyle">

                <div class="optionStyle">Test</div>
                <div class="optionStyle">Test</div>
                <div class="optionStyle">Test</div>
                <div class="optionStyle">Test</div>
                <div class="optionStyle">Test</div>
                
            </div>  
            <button>Submit</button>
                 </div>
        </section>
        <hr>

        <section>
            <h2>Feature Receipes</h2>
        <div id="section-1">
           <?php while(($row=$statement->fetch())): 
            $image = $row['imagepath'] ?? null;

        $image = ($image && $image !== 'No image')  ? $image : $placeholder;
            
            ?>
            <aside>
                
                <a href="<?= $row['recipe_id'] ?>/<?= $row['slug'] ?>/" class="card-link">
                    <img src="<?=  $image ?>" alt="<?= $row['title'] ?>">
                 <h3><?=$row['title'] ?></h3>
                <p>Description</p>
                <p><small><?=($row['description']) ?></small></p>
                </a>
            </aside>

            <?php endwhile ?>
          
        </div>
        <hr>
        </section>
        
    </main>
    
</body>
</html>

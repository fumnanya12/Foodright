<?php
require('connect.php');
session_start();
if(isset($_SESSION['login'])){
    $msg = json_encode($_SESSION['login']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['login']);
    
}

$query="SELECT * FROM recipes LIMIT 10";
 // A PDO::Statement is prepared from the query.
     $statement = $db->prepare($query);

     // Execution on the DB server is delayed until we execute().
     $statement->execute(); 
$placeholder='/pictures/placeholder.png';
$rows = $statement->fetchAll();



$query_cat="SELECT * FROM category ORDER BY category ASC";
$statement_cat = $db->prepare($query_cat);
$statement_cat->execute(); 
$categories=$statement_cat->fetchAll();
$base = '';
$currentKeyword = filter_input(INPUT_GET,'keyword',FILTER_SANITIZE_FULL_SPECIAL_CHARS)?? '';
$currentCategory = filter_input(INPUT_GET,'category',FILTER_SANITIZE_FULL_SPECIAL_CHARS)?? 'all';

$_SESSION['backlocation']="$base/index.php";

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
                <li><a href="allrecipes.php">Receipes</a></li>
                <li><a href="admin.php">Admin</a></li>
                <?php if(isset($_SESSION['username']['user_id'])): 
                     $_SESSION['location']="index.php";
                    ?>
                <li><a href="createrecipe.php">CreateReceipe</a></li>

                <li><a href="logout.php?redirect=allrecipes.php" onclick="return confirm('Are you sure you want to Logout?');" >Logout</a></li>
                <?php else :
                     $_SESSION['location']="index.php";
                    ?>
                <li><a href="login.php?redirect=allrecipes.php">Login</a></li>
                <?php endif?>
            </ul>
        </nav>
    </header>
    <main>
         <section class="search-feature">
    <form class="search-wrapper" action="search.php" method="get">
            <div class="InputContainer">
            <input placeholder="Search.." id="input" class="input" name="keyword" type="text"> 
            </div>

            <select name="category">
            <option value="all">All Categories</option>
            <?php foreach($categories as $cat): ?>
                 <option value="<?= $cat['category'] ?>"><?= $currentCategory === $cat ? 'selected' : '' ?>> <?= $cat['category'] ?></option>
                <?php endforeach ?>
        </select>
               <div class="dropdownStyle">

                <div class="optionStyle">Test</div>
                <div class="optionStyle">Test</div>
                <div class="optionStyle">Test</div>
                <div class="optionStyle">Test</div>
                <div class="optionStyle">Test</div>
                
            </div>  
            <button>Submit</button>
               </form>
        </section>
        <hr>

        <section>
            <h2>Feature Receipes</h2>
        <div id="section-1">
           <?php foreach($rows as $row): 
            $image = $row['imagepath'] ?? null;

           $image = ($image && $image !== 'No image')  ? $image : $placeholder;

            ?>
            <aside>
                
                <a href="<?= $row['recipe_id'] ?>/<?= $row['slug'] ?>/" class="card-link">
                <?php if($row['imagepath']!=='No image'):?>

                <img src="<?= $image?>" alt="<?= $row['title'] ?>">
                 <?php endif?>
                 <h3><?=$row['title'] ?></h3>
                <p>Description</p>
                <p><small><?=($row['description']) ?></small></p>
                </a>
            </aside>

            <?php endforeach ?>
          
        </div>
        <hr>
        </section>
        
    </main>
    
</body>
</html>

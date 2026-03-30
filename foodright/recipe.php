<?php
require('connect.php');
session_start();

$id=filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$slug=filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

$query = "SELECT * FROM recipes WHERE recipe_id = :id AND slug = :slug LIMIT 1";
$statement = $db->prepare($query);
$statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
$statement->bindValue(':slug', $slug);
$statement->execute();
$recipe = $statement->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    http_response_code(404);
    exit('Recipe not found.');
}
$base = '/Assignment/Finalproject/foodright'
?>

 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $recipe['title'] ?></title>
    <link rel="stylesheet" href="<?= $base ?>/mvp.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&display=swap" rel="stylesheet">
   
</head>
<body>
     <header class="homepage">
        <nav class="navigation" >
            <div class="logo" >
            <p>FOOD <br>Right</p>
             <img src="<?= $base ?>/pictures/foodrightlogo.jpg" alt="">
             </div>
            <ul>
                <li><a href="#">Receipes</a></li>
                <li><a href="#">Calorie calculator</a></li>
                <li><a href="#">Test</a></li>
                 <?php if(isset($_SESSION['login'])): ?>
                    <li><a href="<?= $base ?>/logout.php">Logout</a></li>
                <?php else :?>
                <li><a href="<?= $base ?>/login.php">Login</a></li>
                <?php endif?>
            </ul>
        </nav>
    </header>
    <main>
        <section class="receipe-details">
           
            <h2>Feature Receipes</h2>
            <aside class="meal-details">
                  <nav> 
                    <a href="<?= $base ?>/index.php">home</a> 
                    <?php if(isset($_SESSION['username']['user_id']) && $_SESSION['username']['role'] == 'admin'):?>
                        <a href="<?= $base ?>/edit.php?id=<?= $recipe['recipe_id'] ?>&slug=<?= $recipe['slug'] ?>">edit</a>                    

                    <?php endif?>
                   
            </nav>
        
                <h3><?= $recipe['title'] ?></h3>
                <img src="<?= $base ?>/<?= $recipe['imagepath']  ?>" alt="<?= $recipe['title'] ?>">
                <div id="subinfo">
                    <p>Category:</p>
                    <p><small><?= $recipe['category'] ?></small></p>
                    <p>Cooktime:</p>
                     <p><small><?= $recipe['cook_time'] ?>mins</small></p>
                    <p>Servings:</p>
                    <p><small><?= $recipe['servings'] ?></small></p>
                    
                </div>
                <p>Description</p>
                <p><small><?= $recipe['description'] ?></small></p>
                <p>Ingredients</p>
                <p><small><?= nl2br($recipe['ingredients']) ?></small></p>
                <p>Instructions</p>
                <p><small><?= nl2br($recipe['instructions'] )?></small></p>
            </aside>
           
        <hr>
        </section>
        
    </main>
    
</body>
</html>
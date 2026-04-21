<?php
require('connect.php');
session_start();
if(isset($_SESSION['login'])){
    $msg = json_encode($_SESSION['login']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['login']);
    
}
$errors=[];
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
$base = '';$image = $recipe['imagepath'] ?? null;
$placeholder='/pictures/placeholder.png';

$image = ($image && $image !== 'No image')  ? $image : $placeholder;

$commentquery="SELECT * FROM comments WHERE recipe_id = :id ORDER BY created_at desc";
$commentstatement=$db->prepare($commentquery);
$commentstatement->bindValue(':id', (int)$id, PDO::PARAM_INT);
$commentstatement->execute();
$recipecomments=$commentstatement->fetchAll();


if($_SERVER['REQUEST_METHOD']==='POST'){
    $userid=filter_input(INPUT_POST,'user_id', FILTER_SANITIZE_NUMBER_INT);
    $recipeid=filter_input(INPUT_POST,'recipe_id', FILTER_SANITIZE_NUMBER_INT);
    $username=filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $comment=trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
   if($comment===''){
    $errors[]="A comment us required "; 
   }
   if(empty($errors)){
    $query="INSERT INTO comments (recipe_id, user_id,user_name,comment) values(:recipe_id, :user_id,:user_name,:comment)";
    $statement=$db->prepare($query);
    $statement->bindValue(':user_id',$userid);
    $statement->bindValue(':recipe_id',$recipeid);
    $statement->bindValue(':user_name',$username);
    $statement->bindValue(':comment',$comment);
    $statement->execute();
    header("Location: $base/{$id}/{$slug}/");
    exit();
    }
}

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
                <li><a href="<?= $base ?>/index.php">Home</a></li>
                <li><a href="<?= $base ?>/allrecipes.php">Receipes</a></li>
                <li><a href="#">Calorie calculator</a></li>
                <li><a href="<?= $base ?>/createrecipe.php">CreateReceipe</a></li>
                 <?php if(isset($_SESSION['username']['user_id'])):
                    $_SESSION['location']="$base/{$id}/{$slug}/";
                    ?>
                    <li><a href="<?= $base ?>/logout.php">Logout</a></li>
                <?php else :
                $_SESSION['location']="$base/{$id}/{$slug}/";
                    ?>
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
                    <a href="<?=$_SESSION['backlocation']?>">Back</a> 
                    <?php if(isset($_SESSION['username']['user_id']) && $_SESSION['username']['role'] == 'admin'):?>
                        <a href="<?= $base ?>/editrecipe.php?id=<?= $recipe['recipe_id'] ?>&slug=<?= $recipe['slug'] ?>">edit</a>                    

                    <?php endif?>
                   
            </nav>
        
                <h3><?= $recipe['title'] ?></h3>
                <?php if($recipe['imagepath']!=='No image'):?>

                <img src="<?= $base ?>/<?= $image?>" alt="<?= $recipe['title'] ?>">
                <?php endif?>

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
        <aside>
            <h3>Recent comments</h3>

            <form action="<?= $base ?>/<?= $recipe['recipe_id'] ?>/<?= $recipe['slug'] ?>/" method="post">
                <h3>My comment</h3>
                
                <?php if(!isset($_SESSION['username']['user_id'])): ?>
                <label for="username">Name:</label>
                <input type="text" name="user_name" id="user_name" required>
                <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id'] ?>">
                <?php else:?>
                <input type="hidden" name="user_id" value="<?= $_SESSION['username']['user_id'] ?>">
                <input type="hidden" name="user_name" value="<?= $_SESSION['username']['username'] ?>">
                <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id'] ?>">
                <?php endif ?>
                <label for="comment">Enter comment:</label>
                <textarea name="comment" rows="4" id="comment" placeholder="What did you think about this recipe?"></textarea>
                 <p class="commandbuttons">
                        <input type="submit" name="command" value="Submit">
                </p>
            </form>
            <?php foreach($recipecomments as $com): ?>

            <h3><?= $com['user_name']?></h3>
            <p><?= nl2br($com['comment'])?></p>
            <p><?= $com['created_at']?></p>
         
            <hr>

            <?php endforeach?>


        </aside>
        </section>
        
    </main>
    
</body>
</html>
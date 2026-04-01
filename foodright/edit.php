<?php
require('connect.php');
$base = '/Assignment/Finalproject/foodright';
session_start();
$errors=[];
$recipe = null;


if (!isset($_SESSION['username']['user_id']) || $_SESSION['username']['role'] !== 'admin') {
    header("Location: $base/index.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id  = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $slug  = filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_SPECIAL_CHARS);
       $title=trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $title = str_replace('"', '', $title);
        $description=trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $category=trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $cook_time=filter_input(INPUT_POST, 'cook_time', FILTER_VALIDATE_INT);
        $servings=filter_input(INPUT_POST, 'servings', FILTER_VALIDATE_INT);
        $ingredients=trim(filter_input(INPUT_POST, 'ingredients', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $instructions=trim(filter_input(INPUT_POST, 'instructions', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $allowed_categories = ['Breakfast', 'Lunch', 'Dinner', 'Snack', 'Dessert'];
        $current_imagepath = $_POST['current_imagepath'] ?? 'No image';

   
   
    if($_POST['command']==='Update'){
    if ($title === '') {
        $errors[] = "Title is required.";
    }

    if ($description === '') {
        $errors[] = "Description is required.";
    }

    if ($category === '' || !in_array($category, $allowed_categories, true)) {
        $errors[] = "Please select a valid category.";
    }
    if ($cook_time === false || $cook_time < 0) {
        $errors[] = "Cook time must be a valid number.";
    }

    if ($servings === false || $servings < 1) {
        $errors[] = "Servings must be at least 1.";
    }

    if ($ingredients === '') {
        $errors[] = "Ingredients are required.";
    }

    if ($instructions === '') {
        $errors[] = "Instructions are required.";
    }
     
    if(empty($errors)){
        $query="UPDATE recipes SET title= :title, description=:description, category=:category,cook_time=:cook_time,servings=:servings,ingredients=:ingredients,instructions=:instructions WHERE recipe_id = :id AND slug = :slug";
        $statement=$db->prepare($query);
        $statement->bindValue(':title',$title);
        $statement->bindValue(':description',$description);
        $statement->bindValue(':category',$category);
        $statement->bindValue(':cook_time',$cook_time);
        $statement->bindValue(':servings',$servings);
        $statement->bindValue(':ingredients',$ingredients);
        $statement->bindValue(':instructions',$instructions);
        $statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $statement->bindValue(':slug', $slug);
          // Execute the INSERT.
        $statement->execute();
         // Redirect after update.
        if(isset($_POST['imagedelete'])){
            $query="UPDATE recipes SET imagepath=:imagepath WHERE recipe_id = :id AND slug = :slug";
            $statement=$db->prepare($query);
            $statement->bindValue(':imagepath', 'No image');
            $statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $statement->bindValue(':slug', $slug);
                $statement->execute();


        }
        header("Location: $base/{$id}/{$slug}/");
        exit();
    
    }
     $recipe = [
        'recipe_id' => $id,
        'slug' => $slug,
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'cook_time' => $cook_time,
        'servings' => $servings,
        'ingredients' => $ingredients,
        'instructions' => $instructions,
         'imagepath' => $current_imagepath
    ];

    }
    elseif ($_POST['command'] === 'Delete'){
        $query="DELETE FROM recipes WHERE recipe_id = :id";
        $statement=$db->prepare($query);
        $statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $statement->execute();
        header("Location: $base/index.php");



    }
    


}
elseif(isset($_GET['id'])&&isset($_GET['slug'])){
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
}
else{
    $id=false;
}

$image = $recipe['imagepath'] ?? null;
$placeholder='/Assignment/Finalproject/foodright/pictures/placeholder.png';

$image = ($image && $image !== 'No image')  ? $image : $placeholder;
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
                <li><a href="<?= $base ?>/createrecipe.php">CreateReceipe</a></li>
                 <?php if(isset($_SESSION['username']['user_id'])): ?>
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
             <div class="edit-details">
                    <?php if (!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                    <p style="color:red;">Error: <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
                <?php endif; ?>
                <form action="edit.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $recipe['recipe_id'] ?>">
                <input type="hidden" name="slug" value="<?= $recipe['slug'] ?>">
                <input type="hidden" name="current_imagepath" value="<?= $recipe['imagepath'] ?>">

  
                <input type="text" id="title" name="title" value="<?= $recipe['title'] ?>">
                <img src="<?=$image?>" alt="<?= $recipe['title'] ?>">
                <?php if($recipe['imagepath']!=='No image'):?>
                <div id="deletecheckbox"><p>Delete image</p> 
                <input type="checkbox" id="imagedelete" name="imagedelete"  onclick="return confirm('Are you sure you want to delete this image?');"></div>
                <?php endif?>
                

                <div id="subinfo">
                    <p>Category:</p>
                    <input type="text" id="category" name="category" value="<?= $recipe['category'] ?>">
                    <p>Cooktime:</p>
                    <input type="text" id="cook_time" name="cook_time" value="<?= $recipe['cook_time'] ?>">
                    <p>Servings:</p>
                    <input type="text" id="servings" name="servings" value="<?= $recipe['servings'] ?>">             
                </div>
                <p>Description</p>
                <textarea name="description" rows="4" id="description"><?= ($recipe['description']) ?></textarea>
                <p>Ingredients</p>
                <textarea name="ingredients" rows="14" id="ingredients"><?= ($recipe['ingredients'])?></textarea>
                <p>Instructions</p>
                <textarea name="instructions"  rows="18" id="instructions"><?= ($recipe['instructions'])?></textarea>
                <p class="commandbuttons">
                        <input type="submit" name="command" value="Update">
                        <input type="submit" name="command" value="Delete"   onclick="return confirm('Are you sure you want to delete this recipe?');" >

                    </p>
            </form>
            
            </div>
           
    
        </section>
        
    </main>
    
</body>
</html>
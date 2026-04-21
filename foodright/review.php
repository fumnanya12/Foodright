<?php
$base = '';
session_start();
require('connect.php');
require 'php-image-resize-master/lib/ImageResize.php';
require 'php-image-resize-master/lib/ImageResizeException.php';
$errors=[];
use Gumlet\ImageResize; 
if (!isset($_SESSION['pending_recipe'])) {
    header("Location: admin.php");
    exit;
}
if (!isset($_SESSION['username']['user_id']) || $_SESSION['username']['role'] !== 'admin') {
    header("Location: $base/index.php");
    exit;
}
$user_id=$_SESSION['username']['user_id'];

$recipe = $_SESSION['pending_recipe'];
$api_meal_id  = $recipe['api_meal_id'];
$title        = $recipe['title'];
$category     = $recipe['category'];
$description  = $recipe['description'];
$instructions = $recipe['instructions'];
$ingredients  = $recipe['ingredients'];
$imageUrl     = $recipe['image_url'];
$servings     = $recipe['servings'];
$cook_time    = $recipe['cook_time'];


function create_slug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}


$extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
        $filename= $title . '.'. $extension;
        $localPath = 'pictures/' . $filename;

         $imageData=file_get_contents($imageUrl);
    
        if ($imageData === false) {
        $errors[]= "Failed to download image.";
        } 
        // else {
    if($_SERVER['REQUEST_METHOD'] === 'POST'){   
        
        $title=trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $title = str_replace('"', '', $title);
        $description=trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $category=trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $cook_time=filter_input(INPUT_POST, 'cook_time', FILTER_VALIDATE_INT);
        $servings=filter_input(INPUT_POST, 'servings', FILTER_VALIDATE_INT);
        $ingredients=trim(filter_input(INPUT_POST, 'ingredients', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $instructions=trim(filter_input(INPUT_POST, 'instructions', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $slug=create_slug($title);
        $api_meal_id=trim(filter_input(INPUT_POST, 'api_meal_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
        $allowed_categories = ['Breakfast', 'Lunch', 'Dinner', 'Snack', 'Dessert'];
        if($_POST['command']==='Save'){
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
            file_put_contents($localPath, file_get_contents($imageUrl));

            // Step 2: Resize AFTER download
            try {
                $image = new ImageResize($localPath);

                // Best option (keeps aspect ratio)
                $image->resizeToWidth(500);

                // Save resized image (overwrite)
                $image->save($localPath);

            } catch (Exception $e) {
                echo "Resize error: " . $e->getMessage();
            }
            
            $insertquery="INSERT INTO recipes (user_id, title, description, category, cook_time, servings, ingredients, instructions, imagepath, slug,api_meal_id) values(:user_id, :title, :description, :category, :cook_time, :servings, :ingredients, :instructions, :imagepath,:slug,:api_meal_id)";
            $statement=$db->prepare($insertquery);
            $statement->bindValue(':user_id',$user_id);
            $statement->bindValue(':title',$title);
            $statement->bindValue(':description',$description);
            $statement->bindValue(':category',$category);
            $statement->bindValue(':cook_time',$cook_time);
            $statement->bindValue(':servings',$servings);
            $statement->bindValue(':ingredients',$ingredients);
            $statement->bindValue(':instructions',$instructions);
            $statement->bindValue(':imagepath',$localPath);
            $statement->bindValue(':slug',$slug); 
            $statement->bindValue(':api_meal_id',$api_meal_id);   
            try{
            // $addedcount++;
                $statement->execute();
                unset($_SESSION['pending_recipe']);
                header("Location: admin.php");
                exit;
            }
            catch(PDOException $e){
                $errors[]="Database error: " . $e->getMessage();
            }
        }
        }
         elseif ($_POST['command']==='Clear'){
            unlink($filename);
            unset($_SESSION['pending_recipe']);
            header("Location: admin.php");
            exit;
        }
    }// Download image to your project folder
   
$cat_query="SELECT * FROM category";
$cat_statement = $db->prepare($cat_query);
$cat_statement->execute(); 



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

        
         <section class="receipe-details">
             
            <h2>Review Recipe</h2>
             <div class="edit-details">
                    <?php if (!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                    <p style="color:red;">Error: <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
                <?php endif; ?>
                <form action="review.php" method="post" enctype="multipart/form-data">
                
                <input type="hidden" name="api_meal_id" value="<?=$api_meal_id ?>">

                <input type="text" id="title" name="title" value="<?= $recipe['title'] ?>">
                <img src="<?=$localPath?>" alt="<?= $title ?>">

               
                

                <div id="subinfo">
                    <p>Category:</p>
                    <select id="category" name="category" style="width: 180px;" required>
                    <option value="<?=$category  ?>"><?= $category ?></option>
                    <?php while(($category=$cat_statement->fetch())): ?>
                    <?php if($category['category']!== $recipe['category'] ):?>
                    <option value="<?= $category['category'] ?>"><?= $category['category'] ?></option>
                    <?php endif?>

                    <?php endwhile ?>
       
                
                    </select>
                    <p>Cooktime:</p>
                    <input type="text" id="cook_time" name="cook_time" value="<?= $cook_time?>">
                    <p>Servings:</p>
                    <input type="text" id="servings" name="servings" value="<?= $servings ?>">             
                </div>
                <p>Description</p>
                <textarea name="description" rows="4" id="description"><?= $description ?></textarea>
                <p>Ingredients</p>
                <textarea name="ingredients" rows="14" id="ingredients"><?= $ingredients?></textarea>
                <p>Instructions</p>
                <textarea name="instructions"  rows="18" id="instructions"><?= $instructions?></textarea>
                <p class="commandbuttons">
                        <input type="submit" name="command" value="Save">
                        <input type="submit" name="command" value="Clear"   onclick="return confirm('Are you sure you want to clear this recipe?');" >

                    </p>
            </form>
       
           </div>
           
    
        </section>
        
    </main>
    
</body>
</html>

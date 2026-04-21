<?php
require('connect.php');
$base = '';
session_start();
$errors=[];
$recipe = null;

if(isset($_SESSION['login'])){
    $msg = json_encode($_SESSION['login']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['login']);
    
}
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
        $current_imagepath = filter_input(INPUT_POST,'current_imagepath',FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'No image';

   
   
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
     
    function file_upload_path($original_filename, $upload_subfolder_name = 'pictures') {
        $current_folder = dirname(__FILE__);
        $path_segments = [$current_folder, $upload_subfolder_name, basename($original_filename)];
        return join(DIRECTORY_SEPARATOR, $path_segments);
    }
    function file_is_an_image_or_pdf($temporary_path, $new_path) {
    $allowed_mime_types      = ['image/gif', 'image/jpeg', 'image/png'];
    $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];

    $actual_file_extension   = pathinfo($new_path, PATHINFO_EXTENSION);
    $actual_mime_type        = mime_content_type($temporary_path);//getimagesize($temporary_path)['mime'];

    $file_extension_is_valid = in_array($actual_file_extension, $allowed_file_extensions);
    $mime_type_is_valid      = in_array($actual_mime_type, $allowed_mime_types);

    return $file_extension_is_valid && $mime_type_is_valid;
    }
     $pathofimage="";
        $image_path="./pictures/";
        $image_upload_detected = isset($_FILES['image']) && ($_FILES['image']['error'] === 0);
        $upload_error_detected= isset($_FILES['image']) && ($_FILES['image']['error'] > 0);
        
      
    if ($image_upload_detected) {
          $image_filename       = $_FILES['image']['name'];
        $temporary_image_path = $_FILES['image']['tmp_name'];
        $new_image_path       = file_upload_path($image_filename);
    if (!file_is_an_image_or_pdf($temporary_image_path, $new_image_path)) {
        $errors[]= "Invalid file ext it has to be an image";
        }
              }
       

    if(empty($errors)){
        $datetime = new DateTime('now', new DateTimeZone('America/Winnipeg'));
        $updatetime= $datetime->format('Y-m-d H:i:s');
        $query="UPDATE recipes SET title= :title, description=:description, category=:category,cook_time=:cook_time,servings=:servings,ingredients=:ingredients,instructions=:instructions, updated_time=:updated_time WHERE recipe_id = :id AND slug = :slug";
        $statement=$db->prepare($query);
        $statement->bindValue(':title',$title);
        $statement->bindValue(':description',$description);
        $statement->bindValue(':category',$category);
        $statement->bindValue(':cook_time',$cook_time);
        $statement->bindValue(':servings',$servings);
        $statement->bindValue(':ingredients',$ingredients);
        $statement->bindValue(':instructions',$instructions);
        $statement->bindValue(':updated_time',$updatetime);
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
       
  

        $image_filename       = str_replace(' ', '', $_FILES['image']['name']);
        $temporary_image_path = $_FILES['image']['tmp_name'];
        $new_image_path       = file_upload_path($image_filename);
        //$pathofimage="it uploaded";
 if ($image_upload_detected) {
        if (file_is_an_image_or_pdf($temporary_image_path, $new_image_path)) {
            move_uploaded_file($temporary_image_path, $new_image_path);
           $pathofimage=$image_path . str_replace(' ', '', $_FILES['image']['name']);//$new_image_path;
            

            $query="UPDATE recipes SET imagepath=:imagepath WHERE recipe_id = :id AND slug = :slug";
            $statement=$db->prepare($query);
            $statement->bindValue(':imagepath', $pathofimage);
            $statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $statement->bindValue(':slug', $slug);
            $statement->execute();

        }
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

$cat_query="SELECT * FROM category";
$cat_statement = $db->prepare($cat_query);
$cat_statement->execute(); 


$image = $recipe['imagepath'] ?? null;
$placeholder='/pictures/placeholder.png';

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
                <li><a href="<?= $base ?>/index.php">Home</a></li>
                <li><a href="<?= $base ?>/allrecipes.php">Receipes</a></li>
                <li><a href="#">Calorie calculator</a></li>
                
                 <?php if(isset($_SESSION['username']['user_id'])): 
                     $_SESSION['location']="$base/editrecipe.php"; ?>
                     <li><a href="<?= $base ?>/createrecipe.php">CreateReceipe</a></li>
                    <li><a href="<?= $base ?>/logout.php">Logout</a></li>
                    <?php else :
                         $_SESSION['location']="$base/editrecipe.php";
                        ?>
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
                <form action="editrecipe.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $recipe['recipe_id'] ?>">
                <input type="hidden" name="slug" value="<?= $recipe['slug'] ?>">
                <input type="hidden" name="current_imagepath" value="<?= $recipe['imagepath'] ?>">

  
                <input type="text" id="title" name="title" value="<?= $recipe['title'] ?>">
                <?php if($recipe['imagepath']!=='No image'):?>
                <img src="<?=$image?>" alt="<?= $recipe['title'] ?>">

                <div id="deletecheckbox"><p>Delete image</p> 
                <input type="checkbox" id="imagedelete" name="imagedelete"  onclick="return confirm('Are you sure you want to delete this image?');">
                <label for="image">Replace Image</label>
                <input type="file" id="image" name="image">
            </div>
                <?php else: ?>
                    <label for="image">Add Image</label>
                    <input type="file" id="image" name="image">

                <?php endif?>
                

                <div id="subinfo">
                    <p>Category:</p>
                    <select id="category" name="category" style="width: 180px;" required>
                    <option value="<?= $recipe['category'] ?>"><?= $recipe['category'] ?></option>
                  <?php while(($category=$cat_statement->fetch())): ?>
                    <?php if($category['category']!== $recipe['category'] ):?>
                    <option value="<?= $category['category'] ?>"><?= $category['category'] ?></option>
                    <?php endif?>

                    <?php endwhile ?>
       
                
                    </select>
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
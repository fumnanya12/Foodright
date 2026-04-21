<?php
require('connect.php');
require 'php-image-resize-master/lib/ImageResize.php';
require 'php-image-resize-master/lib/ImageResizeException.php';
use Gumlet\ImageResize; 
session_start();
$base = '';
// admin check
if (!isset($_SESSION['username']['user_id']) || $_SESSION['username']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

function create_slug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}
$errors=[];
$details=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
$title=trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
$title = str_replace('"', '', $title);
$slug=create_slug($title);
$description=trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
$category=trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
$cook_time=filter_input(INPUT_POST, 'cook_time', FILTER_VALIDATE_INT);
$servings=filter_input(INPUT_POST, 'servings', FILTER_VALIDATE_INT);
$ingredients=trim(filter_input(INPUT_POST, 'ingredients', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
$instructions=trim(filter_input(INPUT_POST, 'instructions', FILTER_SANITIZE_FULL_SPECIAL_CHARS)??'');
$user_id=$_SESSION['username']['user_id'];
$_SESSION['title']=$title;
$_SESSION['description']=$description;
$_SESSION['category']=$category;
$_SESSION['cook_time']=$cook_time;
$_SESSION['servings']=$servings;
$_SESSION['ingredients']=$ingredients;
$_SESSION['instructions']=$instructions;

// $details[]=$title;
// $details[]=$user_id;
// $details[]=$description;
// $details[]=$ingredients;
// $details[]=$instructions;

$allowed_categories = ['Breakfast', 'Lunch', 'Dinner', 'Snack', 'Dessert'];

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
if($_POST['command']==='Clear'){
        unset($_SESSION['title']);
    unset($_SESSION['description']);
    unset($_SESSION['category']);
    unset($_SESSION['cook_time']);
    unset($_SESSION['servings']);
    unset($_SESSION['ingredients']);
    unset($_SESSION['instructions']);
    $errors=[];
}

   

if(empty($errors)){
$pathofimage="";
$image_path="./pictures/";
 $image_upload_detected = isset($_FILES['image']) && ($_FILES['image']['error'] === 0);
    $upload_error_detected= isset($_FILES['image']) && ($_FILES['image']['error'] > 0);

    if ($image_upload_detected) {
      
        $image_filename       = str_replace(' ', '', $_FILES['image']['name']);
        $temporary_image_path = $_FILES['image']['tmp_name'];
        $new_image_path       = file_upload_path($image_filename);
        //$pathofimage="it uploaded";

   if (file_is_an_image_or_pdf($temporary_image_path, $new_image_path)) {   
            move_uploaded_file($temporary_image_path, $new_image_path);
             $pathofimage=$image_path . str_replace(' ', '', $$_FILES['image']['name']);//$new_image_path;
            
             $image = new ImageResize($pathofimage);
             $image->resize(700,700);
             $image->save($pathofimage);
        $query="INSERT INTO recipes (user_id, title, description, category, cook_time, servings, ingredients, instructions, imagepath, slug) values(:user_id, :title, :description, :category, :cook_time, :servings, :ingredients, :instructions, :imagepath,:slug)";
        $statement=$db->prepare($query);
        $statement->bindValue(':user_id',$user_id);
        $statement->bindValue(':title',$title);
        $statement->bindValue(':description',$description);
        $statement->bindValue(':category',$category);
        $statement->bindValue(':cook_time',$cook_time);
        $statement->bindValue(':servings',$servings);
        $statement->bindValue(':ingredients',$ingredients);
        $statement->bindValue(':instructions',$instructions);
        $statement->bindValue(':imagepath',$pathofimage);
        $statement->bindValue(':slug',$slug);

// Execute the INSERT prepared statement.
    $statement->execute();
    unset($_SESSION['title']);
    unset($_SESSION['description']);
    unset($_SESSION['category']);
    unset($_SESSION['cook_time']);
    unset($_SESSION['servings']);
    unset($_SESSION['ingredients']);
    unset($_SESSION['instructions']);

   }else{
        $errors[]="file uploaded is not an image";
   }

    }else{
$query="INSERT INTO recipes (user_id, title, description, category, cook_time, servings, ingredients, instructions, imagepath,slug) values(:user_id, :title, :description, :category, :cook_time, :servings, :ingredients, :instructions, :imagepath,:slug)";
$statement=$db->prepare($query);
$statement->bindValue(':user_id',$user_id);
$statement->bindValue(':title',$title);
$statement->bindValue(':description',$description);
$statement->bindValue(':category',$category);
$statement->bindValue(':cook_time',$cook_time);
$statement->bindValue(':servings',$servings);
$statement->bindValue(':ingredients',$ingredients);
$statement->bindValue(':instructions',$instructions);
$statement->bindValue(':imagepath',"No image");
$statement->bindValue(':slug',$slug);

// Execute the INSERT prepared statement.
    $statement->execute();
      unset($_SESSION['title']);
    unset($_SESSION['description']);
    unset($_SESSION['category']);
    unset($_SESSION['cook_time']);
    unset($_SESSION['servings']);
    unset($_SESSION['ingredients']);
    unset($_SESSION['instructions']);

    }


}



}
$query="SELECT * FROM category";
$statement = $db->prepare($query);
$statement->execute(); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodRight Recipe</title>
    <link rel="stylesheet" href="./mvp.css" />

</head>
<body>
    <main>
        <section>
    <form action="createrecipe.php" method="post"  enctype="multipart/form-data">
        <header>
                <nav> 
                    <a href="<?= $base ?>/index.php">back</a>
                    <?php if(isset($_SESSION['username']['user_id'])):?>
                    <a href="<?= $base ?>/logout.php">Logout</a>
                    <?php else :?>
                    <a href="<?= $base ?>/login.php">Login</a>
                    <?php endif?>
                    
                    </nav>
                   
               
                    <div class="thumbnail">
                        <img src="./pictures/foodrightlogo.jpg" alt="">

                    </div>

                    <h2>Save your favorite receipe</h2>
                </header>
        <label for="title">Recipe Title</label>
        <input type="text" id="title" name="title" placeholder="Tomato sauce pasta " value="<?= $_SESSION['title'] ?? '' ?>">
        <label for="description">Short Description</label>
        <textarea id="description" name="description" rows="4" placeholder="e.g A zesty, light summer pasta featuring hand-picked basil and a hint of lemon...."  value=""><?= $_SESSION['description'] ?? 'e.g A zesty, light summer pasta featuring hand-picked basil and a hint of lemon.' ?></textarea>
        <div class="short-input">
            <div class="category">
            <label for="category">Category</label>
            <select id="category" name="category" style="width: 180px;" >
                <option value="">Select Category</option>
                  <?php while(($category=$statement->fetch())): ?>
                    <option value="<?= $category['category'] ?>"><?= $category['category'] ?></option>

                <?php endwhile ?>
       
                
            </select>
            </div>
            <div class="cook_time">
            <label for="cook_time">Cook Time (minutes)</label>
            <input type="number" id="cook_time" name="cook_time" min="0" max="1000" style="width: 60px;" value="<?= $_SESSION['cook_time'] ?? '' ?>" >
            </div>
            <div class="servings">
            <label for="servings">Servings</label>
            <input type="number" id="servings" name="servings" min="1" max="100" style="width: 60px;" value="<?= $_SESSION['servings'] ?? '' ?>" >
            </div>
        </div>
        <label for="ingredients">Ingredients</label>
        <textarea id="ingredients" name="ingredients" rows="4"  placeholder="e.g., 2 cups of flour"  ><?= $_SESSION['ingredients'] ?? 'e.g., 2 cups of flour' ?> </textarea>

        <label for="instructions">Instructions</label>
        <textarea id="instructions" name="instructions" rows="4" placeholder="Step 1: Preheat oven to 350°F..." value="" ><?= $_SESSION['instructions'] ?? 'Step 1: Preheat oven to 350°F...' ?></textarea>


         <label for="image">Recipe Image</label>
         <input type="file" id="image" name="image">
        <button type="submit"onclick="return alert('You have Saved  A new Recipe Succesfully ')">Save Recipe</button>
         <input type="submit" name="command" value="Clear">

    </form>
   <?php if (!empty($errors)): ?>
    <?php foreach($errors as $error): ?>
        <p style="color:red;">Error: <?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>
<?php endif; ?>
       
    </section>
    </main>
</body>
</html>
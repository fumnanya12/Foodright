<?php
$base = '/Assignment/Finalproject/foodright';
session_start();
require('connect.php');
require 'php-image-resize-master/lib/ImageResize.php';
require 'php-image-resize-master/lib/ImageResizeException.php';
$errors=[];
use Gumlet\ImageResize; 
if (!isset($_SESSION['username']['user_id']) || $_SESSION['username']['role'] !== 'admin') {
    header("Location: $base/index.php");
    exit;
}
$user_id=$_SESSION['username']['user_id'];

if(isset($_SESSION['registered'])){
    $msg = json_encode($_SESSION['registered']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['registered']);
    
}
if(isset($_SESSION['login'])){
    $msg = json_encode($_SESSION['login']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['login']);
    
}


  
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if($_POST['command']==='Add Random recipes'){
    // $targetCount=5;
    // $addedcount=0;
   // while($addedcount<$targetCount){
    $json= file_get_contents("https://www.themealdb.com/api/json/v1/1/random.php");
    $data = json_decode($json, true);


    if ($json === false) {
        $errors[] = "Failed to fetch recipe data from API.";
    }
    if (!empty($data['meals'])) {
        $meal=$data['meals'][0];

        $api_meal_id  = trim($meal['idMeal']);
        $title = trim($meal['strMeal']??'');
        $category = trim($meal['strCategory']??'');
        $instructions = trim($meal['strInstructions']?? '');
        $imageUrl = trim($meal['strMealThumb']?? '');
        $cook_time=rand(5,70);
        $servings=rand(2,8);
        $ingredientsList = [];
        $area = trim($meal['strArea'] ?? 'International');
        $templates = [
        "$title is a traditional meal from $area cuisine, known for its rich flavors and satisfying taste.",
        "A classic meal from $area, $title combines simple ingredients to create a delicious and well-balanced dish.",
        "Originating from $area cuisine, this meal recipe offers a flavorful and easy-to-make meal perfect for any occasion.",
        "$title is a popular meal dish from $area, loved for its comforting taste and simple preparation."
        ];
        $description = $templates[array_rand($templates)];
        for ($i = 1; $i <= 20; $i++) {
            $ingredient = trim($meal["strIngredient$i"] ?? '');
            $measure = trim($meal["strMeasure$i"] ?? '');

            if ($ingredient !== '') {
                $ingredientsList[] = trim($measure . ' ' . $ingredient);
            }
        }



        $ingredients = implode("\n", $ingredientsList);
        $subcat=["Dinner","Breakfast","Lunch"];
        $category=$subcat[array_rand($subcat)];

         $_SESSION['pending_recipe'] = [
                'api_meal_id'   => $api_meal_id,
                'title'         => $title,
                'category'      => $category,
                'description'   => $description,
                'instructions'  => $instructions,
                'ingredients'   => $ingredients,
                'image_url'     => $imageUrl,
                'cook_time'     =>$cook_time,
                'servings'     =>$servings
            ];

        

      

      
    }

   // }
      header("Location: review.php");
        exit;
    }
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
        
        <section>
              <?php if (!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                    <p style="color:red;">Error: <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
                <?php endif; ?>
            <form action="admin.php" method="post">
                <h3>Add new recipes to database</h3>
                <input type="submit" name="command" value="Add Random recipes">

            </form>
        </section>
       
       
        
    </main>
    
</body>
</html>

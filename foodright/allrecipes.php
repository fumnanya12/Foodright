<?php
require('connect.php');
session_start();

if(isset($_GET['sort'])){
    $allowed_sorts = [
    'title_asc' => 'title ASC',
    'title_desc' => 'title DESC',
    'created_asc' => 'created_at ASC',
    'created_desc' => 'created_at DESC',
    'category_Dinner' => 'Dinner',
    'category_Lunch' => 'Lunch',
    'category_Breakfast' => 'Breakfast',
    'category_Dessert' => 'Dessert',
    'category_Snack' => 'Snack'
];
$title=null;
$date_label=null;
$category_label=null;
$sort=$_GET['sort']?? 'title_asc';
$orderBy = $allowed_sorts[$sort] ?? 'title ASC';


if($sort==='title_asc'||$sort==='title_desc'||$sort==='created_asc'||$sort==='created_desc'){
    $query="SELECT * FROM recipes ORDER BY $orderBy";
      // A PDO::Statement is prepared from the query.
     $statement = $db->prepare($query);
     // Execution on the DB server is delayed until we execute().
     $statement->execute(); 
     if($sort=== 'title_asc' ){
        $title='Title ASC ▲';

     }else if($sort=== 'title_desc'){
         $title='Title DESC ▼';
     } elseif($sort=== 'created_asc'){
        $date_label= 'Date Oldest ▲';
     }else{
        $date_label= 'Date Newest ▼';

     }

}
else if($sort==='category_Dinner'||$sort==='category_Lunch'||$sort==='category_Breakfast'||$sort==='category_Dessert'||$sort==='category_Snack'){
    $query="SELECT * FROM recipes WHERE category= :category";

     // A PDO::Statement is prepared from the query.
     $statement = $db->prepare($query);
     $statement->bindValue(':category',$allowed_sorts[$sort]);
     // Execution on the DB server is delayed until we execute().
     $statement->execute(); 
     $category_label="Category (".$allowed_sorts[$sort].")";
}
}
else{
$query="SELECT * FROM recipes";
    // A PDO::Statement is prepared from the query.
     $statement = $db->prepare($query);
     // Execution on the DB server is delayed until we execute().
     $statement->execute(); 

}
$query_cat="SELECT * FROM category";
$statement_cat = $db->prepare($query_cat);
$statement_cat->execute(); 
$rows = $statement->fetchAll();

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
                <li><a href="index.php">Home</a></li>
                <li><a href="allrecipes.php">Recipes </a></li>
                <li><a href="#">Calorie calculator</a></li>
                <li><a href="createrecipe.php">CreateReceipe</a></li>
                <?php if(isset($_SESSION['username']['user_id'])): 
                     $_SESSION['location']="allrecipes.php";
                    ?>
                    <li><a href="logout.php?redirect=allrecipes.php" onclick="return confirm('Are you sure you want to Logout?');" >Logout</a></li>
                <?php else:
                       $_SESSION['location']="allrecipes.php";?>
                    <li><a href="login.php?redirect=allrecipes.php">Login</a></li>
                <?php endif?>
            </ul>
        </nav>
    </header>
    <main class="allrecipe_section">
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
        <?php if(isset($_SESSION['username']['user_id'])): ?>
        <section class="sort-section">
            <div class="sort-menu">
               
            <div class="sort-item">
            <button type="button" onclick="toggleDropdown('titleSort')"><?=$title ??'Title' ?></button>
            <div class="dropdown" id="titleSort">
                <a href="allrecipes.php?sort=title_asc">A to Z</a>
                <a href="allrecipes.php?sort=title_desc">Z to A</a>
            </div>
        </div>

        <div class="sort-item">
            <button type="button" onclick="toggleDropdown('dateSort')"><?=$date_label ??'Date' ?></button>
            <div class="dropdown" id="dateSort">
                <a href="allrecipes.php?sort=created_desc">Newest</a>
                <a href="allrecipes.php?sort=created_asc">Oldest</a>
            </div>
        </div>
           <div class="sort-item">
            <button type="button" onclick="toggleDropdown('category')"><?=$category_label ??'Category' ?></button>
            <div class="dropdown" id="category">
                <?php while(($category=$statement_cat->fetch())): ?>
                    <a href="allrecipes.php?sort=category_<?= $category['category'] ?>"><?= $category['category'] ?></a>
                <?php endwhile ?>
             
                
            </div>
        </div>
        <a href="allrecipes.php"><button type="button" >Clear</button></a>

        </div>

        </section>
        <?php endif?>

        <section>
            <h2>Food Right Recipes</h2>
        <div id="fooddatabase">
            <?php if(count($rows)):?>
           <?php foreach($rows as $row): 
            $image = $row['imagepath'] ?? null;

        $image = ($image && $image !== 'No image')  ? $image : $placeholder;
            
            ?>
            <aside>
                
                <a href="<?= $row['recipe_id'] ?>/<?= $row['slug'] ?>/" class="card-link">
                    <?php if($row['imagepath']!=='No image'):?>
                    <img src="<?=  $image ?>" alt="<?= $row['title'] ?>">
                    <?php endif?>
                 <h3><?=$row['title'] ?></h3>
                <p>Description</p>
                <p><small><?=($row['description']) ?></small></p>
                </a>
            </aside>

            <?php endforeach ?>
            <?php else :?>
                <p>No result</p>
        <?php endif?>
        </div>
        <hr>
        </section>
        
    </main>
<script src="allrecipes.js"></script>
</body>
</html>

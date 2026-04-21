<?php
require('connect.php');
session_start();
if(isset($_SESSION['login'])){
    $msg = json_encode($_SESSION['login']);
    echo "<script>alert($msg);</script>";
     unset($_SESSION['login']);
    
}
$base = '/Assignment/Finalproject/foodright';

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
// Current page
$page = ($page && $page > 0) ? $page : 1;

// Easy to change for testing pagination
$resultsPerPage = 16;

// Offset
$offset = ($page - 1) * $resultsPerPage;



$countQuery = "SELECT COUNT(*) FROM recipes ";
$countStmt = $db->prepare($countQuery);

$countStmt->execute();
$totalResults = (int)$countStmt->fetchColumn();

$totalPages = ($totalResults > 0) ? (int)ceil($totalResults / $resultsPerPage) : 1;
// If page is bigger than total pages, reset it
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $resultsPerPage;
}
if(isset($_GET['sort'])){
    $allowed_sorts = [
    'title_asc' => 'title ASC',
    'title_desc' => 'title DESC',
    'created_asc' => 'created_at ASC',
    'created_desc' => 'created_at DESC',
    'cooktime_desc'=> 'cook_time DESC',
    'cooktime_asc'=> 'cook_time ASC'
    // 'category_Dinner' => 'Dinner',
    // 'category_Lunch' => 'Lunch',
    // 'category_Breakfast' => 'Breakfast',
    // 'category_Dessert' => 'Dessert',
    // 'category_Snack' => 'Snack'
];
$title=null;
$date_label=null;
$category_label=null;
$cooktime_label=null;
$sort=filter_input(INPUT_GET,'sort',FILTER_SANITIZE_FULL_SPECIAL_CHARS)?? 'title_asc';
$orderBy = $allowed_sorts[$sort] ?? 'title ASC';
$rows=null;


if($sort==='title_asc'||$sort==='title_desc'||$sort==='created_asc'||$sort==='created_desc'||$sort==='cooktime_desc'||$sort==='cooktime_asc'){




    $query="SELECT * FROM recipes ORDER BY $orderBy LIMIT :limit OFFSET :offset";
      // A PDO::Statement is prepared from the query.
     $statement = $db->prepare($query);
         $statement->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);  
     // Execution on the DB server is delayed until we execute().
     $statement->execute(); 
    $rows = $statement->fetchAll();

     if($sort=== 'title_asc' ){
        $title='Title ASC ▲';

     }else if($sort=== 'title_desc'){
         $title='Title DESC ▼';
     } elseif($sort=== 'created_asc'){
        $date_label= 'Date Oldest ▲';
     }else if($sort=== 'created_desc'){
        $date_label= 'Date Newest ▼';

     }
     else if($sort=== 'cooktime_desc'){
        $cooktime_label= 'CookTime DESC ▼';

     }
     else if($sort=== 'cooktime_asc'){
        $cooktime_label= 'CookTime ASC ▲';

     }



}
// else if($sort==='category_Dinner'||$sort==='category_Lunch'||$sort==='category_Breakfast'||$sort==='category_Dessert'||$sort==='category_Snack'){
//     $query="SELECT * FROM recipes WHERE category= :category";

//      // A PDO::Statement is prepared from the query.
//      $statement = $db->prepare($query);
//      $statement->bindValue(':category',$allowed_sorts[$sort]);
//      // Execution on the DB server is delayed until we execute().
//      $statement->execute(); 
//      $category_label="Category (".$allowed_sorts[$sort].")";
// }
}

// $countQuery = "SELECT COUNT(*) FROM recipes ";
// $countStmt = $db->prepare($countQuery);

// $countStmt->execute();
// $totalResults = (int)$countStmt->fetchColumn();

// $totalPages = ($totalResults > 0) ? (int)ceil($totalResults / $resultsPerPage) : 1;
// // If page is bigger than total pages, reset it
// if ($page > $totalPages) {
//     $page = $totalPages;
//     $offset = ($page - 1) * $resultsPerPage;
// }

else{

$query="SELECT * FROM recipes LIMIT :limit OFFSET :offset";
    // A PDO::Statement is prepared from the query.
     $statement = $db->prepare($query);
    $statement->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);     
     // Execution on the DB server is delayed until we execute().
     $statement->execute(); 
     $rows = $statement->fetchAll();
}


$query_cat="SELECT * FROM category ORDER BY category ASC";
$statement_cat = $db->prepare($query_cat);
$statement_cat->execute(); 
$categories=$statement_cat->fetchAll();

$placeholder='/Assignment/Finalproject/foodright/pictures/placeholder.png';
$_SESSION['backlocation']="$base/allrecipes.php?page=$page";


$currentKeyword = filter_input(INPUT_GET,'keyword',FILTER_SANITIZE_FULL_SPECIAL_CHARS)?? '';
$currentCategory = filter_input(INPUT_GET,'category',FILTER_SANITIZE_FULL_SPECIAL_CHARS)?? 'all';

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
                <!-- <li><a href="allrecipes.php">Recipes </a></li> -->
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
        <section class="sort-section">
            <div class="sort-menu">
            <?php if(isset($_SESSION['username']['user_id'])): ?>

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
            <button type="button" onclick="toggleDropdown('cookTime')"><?=$cooktime_label ??'CookTime' ?></button>
            <div class="dropdown" id="cookTime">
                <a href="allrecipes.php?sort=cooktime_desc">Highest</a>
                <a href="allrecipes.php?sort=cooktime_asc">Lowest</a>
            </div>

            </div>
              <?php endif?>
              <div class="sort-item"></div>
            <button type="button" onclick="window.location.href='allrecipes.php'">Clear</button>
            </div>

        </div>
      

          

        </section>

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
                <p>CookTime: <?=($row['cook_time']) ?> Mins</p>
                <p><small><?=($row['created_at']) ?></small></p>

                </a>
            </aside>

            <?php endforeach ?>
           

            <?php else :?>
                <p>No result</p>
        <?php endif?>
        </div>
         <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?=isset($sort) ? "?sort=$sort&page=". ($page - 1) : "?page=". ($page - 1) ?> "?>" Previous </a>

                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <strong><?= $i ?></strong>
                    <?php else: ?>
                        <a href="<?=isset($sort)? "?sort=$sort&page=$i " : "?page=$i " ?>"> <?= $i ?> </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                            <a href="<?= isset($sort) ? "?sort=$sort&page=" . ($page + 1) : "?page=" . ($page + 1) ?>">Next</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
        <hr>
        </section>
        
    </main>
<script src="allrecipes.js"></script>
</body>
</html>

<?php
require('connect.php');
session_start();

$currentKeyword = $_GET['keyword'] ?? '';
$currentCategory = $_GET['category'] ?? 'all';
$base = '';// Get search inputs
$keyword = trim(filter_input(INPUT_GET, 'keyword', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
$category = trim(filter_input(INPUT_GET, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'all');
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
// Current page
$page = ($page && $page > 0) ? $page : 1;

// Easy to change for testing pagination
$resultsPerPage = 8;

// Offset
$offset = ($page - 1) * $resultsPerPage;

$query_cat="SELECT * FROM category ORDER BY category ASC";
$statement_cat = $db->prepare($query_cat);
$statement_cat->execute(); 
$categories=$statement_cat->fetchAll();

// Build WHERE conditions
$whereParts = [];
$params = [];

// Search by keyword
if ($keyword !== '') {
    $whereParts[] = "(title LIKE :keyword OR description LIKE :keyword)";
    $params[':keyword'] = '%' . $keyword . '%';
}

// Filter by category
if ($category !== 'all' && $category !== '') {
    $whereParts[] = "category = :category";
    $params[':category'] = $category;
}

// Combine WHERE
$whereSQL = '';
if (!empty($whereParts)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereParts);
}

// Count total results
$countQuery = "SELECT COUNT(*) FROM recipes $whereSQL";
$countStmt = $db->prepare($countQuery);

foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}

$countStmt->execute();
$totalResults = (int)$countStmt->fetchColumn();

$totalPages = ($totalResults > 0) ? (int)ceil($totalResults / $resultsPerPage) : 1;

// If page is bigger than total pages, reset it
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $resultsPerPage;
}
// Fetch matching results
$query = "
    SELECT *
    FROM recipes
    $whereSQL
    ORDER BY title ASC
    LIMIT :limit OFFSET :offset
";

$stmt = $db->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$results=$stmt->fetchAll();


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

           <div class="sort-item">
            <button type="button" onclick="toggleDropdown('category')"><?=$category_label ??'Category' ?></button>
            <div class="dropdown" id="category">
                <?php foreach($categories as $cat): ?>
                    <a href="allrecipes.php?sort=category_<?= $cat['category'] ?>"><?= $cat['category'] ?></a>
                <?php endforeach ?>
             
                
            </div>
        </div>
        <a href="allrecipes.php"><button type="button" >Clear</button></a>

        </div>

        </section>

        <section>
             <h2>Search Results</h2>

        <?php if ($keyword !== '' || $category !== 'all'): ?>
            <p>
                Found <strong><?= $totalResults ?></strong> result(s)
                <?php if ($keyword !== ''): ?>
                    for "<strong><?= htmlspecialchars($keyword) ?></strong>"
                <?php endif; ?>
                <?php if ($category !== 'all'): ?>
                    in category "<strong><?= htmlspecialchars($category) ?></strong>"
                <?php endif; ?>
            </p>
        <?php else: ?>
            <p>Enter a keyword to search recipes.</p>
        <?php endif; ?>





        <div id="fooddatabase">
        <?php if(!empty($results)):?>
           <?php foreach($results  as $row): 
            $image = $row['imagepath'] ?? null;

        $image = ($image && $image !== 'No image')  ? $image : $placeholder;
            
            ?>
            <aside>
                
                <a href="<?= $row['recipe_id'] ?>/<?= $row['slug'] ?>/" class="card-link">
                    <?php if($row['imagepath']!=='No image'):?>
                    <img src="<?=  $image ?>" alt="<?= $row['title'] ?>">
                    <?php endif;?>
                 <h3><?=$row['title'] ?></h3>
                <p>Description</p>
                <p><small><?=($row['description']) ?></small></p>
                <p>CookTime: <?=($row['cook_time']) ?> Mins</p>
                <p><small><?=($row['created_at']) ?></small></p>

                </a>
            </aside>

            <?php endforeach; ?>


             
        </div>
         <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?keyword=<?= urlencode($keyword) ?>&category=<?= urlencode($category) ?>&page=<?= $page - 1 ?>"> Previous </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <strong><?= $i ?></strong>
                    <?php else: ?>
                        <a href="?keyword=<?= urlencode($keyword) ?>&category=<?= urlencode($category) ?>&page=<?= $i ?>"> <?= $i ?> </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?keyword=<?= urlencode($keyword) ?>&category=<?= urlencode($category) ?>&page=<?= $page + 1 ?>"> Next </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

    <?php elseif ($keyword !== '' || $category !== 'all'): ?>
        <p>No matching recipes found.</p>
    <?php endif; ?>
        <hr>
        </section>
        
    </main>
<script src="allrecipes.js"></script>
</body>
</html>

<?php
//// Allereerst zorgen dat de "Autoloader" uit vendor opgenomen wordt:
require_once("./vendor/autoload.php");

/// Twig koppelen:
$loader = new \Twig\Loader\FilesystemLoader("./templates");
/// VOOR PRODUCTIE:
/// $twig = new \Twig\Environment($loader), ["cache" => "./cache/cc"]);

/// VOOR DEVELOPMENT:
$twig = new \Twig\Environment($loader, ["debug" => true ]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

/******************************/

/// Next step, iets met je data doen. Ophalen of zo
require_once("lib/database.php");
require_once("lib/article.php");
require_once("lib/selectUser.php");
require_once("lib/selectKitchenType.php");
require_once("lib/selectIngredient.php");
require_once("lib/selectInfo.php");
require_once("lib/selectDish.php");
require_once("lib/groceryListUserFriendly.php");

$db = new database();
$dish = new dish($db->getConnection());
// $art = new article($db->getConnection());
// $user = new user($db->getConnection());
// $kt = new kitchenType($db->getConnection());
// $ingr = new ingredient($db->getConnection());
$info = new dishInfo($db->getConnection());
// $groce = new groceryList($db->getConnection());

// $deleteAllRatings = $info->deleteRating();

/*
URL:
    http://localhost/educom-verrukkulluk/index.php?dish_id=4&action=detail
    http://localhost/educom-verrukkulluk/index.php?dish_id=4&rating_value=3&action=rating
*/

$dish_id = isset($_GET["dish_id"]) ? $_GET["dish_id"] : "";
$rating_value = isset($_GET["rating_value"]) ? $_GET["rating_value"] : "";
$action = isset($_GET["action"]) ? $_GET["action"] : "homepage";

switch($action) {

    case "homepage": {
        $data = $dish->selectDish();
        $template = 'homepage.html.twig';
        $title = "homepage";
        break;
    }

    case "detail": {
        $data = $dish->selectDish($dish_id);
        $template = 'detail.html.twig';
        $title = "detail page";
        break;
    }

    case "rating": {
        header('Content-type: application/json');

        // Add new rating to database
        if($rating_value !== "null") {
            $info->addRating($dish_id, $rating_value);
        }

        //  Calculate average
        $average = 0;
        $count = 0;
        $total = 0;
        $ratings = $info->selectInfo($dish_id, 'R');

        if(isset($ratings)) {
            foreach($ratings as $rating) {
                $count ++;
                $total += $rating["numerical_field"];
            }
            $average = $total / $count;
        }

        $output = array("success"=>true, "average"=>$average);
        echo json_encode($output);

        die();
        // break;
    }

    /// etc

}

/// Onderstaande code schrijf je idealiter in een layout klasse of iets dergelijks
/// Juiste template laden, in dit geval "homepage"
$template = $twig->load($template);


/// En tonen die handel!
echo $template->render(["title" => $title, "data" => $data]);
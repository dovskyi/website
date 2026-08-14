<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

$date_reg = '/^([0-9]{2})?[0-9]{2}(-)(1[0-2]|0?[1-9])\2(3[01]|[12][0-9]|0?[1-9])$/';
$message = '';
if (isset($_GET["filter_clear"])) {
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit();
}

//get existing tags
$existingTags = [];
$categories = [["language", "language"], ["project_type", "type"], ["completion_type", "status"]];
$pholders = '?' . str_repeat(', ?', count($categories) - 1);

$stmt = $db->prepare("SELECT * FROM tags
        WHERE tags.category IN (".$pholders.");");
$stmt->execute(array_column($categories, 0));
$existingTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

//sanitize input
$order = $_GET["order"]??'';
$orderBy = $_GET["order_by"]??'';
$date_from = $_GET["date_from"]??'';
$date_to = $_GET["date_to"]??'';
$get_tags = $_GET["tags"]??array();

$allowedOrderBy = ["post_date", "modify_date", "title"];

if ($order !== 'ASC') { $order = 'DESC';}
if (!in_array($orderBy, $allowedOrderBy)) { $orderBy = 'modify_date';}
if (!preg_match($date_reg, $date_from)) { $date_from = ''; }
if (!preg_match($date_reg, $date_to)) { $date_to = ''; }
if (!empty($date_from) && !empty($date_to) && $date_from >= $date_to) { 
        $message .= "Note: date_from >= date_to. No results.";
}
$tags = array_intersect($get_tags, array_column($existingTags, "tag_id"));

//compile input into a string
$conditions = [];
$bindParams = [];
$whereString = '';

$dateFilter = in_array($orderBy, ["post_date", "modify_date"])? $orderBy: "modify_date";

if (!empty($date_from)) { 
        $conditions[] = "article_entry.".$dateFilter." >= ?";
        $bindParams[] = $date_from;
}
if (!empty($date_to)) { 
        $conditions[] = "article_entry.".$dateFilter."<= ?";
        $bindParams[] = $date_to;
}

if (!empty($tags)) {
        $tagPlacehldrs = implode(',', array_fill(0, count($tags), '?'));

        $conditions[] = "article_entry.entry_id IN (
                SELECT entry_id
                FROM article_entry_has_tag
                WHERE tag_id IN ($tagPlacehldrs)
                GROUP BY entry_id
                HAVING COUNT(DISTINCT tag_id) = ?
                )";

        foreach ($tags as $tag) {
                $bindParams[] = $tag;
        }
        $bindParams[] = count($tags);
}

if (!empty($conditions)) {
        $whereString = " WHERE ".implode(" AND ", $conditions);
}
//prepare limits
$page = (int)$_GET["page"]??0;
$page = (is_int($page) && !isset($_GET["goto_pg1"]))? $page: 0;
$perPage = 40;

$stmt = $db->prepare("SELECT COUNT(*) FROM article_entry".$whereString.";");
$stmt->execute($bindParams);
$totalEntries = $stmt->fetchColumn();
$pageMax = floor($totalEntries/$perPage);

if (isset($_GET["page_next"]) && isset($_GET["page_prev"])) {
        $page = $page;
} else if (isset($_GET["page_next"]) && $page+1 <= $pageMax) {
        $page++;
} else if (isset($_GET["page_prev"]) && $page-1 >= 0) {
        $page--;
}

if ($page < 0 || $page > $pageMax) {
        $page = 0;
}

$offset = $perPage * $page;

$query = "SELECT 
        article_entry.entry_id, article_entry.title, article_entry.brief, article_entry.post_date, article_entry.modify_date, 
        images.*, images.path AS img_path, images.name AS img_name
        FROM article_entry
        LEFT JOIN article_entry_images AS aei ON article_entry.entry_id = aei.entry_id
        LEFT JOIN images ON aei.img_id = images.img_id
        LEFT JOIN image_has_tag AS iht ON images.img_id = iht.img_id
        LEFT JOIN tags ON iht.tag_id = tags.tag_id AND tags.tag_id = 1001"
        .$whereString." ORDER BY ".$orderBy." ".$order." LIMIT ".$offset.",".$perPage.";";

        try {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->beginTransaction();

        $stmt = $db->prepare($query);
        $stmt->execute($bindParams);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rows = $stmt->rowCount();

        $fetchedIds = array_column($entries, "entry_id"); 

        if (!empty($fetchedIds)) {
        $idsPlaceholder = '?'. str_repeat(', ?', count($fetchedIds) - 1);

        $stmt = $db->prepare("SELECT tags.name, ae.entry_id
        FROM article_entry ae 
        INNER JOIN article_entry_has_tag aeht ON ae.entry_id = aeht.entry_id 
        INNER JOIN tags ON aeht.tag_id = tags.tag_id
        WHERE ae.entry_id IN (".$idsPlaceholder.");");

        $stmt->execute($fetchedIds);
        $entry_tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
        $entry_tags = [];
        }

        $db->commit();

        } catch (Exception $e) {
        $db->rollBack();
        echo "Error: " . $e->getMessage();
        }

        if ($rows <= 0 && empty($message)) {
        $message = "No matching results";
        }
        ?>

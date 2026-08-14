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
$categories = [["medium", "medium"],
        ["tool", "tool"], 
        ["subject", "subject"], 
        ["gallery_type", "type"],
        ["size", "size"], 
        ["misc", "misc"]];
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

if ($order !== 'ASC') { $order = 'DESC';}
if ($orderBy !== 'title') { $orderBy = 'creation_date';}
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

if (!empty($date_from)) { 
        $conditions[] = "gallery_entry.creation_date >= ?";
        $bindParams[] = $date_from;
}
if (!empty($date_to)) { 
        $conditions[] = "gallery_entry.creation_date <= ?";
        $bindParams[] = $date_to;
}

if (!empty($tags)) {
        $tagPlacehldrs = implode(',', array_fill(0, count($tags), '?'));

        $conditions[] = "gallery_entry.entry_id IN (
                SELECT entry_id
                FROM gallery_entry_has_tag
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
        $whereString = " WHERE " . implode(" AND ", $conditions);
}
//prepare limits
$page = (int)$_GET["page"]??0;
$page = (is_int($page) && !isset($_GET["goto_pg1"]))? $page: 0;
$perPage = 40;

$stmt = $db->prepare("SELECT COUNT(*) FROM gallery_entry".$whereString.";");
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
        gallery_entry.*, 
        tags_main.*,
        images_main.*,
        patterns.htmlstring,
        images_thumb.path AS thumb_path,
        images_thumb.name AS thumb_name
        FROM gallery_entry
        JOIN patterns ON gallery_entry.pattern = patterns.pattern_id
        JOIN gallery_entry_images AS gei_main ON gallery_entry.entry_id = gei_main.entry_id
        JOIN images AS images_main ON gei_main.img_id = images_main.img_id
        JOIN image_has_tag AS iht_main ON images_main.img_id = iht_main.img_id
        JOIN tags AS tags_main ON iht_main.tag_id = tags_main.tag_id AND tags_main.tag_id = 1000
        JOIN gallery_entry_images AS gei_thumb ON gallery_entry.entry_id = gei_thumb.entry_id
        JOIN images AS images_thumb ON gei_thumb.img_id = images_thumb.img_id
        JOIN image_has_tag AS iht_thumb ON images_thumb.img_id = iht_thumb.img_id
        JOIN tags AS tags_thumb ON iht_thumb.tag_id = tags_thumb.tag_id AND tags_thumb.tag_id = 1001"
        .$whereString." ORDER BY ".$orderBy." ".$order." LIMIT ".$offset.",".$perPage.";";

        //get entries
        try {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->beginTransaction();

        $stmt = $db->prepare($query);
        $stmt->execute($bindParams);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rows = $stmt->rowCount();

        $db->commit();
        } catch (Exception $e) {
        $db->rollBack();
        echo "Error: " . $e->getMessage();
        }
        if ($rows <= 0 && empty($message)) {
        $message = "No matching results";
        }
        ?>

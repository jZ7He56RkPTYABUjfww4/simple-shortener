 <?php
require 'config.php';

if (isset($_GET['name'])) {
    $db = new MySQLi(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
    $db->set_charset('utf8mb4');

    $slug = $db->real_escape_string(strtolower(preg_replace('/[^a-z0-9]/si', '', $_GET['name']))); // remove non-letter or number chars, sanatize
    $redirectResult = $db->query("SELECT url FROM redirect WHERE slug = '{$slug}'");
    if ($redirectResult->num_rows == 0) {
        echo "
<!--
  Debug Info:
    name: {$_GET["name"]}
    slug: {$slug}

-->
      ";
        die("not found");
    }
    $db->query("UPDATE redirect SET hits = hits + 1 WHERE slug = '{$slug}'");
    $url = $redirectResult->fetch_assoc()["url"];
    $db->close();

    header("Location: {$url}", true, 301);
    exit;
} else {
    echo "url not set";
}

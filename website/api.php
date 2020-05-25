<?php

require 'config.php';
header('Content-Type: application/json');
$pageResult = array();

function random_str($length)
{
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    if ($max < 1) {
        throw new Exception('$keyspace must be at least two characters long');
    }
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}

if (isset($_GET["type"])) {
    $db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
    $db->set_charset('utf8mb4');

    switch ($_GET["type"]) {
      case "new":
        if (isset($_GET['url'])) {
            $pageResult["url"] = null;
            $url = urldecode(trim(strtolower($_GET['url'])));
            if (!preg_match("/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)/", $url)) {
                $pageResult["success"] = false;
                $pageResult["error"] = "Enter a URL starting with http(s)://";
            } else {
                // If the URL is already a short URL on this domain, don’t re-shorten it
                if (strpos($url, SHORT_URL) === 0) {
                    die('{"success": false, "error": "Cannot shorten this domain"}');
                }
                $url = $db->real_escape_string($url);
                $result = $db->query("SELECT slug FROM redirect WHERE url = '{$url}' LIMIT 1");
                if ($result && $result->num_rows > 0) { // If there’s already a short URL for this URL
                    $pageResult["success"] = null;
                    $pageResult["url"] = SHORT_URL . $result->fetch_object()->slug;
                } else {
                    $result = $db->query('SELECT slug, url FROM redirect ORDER BY date DESC, slug DESC LIMIT 1');
                    if ($result && $result->num_rows > 0) {
                        if (isset($_GET["name"])) {
                            $slug = urldecode(trim(strtolower($db->real_escape_string($_GET["name"]))));
                            if ($db->query("SELECT * FROM `redirect` WHERE `slug`='{$slug}'")->num_rows == 0) {
                            } else {
                                $pageResult["success"] = false;
                                $pageResult["error"] = "Name taken";
                            }
                        } else {
                            $slug = random_str(8);
                        }
                        if ($db->query("INSERT INTO redirect (slug, url, date, hits) VALUES ('{$slug}', '{$url}', NOW(), 0)")) {
                            header('HTTP/1.1 201 Created');
                            $pageResult["success"] = true;
                            $pageResult["url"] = SHORT_URL . $slug;
                            $db->query('OPTIMIZE TABLE `redirect`');
                        }
                    }
                }
            }
        } else {
            $pageResult["success"] = false;
            $pageResult["error"] = "Missing URL";
        }
        break;
      case "list":
        $result = $db->query("SELECT * FROM `redirect` WHERE 1");
        $pageResult["success"] = true;
        $pageResult["urls"] = array();
        foreach ($result as $row) {
            array_push($pageResult["urls"], array("name" => $row["slug"], "url" => $row["url"], "hits" => $row["hits"]));
        }
        break;
      case "edit":
        if (isset($_POST['name']) && isset($_POST["url"])) {
            $url = urldecode(trim(strtolower($_POST["url"])));
            if (!preg_match("/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)/", $url)) {
                $pageResult["success"] = false;
                $pageResult["error"] = "Enter a URL starting with http(s)://";
            } else {
                if (strpos($url, SHORT_URL) === 0) {
                    die('{"success": false, "error": "Cannot shorten this domain"}');
                }
                $name = urldecode(trim(strtolower($db->real_escape_string($_POST["name"]))));
                $url = $db->real_escape_string($_POST["url"]);
                if ($db->query("UPDATE `redirect` SET `url`='{$url}' WHERE `slug`='{$name}'")) {
                    $pageResult["success"] = true;
                    $pageResult["new_target_url"] = $url;
                    $pageResult["url"] = SHORT_URL . $name;
                    $db->query('OPTIMIZE TABLE `redirect`');
                } else {
                    $pageResult["success"] = false;
                    $pageResult["error"] = $db->error;
                }
            }
        } else {
            $pageResult["success"] = false;
            $pageResult["error"] = "Missing new name";
        }
        break;
      default:
        $pageResult["error"] = "incorrect route";
        break;
  }
} else {
    $pageResult["error"] = "no route specified";
}

echo json_encode($pageResult);

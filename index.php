<?php
/**
 * User: lixiang
 * Date: 2017/6/28 14:35
 *
 */

include_once 'MySqlHelp.php';

$name = isset($_GET['name']) ? $_GET['name'] : 'CONTENTS';
$app  = new MySqlHelp();
$html = $app->Run($name);

echo $html;


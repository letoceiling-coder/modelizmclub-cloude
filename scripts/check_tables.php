<?php

$p = new PDO('sqlite:'.__DIR__.'/../database/database.sqlite');
$r = $p->query('SELECT name FROM sqlite_master WHERE type="table" ORDER BY name');
foreach ($r as $row) {
    echo $row[0].PHP_EOL;
}

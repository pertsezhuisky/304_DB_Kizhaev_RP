<?php
$pdo = new PDO('sqlite:' . dirname(__DIR__) . '/data/barbershop.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
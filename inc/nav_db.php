<?php
/* to inc in all pages => nav */
require_once 'connect.php';
$sql_nav="SELECT `id`, `lintitule` FROM `rubriques`";
$req_nav=mysqli_query($mysqli, $sql_nav)or die(mysqli_error($mysqli));

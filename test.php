<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

echo json_encode(array(
    "status" => "success",
    "message" => "L'API fonctionne correctement !",
    "timestamp" => date('Y-m-d H:i:s')
));
?>
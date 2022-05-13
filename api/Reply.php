<?php

class Reply
{
    public static function ok(array $data = [])
    {
        header("Content-Type: application/json");
        echo json_encode(["ok" => true, "data" => $data], JSON_PRETTY_PRINT);
        exit;
    }
    public static function error(string $error = "GENERIC_ERROR")
    {
        header("Content-Type: application/json");
        echo json_encode(["ok" => false, "error" => $error], JSON_PRETTY_PRINT);
        exit;
    }
}

<?php

require_once __DIR__ . '/config.php';

function getDatabase(): mysqli
{
    static $db = null;

    if ($db === null) {
        $db = new mysqli(
            DB_Host,
            DB_User,
            DB_Pass,
            DB_Name,
            DB_Port
        );

        if ($db->connect_error) {
            throw new RuntimeException(
                'Database connection failed: ' . $db->connect_error
            );
        }

        $db->set_charset('utf8mb4');
    }

    return $db;
}
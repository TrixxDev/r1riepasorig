<?php

return [
    'host' => env('ACCRUAL_IP', '127.0.0.1'),
    'port' => env('ACCRUAL_PORT', 1444),
    'database' => env('ACCRUAL_DATABASE', 'accrual'),
    'username' => env('ACCRUAL_USER', 'sa'),
    'password' => env('ACCRUAL_PASS', ''),

    // auto | sqlsrv | odbc
    'driver' => env('ACCRUAL_DRIVER', 'auto'),
    // Windows built-in driver name, e.g. "SQL Server" or "ODBC Driver 17 for SQL Server"
    'odbc_driver' => env('ACCRUAL_ODBC_DRIVER', 'SQL Server'),
    'login_timeout' => (int) env('ACCRUAL_LOGIN_TIMEOUT', 10),

    'ftp_user' => env('ACCRUAL_USERNAME', 'r1_web'),
    'ftp_password' => env('ACCRUAL_PASSWORD', ''),

    'prepayment_pz_type' => 8,
    'quick_order_pz_type' => 6,

    // After FTP upload: poll pzh until WEB = QuickOrder id appears (not FTP file disappearance).
    'order_verify' => [
        'max_attempts' => (int) env('ACCRUAL_ORDER_VERIFY_ATTEMPTS', 8),
        'interval_ms' => (int) env('ACCRUAL_ORDER_VERIFY_INTERVAL_MS', 75),
        'max_wait_ms' => (int) env('ACCRUAL_ORDER_VERIFY_MAX_WAIT_MS', 1500),
        'total_tolerance' => (float) env('ACCRUAL_ORDER_VERIFY_TOTAL_TOLERANCE', 0.02),
    ],

    'supplier' => [
        'line1' => 'R1 SIA  Reģ.Nr. 40003479731, LV40003479731',
        'line2' => 'Kalnciema iela 39, Rīga',
        'line3' => 'Luminor Bank AS Latvijas filiāle, RIKOLV2X',
        'line4' => 'LV91RIKO0001060089254',
    ],
];

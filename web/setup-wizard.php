<?php

require_once __DIR__ . '/inc/setup.php';


/*
|--------------------------------------------------------------------------
| AJAX Setup Actions
|--------------------------------------------------------------------------
|
| These actions must run BEFORE the HTML is sent.
|
*/

if (isset($_GET['action'])) {

    header(
        'Content-Type: application/json; charset=utf-8'
    );


    $action =
        $_GET['action'];


    /*
    |--------------------------------------------------------------------------
    | Database connection
    |--------------------------------------------------------------------------
    */

    if ($action === 'database') {

        echo json_encode([
            'success' =>
                checkDatabase()
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Database tables
    |--------------------------------------------------------------------------
    */

    if ($action === 'database_setup') {

        echo json_encode([
            'success' =>
                setupDatabase()
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    */

    if ($action === 'templates') {

        echo json_encode([
            'success' =>
                seedDefaultTemplates()
        ]);

        exit;
    }

    /*
|--------------------------------------------------------------------------
| Ollama
|--------------------------------------------------------------------------
*/

if ($action === 'ollama') {

    echo json_encode([
        'success' =>
            checkOllama()
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| AI Model
|--------------------------------------------------------------------------
*/

if ($action === 'model') {

    echo json_encode([
        'success' =>
            setupAIModel()
    ]);

    exit;
}


    echo json_encode([
        'success' => false,
        'error' => 'Unknown setup action'
    ]);

    exit;
}


?>
<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        ตั้งค่าระบบ — Thai Newsletter AI
    </title>

    <style>

        body {
            margin: 0;
            padding: 30px;
            background: #f3f3f3;
            font-family:
                Arial,
                "Noto Sans Thai",
                sans-serif;
        }

        .setup-container {
            max-width: 700px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .description {
            color: #666;
            line-height: 1.7;
            margin-bottom: 30px;
        }

        .status-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            background: #f8f8f8;
            border-radius: 8px;
        }

        .status-name {
            font-weight: 600;
        }

        .status-ok {
            color: #287a3e;
            font-weight: bold;
        }

        .status-running {
            color: #777;
            font-weight: bold;
        }

        .status-error {
            color: #b42318;
            font-weight: bold;
        }

        .message {
            margin-top: 25px;
            padding: 18px;
            background: #f8f8f8;
            border-radius: 8px;
            line-height: 1.7;
            color: #555;
        }

        .complete-box {
    margin-top: 25px;
    padding: 20px;
    background: #eef8f0;
    border-radius: 8px;
    color: #287a3e;
    line-height: 1.7;
}

.button {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 18px;
    background: #222;
    color: white;
    text-decoration: none;
    border-radius: 7px;
    font-weight: 600;
}

    </style>

</head>

<body>

    <main class="setup-container">

        <h1>
            ตั้งค่า Thai Newsletter AI
        </h1>

        <p class="description">
            ระบบกำลังตรวจสอบและเตรียมส่วนประกอบที่จำเป็น
        </p>


        <div class="status-list">

            <div class="status-item">

                <span class="status-name">
                    Database
                </span>

                <span
                    id="database-status"
                    class="status-running"
                >
                    ⏳ กำลังตรวจสอบ...
                </span>

            </div>


            <div class="status-item">

                <span class="status-name">
                    Database Tables
                </span>

                <span
                    id="database-setup-status"
                    class="status-running"
                >
                    ⏳ รอการตรวจสอบ...
                </span>

            </div>


            <div class="status-item">

                <span class="status-name">
                    Templates
                </span>

                <span
                    id="templates-status"
                    class="status-running"
                >
                    ⏳ รอการตรวจสอบ...
                </span>

            </div>

            <div class="status-item">

    <span class="status-name">
        Ollama
    </span>

    <span
        id="ollama-status"
        class="status-running"
    >
        ⏳ รอการตรวจสอบ...
    </span>

</div>

<div class="status-item">

    <span class="status-name">
        AI Model
    </span>

    <span
        id="model-status"
        class="status-running"
    >
        ⏳ รอการตรวจสอบ...
    </span>

</div>

        </div>


        <div
    id="message"
    class="message"
>
    กำลังเริ่มต้นระบบ...
</div>

<div
    id="complete-box"
    class="complete-box"
    style="display: none;"
>
    <strong>
        ✓ ตั้งค่าระบบเสร็จสมบูรณ์
    </strong>

    <br>

    Thai Newsletter AI
    พร้อมใช้งานแล้ว

    <br>

    <a
        href="index.php"
        class="button"
    >
        ไปหน้าหลัก
    </a>
</div>

    </main>


    <script>

        /*
        |--------------------------------------------------------------------------
        | Step 1 — Database
        |--------------------------------------------------------------------------
        */

        async function setupDatabaseConnection() {

            const status =
                document.getElementById(
                    'database-status'
                );

            const message =
                document.getElementById(
                    'message'
                );


            message.textContent =
                'กำลังตรวจสอบการเชื่อมต่อ Database...';


            /*
            |--------------------------------------------------------------------------
            | We use a small PHP endpoint inside this page.
            |--------------------------------------------------------------------------
            */

            const response =
                await fetch(
                    'setup-wizard.php?action=database'
                );


            const result =
                await response.json();


            if (result.success) {

                status.textContent =
                    '✓ พร้อมใช้งาน';

                status.className =
                    'status-ok';

                message.textContent =
                    'Database พร้อมใช้งานแล้ว';

                return true;

            }


            status.textContent =
                '✗ ไม่พร้อมใช้งาน';

            status.className =
                'status-error';

            message.textContent =
                'ไม่สามารถเชื่อมต่อ Database ได้';

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Step 2 — Database Tables
        |--------------------------------------------------------------------------
        */

        async function setupDatabaseTables() {

            const status =
                document.getElementById(
                    'database-setup-status'
                );

            const message =
                document.getElementById(
                    'message'
                );


            status.textContent =
                '⏳ กำลังสร้างตาราง...';


            message.textContent =
                'กำลังสร้าง Database Tables...';


            const response =
                await fetch(
                    'setup-wizard.php?action=database_setup'
                );


            const result =
                await response.json();


            if (result.success) {

                status.textContent =
                    '✓ พร้อมใช้งาน';

                status.className =
                    'status-ok';

                message.textContent =
                    'สร้าง Database Tables เรียบร้อยแล้ว';

                return true;

            }


            status.textContent =
                '✗ ไม่สำเร็จ';

            status.className =
                'status-error';

            message.textContent =
                'ไม่สามารถสร้าง Database Tables ได้';

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Step 3 — Templates
        |--------------------------------------------------------------------------
        */

        async function setupTemplates() {

            const status =
                document.getElementById(
                    'templates-status'
                );

            const message =
                document.getElementById(
                    'message'
                );


            status.textContent =
                '⏳ กำลังเตรียม Templates...';


            message.textContent =
                'กำลังตรวจสอบ Default Templates...';


            const response =
                await fetch(
                    'setup-wizard.php?action=templates'
                );


            const result =
                await response.json();


            if (result.success) {

                status.textContent =
                    '✓ พร้อมใช้งาน';

                status.className =
                    'status-ok';

                message.textContent =
                    'Templates พร้อมใช้งานแล้ว';

                return true;

            }


            status.textContent =
                '✗ ไม่สำเร็จ';

            status.className =
                'status-error';

            message.textContent =
                'ไม่สามารถเตรียม Templates ได้';

            return false;
        }

/*
|--------------------------------------------------------------------------
| Step 4 — Ollama
|--------------------------------------------------------------------------
*/

async function setupOllama() {

    const status =
        document.getElementById(
            'ollama-status'
        );

    const message =
        document.getElementById(
            'message'
        );


    status.textContent =
        '⏳ กำลังตรวจสอบ...';


    message.textContent =
        'กำลังตรวจสอบการเชื่อมต่อ Ollama...';


    const response =
        await fetch(
            'setup-wizard.php?action=ollama'
        );


    const result =
        await response.json();


    if (result.success) {

        status.textContent =
            '✓ เชื่อมต่อสำเร็จ';

        status.className =
            'status-ok';

        message.textContent =
            'Ollama เชื่อมต่อสำเร็จแล้ว';

        return true;

    }


    status.textContent =
        '✗ เชื่อมต่อไม่ได้';

    status.className =
        'status-error';

    message.textContent =
        'ไม่สามารถเชื่อมต่อ Ollama ได้';

    return false;
}

/*
|--------------------------------------------------------------------------
| Step 5 — AI Model
|--------------------------------------------------------------------------
*/

async function setupModel() {

    const status =
        document.getElementById(
            'model-status'
        );

    const message =
        document.getElementById(
            'message'
        );


    status.textContent =
        '⏳ กำลังตรวจสอบ...';


    message.textContent =
        'กำลังตรวจสอบ AI Model...';


    const response =
        await fetch(
            'setup-wizard.php?action=model'
        );


    const result =
        await response.json();


    if (result.success) {

        status.textContent =
            '✓ พร้อมใช้งาน';

        status.className =
            'status-ok';

        message.textContent =
            'AI Model พร้อมใช้งานแล้ว';

        return true;

    }


    status.textContent =
        '✗ ไม่พร้อมใช้งาน';

    status.className =
        'status-error';

    message.textContent =
        'ไม่สามารถเตรียม AI Model ได้';

    return false;
}

        /*
        |--------------------------------------------------------------------------
        | Run Setup
        |--------------------------------------------------------------------------
        */

        async function runWizard() {

            const databaseReady =
                await setupDatabaseConnection();


            if (!databaseReady) {
                return;
            }


            const databaseSetupReady =
                await setupDatabaseTables();


            if (!databaseSetupReady) {
                return;
            }


            const templatesReady =
    await setupTemplates();


if (!templatesReady) {
    return;
}


const ollamaReady =
    await setupOllama();


if (!ollamaReady) {
    return;
}

const modelReady =
    await setupModel();


if (!modelReady) {
    return;
}


document.getElementById(
    'message'
).textContent =
    'ทุกส่วนของระบบพร้อมใช้งานแล้ว ✓';


document.getElementById(
    'complete-box'
).style.display =
    'block';
        }


        runWizard();

    </script>

</body>

</html>
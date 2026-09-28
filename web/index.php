<?php

require_once __DIR__ . '/inc/setup.php';

$status = checkSystem();

if (!$status['ready']) {

    header('Location: setup-wizard.php');
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
        Thai Newsletter AI
    </title>

    <style>

        body {
            margin: 0;
            padding: 0;
            background: #f3f3f3;
            font-family:
                Arial,
                "Noto Sans Thai",
                sans-serif;
            color: #222;
        }


        /*
        |--------------------------------------------------------------------------
        | Top Bar
        |--------------------------------------------------------------------------
        */

        .top-bar {
            background: #ffffff;
            border-bottom: 1px solid #ddd;
            padding: 14px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            font-size: 18px;
            font-weight: 700;
        }

        .brand small {
            display: block;
            margin-top: 3px;
            font-size: 11px;
            font-weight: normal;
            color: #777;
        }

        .top-nav {
            display: flex;
            gap: 8px;
        }

        .nav-button {
            display: inline-block;
            padding: 9px 14px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .nav-primary {
            background: #222;
            color: white;
        }

        .nav-secondary {
            background: #eeeeee;
            color: #222;
        }


        /*
        |--------------------------------------------------------------------------
        | Main
        |--------------------------------------------------------------------------
        */

        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 25px;
        }

        .hero {
            background: white;
            padding: 45px;
            border-radius: 12px;
            text-align: center;
            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .hero h1 {
            margin: 0;
            font-size: 38px;
        }

        .hero p {
            max-width: 650px;
            margin: 15px auto 0;
            color: #666;
            line-height: 1.8;
        }


        /*
        |--------------------------------------------------------------------------
        | Actions
        |--------------------------------------------------------------------------
        */

        .actions {
            display: grid;
            grid-template-columns:
                repeat(2, 1fr);
            gap: 18px;
            margin-top: 25px;
        }

        .action-card {
            display: block;
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-decoration: none;
            color: #222;
            border: 1px solid #e5e5e5;
            transition:
                transform 0.15s ease,
                box-shadow 0.15s ease;
        }

        .action-card:hover {
            transform: translateY(-2px);
            box-shadow:
                0 5px 18px rgba(0, 0, 0, 0.08);
        }

        .action-icon {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .action-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .action-description {
            font-size: 13px;
            line-height: 1.6;
            color: #777;
        }


        /*
        |--------------------------------------------------------------------------
        | System Status
        |--------------------------------------------------------------------------
        */

        .system-status {
            margin-top: 25px;
            background: white;
            padding: 22px 25px;
            border-radius: 10px;
            border: 1px solid #e5e5e5;
        }

        .system-status h2 {
            margin: 0 0 15px;
            font-size: 17px;
        }

        .status-grid {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 10px;
        }

        .status-item {
            padding: 12px;
            background: #f8f8f8;
            border-radius: 7px;
            font-size: 13px;
        }

        .status-ok {
            color: #287a3e;
            font-weight: 700;
        }


        /*
        |--------------------------------------------------------------------------
        | Footer
        |--------------------------------------------------------------------------
        */

        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 11px;
            color: #888;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 700px) {

            .top-bar {
                padding: 12px 15px;
                flex-direction: column;
                gap: 12px;
            }

            .top-nav {
                width: 100%;
            }

            .nav-button {
                flex: 1;
                text-align: center;
            }

            .container {
                margin: 25px auto;
                padding: 0 15px;
            }

            .hero {
                padding: 30px 20px;
            }

            .hero h1 {
                font-size: 30px;
            }

            .actions {
                grid-template-columns: 1fr;
            }

            .status-grid {
                grid-template-columns: 1fr;
            }
        }

    </style>

</head>

<body>


    <!--
    |--------------------------------------------------------------------------
    | Top Bar
    |--------------------------------------------------------------------------
    -->

    <header class="top-bar">

        <div class="brand">

            TNAI

            <small>
                Thai Newsletter AI
            </small>

        </div>


        <nav class="top-nav">

            <a
                href="index.php"
                class="nav-button nav-secondary"
            >
                🏠 หน้าหลัก
            </a>

            <a
                href="drafts.php"
                class="nav-button nav-secondary"
            >
                📝 ฉบับร่าง
            </a>

        </nav>

    </header>


    <main class="container">


        <!--
        |--------------------------------------------------------------------------
        | Hero
        |--------------------------------------------------------------------------
        -->

        <section class="hero">

            <h1>
                Thai Newsletter AI
            </h1>

            <p>
                ระบบช่วยสร้างจดหมายข่าวภาษาไทย
                ด้วย AI สามารถเลือก Template
                เพิ่มรูปภาพ และให้ AI ช่วยเขียนเนื้อหา
                สำหรับจัดทำจดหมายข่าวได้อย่างสะดวก
            </p>

        </section>


        <!--
        |--------------------------------------------------------------------------
        | Main Actions
        |--------------------------------------------------------------------------
        -->

        <section class="actions">

            <a
                href="editor.php"
                class="action-card"
            >

                <div class="action-icon">
                    ✏️
                </div>

                <div class="action-title">
                    สร้างจดหมายข่าว
                </div>

                <div class="action-description">
                    สร้างจดหมายข่าวฉบับใหม่
                    เลือก Template เพิ่มรูปภาพ
                    และให้ AI ช่วยเขียนเนื้อหา
                </div>

            </a>


            <a
                href="drafts.php"
                class="action-card"
            >

                <div class="action-icon">
                    📝
                </div>

                <div class="action-title">
                    ฉบับร่าง
                </div>

                <div class="action-description">
                    ดู แก้ไข ลบ และดูตัวอย่าง
                    จดหมายข่าวที่บันทึกไว้
                </div>

            </a>

        </section>


        <!--
        |--------------------------------------------------------------------------
        | System Status
        |--------------------------------------------------------------------------
        -->

        <section class="system-status">

            <h2>
                สถานะระบบ
            </h2>


            <div class="status-grid">

                <div class="status-item">

                    Database

                    <span class="status-ok">
                        ✓ พร้อมใช้งาน
                    </span>

                </div>


                <div class="status-item">

                    Ollama

                    <span class="status-ok">
                        ✓ เชื่อมต่อสำเร็จ
                    </span>

                </div>


                <div class="status-item">

                    AI Model

                    <span class="status-ok">
                        ✓ พร้อมใช้งาน
                    </span>

                </div>

            </div>

        </section>


        <footer class="footer">

            TNAI — Thai Newsletter AI

        </footer>

    </main>

</body>

</html>
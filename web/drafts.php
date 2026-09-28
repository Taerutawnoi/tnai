<?php

require_once __DIR__ . '/inc/setup.php';

$status = checkSystem();

if (!$status['ready']) {
    die('System is not ready.');
}

$db = getDatabase();


/*
|--------------------------------------------------------------------------
| Load draft newsletters
|--------------------------------------------------------------------------
*/

$stmt = $db->prepare("
    SELECT
        newsletters.id,
        newsletters.headline,
        newsletters.summary,
        newsletters.publish_date,
        newsletters.location,
        newsletters.edition,
        newsletters.status,
        newsletters.updated_at,
        templates.name AS template_name
    FROM newsletters
    LEFT JOIN templates
        ON newsletters.template_id = templates.id
    WHERE newsletters.status = 'draft'
    ORDER BY newsletters.updated_at DESC, newsletters.id DESC
");

if (!$stmt) {
    die(
        'Failed to prepare draft query: ' .
        $db->error
    );
}


if (!$stmt->execute()) {
    die(
        'Failed to load drafts: ' .
        $stmt->error
    );
}


$result = $stmt->get_result();

$drafts = [];


while ($row = $result->fetch_assoc()) {
    $drafts[] = $row;
}


$stmt->close();

?>
<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ฉบับร่าง - Thai Newsletter AI</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;

            font-family:
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;

            background: #f4f5f7;
            color: #222;
        }

        .top-bar {
    padding: 14px 30px;

    background: #222;
    color: white;

    display: flex;

    justify-content: space-between;
    align-items: center;
}

.top-bar .brand {
    font-size: 20px;
    font-weight: 700;
}

.top-nav {
    display: flex;

    gap: 8px;
}

.nav-button {
    display: inline-block;

    padding: 9px 14px;

    background: #ffffff;
    color: #222;

    text-decoration: none;

    border-radius: 7px;

    font-size: 13px;
    font-weight: 600;
}

.nav-button:hover {
    background: #eeeeee;
}

        .container {
            max-width: 1100px;

            margin: auto;

            padding: 20px;
        }

        .top-bar {
            display: flex;

            justify-content: space-between;
            align-items: center;

            margin-bottom: 20px;
        }

        .top-bar h2 {
            margin: 0;
        }

        .new-button {
            display: inline-block;

            padding: 10px 15px;

            background: #287a3e;
            color: white;

            text-decoration: none;

            border-radius: 7px;

            font-size: 14px;
            font-weight: 600;
        }

        .draft-list {
            display: flex;

            flex-direction: column;

            gap: 15px;
        }

        .draft-card {
            background: white;

            padding: 20px;

            border-radius: 12px;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.08);
        }

        .draft-card h3 {
            margin-top: 0;
            margin-bottom: 8px;

            font-size: 20px;
        }

        .summary {
            color: #555;

            margin-bottom: 15px;

            line-height: 1.6;
        }

        .meta {
            display: flex;

            flex-wrap: wrap;

            gap: 8px 15px;

            margin-bottom: 15px;

            color: #666;

            font-size: 13px;
        }

        .preview-button {
    display: inline-block;

    padding: 9px 14px;

    background: #287a3e;
    color: white;

    text-decoration: none;

    border-radius: 7px;

    font-size: 13px;
    font-weight: 600;
}

        .edit-button {
            display: inline-block;

            padding: 9px 14px;

            background: #222;
            color: white;

            text-decoration: none;

            border-radius: 7px;

            font-size: 13px;
            font-weight: 600;
        }

        .delete-button {
    display: inline-block;

    padding: 9px 14px;

    background: #b42318;
    color: white;

    border: none;

    border-radius: 7px;

    font-family: inherit;
    font-size: 13px;
    font-weight: 600;

    cursor: pointer;
}

.delete-button:hover {
    background: #8f1d14;
}

.action-row {
    display: flex;

    gap: 10px;

    flex-wrap: wrap;
}

        .empty {
            background: white;

            padding: 40px;

            border-radius: 12px;

            text-align: center;

            color: #666;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.08);
        }

        @media (max-width: 700px) {

    .top-bar {
        padding: 12px 15px;

        flex-direction: column;

        gap: 12px;

        align-items: stretch;
    }

    .top-bar .brand {
        text-align: center;
    }

    .top-nav {
        width: 100%;
    }

    .nav-button {
        flex: 1;

        text-align: center;
    }

}

    </style>

</head>

<body>

<header class="top-bar">

    <div class="brand">

        📰 Thai Newsletter AI

    </div>


    <nav class="top-nav">

        <a
            href="index.php"
            class="nav-button"
        >
            🏠 หน้าหลัก
        </a>

        <a
            href="drafts.php"
            class="nav-button"
        >
            📝 ฉบับร่าง
        </a>

    </nav>

</header>


<div class="container">

    <div class="top-bar">

        <h2>📝 ฉบับร่าง</h2>

        <a
            href="editor.php"
            class="new-button"
        >
            ➕ สร้างฉบับใหม่
        </a>

    </div>


    <?php if (count($drafts) === 0): ?>

        <div class="empty">

            <p>
                ยังไม่มีฉบับร่าง
            </p>

            <p>
                กด "สร้างฉบับใหม่"
                เพื่อเริ่มเขียนจดหมายข่าว
            </p>

        </div>

    <?php else: ?>

        <div class="draft-list">

            <?php foreach ($drafts as $draft): ?>

                <div class="draft-card">

                    <h3>

                        <?= htmlspecialchars(
                            $draft['headline'] ?: 'ไม่มีพาดหัวข่าว'
                        ) ?>

                    </h3>


                    <div class="summary">

                        <?= htmlspecialchars(
                            $draft['summary'] ?: 'ไม่มีสรุปข่าว'
                        ) ?>

                    </div>


                    <div class="meta">

                        <span>
                            📄 Template:
                            <?= htmlspecialchars(
                                $draft['template_name'] ?: '-'
                            ) ?>
                        </span>

                        <span>
                            📅 วันที่:
                            <?= htmlspecialchars(
                                $draft['publish_date'] ?: '-'
                            ) ?>
                        </span>

                        <span>
                            📍 สถานที่:
                            <?= htmlspecialchars(
                                $draft['location'] ?: '-'
                            ) ?>
                        </span>

                        <span>
                            📰 <?= htmlspecialchars(
                                $draft['edition'] ?: '-'
                            ) ?>
                        </span>

                    </div>


                    <div class="action-row">

    <a
        href="preview.php?id=<?= (int) $draft['id'] ?>"
        class="preview-button"
        target="_blank"
    >
        👁️ ดูตัวอย่าง
    </a>


    <a
        href="editor.php?id=<?= (int) $draft['id'] ?>"
        class="edit-button"
    >
        ✏️ แก้ไขฉบับร่าง
    </a>


    <button
        type="button"
        class="delete-button"
        data-id="<?= (int) $draft['id'] ?>"
    >
        🗑️ ลบฉบับร่าง
    </button>

</div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<script>

document
    .querySelectorAll('.delete-button')
    .forEach(function (button) {

        button.addEventListener(
            'click',
            async function () {

                const newsletterId =
                    this.dataset.id;


                /*
                |--------------------------------------------------------------------------
                | Confirmation
                |--------------------------------------------------------------------------
                */

                const confirmed =
                    confirm(
                        'ต้องการลบฉบับร่างนี้หรือไม่?\n\n' +
                        'การลบจะลบรูปภาพของฉบับนี้ด้วย'
                    );


                if (!confirmed) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Disable button
                |--------------------------------------------------------------------------
                */

                this.disabled = true;

                this.textContent =
                    '⏳ กำลังลบ...';


                try {

                    /*
                    |--------------------------------------------------------------------------
                    | Prepare request
                    |--------------------------------------------------------------------------
                    */

                    const formData =
                        new FormData();


                    formData.append(
                        'newsletter_id',
                        newsletterId
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Send delete request
                    |--------------------------------------------------------------------------
                    */

                    const response =
                        await fetch(
                            'api/delete.php',
                            {
                                method: 'POST',
                                body: formData
                            }
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Parse response
                    |--------------------------------------------------------------------------
                    */

                    const data =
                        await response.json();


                    /*
                    |--------------------------------------------------------------------------
                    | Handle error
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !response.ok ||
                        !data.success
                    ) {

                        throw new Error(
                            data.error ||
                            'ไม่สามารถลบฉบับร่างได้'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Success
                    |--------------------------------------------------------------------------
                    */

                    alert(
                        'ลบฉบับร่างเรียบร้อยแล้ว!'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Reload draft list
                    |--------------------------------------------------------------------------
                    */

                    window.location.reload();


                } catch (error) {

                    console.error(
                        error
                    );


                    alert(
                        'ไม่สามารถลบฉบับร่างได้:\n' +
                        error.message
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Restore button
                    |--------------------------------------------------------------------------
                    */

                    this.disabled = false;

                    this.textContent =
                        '🗑️ ลบฉบับร่าง';
                }

            }
        );

    });

</script>

</body>

</html>

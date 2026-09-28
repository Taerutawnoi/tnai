<?php

require_once __DIR__ . '/inc/setup.php';

$status = checkSystem();

if (!$status['ready']) {
    die('System is not ready.');
}

$db = getDatabase();


/*
|--------------------------------------------------------------------------
| Load existing newsletter
|--------------------------------------------------------------------------
*/

$newsletter = null;

$newsletterId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($newsletterId > 0) {

    $stmt = $db->prepare("
        SELECT
            id,
            template_id,
            headline,
            summary,
            body,
            image_caption,
            edition,
            publish_date,
            location,
            status
        FROM newsletters
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die(
            'Failed to prepare newsletter query: ' .
            $db->error
        );
    }


    $stmt->bind_param(
        'i',
        $newsletterId
    );


    if (!$stmt->execute()) {
        die(
            'Failed to load newsletter: ' .
            $stmt->error
        );
    }


    $resultNewsletter =
        $stmt->get_result();


    if (
        $resultNewsletter->num_rows === 0
    ) {

        die(
            'ไม่พบฉบับข่าวที่ต้องการแก้ไข'
        );
    }


    $newsletter =
        $resultNewsletter->fetch_assoc();


    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Load existing newsletter images
|--------------------------------------------------------------------------
*/

$existingImages = [];

$imageStmt = $db->prepare("
    SELECT
        id,
        filename,
        original_filename,
        image_order
    FROM newsletter_images
    WHERE newsletter_id = ?
    ORDER BY image_order ASC, id ASC
");

$imageStmt->bind_param(
    'i',
    $newsletterId
);

$imageStmt->execute();

$imageResult =
    $imageStmt->get_result();

while ($image = $imageResult->fetch_assoc()) {

    $existingImages[] = $image;
}

$imageStmt->close();

/*
|--------------------------------------------------------------------------
| Load templates
|--------------------------------------------------------------------------
*/

$result = $db->query("
    SELECT
        id,
        name,
        description,
        template_key
    FROM templates
    ORDER BY id ASC
");

if (!$result) {
    die(
        'Failed to load templates: ' .
        $db->error
    );
}

$templates = [];

while ($row = $result->fetch_assoc()) {
    $templates[] = $row;
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

    <title>Thai Newsletter AI</title>

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
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 20px;

            padding: 20px;

            max-width: 1500px;
            margin: auto;
        }

        .panel {
            background: white;
            border-radius: 12px;
            padding: 20px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, 0.08);
        }

        label {
            display: block;

            margin-top: 15px;
            margin-bottom: 6px;

            font-weight: 600;
        }

        input,
        textarea,
        select {
            width: 100%;

            padding: 10px;

            border: 1px solid #ccc;
            border-radius: 7px;

            font-family: inherit;
            font-size: 14px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button {
            border: none;
            border-radius: 7px;

            padding: 11px 16px;

            font-size: 14px;
            font-weight: 600;

            cursor: pointer;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .button-row {
            display: flex;
            gap: 10px;

            margin-top: 20px;
        }

        .ai-button {
            flex: 1;

            background: #222;
            color: white;
        }

        .save-button {
            flex: 1;

            background: #287a3e;
            color: white;
        }

        .progress {
            margin-top: 12px;

            padding: 10px;

            background: #f1f1f1;

            border-radius: 7px;

            font-size: 13px;
        }

        .preview {
            min-height: 700px;

            padding: 40px;

            background: white;
            border-radius: 12px;
        }

        .preview h1 {
            margin-top: 0;

            font-size: 32px;
        }

        .preview .meta {
            color: #666;

            font-size: 14px;

            margin-bottom: 25px;
        }

        .summary {
            font-size: 18px;
            font-weight: 600;

            margin-bottom: 20px;
        }

        .body {
            white-space: pre-wrap;

            line-height: 1.8;

            margin-bottom: 25px;
        }

        .caption {
            color: #777;

            font-size: 13px;

            font-style: italic;
        }

        .image-selector {
            margin-top: 15px;
        }

        .image-selector input {
            padding: 8px;
        }

        .image-list {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 10px;

            margin-top: 10px;
        }

        .image-item {
            position: relative;

            border: 1px solid #ddd;
            border-radius: 7px;

            overflow: hidden;

            background: #f5f5f5;
        }

        .image-item img {
            width: 100%;
            height: 100px;

            object-fit: cover;

            display: block;
        }

        .image-item span {
            display: block;

            padding: 5px;

            font-size: 11px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 900px) {

            .container {
                grid-template-columns: 1fr;
            }

            .preview {
                min-height: auto;
            }

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

        .remove-image-button {
    width: 100%;

    margin: 0;

    padding: 6px;

    border-radius: 0;

    background: #b42318;
    color: white;

    font-size: 12px;
}

.remove-image-button:hover {
    background: #8f1d14;
}

.template-preview {
    margin-top: 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
}

.template-preview-image {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f3f3;
    color: #888;
    font-size: 14px;
}

.template-preview-info {
    padding: 15px;
}

.template-preview-info strong {
    display: block;
    margin-bottom: 5px;
    font-size: 15px;
}

.template-preview-info p {
    margin: 0;
    font-size: 13px;
    line-height: 1.6;
    color: #777;
}

.preview-mockup {
    width: 72%;
    height: 145px;
    padding: 14px;
    box-sizing: border-box;
    background: #ffffff;
    border: 1px solid #ddd;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}


/* Brand */

.preview-brand {
    width: 30%;
    height: 5px;
    margin-bottom: 8px;
    background: #777;
}


/* Headline */

.preview-headline {
    width: 75%;
    height: 11px;
    margin-bottom: 7px;
    background: #222;
}


/* Summary */

.preview-summary {
    width: 55%;
    height: 5px;
    margin-bottom: 10px;
    background: #bbb;
}


/* Image */

.preview-image {
    width: 100%;
    height: 45px;
    margin-bottom: 9px;
    background: #ddd;
}


/* Article */

.preview-text {
    width: 92%;
    height: 4px;
    margin-bottom: 4px;
    background: #bbb;
}

.preview-text.short {
    width: 75%;
}

.preview-text.shorter {
    width: 55%;
}

/*
|--------------------------------------------------------------------------
| Classic preview
|--------------------------------------------------------------------------
*/

.preview-mockup.classic {
    border-top: 4px double #222;
    text-align: center;
}

.preview-mockup.classic .preview-brand {
    margin-left: auto;
    margin-right: auto;
}

.preview-mockup.classic .preview-headline {
    width: 65%;
    margin-left: auto;
    margin-right: auto;
}

.preview-mockup.classic .preview-summary {
    margin-left: auto;
    margin-right: auto;
}

.preview-mockup.classic .preview-image {
    height: 38px;
}


/*
|--------------------------------------------------------------------------
| Modern preview
|--------------------------------------------------------------------------
*/

.preview-mockup.modern {
    border-left: 5px solid #222;
}

.preview-mockup.modern .preview-brand {
    width: 25%;
}

.preview-mockup.modern .preview-headline {
    width: 85%;
    height: 14px;
}

.preview-mockup.modern .preview-image {
    height: 52px;
}


/*
|--------------------------------------------------------------------------
| Image Focus preview
|--------------------------------------------------------------------------
*/

.preview-mockup.image-focus {
    padding: 10px;
}

.preview-mockup.image-focus .preview-brand {
    width: 20%;
}

.preview-mockup.image-focus .preview-headline {
    width: 90%;
    height: 13px;
}

.preview-mockup.image-focus .preview-image {
    height: 68px;
}

.preview-mockup.image-focus .preview-summary {
    width: 70%;
}

#templatePreviewLimit {
    display: block;
    margin-top: 8px;
    font-size: 12px;
    color: #777;
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


    <!-- =========================================================
         EDITOR
         ========================================================= -->

    <div class="panel">

        <h2>📝 ตัวแก้ไขข่าว</h2>


        <!-- Template -->

        <label for="template">
            Template
        </label>

        <select id="template">

    <?php foreach ($templates as $template): ?>

        <option
            value="<?= htmlspecialchars($template['template_key']) ?>"
            <?=
                (
                    $newsletter &&
                    (int) $newsletter['template_id'] ===
                    (int) $template['id']
                )
                    ? 'selected'
                    : ''
            ?>
        >
            <?= htmlspecialchars($template['name']) ?>
        </option>

    <?php endforeach; ?>

</select>

        <!-- Template Preview -->

<div
    id="templatePreview"
    class="template-preview"
>

    <div
    class="template-preview-image"
    id="templatePreviewImage"
>

    <div class="preview-mockup">

        <div class="preview-brand"></div>

        <div class="preview-headline"></div>

        <div class="preview-summary"></div>

        <div class="preview-image"></div>

        <div class="preview-text"></div>
        <div class="preview-text short"></div>
        <div class="preview-text shorter"></div>

    </div>

</div>

    <div class="template-preview-info">

        <strong id="templatePreviewName">
            Template Preview
        </strong>

        <p id="templatePreviewDescription">
    เลือก Template เพื่อดูตัวอย่าง
</p>

<small id="templatePreviewLimit">
    🖼️ สูงสุด 6 รูป
</small>

    </div>

</div>

        <!-- Headline -->

        <label for="headline">
            พาดหัวข่าว
        </label>

        <input
    type="text"
    id="headline"
    placeholder="ใส่หัวข้อข่าว"
    value="<?= htmlspecialchars($newsletter['headline'] ?? '') ?>"
>


        <!-- Summary -->

        <label for="summary">
            สรุปข่าว
        </label>

        <textarea
            id="summary"
        ><?= htmlspecialchars($newsletter['summary'] ?? '') ?></textarea>


        <!-- Date -->

        <label for="date">
            วันที่
        </label>

        <input
            type="date"
            id="date"
            value="<?= htmlspecialchars($newsletter['publish_date'] ?? '') ?>"
        >


        <!-- Location -->

        <label for="location">
            สถานที่
        </label>

        <input
            type="text"
            id="location"
            placeholder="เช่น วิทยาลัย..."
            value="<?= htmlspecialchars($newsletter['location'] ?? '') ?>"
        >


        <!-- Edition -->

        <label for="edition">
            ฉบับ
        </label>

        <input
            type="text"
            id="edition"
            placeholder="เช่น ฉบับที่ 01/2026"
            value="<?= htmlspecialchars($newsletter['edition'] ?? '') ?>"
        >


        <!-- Context -->

        <label for="context">
            ข้อมูล / บริบทข่าว
        </label>

        <textarea
            id="context"
            placeholder="ใส่ข้อมูลที่ต้องการให้ AI นำไปเขียนข่าว..."
        ></textarea>


        <!-- Body -->

        <label for="body">
            เนื้อหาข่าว
        </label>

        <textarea
            id="body"
        ><?= htmlspecialchars($newsletter['body'] ?? '') ?></textarea>


        <!-- Image caption -->

        <label for="imageCaption">
            คำบรรยายภาพ
        </label>

        <textarea
            id="imageCaption"
        ><?= htmlspecialchars($newsletter['image_caption'] ?? '') ?></textarea>


        <!-- Images -->

        <label>
            รูปภาพ
        </label>

        <div class="image-selector">

            <input
                type="file"
                id="images"
                accept="image/*"
                multiple
            >

            <small id="imageLimitText">
    เลือกได้สูงสุด 6 รูป
</small>

            <div
                class="image-list"
                id="imageList"
            ></div>

        </div>


        <!-- Buttons -->

        <div class="button-row">

            <button
                type="button"
                class="ai-button"
                id="aiButton"
            >
                🤖 AI ช่วยเขียนข่าว
            </button>

            <button
                type="button"
                class="save-button"
                id="saveButton"
            >
                💾 บันทึกฉบับร่าง
            </button>

        </div>


        <!-- Progress -->

        <div
            class="progress"
            id="progress"
        >
            พร้อมใช้งาน
        </div>

    </div>


    <!-- =========================================================
         PREVIEW
         ========================================================= -->

    <div class="panel">

        <h2>👁️ ตัวอย่างจดหมายข่าว</h2>

        <div class="preview">

            <h1 id="previewHeadline">
                พาดหัวข่าวจะแสดงที่นี่
            </h1>


            <div class="meta">

                <span id="previewDate">
                    วันที่
                </span>

                ·

                <span id="previewLocation">
                    สถานที่
                </span>

                ·

                <span id="previewEdition">
                    ฉบับ
                </span>

            </div>


            <div
                class="summary"
                id="previewSummary"
            >
                สรุปข่าวจะแสดงที่นี่
            </div>


            <div
                class="body"
                id="previewBody"
            >
                เนื้อหาข่าวจะแสดงที่นี่
            </div>


            <div
                class="caption"
                id="previewImageCaption"
            >
                คำบรรยายภาพ
            </div>

        </div>

    </div>

</div>


<script>

const existingImages =
    <?= json_encode(
        $existingImages,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) ?>;

/*
|--------------------------------------------------------------------------
| DOM elements
|--------------------------------------------------------------------------
*/

const template =
    document.getElementById('template');

    /*
|--------------------------------------------------------------------------
| Template image limits
|--------------------------------------------------------------------------
*/

const imageLimits = {
    classic: 3,
    modern: 4,
    image_focus: 6
};

const imageLimitText =
    document.getElementById('imageLimitText');

/*
|--------------------------------------------------------------------------
| Template preview information
|--------------------------------------------------------------------------
*/

const templatePreviewName =
    document.getElementById(
        'templatePreviewName'
    );

const templatePreviewDescription =
    document.getElementById(
        'templatePreviewDescription'
    );

const templatePreviewLimit =
    document.getElementById(
        'templatePreviewLimit'
    );

const templatePreviewImage =
    document.getElementById(
        'templatePreviewImage'
    );


const templatePreviewData = {

    classic: {
        name: 'Classic',
        description:
            'รูปแบบหนังสือพิมพ์ เน้นพาดหัวและเนื้อหาข่าว',
        maxImages: 3
    },

    modern: {
        name: 'Modern',
        description:
            'รูปแบบทันสมัย เน้นพาดหัวขนาดใหญ่และภาพประกอบ',
        maxImages: 4
    },

    image_focus: {
        name: 'Image Focus',
        description:
            'รูปแบบเน้นภาพ เหมาะสำหรับข่าวที่มีรูปภาพหลายรูป',
        maxImages: 6
    }

};

/*
|--------------------------------------------------------------------------
| Update template preview
|--------------------------------------------------------------------------
*/

function updateTemplatePreview() {

    const selectedTemplate =
        templatePreviewData[
            template.value
        ];

    if (!selectedTemplate) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Update text
    |--------------------------------------------------------------------------
    */

    templatePreviewName.textContent =
        selectedTemplate.name;

    templatePreviewDescription.textContent =
        selectedTemplate.description;

    templatePreviewLimit.textContent =
    '🖼️ สูงสุด ' +
    selectedTemplate.maxImages +
    ' รูป';


    /*
    |--------------------------------------------------------------------------
    | Update visual style
    |--------------------------------------------------------------------------
    */

    templatePreviewImage.className =
        'template-preview-image';


    if (template.value === 'classic') {

        templatePreviewImage
            .querySelector('.preview-mockup')
            .className =
                'preview-mockup classic';

    }

    else if (template.value === 'modern') {

        templatePreviewImage
            .querySelector('.preview-mockup')
            .className =
                'preview-mockup modern';

    }

    else if (template.value === 'image_focus') {

        templatePreviewImage
            .querySelector('.preview-mockup')
            .className =
                'preview-mockup image-focus';

    }

}

const headline =
    document.getElementById('headline');

const summary =
    document.getElementById('summary');

const body =
    document.getElementById('body');

const imageCaption =
    document.getElementById('imageCaption');

const date =
    document.getElementById('date');

const locationInput =
    document.getElementById('location');

const edition =
    document.getElementById('edition');

const context =
    document.getElementById('context');

const aiButton =
    document.getElementById('aiButton');

const saveButton =
    document.getElementById('saveButton');

const progressElement =
    document.getElementById('progress');

const images =
    document.getElementById('images');

const imageList =
    document.getElementById('imageList');


/*
|--------------------------------------------------------------------------
| Current newsletter
|--------------------------------------------------------------------------
*/

let currentNewsletterId =
    <?= $newsletter
        ? (int) $newsletter['id']
        : 'null'
    ?>;


/*
|--------------------------------------------------------------------------
| Selected images
|--------------------------------------------------------------------------
*/

let selectedImages = [];

/*
|--------------------------------------------------------------------------
| Existing images marked for deletion
|--------------------------------------------------------------------------
*/

let deletedImageIds = [];

/*
|--------------------------------------------------------------------------
| Update image limit
|--------------------------------------------------------------------------
*/

function updateImageLimit() {

    const maxImages =
        imageLimits[template.value] || 6;

    imageLimitText.textContent =
        'เลือกได้สูงสุด ' +
        maxImages +
        ' รูป';
}

template.addEventListener(
    'change',
    updateImageLimit
);

template.addEventListener(
    'change',
    updateTemplatePreview
);

/*
|--------------------------------------------------------------------------
| Live preview elements
|--------------------------------------------------------------------------
*/

const previewHeadline =
    document.getElementById('previewHeadline');

const previewSummary =
    document.getElementById('previewSummary');

const previewBody =
    document.getElementById('previewBody');

const previewImageCaption =
    document.getElementById(
        'previewImageCaption'
    );

const previewDate =
    document.getElementById('previewDate');

const previewLocation =
    document.getElementById('previewLocation');

const previewEdition =
    document.getElementById('previewEdition');


/*
|--------------------------------------------------------------------------
| Update preview
|--------------------------------------------------------------------------
*/

function updatePreview() {

    previewHeadline.textContent =
        headline.value ||
        'พาดหัวข่าวจะแสดงที่นี่';

    previewSummary.textContent =
        summary.value ||
        'สรุปข่าวจะแสดงที่นี่';

    previewBody.textContent =
        body.value ||
        'เนื้อหาข่าวจะแสดงที่นี่';

    previewImageCaption.textContent =
        imageCaption.value ||
        'คำบรรยายภาพ';

    previewDate.textContent =
        date.value ||
        'วันที่';

    previewLocation.textContent =
        locationInput.value ||
        'สถานที่';

    previewEdition.textContent =
        edition.value ||
        'ฉบับ';
}


/*
|--------------------------------------------------------------------------
| Live preview events
|--------------------------------------------------------------------------
*/

[
    headline,
    summary,
    body,
    imageCaption,
    date,
    locationInput,
    edition
].forEach(element => {

    element.addEventListener(
        'input',
        updatePreview
    );

    element.addEventListener(
        'change',
        updatePreview
    );

});


/*
|--------------------------------------------------------------------------
| Image selection
|--------------------------------------------------------------------------
*/

images.addEventListener(
    'change',
    function () {

        const files =
            Array.from(images.files);

        /*
        |--------------------------------------------------------------------------
        | Maximum 6 total images
        |--------------------------------------------------------------------------
        */

        const existingCount =
            existingImages.filter(
                image =>
                    !deletedImageIds.includes(
                        Number(image.id)
                    )
            ).length;

        const totalCount =
    existingCount +
    files.length;

const maxImages =
    imageLimits[template.value] || 6;

if (totalCount > maxImages) {

    alert(
        'เทมเพลตนี้สามารถมีรูปภาพได้สูงสุด ' +
        maxImages +
        ' รูป'
    );

    images.value = '';

    selectedImages = [];

    return;
}


        /*
        |--------------------------------------------------------------------------
        | Store newly selected images
        |--------------------------------------------------------------------------
        */

        selectedImages =
            files;


        /*
        |--------------------------------------------------------------------------
        | Re-render image list
        |--------------------------------------------------------------------------
        */

        renderImageList();
    }
);


/*
|--------------------------------------------------------------------------
| Process SSE event
|--------------------------------------------------------------------------
*/

function processSseEvent(
    event,
    progressElement
) {

    const lines =
        event.split(/\r\n|\r|\n/);


    const dataLines =
        lines
            .filter(
                line =>
                    line.startsWith('data:')
            )
            .map(
                line =>
                    line
                        .substring(5)
                        .replace(/^ /, '')
            );


    if (dataLines.length === 0) {
        return;
    }


    const jsonText =
        dataLines.join('\n');


    let data;


    try {

        data =
            JSON.parse(jsonText);

    } catch (error) {

        console.warn(
            'Invalid SSE JSON:',
            jsonText
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    if (data.type === 'status') {

        progressElement.textContent =
            '🤖 ' + data.message;
    }


    /*
    |--------------------------------------------------------------------------
    | Progress
    |--------------------------------------------------------------------------
    */

    if (data.type === 'progress') {

        progressElement.textContent =
            '🤖 AI กำลังเขียนข่าว... ' +
            data.characters.toLocaleString() +
            ' ตัวอักษร';
    }


    /*
    |--------------------------------------------------------------------------
    | Complete
    |--------------------------------------------------------------------------
    */

    if (data.type === 'complete') {

        const generated =
            data.data;


        headline.value =
            generated.headline || '';


        summary.value =
            generated.summary || '';


        body.value =
            generated.body || '';


        imageCaption.value =
            generated.image_caption || '';


        updatePreview();


        progressElement.textContent =
            '✅ AI สร้างข่าวเรียบร้อยแล้ว';


        alert(
            'AI สร้างข่าวเรียบร้อยแล้ว!'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Error
    |--------------------------------------------------------------------------
    */

    if (data.type === 'error') {

        throw new Error(
            data.error ||
            'ไม่สามารถสร้างข่าวได้'
        );
    }

}


/*
|--------------------------------------------------------------------------
| AI generation
|--------------------------------------------------------------------------
*/

aiButton.addEventListener(
    'click',
    async function () {

        if (
            !template.value ||
            !context.value.trim()
        ) {

            alert(
                'กรุณาเลือก Template และใส่ข้อมูลข่าวก่อน'
            );

            return;
        }


        aiButton.disabled = true;

        saveButton.disabled = true;


        progressElement.textContent =
            '🤖 กำลังเตรียมข้อมูล...';


        try {

            const response =
                await fetch(
                    'api/generate.php',
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json'
                        },

                        body: JSON.stringify({

                            template:
                                template.value,

                            headline:
                                headline.value,

                            date:
                                date.value,

                            location:
                                locationInput.value,

                            edition:
                                edition.value,

                            context:
                                context.value

                        })
                    }
                );


            if (!response.ok) {

                throw new Error(
                    'HTTP ' +
                    response.status
                );
            }


            if (!response.body) {

                throw new Error(
                    'Browser ไม่รองรับ streaming response'
                );
            }


            const reader =
                response.body.getReader();


            const decoder =
                new TextDecoder(
                    'utf-8'
                );


            let buffer = '';


            while (true) {

                const {
                    value,
                    done
                } =
                    await reader.read();


                if (done) {
                    break;
                }


                buffer +=
                    decoder.decode(
                        value,
                        {
                            stream: true
                        }
                    );


                while (true) {

                    const match =
                        buffer.match(
                            /\r\n\r\n|\n\n|\r\r/
                        );


                    if (!match) {
                        break;
                    }


                    const event =
                        buffer.substring(
                            0,
                            match.index
                        );


                    buffer =
                        buffer.substring(
                            match.index +
                            match[0].length
                        );


                    processSseEvent(
                        event,
                        progressElement
                    );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Flush remaining UTF-8 data
            |--------------------------------------------------------------------------
            */

            buffer +=
                decoder.decode();


            /*
            |--------------------------------------------------------------------------
            | Process final SSE event
            |--------------------------------------------------------------------------
            */

            if (buffer.trim() !== '') {

                processSseEvent(
                    buffer,
                    progressElement
                );
            }


        } catch (error) {

            console.error(error);


            progressElement.textContent =
                '❌ เกิดข้อผิดพลาด: ' +
                error.message;


            alert(
                'ไม่สามารถสร้างข่าวได้:\n' +
                error.message
            );

        } finally {

            aiButton.disabled = false;

            saveButton.disabled = false;

        }

    }
);


/*
|--------------------------------------------------------------------------
| Save draft
|--------------------------------------------------------------------------
*/

saveButton.addEventListener(
    'click',
    async function () {

        /*
        |--------------------------------------------------------------------------
        | Basic validation
        |--------------------------------------------------------------------------
        */

        if (!template.value) {

            alert(
                'กรุณาเลือก Template ก่อนบันทึก'
            );

            return;
        }


        if (
            !headline.value.trim() &&
            !body.value.trim()
        ) {

            alert(
                'กรุณาใส่เนื้อหาข่าวก่อนบันทึก'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate requests
        |--------------------------------------------------------------------------
        */

        saveButton.disabled = true;

        aiButton.disabled = true;


        progressElement.textContent =
            '💾 กำลังบันทึกฉบับร่าง...';


        try {

/*
|--------------------------------------------------------------------------
| Prepare FormData
|--------------------------------------------------------------------------
*/

const formData = new FormData();


/*
|--------------------------------------------------------------------------
| Add text fields
|--------------------------------------------------------------------------
*/

formData.append(
    'template',
    template.value
);

formData.append(
    'headline',
    headline.value
);

formData.append(
    'summary',
    summary.value
);

formData.append(
    'body',
    body.value
);

formData.append(
    'image_caption',
    imageCaption.value
);

formData.append(
    'date',
    date.value
);

formData.append(
    'location',
    locationInput.value
);

formData.append(
    'edition',
    edition.value
);

/*
|--------------------------------------------------------------------------
| Add deleted image IDs
|--------------------------------------------------------------------------
*/

deletedImageIds.forEach(
    function (imageId) {

        formData.append(
            'deleted_images[]',
            imageId
        );

    }
);

if (currentNewsletterId !== null) {
    formData.append(
        'newsletter_id',
        currentNewsletterId
    );
}


/*
|--------------------------------------------------------------------------
| Add images
|--------------------------------------------------------------------------
*/

selectedImages.forEach(
    function (file) {

        formData.append(
            'images[]',
            file
        );

    }
);


/*
|--------------------------------------------------------------------------
| Send request
|--------------------------------------------------------------------------
*/

const response =
    await fetch(
        'api/save.php',
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
| Handle API error
|--------------------------------------------------------------------------
*/

if (
    !response.ok ||
    !data.success
) {

    throw new Error(
        data.error ||
        'ไม่สามารถบันทึกฉบับร่างได้'
    );
}


/*
|--------------------------------------------------------------------------
| Store current newsletter ID
|--------------------------------------------------------------------------
*/

currentNewsletterId =
    data.newsletter_id;


/*
|--------------------------------------------------------------------------
| Success
|--------------------------------------------------------------------------
*/

progressElement.textContent =
    '✅ บันทึกฉบับร่างเรียบร้อยแล้ว ' +
    '(ID: ' +
    currentNewsletterId +
    ', รูปภาพ: ' +
    data.image_count +
    ' รูป)';


alert(
    'บันทึกฉบับร่างเรียบร้อยแล้ว!\n\n' +
    'Newsletter ID: ' +
    currentNewsletterId +
    '\n' +
    'รูปภาพ: ' +
    data.image_count +
    ' รูป'
);



        } catch (error) {

            console.error(error);


            progressElement.textContent =
                '❌ บันทึกไม่สำเร็จ: ' +
                error.message;


            alert(
                'ไม่สามารถบันทึกฉบับร่างได้:\n' +
                error.message
            );


        } finally {

            saveButton.disabled = false;

            aiButton.disabled = false;

        }

    }
);

function renderExistingImages() {

    existingImages.forEach(
        function (image) {

            /*
            |--------------------------------------------------------------------------
            | Skip images already marked for deletion
            |--------------------------------------------------------------------------
            */

            if (
                deletedImageIds.includes(
                    Number(image.id)
                )
            ) {
                return;
            }


            const item =
                document.createElement('div');

            item.className =
                'image-item existing-image';


            /*
            |--------------------------------------------------------------------------
            | Image
            |--------------------------------------------------------------------------
            */

            const img =
                document.createElement('img');

            img.src =
                'storage/images/' +
                currentNewsletterId +
                '/' +
                encodeURIComponent(
                    image.filename
                );

            img.alt =
                image.original_filename ||
                'Newsletter image';


            /*
            |--------------------------------------------------------------------------
            | Filename
            |--------------------------------------------------------------------------
            */

            const name =
                document.createElement('span');

            name.textContent =
                image.original_filename ||
                image.filename;


            /*
            |--------------------------------------------------------------------------
            | Remove button
            |--------------------------------------------------------------------------
            */

            const removeButton =
                document.createElement('button');

            removeButton.type =
                'button';

            removeButton.textContent =
                '🗑️ ลบ';

            removeButton.className =
                'remove-image-button';


            removeButton.addEventListener(
                'click',
                function () {

                    const confirmed =
                        confirm(
                            'ต้องการลบรูปภาพนี้หรือไม่?'
                        );

                    if (!confirmed) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Mark image for deletion
                    |--------------------------------------------------------------------------
                    */

                    deletedImageIds.push(
                        Number(image.id)
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Re-render images
                    |--------------------------------------------------------------------------
                    */

                    renderImageList();

                }
            );


            item.appendChild(img);

            item.appendChild(name);

            item.appendChild(removeButton);

            imageList.appendChild(item);
        }
    );
}

function renderSelectedImages() {

    selectedImages.forEach(
        function (file) {

            const item =
                document.createElement('div');

            item.className =
                'image-item new-image';

            const img =
                document.createElement('img');

            img.src =
                URL.createObjectURL(file);

            img.alt =
                file.name;

            const name =
                document.createElement('span');

            name.textContent =
                file.name;

            item.appendChild(img);
            item.appendChild(name);

            imageList.appendChild(item);
        }
    );
}

function renderImageList() {

    imageList.innerHTML = '';

    renderExistingImages();

    renderSelectedImages();
}

/*
|--------------------------------------------------------------------------
| Initial preview
|--------------------------------------------------------------------------
*/

updatePreview();

updateImageLimit();

updateTemplatePreview();

renderImageList();
</script>

</body>

</html>
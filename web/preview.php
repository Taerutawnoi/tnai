<?php

require_once __DIR__ . '/inc/setup.php';

$status = checkSystem();

if (!$status['ready']) {
    die('System is not ready.');
}

$db = getDatabase();


/*
|--------------------------------------------------------------------------
| Get newsletter ID
|--------------------------------------------------------------------------
*/

$newsletterId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($newsletterId <= 0) {
    die('ไม่พบฉบับข่าวที่ต้องการดู');
}


/*
|--------------------------------------------------------------------------
| Load newsletter
|--------------------------------------------------------------------------
*/

$stmt = $db->prepare("
    SELECT
        n.id,
        n.template_id,
        n.headline,
        n.summary,
        n.body,
        n.image_caption,
        n.edition,
        n.publish_date,
        n.location,
        n.status,
        t.template_key
    FROM newsletters n
    LEFT JOIN templates t
        ON t.id = n.template_id
    WHERE n.id = ?
    LIMIT 1
");

if (!$stmt) {
    die('ไม่สามารถเตรียมคำสั่ง SQL ได้');
}


$stmt->bind_param(
    'i',
    $newsletterId
);

$stmt->execute();


$result = $stmt->get_result();

$newsletter = $result->fetch_assoc();


$stmt->close();


if (!$newsletter) {
    die('ไม่พบฉบับข่าวที่ต้องการดู');
}


/*
|--------------------------------------------------------------------------
| Load newsletter images
|--------------------------------------------------------------------------
*/

$images = [];


$stmt = $db->prepare("
    SELECT
        id,
        filename,
        image_order
    FROM newsletter_images
    WHERE newsletter_id = ?
    ORDER BY image_order ASC, id ASC
");


if ($stmt) {

    $stmt->bind_param(
        'i',
        $newsletterId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }

    $stmt->close();
}

$basePath = rtrim(
    dirname($_SERVER['SCRIPT_NAME']),
    '/'
);


$templateKey =
    $newsletter['template_key']
    ?? 'classic';

switch ($templateKey) {

    case 'classic':
        require __DIR__ . '/templates/classic.php';
        break;

    case 'modern':
        require __DIR__ . '/templates/modern.php';
        break;

    case 'image_focus':
        require __DIR__ . '/templates/image_focus.php';
        break;

    default:
        die(
            'ยังไม่มี template สำหรับ: ' .
            htmlspecialchars($templateKey)
        );
}
exit;
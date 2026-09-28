<?php

require_once __DIR__ . '/../inc/setup.php';
require_once __DIR__ . '/../inc/db.php';

header('Content-Type: application/json; charset=utf-8');

try {

    /*
    |--------------------------------------------------------------------------
    | Request method
    |--------------------------------------------------------------------------
    */

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Check database
    |--------------------------------------------------------------------------
    */

    if (!checkDatabase()) {

        http_response_code(503);

        echo json_encode([
            'success' => false,
            'error' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Get database connection
    |--------------------------------------------------------------------------
    */

    $db = getDatabase();


    /*
    |--------------------------------------------------------------------------
    | Get input values
    |--------------------------------------------------------------------------
    */

    $templateKey =
        trim($_POST['template'] ?? '');

    $headline =
        trim($_POST['headline'] ?? '');

    $summary =
        trim($_POST['summary'] ?? '');

    $body =
        trim($_POST['body'] ?? '');

    $imageCaption =
        trim($_POST['image_caption'] ?? '');

    $publishDate =
        trim($_POST['date'] ?? '');

    $location =
        trim($_POST['location'] ?? '');

    $edition =
        trim($_POST['edition'] ?? '');

    $newsletterId = isset($_POST['newsletter_id'])
    ? (int) $_POST['newsletter_id']
    : 0;

    /*
|--------------------------------------------------------------------------
| Deleted image IDs
|--------------------------------------------------------------------------
*/

$deletedImageIds = [];

if (
    isset($_POST['deleted_images']) &&
    is_array($_POST['deleted_images'])
) {

    foreach (
        $_POST['deleted_images']
        as $imageId
    ) {

        $imageId =
            (int) $imageId;

        if ($imageId > 0) {

            $deletedImageIds[] =
                $imageId;
        }
    }
}

    /*
    |--------------------------------------------------------------------------
    | Validate template
    |--------------------------------------------------------------------------
    */

    if ($templateKey === '') {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'error' => 'กรุณาเลือก Template'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate article content
    |--------------------------------------------------------------------------
    */

    if ($headline === '' && $body === '') {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'error' => 'กรุณาใส่เนื้อหาข่าวก่อนบันทึก'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate date
    |--------------------------------------------------------------------------
    */

    if ($publishDate !== '') {

        $dateObject =
            DateTime::createFromFormat(
                'Y-m-d',
                $publishDate
            );

        if (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $publishDate
        ) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'error' => 'รูปแบบวันที่ไม่ถูกต้อง'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate images
    |--------------------------------------------------------------------------
    */

    $uploadedImages = [];

    if (isset($_FILES['images'])) {

        $files = $_FILES['images'];

        /*
        |--------------------------------------------------------------------------
        | Check upload error
        |--------------------------------------------------------------------------
        */

        if (!is_array($files['error'])) {

            throw new RuntimeException(
                'รูปแบบข้อมูลรูปภาพไม่ถูกต้อง'
            );
        }


/*
|--------------------------------------------------------------------------
| Template-specific image limit
|--------------------------------------------------------------------------
*/

$fileCount =
    count($files['name']);

$imageLimits = [
    'classic'     => 3,
    'modern'      => 4,
    'image_focus' => 6
];

$maxImages =
    $imageLimits[$templateKey] ?? 6;


/*
|--------------------------------------------------------------------------
| Count existing images
|--------------------------------------------------------------------------
*/

$existingImageCount = 0;

if ($newsletterId > 0) {

    $countStmt =
        $db->prepare("
            SELECT COUNT(*)
            AS image_count
            FROM newsletter_images
            WHERE newsletter_id = ?
        ");

    if (!$countStmt) {

        throw new RuntimeException(
            'Failed to prepare image count query: ' .
            $db->error
        );
    }

    $countStmt->bind_param(
        'i',
        $newsletterId
    );

    if (!$countStmt->execute()) {

        throw new RuntimeException(
            'Failed to count existing images: ' .
            $countStmt->error
        );
    }

    $countResult =
        $countStmt->get_result();

    $countRow =
        $countResult->fetch_assoc();

    $existingImageCount =
        (int) $countRow['image_count'];

    $countStmt->close();
}


/*
|--------------------------------------------------------------------------
| Exclude images marked for deletion
|--------------------------------------------------------------------------
*/

$deletedExistingCount = 0;

if (!empty($deletedImageIds)) {

    $deletedExistingCount =
        count($deletedImageIds);
}


/*
|--------------------------------------------------------------------------
| Calculate final image count
|--------------------------------------------------------------------------
*/

$finalImageCount =
    $existingImageCount -
    $deletedExistingCount +
    $fileCount;


if ($finalImageCount > $maxImages) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error' =>
            'เทมเพลตนี้สามารถมีรูปภาพได้สูงสุด ' .
            $maxImages .
            ' รูป'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


        /*
        |--------------------------------------------------------------------------
        | Allowed MIME types
        |--------------------------------------------------------------------------
        */

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif'
        ];


        /*
        |--------------------------------------------------------------------------
        | Maximum file size
        |--------------------------------------------------------------------------
        */

        $maxFileSize =
            10 * 1024 * 1024;


        /*
        |--------------------------------------------------------------------------
        | Validate every image
        |--------------------------------------------------------------------------
        */

        $finfo =
            new finfo(FILEINFO_MIME_TYPE);


        for (
            $i = 0;
            $i < $fileCount;
            $i++
        ) {

            /*
            |--------------------------------------------------------------------------
            | Skip empty file entries
            |--------------------------------------------------------------------------
            */

            if (
                $files['error'][$i] ===
                UPLOAD_ERR_NO_FILE
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Check upload error
            |--------------------------------------------------------------------------
            */

            if (
                $files['error'][$i] !==
                UPLOAD_ERR_OK
            ) {

                throw new RuntimeException(
                    'เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Check file size
            |--------------------------------------------------------------------------
            */

            if (
                $files['size'][$i] >
                $maxFileSize
            ) {

                throw new RuntimeException(
                    'รูปภาพแต่ละไฟล์ต้องมีขนาดไม่เกิน 10 MB'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Detect MIME type
            |--------------------------------------------------------------------------
            */

            $tmpName =
                $files['tmp_name'][$i];

            $mimeType =
                $finfo->file($tmpName);


            if (
                !isset(
                    $allowedTypes[$mimeType]
                )
            ) {

                throw new RuntimeException(
                    'พบไฟล์รูปภาพที่ไม่รองรับ'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Store validated image information
            |--------------------------------------------------------------------------
            */

            $uploadedImages[] = [

                'tmp_name' =>
                    $tmpName,

                'original_name' =>
                    $files['name'][$i],

                'extension' =>
                    $allowedTypes[$mimeType],

                'image_order' =>
                    $i

            ];
        }
    }


    


    /*
    |--------------------------------------------------------------------------
    | Find template
    |--------------------------------------------------------------------------
    */

    $templateStmt =
        $db->prepare("
            SELECT id
            FROM templates
            WHERE template_key = ?
            LIMIT 1
        ");


    if (!$templateStmt) {

        throw new RuntimeException(
            'Failed to prepare template query: ' .
            $db->error
        );
    }


    $templateStmt->bind_param(
        's',
        $templateKey
    );


    if (!$templateStmt->execute()) {

        throw new RuntimeException(
            'Failed to find template: ' .
            $templateStmt->error
        );
    }


    $templateResult =
        $templateStmt->get_result();


    $template =
        $templateResult->fetch_assoc();


    $templateStmt->close();


    if (!$template) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'error' => 'ไม่พบ Template ที่เลือก'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $templateId =
        (int) $template['id'];


    /*
    |--------------------------------------------------------------------------
    | Begin transaction
    |--------------------------------------------------------------------------
    */

    $db->begin_transaction();


    try {

        /*
        |--------------------------------------------------------------------------
        | Insert newsletter
        |--------------------------------------------------------------------------
        */

/*
|--------------------------------------------------------------------------
| Insert or update newsletter
|--------------------------------------------------------------------------
*/

if ($newsletterId > 0) {

    // Check that the newsletter exists
    $checkStmt = $db->prepare("
        SELECT id
        FROM newsletters
        WHERE id = ?
        LIMIT 1
    ");

    if (!$checkStmt) {
        throw new RuntimeException(
            'Failed to prepare newsletter check: ' .
            $db->error
        );
    }

    $checkStmt->bind_param(
        'i',
        $newsletterId
    );

    if (!$checkStmt->execute()) {
        throw new RuntimeException(
            'Failed to check newsletter: ' .
            $checkStmt->error
        );
    }

    $checkResult =
        $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        throw new RuntimeException(
            'ไม่พบฉบับข่าวที่ต้องการแก้ไข'
        );
    }

    $checkStmt->close();


    // Update existing newsletter
    $stmt = $db->prepare("
        UPDATE newsletters
        SET
            template_id = ?,
            headline = ?,
            summary = ?,
            body = ?,
            image_caption = ?,
            publish_date = ?,
            location = ?,
            edition = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        throw new RuntimeException(
            'Failed to prepare newsletter update: ' .
            $db->error
        );
    }

    $stmt->bind_param(
    'isssssssi',
    $templateId,
    $headline,
    $summary,
    $body,
    $imageCaption,
    $publishDate,
    $location,
    $edition,
    $newsletterId
);

    if (!$stmt->execute()) {
        throw new RuntimeException(
            'Failed to update newsletter: ' .
            $stmt->error
        );
    }

    $stmt->close();

} else {

    // Create a new newsletter
    $stmt = $db->prepare("
        INSERT INTO newsletters (
            template_id,
            headline,
            summary,
            body,
            image_caption,
            publish_date,
            location,
            edition
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new RuntimeException(
            'Failed to prepare newsletter insert: ' .
            $db->error
        );
    }

    $stmt->bind_param(
        'isssssss',
        $templateId,
        $headline,
        $summary,
        $body,
        $imageCaption,
        $publishDate,
        $location,
        $edition
    );

    if (!$stmt->execute()) {
        throw new RuntimeException(
            'Failed to save newsletter: ' .
            $stmt->error
        );
    }

    $newsletterId =
        $db->insert_id;

    $stmt->close();
}


        /*
        |--------------------------------------------------------------------------
        | Create newsletter image directory
        |--------------------------------------------------------------------------
        */

        $imageDirectory =
            __DIR__ .
            '/../storage/images/' .
            $newsletterId;
        /*
|--------------------------------------------------------------------------
| Determine next image order
|--------------------------------------------------------------------------
*/

$nextImageOrder = 0;

if ($newsletterId > 0) {

    $orderStmt = $db->prepare("
        SELECT
            COALESCE(
                MAX(image_order) + 1,
                0
            ) AS next_order
        FROM newsletter_images
        WHERE newsletter_id = ?
    ");

    if (!$orderStmt) {

        throw new RuntimeException(
            'Failed to prepare image order query: ' .
            $db->error
        );
    }

    $orderStmt->bind_param(
        'i',
        $newsletterId
    );

    if (!$orderStmt->execute()) {

        throw new RuntimeException(
            'Failed to determine image order: ' .
            $orderStmt->error
        );
    }

    $orderResult =
        $orderStmt->get_result();

    $orderRow =
        $orderResult->fetch_assoc();

    $nextImageOrder =
        (int) $orderRow['next_order'];

    $orderStmt->close();
}

        if (
            !is_dir($imageDirectory) &&
            !mkdir(
                $imageDirectory,
                0755,
                true
            )
        ) {

            throw new RuntimeException(
                'ไม่สามารถสร้างโฟลเดอร์เก็บรูปภาพได้'
            );
        }

        /*
|--------------------------------------------------------------------------
| Delete existing images
|--------------------------------------------------------------------------
*/

foreach (
    $deletedImageIds
    as $imageId
) {

    /*
    |--------------------------------------------------------------------------
    | Find image information
    |--------------------------------------------------------------------------
    */

    $deleteStmt =
        $db->prepare("
            SELECT
                filename
            FROM newsletter_images
            WHERE id = ?
              AND newsletter_id = ?
            LIMIT 1
        ");

    if (!$deleteStmt) {

        throw new RuntimeException(
            'Failed to prepare image deletion query: ' .
            $db->error
        );
    }


    $deleteStmt->bind_param(
        'ii',
        $imageId,
        $newsletterId
    );


    if (!$deleteStmt->execute()) {

        throw new RuntimeException(
            'Failed to find image for deletion: ' .
            $deleteStmt->error
        );
    }


    $deleteResult =
        $deleteStmt->get_result();


    $imageToDelete =
        $deleteResult->fetch_assoc();


    $deleteStmt->close();


    /*
    |--------------------------------------------------------------------------
    | Delete physical file
    |--------------------------------------------------------------------------
    */

    if ($imageToDelete) {

        $imagePath =
            $imageDirectory .
            '/' .
            $imageToDelete['filename'];


        if (
            is_file($imagePath) &&
            !unlink($imagePath)
        ) {

            throw new RuntimeException(
                'ไม่สามารถลบไฟล์รูปภาพได้'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Delete database record
        |--------------------------------------------------------------------------
        */

        $deleteRecordStmt =
            $db->prepare("
                DELETE FROM newsletter_images
                WHERE id = ?
                  AND newsletter_id = ?
            ");

        if (!$deleteRecordStmt) {

            throw new RuntimeException(
                'Failed to prepare image record deletion: ' .
                $db->error
            );
        }


        $deleteRecordStmt->bind_param(
            'ii',
            $imageId,
            $newsletterId
        );


        if (
            !$deleteRecordStmt->execute()
        ) {

            throw new RuntimeException(
                'Failed to delete image record: ' .
                $deleteRecordStmt->error
            );
        }


        $deleteRecordStmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Reorder remaining images
|--------------------------------------------------------------------------
*/

$reorderStmt =
    $db->prepare("
        SELECT
            id
        FROM newsletter_images
        WHERE newsletter_id = ?
        ORDER BY image_order ASC, id ASC
    ");

if (!$reorderStmt) {

    throw new RuntimeException(
        'Failed to prepare image reorder query: ' .
        $db->error
    );
}

$reorderStmt->bind_param(
    'i',
    $newsletterId
);

if (!$reorderStmt->execute()) {

    throw new RuntimeException(
        'Failed to load images for reorder: ' .
        $reorderStmt->error
    );
}

$reorderResult =
    $reorderStmt->get_result();

$reorderIds = [];

while (
    $row =
    $reorderResult->fetch_assoc()
) {

    $reorderIds[] =
        (int) $row['id'];
}

$reorderStmt->close();


$updateOrderStmt =
    $db->prepare("
        UPDATE newsletter_images
        SET image_order = ?
        WHERE id = ?
          AND newsletter_id = ?
    ");

if (!$updateOrderStmt) {

    throw new RuntimeException(
        'Failed to prepare image reorder update: ' .
        $db->error
    );
}


foreach (
    $reorderIds
    as $newOrder => $imageId
) {

    $updateOrderStmt->bind_param(
        'iii',
        $newOrder,
        $imageId,
        $newsletterId
    );

    if (
        !$updateOrderStmt->execute()
    ) {

        throw new RuntimeException(
            'Failed to reorder images: ' .
            $updateOrderStmt->error
        );
    }
}

$updateOrderStmt->close();

        /*
        |--------------------------------------------------------------------------
        | Insert and move images
        |--------------------------------------------------------------------------
        */

        $imageStmt =
            $db->prepare("
                INSERT INTO newsletter_images (
                    newsletter_id,
                    filename,
                    original_filename,
                    image_order
                )
                VALUES (?, ?, ?, ?)
            ");


        if (!$imageStmt) {

            throw new RuntimeException(
                'Failed to prepare image query: ' .
                $db->error
            );
        }


        foreach (
            $uploadedImages
            as $image
        ) {

            /*
            |--------------------------------------------------------------------------
            | Generate random filename
            |--------------------------------------------------------------------------
            */

            $filename =
                bin2hex(
                    random_bytes(16)
                ) .
                '.' .
                $image['extension'];


            $destination =
                $imageDirectory .
                '/' .
                $filename;


            /*
            |--------------------------------------------------------------------------
            | Move uploaded file
            |--------------------------------------------------------------------------
            */

            if (
                !move_uploaded_file(
                    $image['tmp_name'],
                    $destination
                )
            ) {

                throw new RuntimeException(
                    'ไม่สามารถบันทึกรูปภาพได้'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Store database record
            |--------------------------------------------------------------------------
            */

if ($newsletterId > 0) {

    $imageOrder =
        $nextImageOrder;

    $nextImageOrder++;

} else {

    $imageOrder =
        (int) $image['image_order'];
}


            $originalName =
                $image['original_name'];


            $imageStmt->bind_param(
                'issi',
                $newsletterId,
                $filename,
                $originalName,
                $imageOrder
            );


            if (!$imageStmt->execute()) {

                throw new RuntimeException(
                    'ไม่สามารถบันทึกข้อมูลรูปภาพได้: ' .
                    $imageStmt->error
                );
            }
        }


        $imageStmt->close();


        /*
        |--------------------------------------------------------------------------
        | Commit transaction
        |--------------------------------------------------------------------------
        */

        $db->commit();


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        echo json_encode([
            'success' => true,
            'newsletter_id' => $newsletterId,
            'image_count' => count($uploadedImages),
            'message' =>
                'บันทึกฉบับร่างเรียบร้อยแล้ว'
        ], JSON_UNESCAPED_UNICODE);


    } catch (Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Rollback
        |--------------------------------------------------------------------------
        */

        $db->rollback();

        throw $e;
    }


} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | Unexpected error
    |--------------------------------------------------------------------------
    */

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
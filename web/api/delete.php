<?php

require_once __DIR__ . '/../inc/setup.php';
require_once __DIR__ . '/../inc/db.php';

header(
    'Content-Type: application/json; charset=utf-8'
);


try {

    /*
    |--------------------------------------------------------------------------
    | Request method
    |--------------------------------------------------------------------------
    */

    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST'
    ) {

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
            'error' =>
                'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Get newsletter ID
    |--------------------------------------------------------------------------
    */

    $newsletterId =
        isset($_POST['newsletter_id'])
            ? (int) $_POST['newsletter_id']
            : 0;


    /*
    |--------------------------------------------------------------------------
    | Validate newsletter ID
    |--------------------------------------------------------------------------
    */

    if ($newsletterId <= 0) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'error' =>
                'ไม่พบ Newsletter ID ที่ต้องการลบ'
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
    | Begin transaction
    |--------------------------------------------------------------------------
    */

    $db->begin_transaction();


    try {

        /*
        |--------------------------------------------------------------------------
        | Check newsletter exists
        |--------------------------------------------------------------------------
        */

        $checkStmt =
            $db->prepare("
                SELECT
                    id
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


        if (
            !$checkStmt->execute()
        ) {

            throw new RuntimeException(
                'Failed to check newsletter: ' .
                $checkStmt->error
            );
        }


        $checkResult =
            $checkStmt->get_result();


        if (
            $checkResult->num_rows === 0
        ) {

            $checkStmt->close();

            http_response_code(404);

            $db->rollback();

            echo json_encode([
                'success' => false,
                'error' =>
                    'ไม่พบฉบับข่าวที่ต้องการลบ'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        $checkStmt->close();


        /*
        |--------------------------------------------------------------------------
        | Find newsletter images
        |--------------------------------------------------------------------------
        */

        $imageStmt =
            $db->prepare("
                SELECT
                    filename
                FROM newsletter_images
                WHERE newsletter_id = ?
            ");


        if (!$imageStmt) {

            throw new RuntimeException(
                'Failed to prepare image query: ' .
                $db->error
            );
        }


        $imageStmt->bind_param(
            'i',
            $newsletterId
        );


        if (
            !$imageStmt->execute()
        ) {

            throw new RuntimeException(
                'Failed to load newsletter images: ' .
                $imageStmt->error
            );
        }


        $imageResult =
            $imageStmt->get_result();


        $imageFiles = [];


        while (
            $image =
            $imageResult->fetch_assoc()
        ) {

            $imageFiles[] =
                $image['filename'];
        }


        $imageStmt->close();


        /*
        |--------------------------------------------------------------------------
        | Delete image database records
        |--------------------------------------------------------------------------
        */

        $deleteImagesStmt =
            $db->prepare("
                DELETE FROM newsletter_images
                WHERE newsletter_id = ?
            ");


        if (!$deleteImagesStmt) {

            throw new RuntimeException(
                'Failed to prepare image deletion: ' .
                $db->error
            );
        }


        $deleteImagesStmt->bind_param(
            'i',
            $newsletterId
        );


        if (
            !$deleteImagesStmt->execute()
        ) {

            throw new RuntimeException(
                'Failed to delete newsletter images: ' .
                $deleteImagesStmt->error
            );
        }


        $deleteImagesStmt->close();


        /*
        |--------------------------------------------------------------------------
        | Delete newsletter
        |--------------------------------------------------------------------------
        */

        $deleteNewsletterStmt =
            $db->prepare("
                DELETE FROM newsletters
                WHERE id = ?
            ");


        if (!$deleteNewsletterStmt) {

            throw new RuntimeException(
                'Failed to prepare newsletter deletion: ' .
                $db->error
            );
        }


        $deleteNewsletterStmt->bind_param(
            'i',
            $newsletterId
        );


        if (
            !$deleteNewsletterStmt->execute()
        ) {

            throw new RuntimeException(
                'Failed to delete newsletter: ' .
                $deleteNewsletterStmt->error
            );
        }


        $deleteNewsletterStmt->close();


        /*
        |--------------------------------------------------------------------------
        | Commit database changes
        |--------------------------------------------------------------------------
        */

        $db->commit();


        /*
        |--------------------------------------------------------------------------
        | Delete physical image files
        |--------------------------------------------------------------------------
        */

        $imageDirectory =
            __DIR__ .
            '/../storage/images/' .
            $newsletterId;


        foreach (
            $imageFiles
            as $filename
        ) {

            $imagePath =
                $imageDirectory .
                '/' .
                $filename;


            if (
                is_file($imagePath)
            ) {

                if (
                    !unlink($imagePath)
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Database deletion already succeeded.
                    | Do not report the whole operation as failed.
                    |--------------------------------------------------------------------------
                    */

                    error_log(
                        'Failed to delete image file: ' .
                        $imagePath
                    );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Remove newsletter image directory
        |--------------------------------------------------------------------------
        */

        if (
            is_dir($imageDirectory)
        ) {

            /*
            |--------------------------------------------------------------------------
            | Remove directory only if empty
            |--------------------------------------------------------------------------
            */

            @rmdir(
                $imageDirectory
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        echo json_encode([
            'success' => true,
            'newsletter_id' =>
                $newsletterId,
            'message' =>
                'ลบฉบับข่าวเรียบร้อยแล้ว'
        ], JSON_UNESCAPED_UNICODE);


    } catch (Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Rollback database changes
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
        'error' =>
            $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/ollama.php';


/*
|--------------------------------------------------------------------------
| Basic System Checks
|--------------------------------------------------------------------------
*/

function checkDatabase(): bool
{
    try {

        getDatabase();

        return true;

    } catch (Throwable $e) {

        return false;
    }
}


function checkOllama(): bool
{
    try {

        ollamaRequest('/api/tags');

        return true;

    } catch (Throwable $e) {

        return false;
    }
}


function checkAIModel(): bool
{
    try {

        return ollamaModelExists(AI_MODEL);

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Actual AI Test
|
| This is intentionally NOT used during normal page loading.
|--------------------------------------------------------------------------
*/

function testAIModel(): bool
{
    try {

        $response =
            ollamaGenerate(
                AI_MODEL,
                'ตอบว่า "พร้อมใช้งาน" เท่านั้น'
            );

        return trim($response) !== '';

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Lightweight System Check
|
| Used by normal pages such as:
| - editor.php
| - drafts.php
|
| IMPORTANT:
| This does NOT generate text with the LLM.
|--------------------------------------------------------------------------
*/

function checkSystem(): array
{
    $status = [
        'database' => false,
        'ollama' => false,
        'model' => false,
        'ready' => false
    ];


    /*
    |--------------------------------------------------------------------------
    | Check Database
    |--------------------------------------------------------------------------
    */

    $status['database'] =
        checkDatabase();

    if (!$status['database']) {

        return $status;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Ollama API
    |--------------------------------------------------------------------------
    */

    $status['ollama'] =
        checkOllama();

    if (!$status['ollama']) {

        return $status;
    }


    /*
    |--------------------------------------------------------------------------
    | Check AI Model
    |--------------------------------------------------------------------------
    */

    $status['model'] =
        checkAIModel();

    if (!$status['model']) {

        return $status;
    }


    /*
    |--------------------------------------------------------------------------
    | Everything required is available
    |--------------------------------------------------------------------------
    */

    $status['ready'] = true;


    return $status;
}


/*
|--------------------------------------------------------------------------
| AI Model Setup
|
| Used only during initial setup.
|--------------------------------------------------------------------------
*/

function setupAIModel(): bool
{
    /*
    |--------------------------------------------------------------------------
    | Model already installed
    |--------------------------------------------------------------------------
    */

    if (checkAIModel()) {

        /*
        |--------------------------------------------------------------------------
        | Test the actual AI only during setup.
        |--------------------------------------------------------------------------
        */

        return testAIModel();
    }


    /*
    |--------------------------------------------------------------------------
    | Model does not exist → download it.
    |--------------------------------------------------------------------------
    */

    try {

        ollamaPullModel(
            AI_MODEL
        );

        /*
        |--------------------------------------------------------------------------
        | Test the AI after downloading.
        |--------------------------------------------------------------------------
        */

        return testAIModel();

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Database Setup
|--------------------------------------------------------------------------
*/

function setupDatabase(): bool
{
    try {

        $db =
            getDatabase();


        $queries = [

            "
            CREATE TABLE IF NOT EXISTS templates (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT NULL,
                template_key VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",

            "
            CREATE TABLE IF NOT EXISTS newsletters (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                template_id INT UNSIGNED NOT NULL,
                headline VARCHAR(255) NULL,
                summary TEXT NULL,
                body TEXT NULL,
                image_caption TEXT NULL,
                edition VARCHAR(100) NULL,
                publish_date DATE NULL,
                location VARCHAR(255) NULL,
                status ENUM('draft', 'published') DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,

                CONSTRAINT fk_newsletters_template
                    FOREIGN KEY (template_id)
                    REFERENCES templates(id)
                    ON DELETE RESTRICT
                    ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",

            "
            CREATE TABLE IF NOT EXISTS newsletter_images (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                newsletter_id INT UNSIGNED NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_filename VARCHAR(255) NULL,
                image_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT fk_newsletter_images_newsletter
                    FOREIGN KEY (newsletter_id)
                    REFERENCES newsletters(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            "
        ];


        foreach ($queries as $query) {

            if (!$db->query($query)) {

                throw new RuntimeException(
                    'Database setup failed: ' .
                    $db->error
                );
            }
        }


        return true;

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Seed Default Templates
|--------------------------------------------------------------------------
*/

function seedDefaultTemplates(): bool
{
    try {

        $db =
            getDatabase();


        $templates = [

            [
                'name' =>
                    'Classic',

                'description' =>
                    'A traditional newsletter layout suitable for daily or weekly news.',

                'template_key' =>
                    'classic'
            ],

            [
                'name' =>
                    'Modern',

                'description' =>
                    'A clean and modern newsletter layout.',

                'template_key' =>
                    'modern'
            ],

            [
                'name' =>
                    'Image Focus',

                'description' =>
                    'A newsletter layout that emphasizes the main image.',

                'template_key' =>
                    'image_focus'
            ]
        ];


        $stmt =
            $db->prepare("
                INSERT IGNORE INTO templates
                    (name, description, template_key)
                VALUES
                    (?, ?, ?)
            ");


        if (!$stmt) {

            throw new RuntimeException(
                'Failed to prepare template query: ' .
                $db->error
            );
        }


        foreach ($templates as $template) {

            $stmt->bind_param(
                'sss',
                $template['name'],
                $template['description'],
                $template['template_key']
            );


            if (!$stmt->execute()) {

                throw new RuntimeException(
                    'Failed to insert template: ' .
                    $stmt->error
                );
            }
        }


        $stmt->close();


        return true;

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Full Initial Setup
|
| This is still allowed to perform the expensive AI test.
| Do NOT use this on every page.
|--------------------------------------------------------------------------
*/

function runSetup(): array
{
    $status = [

        'database' =>
            false,

        'database_setup' =>
            false,

        'templates' =>
            false,

        'ollama' =>
            false,

        'model' =>
            false,

        'ai' =>
            false,

        'ready' =>
            false
    ];


    /*
    |--------------------------------------------------------------------------
    | Database connection
    |--------------------------------------------------------------------------
    */

    $status['database'] =
        checkDatabase();


    if (!$status['database']) {

        return $status;
    }


    /*
    |--------------------------------------------------------------------------
    | Database tables
    |--------------------------------------------------------------------------
    */

    $status['database_setup'] =
        setupDatabase();


    if (!$status['database_setup']) {

        return $status;
    }


    /*
    |--------------------------------------------------------------------------
    | Default templates
    |--------------------------------------------------------------------------
    */

    $status['templates'] =
        seedDefaultTemplates();


    if (!$status['templates']) {

        return $status;
    }


    /*
    |--------------------------------------------------------------------------
    | Ollama
    |--------------------------------------------------------------------------
    */

    $status['ollama'] =
        checkOllama();


    if (!$status['ollama']) {

        return $status;
    }


    /*
    |--------------------------------------------------------------------------
    | AI model
    |--------------------------------------------------------------------------
    */

    $status['model'] =
        setupAIModel();


    if (!$status['model']) {

        return $status;
    }


    /*
    |--------------------------------------------------------------------------
    | AI test passed
    |--------------------------------------------------------------------------
    */

    $status['ai'] =
        true;

    $status['ready'] =
        true;


    return $status;
}

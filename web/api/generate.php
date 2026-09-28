<?php

require_once __DIR__ . '/../inc/setup.php';
require_once __DIR__ . '/../inc/ollama.php';

/*
|--------------------------------------------------------------------------
| SSE setup
|--------------------------------------------------------------------------
*/

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('X-Accel-Buffering: no');

/*
 * Disable PHP output buffering where possible.
 */
while (ob_get_level() > 0) {
    ob_end_flush();
}

ob_implicit_flush(true);


/*
|--------------------------------------------------------------------------
| Helper: Send SSE event
|--------------------------------------------------------------------------
*/

function sendSse(array $data): void
{
    echo 'data: ' .
        json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
        ) .
        "\n\n";

    flush();
}


/*
|--------------------------------------------------------------------------
| Request validation
|--------------------------------------------------------------------------
*/

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        sendSse([
            'type' => 'error',
            'error' => 'Method not allowed'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Lightweight system check
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | We intentionally do NOT call runSetup().
    |
    | runSetup() performs an AI test generation, which would make
    | our potato server generate twice.
    |
    */

    if (!checkDatabase()) {

        http_response_code(503);

        sendSse([
            'type' => 'error',
            'error' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'
        ]);

        exit;
    }

    if (!checkOllama()) {

        http_response_code(503);

        sendSse([
            'type' => 'error',
            'error' => 'ไม่สามารถเชื่อมต่อ Ollama ได้'
        ]);

        exit;
    }

    if (!checkAIModel()) {

        http_response_code(503);

        sendSse([
            'type' => 'error',
            'error' => 'ไม่พบ AI Model ที่ติดตั้ง'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Read request
    |--------------------------------------------------------------------------
    */

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {

        http_response_code(400);

        sendSse([
            'type' => 'error',
            'error' => 'ข้อมูลไม่ถูกต้อง'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Read fields
    |--------------------------------------------------------------------------
    */

    $headline = trim(
        $input['headline'] ?? ''
    );

    $date = trim(
        $input['date'] ?? ''
    );

    $location = trim(
        $input['location'] ?? ''
    );

    $edition = trim(
        $input['edition'] ?? ''
    );

    $context = trim(
        $input['context'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Validate context
    |--------------------------------------------------------------------------
    */

    if ($context === '') {

        http_response_code(400);

        sendSse([
            'type' => 'error',
            'error' => 'กรุณาใส่ข้อมูลหรือบริบทของข่าวก่อน'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Tell browser that AI has started
    |--------------------------------------------------------------------------
    */

    sendSse([
        'type' => 'status',
        'message' => 'AI กำลังเริ่มทำงาน...'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Build AI prompt
    |--------------------------------------------------------------------------
    */

    $prompt = <<<PROMPT
คุณคือผู้ช่วยเขียนจดหมายข่าวภาษาไทยสำหรับระบบ Thai Newsletter AI

หน้าที่ของคุณคือเรียบเรียงข้อมูลที่ผู้ใช้ให้มาเป็นข่าวที่อ่านง่าย
เป็นทางการ และเหมาะสำหรับนำไปใช้ในจดหมายข่าวของสถานศึกษา
หรือองค์กร

กฎสำคัญ:
1. ใช้เฉพาะข้อมูลที่ผู้ใช้ให้มา
2. ห้ามสร้างชื่อ บุคคล สถานที่ วันที่ ตัวเลข หรือเหตุการณ์เพิ่มเติมขึ้นมาเอง
3. หากข้อมูลบางอย่างไม่มี ให้ละเว้นข้อมูลนั้น
4. ใช้ภาษาไทยที่สุภาพและเป็นทางการ
5. ห้ามใส่ Markdown
6. ห้ามใส่คำอธิบายเพิ่มเติมนอก JSON
7. ต้องตอบเป็น JSON เท่านั้น

ข้อมูลจากผู้ใช้:

หัวข้อเดิม:
{$headline}

วันที่:
{$date}

สถานที่:
{$location}

ฉบับ:
{$edition}

ข้อมูล / บริบทข่าว:
{$context}

ให้สร้าง JSON ตามรูปแบบนี้เท่านั้น:

{
  "headline": "หัวข้อข่าว",
  "summary": "สรุปข่าวสั้น ๆ",
  "body": "เนื้อหาข่าวฉบับเต็ม",
  "image_caption": "คำบรรยายภาพ"
}
PROMPT;


    /*
    |--------------------------------------------------------------------------
    | Generate using streaming
    |--------------------------------------------------------------------------
    */

    $fullResponse = '';

    $characterCount = 0;

    sendSse([
        'type' => 'status',
        'message' => 'AI กำลังเขียนข่าว...'
    ]);


    ollamaGenerateStream(
        AI_MODEL,
        $prompt,

        function (array $chunk) use (
            &$fullResponse,
            &$characterCount
        ) {

            /*
             * Ollama sends generated text in:
             *
             * "response": "..."
             */

            if (isset($chunk['response'])) {

                $text = $chunk['response'];

                $fullResponse .= $text;

                $characterCount += mb_strlen(
                    $text,
                    'UTF-8'
                );

                /*
                 * We don't expose the raw JSON to the user.
                 * Instead we send progress information.
                 */

                sendSse([
                    'type' => 'progress',
                    'characters' => $characterCount
                ]);
            }

            /*
             * Ollama marks the final chunk with:
             *
             * "done": true
             */

            if (
                isset($chunk['done']) &&
                $chunk['done'] === true
            ) {

                sendSse([
                    'type' => 'status',
                    'message' => 'AI สร้างเนื้อหาเสร็จแล้ว กำลังตรวจสอบข้อมูล...'
                ]);
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Parse AI JSON
    |--------------------------------------------------------------------------
    */

    if (trim($fullResponse) === '') {

        throw new RuntimeException(
            'AI returned an empty response'
        );
    }


    $jsonStart = strpos(
        $fullResponse,
        '{'
    );

    $jsonEnd = strrpos(
        $fullResponse,
        '}'
    );


    if (
        $jsonStart === false ||
        $jsonEnd === false ||
        $jsonEnd <= $jsonStart
    ) {

        throw new RuntimeException(
            'AI did not return valid JSON'
        );
    }


    $jsonText = substr(
        $fullResponse,
        $jsonStart,
        $jsonEnd - $jsonStart + 1
    );


    $generated = json_decode(
        $jsonText,
        true
    );


    if (!is_array($generated)) {

        throw new RuntimeException(
            'AI returned invalid JSON'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize result
    |--------------------------------------------------------------------------
    */

    $result = [

        'headline' =>
            trim(
                $generated['headline'] ?? ''
            ),

        'summary' =>
            trim(
                $generated['summary'] ?? ''
            ),

        'body' =>
            trim(
                $generated['body'] ?? ''
            ),

        'image_caption' =>
            trim(
                $generated['image_caption'] ?? ''
            )
    ];


    /*
    |--------------------------------------------------------------------------
    | Fallback / validation
    |--------------------------------------------------------------------------
    */

    if ($result['headline'] === '') {

        $result['headline'] =
            $headline;
    }


    if ($result['body'] === '') {

        throw new RuntimeException(
            'AI did not generate article content'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Send final result
    |--------------------------------------------------------------------------
    */

    sendSse([
        'type' => 'complete',
        'data' => $result
    ]);


} catch (Throwable $e) {

    sendSse([
        'type' => 'error',
        'error' => $e->getMessage()
    ]);
}
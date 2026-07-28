<?php
// api_call.php — يستقبل نص المستخدم ويرسله بأمان إلى Groq API

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/config.php';

// اسمح فقط بطلبات POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'الطريقة غير مسموحة']);
    exit;
}

// اقرأ نص المستخدم
$input  = json_decode(file_get_contents('php://input'), true);
$prompt = isset($input['prompt']) ? trim($input['prompt']) : '';

if ($prompt === '') {
    http_response_code(400);
    echo json_encode(['error' => 'الرجاء إرسال نص صالح في الحقل prompt']);
    exit;
}

// تأكد من وجود المفتاح
if (!defined('GROQ_API_KEY') || trim(GROQ_API_KEY) === '' || GROQ_API_KEY === 'اكتب مفتاحك هنا') {
    http_response_code(500);
    echo json_encode(['error' => 'لم يتم ضبط مفتاح Groq في config.php بعد']);
    exit;
}

// استدعاء Groq API
$url = "https://api.groq.com/openai/v1/chat/completions";

$body = json_encode([
    'model'    => 'openai/gpt-oss-120b',
    'messages' => [
        ['role' => 'user', 'content' => $prompt],
    ],
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY,
    ],
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_TIMEOUT        => 25,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// خطأ SSL معروف مع بعض الاستضافات المجانية (cURL error 60)؟ فعّل هذين السطرين مؤقتًا:
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'فشل الاتصال بـ Groq API: ' . $curlErr]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode >= 400) {
    http_response_code(502);
    echo json_encode(['error' => 'رفض Groq API الطلب', 'details' => $data]);
    exit;
}

$reply = $data['choices'][0]['message']['content'] ?? 'تعذر الحصول على رد من Groq.';

echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);

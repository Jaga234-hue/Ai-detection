<?php
// API Key
$apiKey = "AIzaSyBlvPtLj0A2udVIuyOx2B7EEfXaG6ltzO0";  // Replace with your actual API key

// Sample Text & Image URL (Replace with user input)
$text = " are you a motherfucker :";
$imageUrl = "https://example.com/image.jpg";  // URL of the image
$videoUrl = "https://youtu.be/TNUV1IMfQ1s?si=UyrrwvmKqQq4CxOb";  // Google Cloud Storage URL for Video

// Call APIs
$visionResult = analyzeVisionAPI($imageUrl, $apiKey);
$nlpResult = analyzeNaturalLanguageAPI($text, $apiKey);
$videoResult = analyzeVideoIntelligenceAPI($videoUrl, $apiKey);

// Display Results
echo "💡 Vision API Result:<br>";
echo json_encode($visionResult, JSON_PRETTY_PRINT);

echo "<br><br>💡 Cloud NLP API Result:<br>";
echo json_encode($nlpResult, JSON_PRETTY_PRINT);

echo "<br><br>💡 Video Intelligence API Result:<br>";
echo json_encode($videoResult, JSON_PRETTY_PRINT);

// ----------------- API CALL FUNCTIONS -----------------

// 1️⃣ Vision API (Object detection, OCR, etc.)
function analyzeVisionAPI($imageUrl, $apiKey) {
    $url = "https://vision.googleapis.com/v1/images:annotate?key=$apiKey";

    $data = [
        "requests" => [
            [
                "image" => ["source" => ["imageUri" => $imageUrl]],
                "features" => [["type" => "LABEL_DETECTION"], ["type" => "TEXT_DETECTION"]]
            ]
        ]
    ];

    return sendPostRequest($url, $data);
}

// 2️⃣ Cloud NLP API (Analyzes sentiment, syntax)
function analyzeNaturalLanguageAPI($text, $apiKey) {
    $url = "https://language.googleapis.com/v1/documents:analyzeSentiment?key=$apiKey";

    $data = [
        "document" => [
            "type" => "PLAIN_TEXT",
            "content" => $text
        ]
    ];

    return sendPostRequest($url, $data);
}

// 3️⃣ Video Intelligence API (Object tracking in videos)
function analyzeVideoIntelligenceAPI($videoUrl, $apiKey) {
    $url = "https://videointelligence.googleapis.com/v1/videos:annotate?key=$apiKey";

    $data = [
        "inputUri" => $videoUrl,
        "features" => ["LABEL_DETECTION"]
    ];

    return sendPostRequest($url, $data);
}

// 🌍 Function to send API requests
function sendPostRequest($url, $data) {
    $options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n",
            "method" => "POST",
            "content" => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    return json_decode($response, true);
}
?>
<?php
// Configuration
$apiKey = "AIzaSyBlvPtLj0A2udVIuyOx2B7EEfXaG6ltzO0"; // Consider moving to environment variables

header('Content-Type: application/json'); // Set consistent content type

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = [];
    
    try {
        // Text processing
        if (!empty($_POST['text'])) {
            $text = trim($_POST['text']);
            $response['text_analysis'] = analyzeNaturalLanguageAPI($text, $apiKey);
        }

        // File processing
        if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['file']['tmp_name'];
            $fileType = mime_content_type($fileTmpPath);

            if (str_starts_with($fileType, 'image/')) {
                $response['image_analysis'] = analyzeVisionAPI($fileTmpPath, $apiKey, true);
            } elseif (str_starts_with($fileType, 'video/')) {
                // For video processing, you need to upload to Google Cloud Storage first
                throw new Exception("Video upload requires Google Cloud Storage integration");
            } else {
                throw new Exception("Unsupported file type: $fileType");
            }
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit;
}

// If not POST request
http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);

// ----------------- API FUNCTIONS -----------------

function analyzeVisionAPI($imagePath, $apiKey, $isLocal = false) {
    $url = "https://vision.googleapis.com/v1/images:annotate?key=$apiKey";
    
    $imageData = [];
    if ($isLocal) {
        $imageContent = base64_encode(file_get_contents($imagePath));
        $imageData = ["content" => $imageContent];
    } else {
        $imageData = ["source" => ["imageUri" => $imagePath]];
    }

    $data = [
        "requests" => [[
            "image" => $imageData,
            "features" => [
                ["type" => "LABEL_DETECTION"],
                ["type" => "TEXT_DETECTION"],
                ["type" => "SAFE_SEARCH_DETECTION"]
            ]
        ]]
    ];

    return sendPostRequest($url, $data);
}

function analyzeNaturalLanguageAPI($text, $apiKey) {
    $url = "https://language.googleapis.com/v1/documents:analyzeSentiment?key=$apiKey";
    
    $data = [
        "document" => [
            "type" => "PLAIN_TEXT",
            "content" => $text,
            "language" => "en"
        ],
        "encodingType" => "UTF8"
    ];

    return sendPostRequest($url, $data);
}

function sendPostRequest($url, $data) {
    $options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n",
            "method" => "POST",
            "content" => json_encode($data),
            "ignore_errors" => true // To handle HTTP error codes
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        throw new Exception("API request failed");
    }

    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception("API Error: " . $result['error']['message']);
    }
     echo $result;
      //header("Location: index.html"); 
    return $result;
}
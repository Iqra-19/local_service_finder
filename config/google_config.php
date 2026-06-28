<?php
// Google OAuth Configuration

// Replace with your actual Google Client ID from Google Cloud Console (https://console.cloud.google.com/)
define('GOOGLE_CLIENT_ID', '882175776410-4doea0cfhe07oeufqkd2o8eosa9h4d9s.apps.googleusercontent.com');

/**
 * Verify Google ID Token using Google tokeninfo API endpoint
 * 
 * @param string $idToken
 * @return array|false Returns user payload array on success, false on failure
 */
function verifyGoogleIdToken(string $idToken) {
    if (empty($idToken)) {
        return false;
    }

    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return false;
    }

    $data = json_decode($response, true);

    if (isset($data['error_description']) || !isset($data['sub']) || !isset($data['email'])) {
        return false;
    }

    return [
        'sub' => $data['sub'], // Unique Google User ID
        'email' => $data['email'],
        'name' => $data['name'] ?? explode('@', $data['email'])[0],
        'picture' => $data['picture'] ?? null
    ];
}

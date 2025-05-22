<?php

require_once 'config.php';

function checkUsernameExists($url, $apiKey, $username) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "{$url}/rest/v1/users?username=eq.{$username}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$apiKey}",
        "Authorization: Bearer {$apiKey}"
    ]);

    $ch = disableSSLVerification($ch);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);

    curl_close($ch);

    if ($info['http_code'] == 200) {
        $users = json_decode($response, true);
        return !empty($users);
    }
    return false;
}

function getUserByUsername($url, $apiKey, $username) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "{$url}/rest/v1/users?username=eq.{$username}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$apiKey}",
        "Authorization: Bearer {$apiKey}"
    ]);

    $ch = disableSSLVerification($ch);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);

    curl_close($ch);

    return [
        'response' => $response,
        'error' => $error,
        'info' => $info
    ];
}

function registerUser($url, $apiKey, $username, $password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $userData = [
        'username' => $username,
        'password' => $hashedPassword
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "{$url}/rest/v1/users");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$apiKey}",
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);

    $ch = disableSSLVerification($ch);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);

    curl_close($ch);

    return [
        'response' => $response,
        'error' => $error,
        'info' => $info
    ];
}

function loginUser($url, $apiKey, $username, $password) {
    $result = getUserByUsername($url, $apiKey, $username);

    if ($result['error']) {
        return [
            'success' => false,
            'error' => $result['error'],
            'info' => $result['info']
        ];
    }

    $users = json_decode($result['response'], true);

    if (empty($users)) {
        return [
            'success' => false,
            'error' => 'Invalid username or password',
            'info' => $result['info']
        ];
    }

    $user = $users[0];

    if (!password_verify($password, $user['password'])) {
        return [
            'success' => false,
            'error' => 'Invalid username or password',
            'info' => $result['info']
        ];
    }

    return [
        'success' => true,
        'user' => $user,
        'info' => $result['info']
    ];
}

function processRegistration($username, $password, $confirmPassword) {
    global $supabaseUrl, $supabaseKey;

    $response = [
        'success' => false,
        'message' => '',
        'messageType' => 'error'
    ];

    if (empty($username)) {
        $response['message'] = "Username is required.";
        return $response;
    } elseif (empty($password)) {
        $response['message'] = "Password is required.";
        return $response;
    } elseif (strlen($password) < 6) {
        $response['message'] = "Password must be at least 6 characters long.";
        return $response;
    } elseif ($password !== $confirmPassword) {
        $response['message'] = "Passwords do not match.";
        return $response;
    }

    if (checkUsernameExists($supabaseUrl, $supabaseKey, $username)) {
        $response['message'] = "Username already exists. Please choose a different username.";
        return $response;
    } else {
        $result = registerUser($supabaseUrl, $supabaseKey, $username, $password);

        if ($result['error']) {
            $response['message'] = "Error registering user: " . $result['error'];
            return $response;
        } else {
            $responseData = json_decode($result['response'], true);
            $statusCode = $result['info']['http_code'];

            error_log("Registration response: " . print_r($responseData, true));
            error_log("Status code: " . $statusCode);

            if ($statusCode >= 200 && $statusCode < 300) {
                error_log("User registered successfully: {$username}");
                $response['success'] = true;
                $response['message'] = "Registration successful!";
                $response['messageType'] = "success";
                return $response;
            } else {
                $errorMessage = "Unknown error";

                if (isset($responseData['msg'])) {
                    $errorMessage = $responseData['msg'];
                } elseif (isset($responseData['message'])) {
                    $errorMessage = $responseData['message'];
                } elseif (isset($responseData['error'])) {
                    $errorMessage = $responseData['error'];
                } elseif (isset($responseData['error_description'])) {
                    $errorMessage = $responseData['error_description'];
                }

                $response['message'] = "Error registering: {$errorMessage}";
                return $response;
            }
        }
    }
}

function processLogin($username, $password) {
    global $supabaseUrl, $supabaseKey;

    $response = [
        'success' => false,
        'message' => '',
        'messageType' => 'error',
        'user' => null
    ];

    if (empty($username)) {
        $response['message'] = "Username is required.";
        return $response;
    } elseif (empty($password)) {
        $response['message'] = "Password is required.";
        return $response;
    }

    $result = loginUser($supabaseUrl, $supabaseKey, $username, $password);

    if (!$result['success']) {
        $response['message'] = "Invalid username or password.";
        return $response;
    } else {
        $response['success'] = true;
        $response['user'] = [
            'id' => $result['user']['id'],
            'username' => $result['user']['username'],
            'created_at' => $result['user']['created_at']
        ];
        return $response;
    }
}

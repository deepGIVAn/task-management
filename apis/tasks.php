<?php
require_once 'config.php';

function getAllTasks($url, $apiKey, $userId = null, $category = null, $dueDate = null, $searchQuery = null) {
    $ch = curl_init();

    $queryUrl = "{$url}/rest/v1/tasks?select=*,users(username)";

    if ($userId) {
        // $queryUrl .= "&user=eq.{$userId}";
    }

    if ($dueDate) {
        $queryUrl .= "&due_date=eq.{$dueDate}";
    }

    $queryUrl .= "&order=due_date.asc,created_at.desc";

    curl_setopt($ch, CURLOPT_URL, $queryUrl);
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

function addTask($url, $apiKey, $taskData) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "{$url}/rest/v1/tasks");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($taskData));
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

function updateTask($url, $apiKey, $taskId, $taskData) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "{$url}/rest/v1/tasks?id=eq.{$taskId}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($taskData));
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

function deleteTask($url, $apiKey, $taskId) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "{$url}/rest/v1/tasks?id=eq.{$taskId}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
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
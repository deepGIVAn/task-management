<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'apis/tasks.php';

$taskId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($taskId <= 0) {
    header('Location: index.php');
    exit;
}

function getTaskById($url, $apiKey, $taskId) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "{$url}/rest/v1/tasks?id=eq.{$taskId}");
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

$taskResult = getTaskById($supabaseUrl, $supabaseKey, $taskId);

if ($taskResult['error']) {
    header('Location: index.php?error=fetch');
    exit;
} else if ($taskResult['info']['http_code'] == 200) {
    $tasks = json_decode($taskResult['response'], true);

    if (is_array($tasks) && count($tasks) > 0) {
        $task = $tasks[0];

        if (!isset($task['user']) || $task['user'] != $_SESSION['user']['id']) {
            header('Location: index.php?error=unauthorized');
            exit;
        }

        $result = deleteTask($supabaseUrl, $supabaseKey, $taskId);

        if ($result['error']) {
            header('Location: index.php?error=delete&message=' . urlencode($result['error']));
            exit;
        } else if ($result['info']['http_code'] >= 200 && $result['info']['http_code'] < 300) {
            ?>
            <!DOCTYPE html>
            <html>
            <?php include 'header.php'; ?>
            <body>
            <?php include 'footer.php'; ?>
                <script>
                    notyf.success('Task deleted successfully!');
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 1500);
                </script>
                <div style="text-align: center; margin-top: 100px;">
                    <h3>Deleting task...</h3>
                    <p>You will be redirected automatically.</p>
                </div>
            </body>
            </html>
            <?php
            exit;
        } else {
            header('Location: index.php?error=delete&code=' . $result['info']['http_code']);
            exit;
        }
    } else {
        header('Location: index.php?error=notfound');
        exit;
    }
} else {
    header('Location: index.php?error=fetch&code=' . $taskResult['info']['http_code']);
    exit;
}

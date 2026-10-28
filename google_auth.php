<?php
session_start();

require 'vendor/autoload.php'; // Install Google Client Library using Composer

use Google\Client;
use Google\Service\Oauth2;

$client = new Client();
$client->setClientId('812387011334-sb5mho35oqpvplqsuk3s1gfogcollrnh.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/food/google-auth.php'); // Change if hosted online
$client->addScope(Oauth2::USERINFO_EMAIL);
$client->addScope(Oauth2::USERINFO_PROFILE);

if (!isset($_GET['code'])) {
    // Redirect to Google OAuth
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit;
} else {
    // Exchange authorization code for an access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    // Check if the token contains 'access_token'
    if (isset($token['access_token'])) {
        $client->setAccessToken($token['access_token']);
    } else {
        // Handle error if no access token is found
        die('Error fetching access token.');
    }

    // Get user information
    $oauth2 = new Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $_SESSION['user'] = [
        'name' => $userInfo->name,
        'email' => $userInfo->email,
        'picture' => $userInfo->picture
    ];

    $conn = new mysqli("localhost", "root", "", "crm");
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO users (email) VALUES (?) ON DUPLICATE KEY UPDATE email=email");
    $stmt->bind_param("s", $userInfo->email);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header('Location:index.php'); // Redirect to a protected page
    exit;
}
?>

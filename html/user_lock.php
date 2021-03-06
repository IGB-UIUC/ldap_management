<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

$errors = [];
if (count($_POST) > 0) {
    $user = new User($_POST['uid']);
    $result = $user->lock();

    if ($result['RESULT'] == true) {
        header("Location: user.php?uid=" . $_POST['uid']);
    } else {
        if ($result['RESULT'] == false) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Lock User',
        'inputs' => [],
        'message' => "The user will not be able to log in or change their password until their account is unlocked.",
        'button' => ['color' => 'danger', 'text' => 'Lock'],
        'errors' => $errors,
    ]
);

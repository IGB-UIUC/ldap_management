<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$username = requireGetKey('uid', 'User');
$user = new User($username);

$logs = Log::getLogs($user->getId(), Log::TYPE_USER);

renderTwigTemplate(
    'user/view_log.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'logs' => $logs,
    ]
);

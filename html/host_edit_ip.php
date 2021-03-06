<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$hid = requireGetKey('hid', 'Host');
$host = new Host($hid);

$errors = [];
if (count($_POST) > 0) {
    $_POST = array_map("trim", $_POST);

    if ($_POST['ip'] == "") {
        $_POST['ip'] = gethostbyname($_POST['hid']);
    }

    if (count($errors) == 0) {
        $host = new Host($_POST['hid']);
        $result = $host->setIp($_POST['ip']);

        if ($result['RESULT'] == true) {
            header("Location: host.php?hid=" . $result['hid']);
        } else {
            if ($result['RESULT'] == false) {
                $errors[] = $result['MESSAGE'];
            }
        }
    }
}

renderTwigTemplate(
    'host/edit.html.twig',
    [
        'siteArea' => 'hosts',
        'host' => $host,
        'header' => 'Edit IP',
        'inputs' => [
            ['attr' => 'ip', 'name' => 'IP', 'type' => 'text', 'value' => $host->getIp()],
        ],
        'errors' => $errors,
    ]
);

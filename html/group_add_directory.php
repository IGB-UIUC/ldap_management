<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid', 'Group');
$group = new Group($gid);

$errors = [];
if (count($_POST) > 0) {
    $_POST = array_map("trim", $_POST);

    if ($_POST['host'] == "") {
        $errors[] = "Please enter a server.";
    }
    if ($_POST['directory'] == "") {
        $errors[] = "Please enter a directory.";
    }

    if (count($errors) == 0) {
        $result = $group->addDirectory($_POST['host'], $_POST['directory']);

        if ($result['RESULT'] == true) {
            header("Location: group.php?gid=" . $result['gid']);
        } else {
            if ($result['RESULT'] == false) {
                $errors[] = $result['MESSAGE'];
            }
        }
    }
}
$allHosts = Host::all();
$allHosts = array_map(
    function (Host $v) {
        return $v->getName();
    },
    $allHosts
);

renderTwigTemplate(
    'group/edit.html.twig',
    [
        'siteArea' => 'groups',
        'group' => $group,
        'header' => 'Add Managed Directory',
        'inputs' => [
            ['attr' => 'host', 'name' => 'Host', 'type' => 'select', 'options' => $allHosts],
            ['attr' => 'directory', 'name' => 'Directory', 'type' => 'text'],
        ],
        'errors' => $errors,
    ]
);

<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid', 'Group');
$group = new Group($gid);
$isUserGroup = User::exists($group->getName());

$users = $group->getMemberUIDs();
usort($users, "LdapObject::username_cmp");

renderTwigTemplate(
    'group/view.html.twig',
    [
        'siteArea' => 'groups',
        'group' => $group,
        'editable' => !$isUserGroup,
    ]
);

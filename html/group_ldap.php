<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid', 'Group');
$group = new Group($gid);
$ldapattributes = $group->getLdapAttributes();

$attributes = [];
for ($i = 0; $i < $ldapattributes['count']; $i++) {
    $attributes[] = $ldapattributes[$i];
}
sort($attributes);

renderTwigTemplate(
    'group/view_advanced.html.twig',
    [
        'siteArea' => 'groups',
        'group' => $group,
        'attributes' => $attributes,
        'attributeValues' => $ldapattributes,
    ]
);
<?php

require_once "includes/main.inc.php";
header('content-type:application/json');

if (isset($_REQUEST['task'])) {
    if ($_REQUEST['task'] == 'group_members' && isset($_REQUEST['gid'])) {
        $filter = "(cn=" . $_REQUEST['gid'] . ")";
        $attributes = ['cn', 'memberUID'];
        $groupinfo = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
        if ($groupinfo['count'] == 0) {
            echo json_encode(
                [
                    'code' => 404,
                    'msg' => 'No such group',
                ]
            );
            exit();
        } else {
            $group = new Group($_REQUEST['gid']);
            $users = $group->getMemberUIDs();
            echo json_encode(
                [
                    'code' => 200,
                    'msg' => 'OK',
                    'group' => $_REQUEST['gid'],
                    'members' => $users,
                ]
            );
            exit();
        }
    }
    if ($_REQUEST['task'] == 'member_of') {
        if (!isset($_REQUEST['gid']) || !isset($_REQUEST['uid'])) {
            echo json_encode(
                [
                    'code' => 400,
                    'msg' => 'Invalid parameters',
                ]
            );
            exit();
        }
        $filter = "(cn=" . $_REQUEST['gid'] . ")";
        $attributes = ['cn', 'memberUID'];
        $groupinfo = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
        if ($groupinfo['count'] > 0 && isset($groupinfo[0]['memberuid'])) {
            for ($i = 0; $i < $groupinfo[0]['memberuid']['count']; $i++) {
                if ($groupinfo[0]['memberuid'][$i] == $_REQUEST['uid']) {
                    echo json_encode(
                        [
                            'code' => 200,
                            'msg' => 'OK',
                            'memberOf' => true
                        ]
                    );
                    exit();
                }
            }
        }
        echo json_encode(
            [
                'code' => 200,
                'msg' => 'OK',
                'memberOf' => false
            ]
        );
        exit();
    }
    if ($_REQUEST['task'] == 'password') {
        echo json_encode(
            [
                'code' => 200,
                'msg' => 'OK',
                'password' => User::generatePassword()
            ]
        );
        exit();
    }
    if ($_REQUEST['task'] == 'user' && isset($_REQUEST['uid'])) {
        $user = new User($_REQUEST['uid']);
        if ($user->getUsername() == null) {
            echo json_encode(
                [
                    'code' => 404,
                    'msg' => 'No such user',
                ]
            );
        } else {
            echo json_encode(
                [
                    'code' => 200,
                    'msg' => 'OK',
                    'user' => $user->serializable(),
                ]
            );
        }
        exit();
    }
    if ($_REQUEST['task'] == 'group' && isset($_REQUEST['gid'])) {
        $group = new Group($_REQUEST['gid']);
        if ($group->getName() == null) {
            echo json_encode(
                [
                    'code' => 404,
                    'msg' => 'No such group',
                ]
            );
        } else {
            echo json_encode(
                [
                    'code' => 200,
                    'msg' => 'OK',
                    'group' => $group->serializable(),
                ]
            );
        }
        exit();
    }

    if ($_REQUEST['task'] == 'add_group') {
        if (!isset($_REQUEST['username']) || !isset($_REQUEST['password'])) {
            echo json_encode(
                [
                    'code' => 401,
                    'msg' => 'Username/password required',
                ]
            );
            exit();
        } else {
            if (!Ldap::getInstance()->bind(
                'uid=' . $_REQUEST['username'] . ',' . __LDAP_PEOPLE_OU__,
                $_REQUEST['password']
            )) {
                echo json_encode(
                    [
                        'code' => 401,
                        'msg' => 'Invalid username/password',
                    ]
                );
                exit();
            } else {
                Ldap::getInstance()->set_bind_user('uid=' . $_REQUEST['username'] . ',' . __LDAP_PEOPLE_OU__);
                Ldap::getInstance()->set_bind_pass($_REQUEST['password']);

                if (!isset($_REQUEST['gid'])) {
                    echo json_encode(
                        [
                            'code' => 400,
                            'msg' => 'Invalid parameters',
                        ]
                    );
                }
                if (!isset($_REQUEST['description'])) {
                    $description = $_REQUEST['gid'];
                } else {
                    $description = $_REQUEST['description'];
                }
                $filter = "(cn=" . $_REQUEST['gid'] . ")";
                $attributes = ['cn', 'memberUID'];
                $groupinfo = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
                if ($groupinfo['count'] !== 0) {
                    echo json_encode(
                        [
                            'code' => 400,
                            'msg' => 'Invalid parameters',
                        ]
                    );
                } else {
                    $group = new Group();
                    $group->create($_REQUEST['gid'], $description);
                    echo json_encode(
                        [
                            'code' => 200,
                            'msg' => 'Group added'
                        ]
                    );
                    exit();
                }
            }
        }
    }
    if ($_REQUEST['task'] == 'add_to_group') {
        if (!isset($_REQUEST['username']) || !isset($_REQUEST['password'])) {
            echo json_encode(
                [
                    'code' => 401,
                    'msg' => 'Username/password required',
                ]
            );
            exit();
        } else {
            if (!Ldap::getInstance()->bind(
                'uid=' . $_REQUEST['username'] . ',' . __LDAP_PEOPLE_OU__,
                $_REQUEST['password']
            )) {
                echo json_encode(
                    [
                        'code' => 401,
                        'msg' => 'Invalid username/password',
                    ]
                );
                exit();
            } else {
                Ldap::getInstance()->set_bind_user('uid=' . $_REQUEST['username'] . ',' . __LDAP_PEOPLE_OU__);
                Ldap::getInstance()->set_bind_pass($_REQUEST['password']);

                if (!isset($_REQUEST['gid']) || !isset($_REQUEST['uid'])) {
                    echo json_encode(
                        [
                            'code' => 400,
                            'msg' => 'Invalid parameters',
                        ]
                    );
                    exit();
                }
                $filter = "(cn=" . $_REQUEST['gid'] . ")";
                $attributes = ['cn', 'memberUID'];
                $groupinfo = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
                if ($groupinfo['count'] == 0) {
                    echo json_encode(
                        [
                            'code' => 404,
                            'msg' => 'No such group',
                        ]
                    );
                    exit();
                } else {
                    if (isset($groupinfo[0]['memberuid']) && in_array($_REQUEST['uid'], $groupinfo[0]['memberuid'])) {
                        echo json_encode(
                            [
                                'code' => 200,
                                'msg' => 'User already in group',
                            ]
                        );
                    } else {
                        $group = new Group($_REQUEST['gid']);
                        $result = $group->addUser($_REQUEST['uid']);
                        if ($result['RESULT']) {
                            echo json_encode(
                                [
                                    'code' => 200,
                                    'msg' => 'User added',
                                ]
                            );
                        } else {
                            echo json_encode(
                                [
                                    'code' => 500,
                                    'msg' => $result['MESSAGE'],
                                ]
                            );
                        }
                    }
                }
                exit();
            }
        }
    }
    if ($_REQUEST['task'] == 'remove_from_group') {
        if (!isset($_REQUEST['username']) || !isset($_REQUEST['password'])) {
            echo json_encode(
                [
                    'code' => 401,
                    'msg' => 'Username/password required',
                ]
            );
            exit();
        } else {
            if (!Ldap::getInstance()->bind(
                'uid=' . $_REQUEST['username'] . ',' . __LDAP_PEOPLE_OU__,
                $_REQUEST['password']
            )) {
                echo json_encode(
                    [
                        'code' => 401,
                        'msg' => 'Invalid username/password',
                    ]
                );
                exit();
            } else {
                Ldap::getInstance()->set_bind_user('uid=' . $_REQUEST['username'] . ',' . __LDAP_PEOPLE_OU__);
                Ldap::getInstance()->set_bind_pass($_REQUEST['password']);

                if (!isset($_REQUEST['gid']) || !isset($_REQUEST['uid'])) {
                    echo json_encode(
                        [
                            'code' => 400,
                            'msg' => 'Invalid parameters',
                        ]
                    );
                    exit();
                }
                $filter = "(cn=" . $_REQUEST['gid'] . ")";
                $attributes = ['cn', 'memberUID'];
                $groupinfo = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
                if ($groupinfo['count'] == 0) {
                    echo json_encode(
                        [
                            'code' => 404,
                            'msg' => 'No such group',
                        ]
                    );
                    exit();
                } else {
                    if (isset($groupinfo[0]['memberuid']) && !in_array($_REQUEST['uid'], $groupinfo[0]['memberuid'])) {
                        echo json_encode(
                            [
                                'code' => 200,
                                'msg' => 'User not in group',
                            ]
                        );
                    } else {
                        $group = new Group($_REQUEST['gid']);
                        $result = $group->removeUser($_REQUEST['uid']);
                        if ($result['RESULT']) {
                            echo json_encode(
                                [
                                    'code' => 200,
                                    'msg' => 'User removed',
                                ]
                            );
                        } else {
                            echo json_encode(
                                [
                                    'code' => 500,
                                    'msg' => print_r($_REQUEST, true),
                                ]
                            );
                        }
                    }
                }
                exit();
            }
        }
    }
}

echo json_encode(
    [
        'code' => 418,
        'msg' => 'I\'m a teapot',
    ]
);
		
	
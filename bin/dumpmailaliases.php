<?php
ini_set("display_errors", 1);
chdir(dirname(__FILE__));
require_once '../conf/settings.inc.php';
require_once '../vendor/autoload.php';

function cmp($a, $b) {
    if ( isset($a['uid'][0]) && isset($b['uid'][0]) ) {
        if ( $a['uid'][0] == $b['uid'][0] ) {
            return 0;
        }
        return ($a['uid'][0] < $b['uid'][0]) ? -1 : 1;
    }
    return 0;
}

$sapi_type = php_sapi_name();
// If run from command line
if ( $sapi_type != 'cli' ) {
    echo "Error: This script can only be run from the command line.\n";
} else {
    // Connect to ldap
    Ldap::init(__LDAP_HOST__, __LDAP_SSL__, __LDAP_PORT__, __LDAP_BASE_DN__);

    $filter = "(postalAddress=*)";
    $ou = __LDAP_PEOPLE_OU__;
    $attributes = ['uid', 'postalAddress'];
    $results = Ldap::getInstance()->search($filter, $ou, $attributes);
    unset($results['count']);
    usort($results, "cmp");
    for ( $i = 0; $i < count($results); $i++ ) {
        echo $results[$i]['uid'][0] . ": " . $results[$i]['postaladdress'][0] . "\n";
    }
}
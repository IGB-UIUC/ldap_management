<?php
ini_set("display_errors", 1);
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ":../libs");
require_once('../conf/settings.inc.php');
function my_autoloader($class_name) {
    $class_name = str_replace('\\', '/', $class_name);
    if ( file_exists("../libs/" . $class_name . ".class.inc.php") ) {
        require_once $class_name . '.class.inc.php';
    }
}

spl_autoload_register('my_autoloader');

require_once '../vendor/autoload.php';

/**
 * @param User   $user
 * @param string $subject
 * @param string $duration
 * @throws \Twig\Error\LoaderError
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 */
function emailmessage($user, $subject, $duration) {
    // Initialize Twig
    require_once '../vendor/autoload.php';
    $loader = new Twig_Loader_Filesystem('../templates');
    $twig = new Twig_Environment($loader, array());

    $to = $user->getEmail();
    $boundary = uniqid('mp');

    $emailMessage = "\r\n\r\n--" . $boundary . "\r\n";
    $emailMessage .= "Content-type: text/plain; charset=utf-8\r\n\r\n";

    $txtTemplate = $twig->load('email/expiring_password.txt.twig');
    $emailMessage .= $txtTemplate->render(
        array(
            'name' => $user->getName(),
            'duration' => $duration,
            'expiration' => $user->getPasswordExpiration(),
        ));

    $emailMessage .= "\r\n\r\n--" . $boundary . "\r\n";
    $emailMessage .= "Content-type: text/html; charset=utf-8\r\n\r\n";

    $htmlTemplate = $twig->load('email/expiring_password.html.twig');
    $emailMessage .= $htmlTemplate->render(
        array(
            'name' => $user->getName(),
            'duration' => $duration,
            'expiration' => $user->getPasswordExpiration(),
        ));

    $emailMessage .= "\r\n\r\n--" . $boundary . "--";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: do-not-reply@igb.illinois.edu\r\n";
    $headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";
    mail($to, $subject, $emailMessage, $headers, " -f do-not-reply@igb.illinois.edu");

    Log::info(
        "Expiration email sent to " . $user->getUsername() . ".", Log::EXP_EMAIL_SENT, $user);
}

$sapi_type = php_sapi_name();
// If run from command line
if ( $sapi_type != 'cli' ) {
    echo "Error: This script can only be run from the command line.\n";
} else {
    echo "Analyzing users...";
    // Connect to ldap
    Ldap::init(__LDAP_HOST__, __LDAP_SSL__, __LDAP_PORT__, __LDAP_BASE_DN__);
    Ldap::getInstance()
        ->set_bind_user(__LDAP_BIND_USER__);
    Ldap::getInstance()
        ->set_bind_pass(__LDAP_BIND_PASS__);
    $users = User::all();
    /** @var User[] $emailToday */
    $emailToday = array();
    $userexpdate = date_format(date_add(date_create(), new DateInterval('P6M')), 'U');
    $userexpreason = "Password expired";
    foreach ( $users as $uid ) {
        $user = new User($uid);
        if ( $user->getPasswordExpiration() != null && $user->getEmail() != null ) {
            $expiration = $user->getPasswordExpiration();
            $timetoexp = intval(($expiration - time()) / (60 * 60 * 24));

            if ( $timetoexp < 30 && $timetoexp % 7 == 6 ) {
                $emailToday[] = $user;
            }

        }
        if ( $user->isPasswordExpired() && !$user->isLocked() ) {
            echo "Password expired for " . $user->getUsername() . "\n";
            $user->lock();
            if ( $user->getExpiration() == null ) { // Don't extend the user's expiration date if they're already set to expire
                $user->setExpiration($userexpdate, $userexpreason);
            }
        }
    }

    if ( count($emailToday) > 0 ) {
        echo "\n==== Expiring users ====\n";
        foreach ( $emailToday as $user ) {
            $weeks = intval(($user->getPasswordExpiration() - time()) / (60 * 60 * 24 * 7)) + 1;
            $duration = sprintf("%d week%s", $weeks, $weeks == 1 ? '' : 's');
            echo date(
                    'Y-m-d',
                    $user->getPasswordExpiration()) . "\t" . $user->getUsername() . "  \t" . $user->getName() . "\t" . $duration . "\n";
        }
        echo "Sending mail...\n";
        foreach ( $emailToday as $user ) {
            $weeks = intval(($user->getPasswordExpiration() - time()) / (60 * 60 * 24 * 7)) + 1;
            $duration = sprintf("%d week%s", $weeks, $weeks == 1 ? '' : 's');
            emailmessage($user, "IGB Password Expiration", $duration);
        }
    } else {
        echo "\nNo users expiring within one month.\n";
    }

    if ( count($emailToday) > 0 ) {
        // Email joe secretly who's going to be emailed today
        $subject = "IGB Password Expiration Notices Pending";
        $to = "jleigh@illinois.edu";

        $loader = new Twig_Loader_Filesystem('../templates');
        $twig = new Twig_Environment($loader, array());
        $htmlTemplate = $twig->load('email/expiring_password_digest.html.twig');
        $emailmessage = $htmlTemplate->render(
            array(
                'users' => $emailToday,
            ));

        $headers = "From: do-not-reply@igb.illinois.edu\r\n";
        $headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
        mail($to, $subject, $emailmessage, $headers, " -f " . __ADMIN_EMAIL__);
    }

}
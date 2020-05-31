<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'tools.php';

class MailsManager
{
    private static function getHostLink(): string
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            $link = "https";
        else
            $link = "http";

        // Here append the common URL characters. 
        $link .= "://";

        // Append the host(domain name, ip) to the URL. 
        $link .= $_SERVER['HTTP_HOST'];

        return $link;
    }

    /**
     * Returns the success of the operation
     */
    static function sendRegistrationConfirmationMail(CVEUser $user): bool
    {
        if (!$user->isRegistered()) {
            $subject = "CVE registration confirmation";
            $headers = 'From: CVE Server <u986909396@srv190.main-hosting.eu>' . PHP_EOL .
                'Content-type: text/html' . PHP_EOL .
                'X-Mailer: PHP/' . phpversion();
            $confirm_link = MailsManager::getHostLink() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'accounts.php' . "?token=" . $user->getRegistrationToken() . "&user_id=" . $user->getId() . "&action=register-confirmation";
            $img_link = MailsManager::getHostLink() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'confirm_icon.png';
            $message = '<html><body>Dear ' . urldecode($user->getUsername()) . ',<br>Welcom to the complete voyaging experience ! Please click on the link bellow to confirm your account.<br><a href="' . $confirm_link . '"><img src="' . $img_link . '" alt="link"/></a></body></html>';
            return mail($user->getEmail(), $subject, $message, $headers);
        } else {
            return false;
        }
    }

    /**
     * Returns the success of the operation
     */
    static function sendPasswordResetRequestMail(CVEUser $user): bool
    {
        $subject = "CVE password reset";
        $headers = 'From: CVE Server <u986909396@srv190.main-hosting.eu>' . PHP_EOL .
            'Content-type: text/html' . PHP_EOL .
            'X-Mailer: PHP/' . phpversion();
        $reset_link = MailsManager::getHostLink() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'password_change' . "?token=" . $user->getPasswordToken() . "&user_id=" . $user->getId() . "&username=" . $user->getUsername() . "&action=password-reset";
        $img_link = MailsManager::getHostLink() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'pwd_change_icon.png';
        $message = '<html><body>Dear ' . urldecode($user->getUsername()) . ',<br>Please click on the link bellow to set your new password.<br><a href="' . $reset_link . '"><img src="' . $img_link . '" alt="link"/></a></body></html>';
        return mail($user->getEmail(), $subject, $message, $headers);
    }
}

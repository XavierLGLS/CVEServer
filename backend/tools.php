<?php

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config_v2.php';

function generateRandomStr($length)
{
    $alphabet = "0123456789azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN";
    return substr(str_shuffle(str_repeat($alphabet, $length)), 0, $length);
}

/**
 * Returns the hashed password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function addFlashError($error)
{
    if (!isset($_SESSION['flash-error'])) {
        $_SESSION['flash-error'] = [$error];
    } else {
        if (!in_array($error, $_SESSION['flash-error'])) {
            array_push($_SESSION['flash-error'], $error);
        }
    }
}

function addFlashSuccess($success)
{
    if (!isset($_SESSION['flash-success'])) {
        $_SESSION['flash-success'] = [$success];
    } else {
        if (!in_array($success, $_SESSION['flash-success'])) {
            array_push($_SESSION['flash-success'], $success);
        }
    }
}

/**
 * Returns true if the request contents allows the service
 */
function isRequestAuthenticated()
{
    global $APP_WHITELIST;
    if (isset($_POST["app-token"])) {
        return in_array($_POST["app-token"], $APP_WHITELIST, true);
    } else if (isset($_GET["app-token"])) {
        return in_array($_GET["app-token"], $APP_WHITELIST, true);
    } else {
        return false;
    }
}

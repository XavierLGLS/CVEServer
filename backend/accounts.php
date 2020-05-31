<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AccountsManager.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SailawayDataManager.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'BoatsManager.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'MailsManager.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tools.php';

session_start();

$accountsManager = new AccountsManager();
$swManager = new SailawayDataManager();
$boatsManager = new BoatsManager();

// Request authentication check
function checkAppAuthentication()
{
    if (!isRequestAuthenticated()) {
        addFlashError('You are not authorized to use this service');
        header('Location: ../pages/home');
        return;
    }
}

function checkAdminRequest()
{
    if (!$_SESSION['auth']->isAdmin()) {
        addFlashError('You must be administrator');
        header('Location: ../pages/home');
        return;
    }
}

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "login":
            checkAppAuthentication();
            handleLogin();
            break;
        case "logout":
            checkAppAuthentication();
            handleLogout();
            break;
        case "register":
            checkAppAuthentication();
            handleRegister();
            break;
        case "unregister":
            checkAppAuthentication();
            handleUnregister();
            break;
        case "password-reset-request":
            checkAppAuthentication();
            handlePasswordResetRequest();
            break;
        case "delete":
            checkAppAuthentication();
            handleUserDeletion();
            break;
        case "change-boat":
            checkAppAuthentication();
            handleChangeBoat();
            break;
        case "password-reset":
            checkAppAuthentication();
            handlePasswordReset();
            break;
        case "perm-edit":
            checkAppAuthentication();
            checkAdminRequest();
            handlePermEdit();
            break;
        case "manual-confirm":
            checkAppAuthentication();
            checkAdminRequest();
            handleManualConfirm();
            break;
        case "admin-delete":
            checkAppAuthentication();
            checkAdminRequest();
            handleAdminDeletion();
            break;
        default:
            handleError();
    }
} else if (isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case 'register-confirmation':
            //from mail
            handleRegisterConfirmation();
            break;
        default:
            handleError();
    }
} else {
    handleError();
}

function handleError()
{
    addFlashError('wrong request structure');
    header('Location: ../pages/home');
}

function handleLogin()
{
    global $accountsManager;

    if (isset($_POST['email']) and isset($_POST['password'])) {
        $mail = htmlspecialchars($_POST['email']);
        $attemptPassword = htmlspecialchars($_POST['password']);
        $result = $accountsManager->checkLogin($mail, $attemptPassword);
        if ($result["status"] === "success") {
            $accountsManager->connectUser($result["user"]);
            if (isset($_POST['remember'])) {
                if ($result["user"]->isContributor() || $result["user"]->isAdmin()) {
                    addFlashError('because you have special permissions, your account is not remembered by the browser');
                } else {
                    $remember_token = generateRandomStr(60);
                    $accountsManager->setRememberToken($result["user"]->getId(), $remember_token);
                    setcookie('remember', $result["user"]->getId() . '==' . $remember_token/* . sha1($user->id, $REMEMBER_SALT_KEY)*/, time() + 60 * 60 * 24 * 7, "/");
                }
            }
            header('Location: ../pages/dashboard');
        } else {
            addFlashError($result["status"]);
            header('Location: ../pages/login');
        }
    } else {
        addFlashError('request content error');
        header('Location: ../pages/login');
    }
}

function handleLogout()
{
    unset($_SESSION['auth']);
    setcookie('remember', NULL, -1, "/");
    $_SESSION['flash-success'] = 'successfully logout';
    header('Location: ../pages/home');
}

function handleRegister()
{
    global $accountsManager;
    global $swManager;
    global $boatsManager;

    if (isset($_POST['email-1']) and isset($_POST['password-1']) and isset($_POST['username']) and isset($_POST['boat-id'])) {
        $username = rawurlencode($_POST['username']);
        if (!$swManager->userExists($username)) {
            addFlashError("This is not a sailaway username ! (" . $_POST['username'] . ")");
            header('Location: ../pages/register');
        }
        $sw_boat_id = intval(explode("-", $_POST['boat-id'])[0]);
        $sw_boat_type = intval(explode("-", $_POST['boat-id'])[1]);
        $email = htmlspecialchars($_POST['email-1']);
        $password = hashPassword(htmlspecialchars($_POST['password-1']));
        if (!$accountsManager->isMailAlreadyUsed($email)) {
            try {
                $confirmationToken = generateRandomStr(60);
                // add a new cve_user
                $sw_user_id = $swManager->getUserId($username);
                $user = $accountsManager->addUser($email, $password, $sw_user_id, $confirmationToken);
                // add a new cve_boat
                $boatsManager->addBoat($user->getId(), $sw_boat_id, $sw_boat_type);

                MailsManager::sendRegistrationConfirmationMail($user);
                addFlashSuccess('a confirmation email has been sent to <u>' . $email . '</u> (have a look to your trashbox too)');
                header('Location: ../pages/home');
            } catch (Exception $e) {
                addFlashError($e->getMessage());
                header('Location: ../pages/register');
            }
        } else {
            addFlashError('this mail is already used');
            header('Location: ../pages/register');
        }
    } else {
        addFlashError('request content error');
        header('Location: ../pages/register');
    }
}

function handleUnregister()
{
    global $accountsManager;

    try {
        $accountsManager->removeUser($_SESSION['auth']->id);
        unset($_SESSION['auth']);
        header('Location: ../pages/home');
    } catch (Exception $e) {
        addFlashError($e->getMessage());
    }
}

function handleRegisterConfirmation()
{
    global $accountsManager;

    if (isset($_GET['token']) and isset($_GET['user_id'])) {
        try {
            $id = intval($_GET['user_id']);
            $token = htmlspecialchars($_GET['token']);
            $user = $accountsManager->getUserFromId($id);
            $result = $accountsManager->checkRegistrationConfirmation($user, $token);
            if ($result["status"] === "success") {
                $accountsManager->connectUser($user);
                addFlashSuccess("Welcome " . $user->getUsername() . " !");
            } else {
                addFlashError($result["status"]);
            }
        } catch (Exception $e) {
            addFlashError($e->getMessage());
        }
    } else {
        addFlashError("The content of the request is wrong, it seems you uses a partial part of the link");
    }
    header('Location: ../pages/home');
}

function handlePasswordResetRequest()
{
    global $accountsManager;

    if (isset($_POST['email'])) {
        $email = htmlspecialchars($_POST['email']);
        try {
            $user  = $accountsManager->getUserFromMail($email);
        } catch (Exception $e) {
            addFlashError('unknown email');
            header('Location: ../pages/password_change_request');
        }
        try {
            $token = generateRandomStr(60);
            $accountsManager->setPasswordReset($user, $token);
            MailsManager::sendPasswordResetRequestMail($user);
            addFlashSuccess('a mail has been sent to <u>' . $user->email . '</u> to reset your password (have a look to your trashbox too)');
        } catch (Exception $e) {
            addFlashError($e->getMessage());
        } finally {
            header('Location: ../pages/home');
        }
    } else {
        addFlashError('request content error');
        header('Location: ../pages/password_change_request');
    }
}

function handlePasswordReset()
{
    global $accountsManager;

    if (isset($_SESSION['auth']) && isset($_POST['password-1'])) {
        try {
            $newPassword = htmlspecialchars($_POST['password-1']);
            $accountsManager->setNewPassword($_SESSION['auth'], $newPassword);
            addFlashSuccess("new password set");
        } catch (Exception $e) {
            addFlashError($e->getMessage());
        }
    } else {
        addFlashError('request content error');
    }
    header('Location: ../pages/home');
}

function handleUserDeletion()
{
    global $accountsManager;

    if (isset($_POST['user-id'])) {
        $id = intval($_POST['user-id']);
        if ($_SESSION['auth']->getId() === $id) {
            $accountsManager->removeUser($id);
            unset($_SESSION['auth']);
            setcookie('remember', NULL, -1, "/");
            addFlashSuccess('account deleted !');
        } else {
            addFlashError('you cannot delete this account !');
        }
        header('Location: ../pages/home');
    } else {
        addFlashError('something went wrong !');
        header('Location: ../pages/my_account');
    }
}

function handleChangeBoat()
{
    global $accountsManager;

    if (isset($_POST['boat-id']) && isset($_POST['user-id'])) {
        $boatId = intval($_POST['boat-id']);
        $userId = intval($_POST['user-id']);
        $accountsManager->changeBoat($userId, $boatId);
        addFlashSuccess('boat changed !');
    } else {
        addFlashError('something went wrong !');
    }
    header('Location: ../pages/my_account');
}

function handlePermEdit()
{
    global $accountsManager;

    if (isset($_POST['user-id'])) {
        $userId = intval($_POST['user-id']);
        $poiPerm = isset($_POST['poi']);
        $missionPerm = isset($_POST['mission']);
        $accountsManager->setUserPermissions($userId, $poiPerm, $missionPerm);
    } else {
        addFlashError('wrong request content');
    }
    header('Location: ../pages/users_management');
}

function handleAdminDeletion()
{
    global $accountsManager;

    if (isset($_POST['user-id'])) {
        $userId = intval($_POST['user-id']);
        $accountsManager->removeUser($userId);
    } else {
        addFlashError('wrong request content');
    }
    header('Location: ../pages/users_management');
}

function handleManualConfirm()
{
    global $accountsManager;

    if (isset($_POST['user-id'])) {
        $userId = intval($_POST['user-id']);
        $accountsManager->confirmRegistration($userId);
    } else {
        addFlashError('wrong request content');
    }
    header('Location: ../pages/users_management');
}

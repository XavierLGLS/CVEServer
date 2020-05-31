<?php
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config_v2.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AccountsManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SailawayUsersManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'CVEUser.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$sailawayUsersManager = new SailawayUsersManager();
$accountsManager = new AccountsManager();

if (isset($_COOKIE['remember']) and !isset($_SESSION['auth'])) {
    $remember_token = $_COOKIE['remember'];
    $parts = explode('==', $remember_token);
    $user_id = $parts[0];
    $user = $accountsManager->getUserFromId($user_id);
    if ($user != NULL) {
        $expected_token = $user->id . '==' . $user->remember_token/* . sha1($user->id, $REMEMBER_SALT_KEY)*/;
        if ($remember_token == $expected_token) {
            if ($user->admin_permission || $user->poi_editor_permission || $user->mission_editor_permission) {
                addFlashError('The previous account had permission, the user has to manually sign in');
            } else {
                $accountsManager->updateUserLastConnectionDate($user);
                $_SESSION['auth'] = $user;
                addFlashSuccess('Welcome back <u>' . $user->username . '</u> ! You have been automatically logged in because you have checked "remember". If you don\'t want anymore, please click on logout while exiting.');
                setcookie('remember', $remember_token, time() + 60 * 60 * 24 * 7, "/");
            }
        } else {
            setcookie('remember', NULL, -1, "/");
        }
    } else {
        setcookie('remember', NULL, -1, "/");
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Complete Voyaging Experience</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- bootstrap css -->
    <link rel="stylesheet" href="../../libs/bootstrap/bootstrap.min.css">
    <!-- font awesome icons -->
    <script src="https://kit.fontawesome.com/<?= $FONTAWESOME_CODE ?>.js" crossorigin="anonymous"></script><!-- online -->
    <!-- local css -->
    <link rel="stylesheet" href="../styles/style.css">
    <!-- jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script><!-- online -->
    <!-- bootstrap js -->
    <script src="../../libs/bootstrap/bootstrap.bundle.min.js"></script>
</head>

<body>

    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">

        <a class="navbar-brand" href="../credits">CVE</a>

        <?php
        if (isset($_SESSION['auth'])) {
            if ($_SESSION['auth']->isAdmin()) {
                echo '<a class="navbar-brand text-danger">Administrator</a>';
            } else if ($_SESSION['auth']->isContributor()) {
                echo '<a class="navbar-brand text-danger">Contributor</a>';
            }
            echo '<a class="navbar-brand text-warning">' . urldecode($_SESSION['auth']->getUsername()) . '</a>';
            echo '<span id="time-update" style="color: white; transition: 1s;"></span>';
        }
        ?>

        <!-- Toggler/collapsibe Button -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse d-flex flex-row-reverse" id="collapsibleNavbar">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../home">Home</a>
                </li>
                <li class="nav-item">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Navigation</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="../pois">POIs</a>
                            <a class="dropdown-item" href="../damage_model_info">Damage model</a>
                            <a class="dropdown-item" href="../boat_characteristics">Boats characteristics</a>
                        </div>
                    </div>
                </li>
                <?php if (isset($_SESSION['auth'])) : ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard">Dashboard</a>
                    </li>
                    <?php if ($_SESSION['auth']->isContributor()) : ?>
                        <li class="nav-item">
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Contributor panel</a> <!-- lien -->
                                <div class="dropdown-menu">
                                    <?php if ($_SESSION['auth']->hasPOIEditionPerm()) : ?>
                                        <a class="dropdown-item" href="../pois_editor">POIs editor</a>
                                    <?php endif ?>
                                    <?php if ($_SESSION['auth']->hasMissionEditionPerm()) : ?>
                                        <a class="dropdown-item" href="../mission_add">Create a mission</a>
                                        <a class="dropdown-item" href="../missions_edit">Edit a mission</a>
                                    <?php endif ?>
                                </div>
                            </div>
                        </li>
                    <?php endif ?>
                    <?php if ($_SESSION['auth']->isAdmin()) : ?>
                        <li class="nav-item">
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Admin panel</a> <!-- lien -->
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="../users_management">Users</a>
                                    <a class="dropdown-item" href="../boat_characteristics_management">Boat characteristics</a>
                                    <a class="dropdown-item" href="../cron_jobs_management">Cron jobs</a>
                                    <a class="dropdown-item" href="../logs">Logs</a>
                                </div>
                            </div>
                        </li>
                    <?php endif ?>
                    <li class="nav-item">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">My account</a> <!-- lien -->
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="../my_account">Settings</a>
                                <a class="dropdown-item" onclick="logOut();" style="cursor: pointer;">Log out</a>
                            </div>
                        </div>
                    </li>
                <?php else : ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../login">Log in</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../register">Sign up</a>
                    </li>
                <?php endif ?>
                <li class="nav-item">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Help</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="../help_en"><img src="../../assets/english_flag.png" alt="english" height="15"></a>
                            <a class="dropdown-item" href="../help_fr"><img src="../../assets/french_flag.png" alt="français" height="15"></a>
                            <a class="dropdown-item" href="../help_ned"><img src="../../assets/dutch_flag.png" alt="frans" height="15"></a>
                            <a class="dropdown-item" href="../help_es"><img src="../../assets/spanish_flag.png" alt="español" height="15"></a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

    </nav>

    <?php
    if (isset($_SESSION['flash-error'])) {
        foreach ($_SESSION['flash-error'] as $error) {
    ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php
        }
        unset($_SESSION['flash-error']);
    }
    if (isset($_SESSION['flash-success'])) {
        foreach ($_SESSION['flash-success'] as $success) {
        ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
    <?php
        }
        unset($_SESSION['flash-success']);
    }
    ?>
    <script>
        // Returns the current app token
        function getAppToken() {
            return "<?= $CVE_WEB_API_KEY ?>";
        }

        // Returns the detection radius (for POIs) in km
        function getDetectionRadiusKm() {
            return <?= $POI_RADIUS ?> * 1.852;
        }

        // Returns the detection radius (for POIs) in nm
        function getDetectionRadiusNm() {
            return <?= $POI_RADIUS ?>;
        }

        function getFoodPrice() {
            return <?= $PRICE_FOOD ?>;
        }

        function getWaterPrice() {
            return <?= $PRICE_WATER ?>;
        }

        function getSparePartPrice() {
            return <?= $PRICE_SPARE_PART ?>;
        }

        <?php if (isset($_SESSION['auth'])) : ?>

            function getUserId() {
                return <?= $_SESSION['auth']->getId() ?>;
            }

        <?php endif ?>

        <?php if (isset($_SESSION['auth'])) : ?>

            // min
            function getTimeToNextSync() {
                return <?php
                        $now = (new DateTime())->getTimestamp();
                        $configJson = json_decode(file_get_contents('../../../temp_v2.json'));
                        $lastSyncDate = intval($configJson->location_update->last_date);
                        if ($configJson->location_update->enabled) {
                            echo ($configJson->location_update->period - floor(($now - $lastSyncDate) / 60));
                        } else {
                            echo -1;
                        }
                        ?>;
            }

        <?php endif ?>

        function logOut() {
            $.post("../../backend/accounts.php", {
                    action: "logout",
                    "app-token": "<?= $CVE_WEB_API_KEY ?>"
                },
                function(result, status) {
                    if (status == "success") {
                        window.location.href = "../home";
                    }
                }
            );
        }
    </script>
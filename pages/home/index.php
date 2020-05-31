<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'BoatsManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PoisManager.php';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
$cveBoatsManager = new BoatsManager();
$poisManager = new PoisManager();

function displayScore($boat)
{
    $str = '<li>';
    $str .= $boat->username . ' on "' . $boat->boatname . '" (' . $boat->boattype . '), score: ' . $boat->score . ' nm';
    $str .= '</li>';
    echo $str;
}
?>
<style>
    .card {
        margin: 0 5px;
        padding: 0;
    }

    .card:hover {
        border: 2px solid #343a40;
    }

    .card:hover .card-header {
        color: white;
        background: #343a40;
    }

    .card a {
        color: inherit;
        text-decoration: none;
    }
</style>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Home</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
    <div class="jumbotron">
        <p class="lead">
            Sailaway is only a sailing simulator. Because aiming for a destination is more exciting than simply sailing, this addon challenges your management and navigation skills. You must deal with the weather and plan some stopovers to maintain your boat and your crew. Almost <?= $poisManager->countAll() ?> places of interests are registered worldwide to simulate your trip. Have a nice navigation !
        </p>
    </div>
    <div></div>
    <div class="row">
        <div class="col-sm-2"></div>
        <div class="card col-sm-2">
            <a href="../help_en">
                <div class="card-header text-center">how to use this addon ?</div>
                <img class="card-img" src="../../assets/english_flag.png" alt="english flag">
            </a>
        </div>
        <div class="card col-sm-2">
            <a href="../help_fr">
                <div class="card-header text-center">comment utiliser cet addon ?</div>
                <img class="card-img" src="../../assets/french_flag.png" alt="french flag">
            </a>
        </div>
        <div class="card col-sm-2">
            <a href="../help_ned">
                <div class="card-header text-center">hoe gebruik ik dit add-on?</div>
                <img class="card-img" src="../../assets/dutch_flag.png" alt="dutch flag">
            </a>
        </div>
        <div class="card col-sm-2">
            <a href="../help_es">
                <div class="card-header text-center">¿Cómo usar este complemento?</div>
                <img class="card-img" src="../../assets/spanish_flag.png" alt="spanish flag">
            </a>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
<?php
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config_v2.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
?>
<style>
    .header-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
    }

    .header-container:hover {
        color: #ffc107;
    }

    .part-container {
        margin-bottom: 5em;
        max-height: 200vh;
        overflow: hidden;
        transition: max-height 0.25s ease-out;
    }
</style>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Comment utiliser cet addon ?</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">




    <div class="header-container">
        <h2>Brève introduction</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p class="text-info">
            Cet addon est une plateforme conçue et développée pour la communauté et par la communauté. Il permet de rajouter un peu de réalisme à la planification des navigations dans le jeu <a href="https://sailaway.world" target="_blank">sailaway</a>. L'objectif est de faire naviguer son voilier, de l'entretenir et de planifier ses navigations tout en conservant un équilibre financier.
        </p>
        <div class="container text-center">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle" style="font-size: 1.2em; margin: 0 1em;"></i>
                Si vous rencontrez un bug ou si vous avez une question, merci de vous rendre sur le <a href="<?= $DISCORD_LINK ?>" target="_blank">discord de l'addon</a>
            </div>
        </div>
        <h4>Où commencer ?</h4>
        <p>
            Commencez par vous <a href="../register">créer un compte</a> sur ce site. Lors de sa création, vous le relierez à l'un de vos bateaux de sailaway. Chaque type de bateau a ses caractéristiques propres, elles sont décrites <a href="../boat_characteristics" target="_blank">ici</a>. Choisissez-le bien !
        </p>
        <h4>Une fois connecté, comment jouer ?</h4>
        <p>
            Cette plateforme est connectée à sailaway. Elle permet de suivre la progression de votre bateau. Vous ne pouvez cependant le contrôler uniquement que depuis le jeu. Vous devrez naviguer de port en port pour assurer le ravitaillement de votre embarcation, trouver des missions pour financer l'entretien de votre bateau.
        </p>
    </div>




    <div class="header-container">
        <h2>Explorer les ports et les missions</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p>
            Cette <a href="../pois" target="_blank">page</a> vous permet d'explorer sans aucune limite l'ensemble des ports, mouillages et lieux remarquables disponibles dans CVE. Vous pouvez ainsi repérer les lieux vous permettant:
            <ul>
                <li>de vous procurer de l'eau</li>
                <li>d'acheter de la nourriture</li>
                <li>d'acheter des pièces de réparation</li>
                <li>d'utiliser une cale sèche</li>
            </ul>
            La liste des missions disponibles dans chacun des ports est également affichée.
        </p>
        <div class="text-center" style="margin: 1em;">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/q16eT2JHqmU" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <div class="container text-center">
            <div class="alert alert-info">
                <i class="fas fa-info-circle" style="font-size: 1.2em; margin: 0 1em;"></i>
                Le disque bleu qui apparait au survol des icônes correspond à la zone dans laquelle CVE considère qu'un bateau est arrivé dans sa destination
            </div>
        </div>
        <h4>Ensemble des points d'intérêt</h4>
        <ul style="list-style-type:none">
            <li style="margin: 1em 0;"><img src="../../assets/poi_harbour_icon.png" height="30" width="30"> port (<img src="../../assets/poi_harbour_mission_icon.png" height="30" width="30"> lorsqu'il contient une mission)</li>
            <li style="margin: 1em 0;"><img src="../../assets/poi_anchorage_icon.png" height="30" width="30"> mouillage (<img src="../../assets/poi_anchorage_mission_icon.png" height="30" width="30"> lorsqu'il contient une mission)</li>
            <li style="margin: 1em 0;"><img src="../../assets/poi_oddity_icon.png" height="30" width="30"> curiosité locale</li>
            <li style="margin: 1em 0;"><img src="../../assets/poi_waypoint_icon.png" height="30" width="30"> point de passage d'une mission</li>
        </ul>
    </div>




    <div class="header-container">
        <h2>Gérer l'entretien de son bateau</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p>
            La présence d'eau et de nourriture à bord est essentielle au maintient de la forme du skipper et de son équipage. Plus il y a du monde à bord et plus ces stocks sont consommés rapidement au fil du temps.
        </p>
        <p>
            Les voiles et la coques se dégradent avec le temps. Attention, en dessous de 80%, la dégradation s'accélère sensiblement ! Une mauvaise météo accentue également les dégradations. La méthode de calcul des dégâts est expliquée <a href="../damage_model_info" target="_blank">ici</a>.
        </p>
        <h4>Réparer son embarcation</h4>
        <p>
            <ul>
                <li>
                    utiliser une pièce de réparation qui permet de récupérer 10% de structure
                </li>
                <li>
                    placer son bateau dans une cale sèche qui répare de 5% chaque jour la coque et les voiles
                </li>
            </ul>
        </p>
        <div class="container text-center">
            <div class="alert alert-info">
                <i class="fas fa-info-circle" style="font-size: 1.2em; margin: 0 1em;"></i>
                Lorsque un bateau est en cale sèche, aucune ressource n'est consommée. C'est la solution idéale pour mettre CVE en pause !
            </div>
        </div>
        <div class="text-center" style="margin: 1em;">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/KmzqeRZMfyo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>




    <div class="header-container">
        <h2>Effectuer des missions</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p>
            Il faut bien renflouer les caisses pour permettre l'entretien de votre bateau ! Les missions sont là pour ça ! Une fois que vous avez repéré la mission qui vous correspond, vous pouvez vous rendre dans un de ses ports de départ pour l'activer.
        </p>
        <div class="text-center" style="margin: 1em;">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/x4My3CGHopQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <p>
            Vous recevrez votre récompense une fois que votre tâche aura été remplie.
        </p>
        <div class="container text-center">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle" style="font-size: 1.2em; margin: 0 1em;"></i>
                Vous ne pouvez faire qu'une seule mission à la fois
            </div>
        </div>
    </div>




    <div class="header-container">
        <h2>Mieux comprendre la modélisation de CVE</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p class="text-info">à venir...</p>
    </div>




    <div class="header-container">
        <h2>Proposer de nouveaux ports et lieux remarquables</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p class="text-info">à venir...</p>
    </div>




    <div class="header-container">
        <h2>Proposer de nouvelles missions</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p class="text-info">à venir...</p>
    </div>
</div>

<script>
    $('.header-container').click(function() {
        if ($(this).find('.fas').hasClass('fa-chevron-down')) {
            $(this).find('.fas').removeClass('fa-chevron-down');
            $(this).find('.fas').addClass('fa-chevron-right');
            $(this).next().next().css('max-height', '0');
            $(this).next().next().css('margin-bottom', '0');
        } else if ($(this).find('.fas').hasClass('fa-chevron-right')) {
            $(this).find('.fas').removeClass('fa-chevron-right');
            $(this).find('.fas').addClass('fa-chevron-down');
            $(this).next().next().css('max-height', '100vh');
            $(this).next().next().css('margin-bottom', '5em');
        }
    });
</script>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
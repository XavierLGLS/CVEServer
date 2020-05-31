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
    <h3>How to use this addon?</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">




    <div class="header-container">
        <h2>Short introduction</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p class="text-info">
            This addon is a platform designed and developed for the community and by the community. It allows you to add a bit of realism to the navigation planning in the <a href="https://sailaway.world/" target="_blank">sailaway game</a>. The goal is to sail your boat, maintain it and plan your sailings while keeping a financial balance.
        </p>
        <div class="container text-center">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle" style="font-size: 1.2em; margin: 0 1em;"></i>
                If you encounter a bug or if you have a question, please go to <a href="<?= $DISCORD_LINK ?>" target="_blank">the discord of the addon</a>
            </div>
        </div>
        <h4>Getting started</h4>
        <p>
            Start by <a href="../register">creating an account</a> on this site. When you create it, you will link it to one of your sailaway boats. Each type of boat has its own characteristics, they are described <a href="../boat_characteristics" target="_blank">here</a>. Choose it well!
        </p>
        <h4>How to play ?</h4>
        <p>
            This platform is connected to sailaway. It allows you to follow the progress of your boat. However, you can only control it from within the game. You will have to navigate from port to port to ensure the supply of your boat, find missions to finance the maintenance of your boat.
        </p>
    </div>




    <div class="header-container">
        <h2>Explore ports and missions</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p>
            This <a href="../pois" target="_blank">page</a> allows you to explore without any limit all the ports, anchorages and remarkable places available in CVE. You can thus locate the places that will allow you:
            <ul>
                <li>to get water</li>
                <li>to buy some food</li>
                <li>to buy spare parts</li>
                <li>to use a dry dock</li>
            </ul>
            You can also watch missions available in each harbour.
        </p>
        <div class="text-center" style="margin: 1em;">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/q16eT2JHqmU" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <div class="container text-center">
            <div class="alert alert-info">
                <i class="fas fa-info-circle" style="font-size: 1.2em; margin: 0 1em;"></i>
                The blue disk that appears when hovering over the icons corresponds to the area in which CVE considers that a boat has arrived at its destination.
            </div>
        </div>
        <h4>Points of interest</h4>
        <ul style="list-style-type:none">
            <li style="margin: 1em 0;"><img src="../../assets/poi_harbour_icon.png" height="30" width="30"> harbour (<img src="../../assets/poi_harbour_mission_icon.png" height="30" width="30"> when it contains a mission)</li>
            <li style="margin: 1em 0;"><img src="../../assets/poi_anchorage_icon.png" height="30" width="30"> anchorage (<img src="../../assets/poi_anchorage_mission_icon.png" height="30" width="30"> when it contains a mission)</li>
            <li style="margin: 1em 0;"><img src="../../assets/poi_oddity_icon.png" height="30" width="30"> oddity</li>
            <li style="margin: 1em 0;"><img src="../../assets/poi_waypoint_icon.png" height="30" width="30"> mission waypoint</li>
        </ul>
    </div>




    <div class="header-container">
        <h2>Manage the maintenance of your boat</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p>
            The presence of water and food on board is essential to maintain the fitness of the skipper and his crew. The more people on board, the faster these stocks are consumed over time.
        </p>
        <p>
            Sails and hulls degrade over time. Beware, below 80%, the degradation accelerates significantly! Bad weather also accelerates the degradation. The method of damage calculation is explained <a href="../damage_model_info" target="_blank">here</a>.
        </p>
        <h4>Repairing your boat</h4>
        <p>
            <ul>
                <li>
                    use a repair part that recovers 10% of structure
                </li>
                <li>
                    place your boat in a dry dock that repairs 5% of the hull and sails every day
                </li>
            </ul>
        </p>
        <div class="container text-center">
            <div class="alert alert-info">
                <i class="fas fa-info-circle" style="font-size: 1.2em; margin: 0 1em;"></i>
                When a boat is in dry dock, no resources are consumed. It is the ideal solution to put CVE on pause!
            </div>
        </div>
        <div class="text-center" style="margin: 1em;">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/KmzqeRZMfyo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>




    <div class="header-container">
        <h2>Conducting missions</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p>
            You have to cover your expenses to allow for the maintenance of your boat! That's what missions are for! Once you've spotted the mission that corresponds to you, you can go to one of its ports of departure to activate it.
        </p>
        <div class="text-center" style="margin: 1em;">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/x4My3CGHopQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <p>
            You will receive your reward once your task has been completed.
        </p>
        <div class="container text-center">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle" style="font-size: 1.2em; margin: 0 1em;"></i>
                You can only do one mission at a time...
            </div>
        </div>
    </div>




    <div class="header-container">
        <h2>Better understand of the CVE model</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p class="text-info">coming up...</p>
    </div>




    <div class="header-container">
        <h2>Propose new ports, anchorages and oddities</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p class="text-info">coming up...</p>
    </div>




    <div class="header-container">
        <h2>Propose new missions</h2>
        <i class="fas fa-chevron-down" style="font-size: 2em; margin-right: 1em;"></i>
    </div>
    <hr>
    <div class="part-container">
        <p class="text-info">coming up...</p>
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
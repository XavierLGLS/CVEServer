<?php require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config_v2.php'; ?>

<style>
    .icons-tab{
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fab.fa-discord:hover {
        transition: 0.2s;
    }

    .fab.fa-discord:hover {
        color: #ffc107;
    }

    .link-icon {
        transition: 0.2s;
    }

    .link-icon:hover {
        fill: #ffc107;
    }
</style>

<div class="footer" style="height: 90px; padding: 0; margin: 0;">
    <hr style="border: 1px solid #ffc107; margin: 0;">
    <div style="background: #eeeeee; padding: 1em; margin: 0;">
        <div class="icons-tab">
            <a href="https://sailaway.world" target="_blank" style="margin-right: 10px;">
                <svg class="link-icon" style="height: 30px; width: 30px;">
                    <title>sailaway</title>
                    <use href="../../assets/custom_logos.svg#sailaway2"></use>
                </svg>
            </a>
            <a href="<?= $DISCORD_LINK ?>" target="_blank" style="text-decoration: none; color: inherit;"><i class="fab fa-discord" style="font-size: 2em;"></i></a>
        </div>
        <div class="text-center">
            platform developed by <a href="https://www.linkedin.com/in/xavier-le-gal-la-salle-9a6437113/">FinistèreForEver</a> with the active support of EaglePet, Shaun, Blackboro
        </div>
    </div>
</div>
</body>

</html>
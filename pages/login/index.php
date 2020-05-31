<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Login in</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
    <div class="row">
        <div class="col-md text-center"></div>
        <div class="col-md text-center">
            <form action="../../backend/accounts.php" method="post">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="app-token" value="<?= $CVE_WEB_API_KEY ?>">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">@</span>
                        </div>
                        <input type="mail" class="form-control" name="email" placeholder="mail" style="text-align:center;" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">&#128273;</span>
                        </div>
                        <input type="password" class="form-control" name="password" placeholder="password" style="text-align:center;" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">remember me</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Log in</button>
            </form>
            <p class="text-center">
                <a href="../password_change_request" class="btn">forgot password ?</a>
                <a href="../register" class="btn">not registered yet ?</a>
            </p>
        </div>
        <div class="col-sm text-center"></div>
    </div>
</div>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
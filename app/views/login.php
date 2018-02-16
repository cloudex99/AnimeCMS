<?php
$_SESSION['login_redirect'] = $_SERVER['HTTP_REFERER'] ?? '/';
if (User::isLoggedIn()) {
    header('Location: /');
}
?>
<?php include 'header.php'; ?>
    <section>
        <div class="container">
            <?php
            if(isset($_GET['error'])){
                echo "<div class='alert alert-danger' role='alert'>Incorrect Username/Password</div>";
            }
            ?>
            <div class="jumbotron">
                <form class="form-signin" method="post" action="<?=LOGIN_ENDPOINT?>">
                    <h2 class="form-signin-heading">Please sign in</h2>

                    <div class="form-group">
                        <label for="login-username" class="sr-only">Username</label>
                        <input type="text" id="login-username" class="form-control" placeholder="Username" name="username" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="inputPassword" class="sr-only">Password</label>
                        <input type="password" id="login-password" class="form-control" placeholder="Password" name="password" required>
                    </div>

                    <div class="form-check mb-2">
                        <input type="hidden" name="remember" value="0" />
                        <input type="checkbox" name="remember" value="1" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Stay logged in?</label>
                    </div>


                    <div class="invalid-feedback mb-2"></div>

                    <button id="btn-login" class="btn btn-lg btn-primary btn-block">Sign in</button>
                </form>
            </div>
        </div> <!-- /container -->
    </section>
<?php include 'footer.php';?>
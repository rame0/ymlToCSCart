<?php
/*
 * Copyright (C) 2016 R@Me0 <r@me0.biz>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * Sign In page
 *
 */

// load config
include 'config.php';

// check if useser is logged in
if ($isLoggedIn === true) {
    header("Location:$mainLocation");
    die();
} else {
    // if not, check if he send POST with creditionals and if they are correct
    if (!empty($_POST['login']) && !empty($_POST['password'])) {
        if ($_POST['login'] == $config['login']['USER_LOGIN'] && md5($_POST['password']) == md5($config['login']['USER_PASS'])) {
            setcookie('login', $config['login']['USER_LOGIN']);
            setcookie('password', md5($config['login']['USER_PASS']));
            header("Location:$mainLocation");
            die();
        } else {
            // if creditionals not correct show login form with error
            show_form('Login or password incorrect', $_POST['login']);
        }
    } else {
        // if no data sent show login form
        show_form();
    }
}

/**
 * Shows login box
 * @param mixed $isError [optional] If no message nidded set to false, otherwise provide error message
 * @param string $login [optional] Provide user login to fill input on form
 */
function show_form($isError = false, $login = '') {
    include 'tpl/head.php';
    if ($isError) {
        ?><div class="alert alert-danger" role="alert"><?=$isError?></div><?php
    }
    ?>
    <form class="form-signin" method="POST">
        <h2 class="form-signin-heading">Please sign in</h2>
        <label for="login" class="sr-only">Login</label>
        <input name="login" id="login" class="form-control" placeholder="Login" required autofocus<?= empty($login) ? '' : " value=\"$login\"" ?>>
        <label for="password" class="sr-only">Password</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
        <div class="checkbox">
            <label>
                <input type="checkbox" value="remember-me"> Remember me
            </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>

    <?php
    include 'tpl/foot.php';
}

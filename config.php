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

// Load Config
$config = parse_ini_file('config.ini.php', TRUE);

// Die if config not found or broken
if ($config === false) {
    die('System error: no config file or it can\'t be opened!');
}

// Load sign in page if not signed
$isLoggedIn = false;
if ($config['login']['USE_PASS'] === 'NO') {
    $isLoggedIn = true;
} elseif (isset($_COOKIE['login']) && isset($_COOKIE['password']) && $_COOKIE['login'] == $config['login']['USER_LOGIN'] && $_COOKIE['password'] == md5($config['login']['USER_PASS'])) {
    $isLoggedIn = true;
}

// define main scripts
$mainLocation = 'http://' . $_SERVER['HTTP_HOST'] . '/main.php';
$loginLocation = 'http://' . $_SERVER['HTTP_HOST'] . '/index.php';

// file folders
$ymlDir = 'files/yml' . DIRECTORY_SEPARATOR;
$csvDir = 'files/csv' . DIRECTORY_SEPARATOR;
$presetDir = 'files/preset' . DIRECTORY_SEPARATOR;
$resultsDir = 'files/results' . DIRECTORY_SEPARATOR;

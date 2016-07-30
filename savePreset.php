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

include 'config.php';

if (!empty($_POST) && !empty($_POST['data']) && is_array($_POST['data']) && !empty($_POST['fname']) && is_string($_POST['fname'])) {
    if (file_put_contents("$presetDir{$_POST['fname']}.preset", serialize($_POST['data']))) {
        return json_encode(["result" => 'done']);
    } else {
        json_encode(["result" => 'error', 'message' => "Can't save file"]);
    }
}
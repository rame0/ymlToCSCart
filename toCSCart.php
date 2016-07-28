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

if (file_exists('laptop2.xml')) {
    $xml = simplexml_load_file('laptop2.xml');

    $k = 0;
    $catrgories = [];

    foreach ($xml->shop->categories->category as $cat) {
        /* @var $cat SimpleXMLElement */
        $catrgories["{$cat['id']}"] = "$cat[0]";
    }
    print_r($catrgories);
    foreach ($xml->shop->offers->offer as $offer) {
        print_r($offer);
        $k++;
        if ($k > 3)
            break;
    }
}
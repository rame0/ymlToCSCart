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

if ($isLoggedIn === false) {
    header("Location:$loginLocation");
    die();
}

include 'tpl/head.php';
?>
<h1>Step 1: Select YML and CSV</h1>
<p>* CSV file is exported products from CS-Cart. It is used for fields detection for next step.</p>
<p>CSV file should contain the first line with field names and the second line with goods data (for detecting goods features). Other lines will be ignored.</p>
<p>This files should be uploaded via FTP. Paths is:</p>
<ol>
    <li>YML: &lt;script_dir&gt;/files/yml</li>
    <li>CSV: &lt;script_dir&gt;/files/csv</li>
</ol>

<p class='alert alert-info'>It is highly recommended to remove unnecessary columns from CSV file. It might be easier to bind CSV and YML on next step without them.</p>

<form method="POST" action="step2.php">
    <label for="yml">YML</label>

    <?
    // get availuble YMLs
    $ymlFiles = scandir('files/yml');
    if ($ymlFiles === false):
        ?>
        <input class="form-control" type="text" value="Files not found" readonly="">
    <? else: ?>
        <select class="form-control" name="yml">
            <? foreach ($ymlFiles as $yml) : ?>
                <? if (!in_array($yml, array('.', '..'))): ?>
                    <option value="<?= $yml ?>"><?= $yml ?></option>
                <? endif; ?>
            <? endforeach; ?>
        </select>
    <? endif; ?>
    <label for = "csv">CSV</label>
    <?
    // get availuble CSVs
    $csvFiles = scandir('files/csv');
    if ($csvFiles === false):
        ?>
        <input class="form-control" type="text" value="Files not found" readonly="">
    <? else: ?>
        <select class="form-control" name="csv">
            <? foreach ($csvFiles as $csv) : ?>
                <? if (!in_array($csv, array('.', '..'))): ?>
                    <option value="<?= $csv ?>"><?= $csv ?></option>
                <? endif; ?>1
            <? endforeach; ?>
        </select>
    <? endif; ?>
    <label for = "images">
        <input name="images" id="images" type="checkbox">
        It is Images CSV
    </label>
    <button class = "btn btn-lg btn-primary btn-block" type = "submit">Next Step</button>
</form>

<?
include 'tpl/foot.php';

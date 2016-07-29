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
include 'tpl/head.php';
?>
<h1>Step 2: Fields mapping</h1>
<div class="row">
    <div class="col-sm-10">
        <form method="POST" action="step3.php" class="form-horizontal field-mapping">
            <? if (!is_file($ymlDir . $_POST['yml'])) { ?>
                <h3 class="alert alert-danger">YML not found!</h3>
                <button class="btn btn-danger" onclick="javascript:history.go(-1)">Back</button>
            <? } elseif (!is_file($csvDir . $_POST['csv'])) { ?>
                <h3 class="alert alert-danger">CSV not found!</h3>
                <button class="btn btn-danger" onclick="javascript:history.go(-1)">Back</button>
                <?
            } else {

                // if filea are availuble, load them
                $yml = simplexml_load_file($ymlDir . $_POST['yml']);
                $csv = fopen($csvDir . $_POST['csv'], "r");
                // Get data about fields from CSV
                $csvFields = fgetcsv($csv, 0, ';');
                $csvGoodsData = fgetcsv($csv, 0, ';');

                // Load offers child nodes from YML
                $params = $yml->xpath('/yml_catalog/shop/offers/offer/child::*');
                //var_dump($params);die();
                $usedYMLParams = [];
                $ymlParams = [];

                /* @var $params SimpleXMLElement */
                // Find out what we got in offer node attributes and add them in our list
                // also save in value where option is come from
                foreach ($yml->shop->offers->offer[0]->attributes() as $atrName => $atrVal) {
                    $usedYMLParams[] = "attr-$atrName";
                    $ymlParams[] = ["attr-$atrName", $atrVal->__toString()];
                }

                // find out which child nodes of offer we have
                // and save 'param' childs with unique names as separate fields
                // we need only unique tags and params, so we have to check that too
                // also save in value where option is come from
                foreach ($params as $param) {
                    /* @var $param SimpleXMLElement */
                    $tagName = $param->getName();
                    // if we find param, thet get its attr 'name' value as param name
                    if ($tagName == "param") {
                        if (!in_array('tag-param-' . $param['name']->__toString(), $usedYMLParams)) {
                            $usedYMLParams[] = 'tag-param-' . $param['name']->__toString();
                            $ymlParams[] = ['tag-param-' . $param['name']->__toString(), $param->__toString()];
                            // set special value, so we will know that we have to build this value before add to results
                        } else {
                            continue;
                        }
                    } elseif ($tagName == "categoryId" && !in_array("build_Cat_Name", $usedYMLParams)) {
                        $usedYMLParams[] = "build_Cat_Name";
                        $ymlParams[] = ['build_Cat_Name', ""];
                        // save other tags
                    } elseif (!in_array("tag-$tagName", $usedYMLParams)) {
                        $usedYMLParams[] = "tag-$tagName";
                        $ymlParams[] = ["tag-$tagName", $param->__toString()];
                    }
                }
                // sorting array might be usefull
                sort($ymlParams, SORT_ASC);

                // now, we'll fill our forv
                ?>
                <div class="form-group">
                    <h3 class="col-sm-3">CSV field</h3>
                    <div class="col-sm-9">
                        <h3>YML field</h3>
                    </div>
                </div>
                <?
                foreach ($csvFields as $csvFldKey => $fld) :
                    if ($fld == "Features") {
                        $features = explode("; ", $csvGoodsData[$csvFldKey]);
                        ?>
                        <div class="col-sm-11 col-sm-offset-1">
                            <h3>Params</h3>
                            <?
                            foreach ($features as $feature) {
                                list($name, $tmp) = explode(": ", $feature);
                                $tmp = explode("[", $tmp);
                                $type = $tmp[0];
                                $value = trim($tmp[1], "]");

                                showOption("feature-$name", $name, $value, $ymlParams, $config);
                            }
                            ?>
                        </div>
                        <?
                        continue;
                    }
                    showOption($csvFldKey, $fld, $csvGoodsData[$csvFldKey], $ymlParams, $config);
                    ?>
                <? endforeach; ?>
            <? }
            ?>
        </form>
    </div>
    <div class="col-sm-2">
        <div class="row">
            <h4>Use preset</h4>
            <span class="small alert-info">In development</span>
            <div>
                <select class="form-control" id="preset">
                    <option value="none">--</option>
                </select>
            </div>
        </div>
        <hr/>
        <div class="row">
            <button id="savePreset" class="btn btn-primary">Save Preset</button>
        </div>

        <div class="row">
            <button id="nextStep" class="btn btn-primary">Next Step</button>
        </div>
    </div>
</div>
<?
include 'tpl/foot.php';

/**
 * Function to add select with YML fields for CSV<->YML field mapping
 * @param int $id id of CSV field
 * @param string $name Name of CSV field
 * @param string $value Demo value of CSV field
 * @param array $ymlParams Array with YML fields
 * @param array $config Array with config
 */
function showOption($id, $name, $value, $ymlParams, $config) {
    ?>
    <div class="form-group">
        <label for="fld-<?= $id ?>" class="col-sm-3"><div><?= $id ?> - <?= $name ?></div><span class="small"><?= $value ?></span></label>
        <div class="col-sm-9">
            <select class="form-control ympOption" name="csv" id="fld-<?= $id ?>">
                <option value='-1'> -- </option>
                <option value='text'> Custom text</option>
                <?
                foreach ($ymlParams as $ymlParam) :
                    // if field mapped in config make it selected by default
                    ?>
                    <option value="<?= $ymlParam[0] ?>" data-sample="<?= base64_encode($ymlParam[1]) ?>"<?= isset($config['default_mapping']['field'][$id]) && $config['default_mapping']['field'][$id] == $ymlParam[0] ? ' selected' : '' ?>><?= "$ymlParam[0]" ?></option>
                <? endforeach; ?>
            </select>
            <div class="sampleData"><span class="title">Пример: </span><span class="data"></span></div>
            <div id="dataModifer">
                <div><label><input type="checkbox" id="enableConstructor"/> Enable data modifer</label></div>
                <div class="constructor"></div>
            </div>
        </div>
    </div>
    <?
}

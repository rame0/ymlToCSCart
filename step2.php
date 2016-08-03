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

$presetsFiles = scandir($presetDir);
$presets = [];
foreach ($presetsFiles as $value) {
    if (!in_array($value, array('.', '..'))) {
        $presets[] = $value;
    }
}
?>

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
    fclose($csv);

    // Load offers child nodes from YML
    $params = $yml->xpath('/yml_catalog/shop/offers/offer/child::*');
    $usedYMLParams = [];
    $ymlParams = [];
    ?>
    <input id="ymlFilePath" value="<?= $ymlDir . $_POST['yml'] ?>" style="display:none">
    <input id="csvFilePath" value="<?= $csvDir . $_POST['csv'] ?>" style="display:none">
    <h1>Step 2: Fields mapping</h1>
    <div class="row">
        <div class="col-sm-10">
            <h2>YML categories to export</h2>
            <select class="form-control catSelector">
                <option value='-1'> All </option>
                <? foreach ($yml->shop->categories->category as $category) : ?>
                    <option value="<?= $category['id'] ?>"><?= $category->__toString() ?></option>
                <? endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-10">
            <h2>Map the fields</h2>
            <?
            if (!empty($_POST['images']) && $_POST['images'] == 'on') {
                ?><div class="alert alert-danger">
                    Only "Product Code" is necessary. All other fields will be ignored!
                </div><?
                echo '<form method="POST" action="convertForImages.php" class="form-horizontal field-mapping">';
            } else {
                echo '<form method="POST" action="step3.php" class="form-horizontal field-mapping">';
            }
            /* @var $params SimpleXMLElement */
            // Find out what we got in offer node attributes and add them in our list
            // also save in value where option is come from
            foreach ($yml->shop->offers->offer[0]->attributes() as $atrName => $atrVal) {
                $usedYMLParams[] = "attr-->$atrName";
                $ymlParams[] = ["attr-->$atrName", $atrVal->__toString()];
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
                    if (!in_array('tag-->param-->' . $param['name']->__toString(), $usedYMLParams)) {
                        $usedYMLParams[] = 'tag-->param-->' . $param['name']->__toString();
                        $ymlParams[] = ['tag-->param-->' . $param['name']->__toString(), $param->__toString()];
                        // set special value, so we will know that we have to build this value before add to results
                    } else {
                        continue;
                    }
                } elseif ($tagName == "categoryId" && !in_array("build_Cat_Name", $usedYMLParams)) {
                    $usedYMLParams[] = "build_Cat_Name";
                    $ymlParams[] = ['build_Cat_Name', ""];
                    $usedYMLParams[] = "cat_Name";
                    $ymlParams[] = ['cat_Name', ""];
                    // save other tags
                } elseif (!in_array("tag-->$tagName", $usedYMLParams)) {
                    $usedYMLParams[] = "tag-->$tagName";
                    $ymlParams[] = ["tag-->$tagName", $param->__toString()];
                }
            }
            // sorting array might be usefull
            sort($ymlParams, SORT_ASC);

            // now, we'll fill our form
            ?>
            <div class="form-group">
                <h3 class="col-sm-3">CSV field</h3>
                <div class="col-sm-9">
                    <h3>YML field</h3>
                </div>
            </div>
            <?
            foreach ($csvFields as $csvFldKey => $fld) {
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

                            showOption(crc32("feature-$name"), $name, [$value, $type], $ymlParams, $config);
                        }
                        ?>
                    </div>
                    <?
                    continue;
                }
                showOption(crc32($fld), $fld, $csvGoodsData[$csvFldKey], $ymlParams, $config);
            }
            echo '</form>';
            ?>
        </div>
        <div class="col-sm-2">
            <div class="row">
                <h4>Use preset</h4>
                <div>
                    <select class="form-control" id="preset">
                        <option value="-1">--</option>
                        <?
                        for ($i = 0; $i < count($presets); $i++):
                            ?>
                            <option value="<?= $presets[$i] ?>"><?= $presets[$i] ?></option>
                        <? endfor; ?>
                    </select>
                    <button id="loadPreset" class="btn btn-success btn-sm">Load preset</button>
                </div>
            </div>
            <hr/>
            <div class="row">
                <button id="savePreset" class="btn btn-primary btn-sm">Save Preset</button>
            </div>
            <hr/>
            <div class="row">
                <button id="nextStep" class="btn btn-primary">Next Step</button>
                <a class="btn btn-danger" href="/main.php">go HOME</a>
            </div>
        </div>
    </div>
    <?
}
include 'tpl/foot.php';

/**
 * Function to add select with YML fields for CSV<->YML field mapping
 * @param int $id id of CSV field
 * @param string $name Name of CSV field
 * @param mixed $value Demo value of CSV field. If $name is string it is used as it is. If $name is array, then the first item used as demo-value, and the second used as type.
 * @param array $ymlParams Array with YML fields
 * @param array $config Array with config
 */
function showOption($id, $name, $value, $ymlParams, $config) {
    $type = false;
    if (is_array($value)) {
        $type = $value[1];
        $value = $value[0];
    }
    ?>
    <div class="form-group">
        <label for="<?= $id ?>" class="col-sm-3">
            <div><?= $name ?></div>
            <span class="small"><?= $value ?></span>
            <? if ($type !== false && !empty($config['features_types']['feature'][$type])): ?>
                <div>Type: <?= $config['features_types']['feature'][$type] ?></div>
            <? endif ?>
        </label>
        <div class="col-sm-9">
            <select class="form-control ympOption" name="<?= $id ?>" id="<?= $id ?>" data-type="<?= $type === false ? "field" : "feature" ?>">
                <option value='-1'> -- </option>
                <option value='text'> Custom text</option>
                <?
                foreach ($ymlParams as $ymlParam) :
                    // if field mapped in config make it selected by default
                    ?>
                    <option value="<?= $ymlParam[0] ?>" data-sample="<?= base64_encode($ymlParam[1]) ?>"<?= isset($config['default_mapping']['field'][$id]) && $config['default_mapping']['field'][$id] == $ymlParam[0] ? ' selected' : '' ?>><?= "$ymlParam[0]" ?></option>
                <? endforeach; ?>
            </select>
            <div class="sampleData"><span class="title">Example: </span><span class="data"></span></div>
            <div class="dataModifer"></div>
        </div>
    </div>
    <?
}

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

header('Content-Type: application/json');

include 'config.php';

if (empty($_POST) || empty($_POST['data'])) {
    echo json_encode(['type' => 'error', 'msg' => 'Provide correct data!']);
} elseif (empty($_POST['yml']) || !is_file($_POST['yml'])) {
    echo json_encode(['type' => 'error', 'msg' => 'YML not found!']);
} elseif (empty($_POST['csv']) || !is_file($_POST['csv'])) {
    echo json_encode(['type' => 'error', 'msg' => 'CSV not found!']);
} else {

    $data = $_POST['data'];
    // if filea are availuble, load them
    $yml = simplexml_load_file($_POST['yml']);
    $csv = fopen($_POST['csv'], "r");
    // Get data about fields from CSV
    $csvFields = fgetcsv($csv, 0, ';');
    $csvGoodsData = fgetcsv($csv, 0, ';');
    fclose($csv);

//    print_r($csvFields);
//    print_r($_POST['data']);

    if (!is_dir($resultsDir)) {
        mkdir($resultsDir, '0764');
    }
    $newCSVFilename = basename($_POST['csv']);
    if (is_file($resultsDir . $newCSVFilename)) {
        $newCSVFilename = 'new_' . $newCSVFilename;
    }

    $newCSVFile = fopen($resultsDir . $newCSVFilename, 'w+');

    fputcsv($newCSVFile, $csvFields, ";", '"');

    $csvFieldsMap = [];
    $csvFeaturesMap = [];
    $featuresCol = -1;

    for ($i = 0; $i < count($csvFields); $i++) {
        $csvFieldsMap[crc32($csvFields[$i])] = $i;
        if ($csvFields[$i] === 'Features') {
            $featuresCol = $i;
            $features = explode("; ", $csvGoodsData[$i]);
            foreach ($features as $feature) {
                list($nameType, ) = explode("[", $feature);
                list($name, ) = explode(": ", $nameType);

                $csvFeaturesMap[crc32("feature-$name")] = $nameType;
            }
        }
    }
//    print_r($csvFieldsMap);


    $categoryesToConvert = '';
    foreach ($yml->shop->categories->category as $cat) {
        if ($_POST['catId'] == -1 || $cat['id'] == $_POST['catId'] || $cat['parentId'] == $_POST['catId']) {
            $categoryesToConvert [] = $cat['id']->__toString();
        }
    }
    $xpathOrderFilter = "//offer[categoryId=" . implode(" or categoryId=", $categoryesToConvert) . "]";

    $offers = $yml->xpath($xpathOrderFilter);

    $results = [];
    foreach ($offers as $offer) {
        $row = array_fill(0, count($csvFieldsMap), '');

        foreach ($csvFieldsMap as $fldMapKey => $fldMapIndex) {
            if (!empty($data['field'][$fldMapKey])) {
                $row[$fldMapIndex] = parseColData($data['field'][$fldMapKey], $offer);
            }
        }

        $featuresString = '';
        foreach ($csvFeaturesMap as $featureMapKey => $featureMapIndex) {
            if (!empty($data['feature'][$featureMapKey])) {
                $ftData = parseColData($data['feature'][$featureMapKey], $offer);
                $featuresString .= $featureMapIndex . "[$ftData]; ";
            }
        }
        $row[$featuresCol] = $featuresString;

        fputcsv($newCSVFile, $row, ";", '"');
    }

    fclose($newCSVFile);
}

function parseColData($data, $offer) {
    $catId = $offer->categoryId->__toString();
    $action = $data['ymlField'];
    $ret = '';
    switch ($action) {
        case 'text':
            $ret = $data['val'];
            break;
        case 'build_Cat_Name':
            // due to the nature of SimpleXMLElement, the xpath applied to the child will still be applied to the root of XML
            $offerCatalog = $offer->xpath("//category[@id=$catId]")[0];
            $catString = '';
            while ($offerCatalog !== false) {
                $catString = "$offerCatalog///$catString";
                if (!empty($offerCatalog['parentId'])) {
                    $offerCatalog = $offer->xpath("//category[@id=" . $offerCatalog['parentId'] . "]")[0];
                } else {
                    $offerCatalog = false;
                }
            }
            $ret = trim($catString, '/');
            break;
        default :
            $tmp = explode('-->', $action);
            if (count($tmp) == 2) {
                list($elType, $elName) = $tmp;
            } elseif (count($tmp) == 3) {
                list($elType, $elName, $elVal) = $tmp;
            } else {
                die(json_encode(['type' => 'error', 'msg' => 'Usupporded data structure!']));
            }
            switch ($elType) {
                case 'attr':
                    $ret = $offer[$elName]->__toString();
                    break;
                case 'tag':
                    if (empty($elVal)) {
                        $ret = $offer->$elName->__toString();
                    } else {
                        foreach ($offer->$elName as $tmpEl) {
                            if ($tmpEl['name'] == $elVal) {
                                $ret = $tmpEl->__toString();
                            }
                        }
                    }
                    break;
            }
            if (!empty($data['modifer'])) {
                switch ($data['modifer']['type']) {
                    case 'replace':
                        $what = $with = false;
                        foreach ($data['modifer']['params'] as $param) {
                            if ($param['name'] == "what") {
                                $what = $param['val'];
                            } elseif ($param['name'] == "with") {
                                $with = $param['val'];
                            }
                        }
                        if ($what !== false && $with !== false) {
                            $ret = str_replace($what, $with, $ret);
                        }
                        break;
                }
            }
            unset($elType, $elName, $elVal);
            break;
    }
    return $ret;
}

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Aero\Test\Options;
use Bitrix\Main\Loader;

function aeroCleanString($string, $limit = false){
    $string = strip_tags($string);
    $string = htmlspecialchars($string);
    if($limit){
        $string = substr($string, 0, $limit);
    }
    return $string;
}

try {
    if (!Loader::includeModule('iblock') || !Loader::includeModule('sale') || !Loader::includeModule('catalog') || !Loader::includeModule('aero.test')) 
	{
		throw new \Exception('Не загружены модули необходимые для работы компонента');
    }

    $options_instance = new \Aero\Test\Options;
	$arParams["IBLOCK_ID"] = $options_instance->GetIBlockID();
	$arParams["PRICE_TYPE"] = $options_instance->GetPriceType();
    $arParams["DISPLAY_OPTIONS"] = $options_instance->GetPropertiesFull();
    
    if (!$arParams["IBLOCK_ID"] || !$arParams["PRICE_TYPE"] || !$arParams["DISPLAY_OPTIONS"]) 
	{
		throw new \Exception('Модуль не настроен, пожалуйста перейдите в настройки модуля aero.test');
    }

    $arResult = array();

    $arResult['CURRENCY'] = COption::GetOptionString("sale", "default_currency","RUB");

    if (!$arResult['CURRENCY']) 
	{
		throw new \Exception('На сайте не настроена валюта по умолчанию');
    }

    $arResult["NAME"] = aeroCleanString($_POST["NAME"], 50);
    if(!$arResult["NAME"]){
        throw new \Exception('Некорректное название товара');
    }

    $arResult["PRICE"] = aeroCleanString(preg_replace('/[^0-9]/', '', $_POST["BASE_PRICE"]));
    if(!$arResult["PRICE"]){
        throw new \Exception('Некорректная стоимость товара');
    }

    if($_FILES["PREVIEW_PICTURE"] && in_array($_FILES["PREVIEW_PICTURE"]["type"], array('image/png', 'image/jpeg', 'image/gif'))){
        $arResult["PREVIEW_PICTURE"] = $_FILES["PREVIEW_PICTURE"];
    }

    foreach($arParams["DISPLAY_OPTIONS"] as $option){
        switch($option["PROPERTY_TYPE"]){
            case "S": //Строка
                if($_POST["PROPERTIES"][$option["CODE"]] && !is_array($_POST["PROPERTIES"][$option["CODE"]]) && $option["MULTIPLE"] != "Y"){
                    $arResult["PROPERTIES"][$option["CODE"]] = aeroCleanString($_POST["PROPERTIES"][$option["CODE"]]);
                } elseif(is_array($_POST["PROPERTIES"][$option["CODE"]]) && $option["MULTIPLE"] == "Y"){
                    foreach($_POST["PROPERTIES"][$option["CODE"]] as $prop_value){
                        $arResult["PROPERTIES"][$option["CODE"]][] = aeroCleanString($prop_value);
                    }
                }
                
                if($option["IS_REQUIRED"] == "Y" && !$arResult["PROPERTIES"][$option["CODE"]]){
                    throw new \Exception('Некорректное значение обязательного свойства '.$option["NAME"]);
                }
            break;
        }
    }

    if($_POST["CODE"] && aeroCleanString($_POST["CODE"]) != ""){
        $arResult["CODE"] = aeroCleanString($_POST["CODE"]);
    } else {
        $translit_params = array(
            "max_len" => "100", 
            "change_case" => "L", 
            "replace_space" => "_", 
            "replace_other" => "_", 
            "delete_repeat_replace" => "true", 
            "use_google" => "false", 
        );
        $arResult['CODE'] = CUtil::translit($arResult["NAME"], "ru", $params); // Транслитерация кода из названия
    }

    $el = new CIBlockElement;
            
    $arLoadProductArray = Array(
        "MODIFIED_BY"       => 1,
        "IBLOCK_SECTION_ID" => false, // На выбор категории времени не хватело
        "CODE"              => $arResult['CODE'],
        "IBLOCK_ID"         => $arParams["IBLOCK_ID"],
        "PROPERTY_VALUES"   => $arResult["PROPERTIES"],
        "NAME"              => $arResult["NAME"],
        "ACTIVE"            => "Y",
        "PREVIEW_PICTURE"   => $arResult["PREVIEW_PICTURE"],
    );


    if($ELEM_ID = $el->Add($arLoadProductArray)){
        $PROD_ID = CCatalogProduct::add(array("ID" => $ELEM_ID));

        if($PROD_ID){
            $arFields = Array(
                "CURRENCY" => $arResult['CURRENCY'],
                "PRICE" => $arResult["PRICE"],
                "CATALOG_GROUP_ID" => $arParams["PRICE_TYPE"],
                "PRODUCT_ID" => $ELEM_ID,
            );
            if($price_added = CPrice::Add($arFields)){
                echo 'success';
            }
        }
    } else {
        throw new \Exception($el->LAST_ERROR);
    }

}
catch (\Exception $ex)
{
	echo $ex->getMessage();
}

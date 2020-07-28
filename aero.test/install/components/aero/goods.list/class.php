<?php

use Aero\Test\Options;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class GoodsListComponent extends CBitrixComponent
{
    private $_request;

    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    private function _checkModules()
	{
		if (!Loader::includeModule('iblock') || !Loader::includeModule('sale') || !Loader::includeModule('aero.test')) 
		{
			throw new \Exception('Не загружены модули необходимые для работы компонента');
		}
    }

    public function executeComponent()
    {
		
		try
		{
	        $this->_checkModules();

			$options_instance = new \Aero\Test\Options;
	        $this->arParams["IBLOCK_ID"] = $options_instance->GetIBlockID();
	        $this->arParams["PRICE_TYPE"] = $options_instance->GetPriceType();
			$this->arParams["DISPLAY_OPTIONS"] = $options_instance->GetPropertiesFull();

			if (!$this->arParams["IBLOCK_ID"] || !$this->arParams["PRICE_TYPE"] || !$this->arParams["DISPLAY_OPTIONS"]) 
			{
				throw new \Exception('Модуль не настроен, пожалуйста перейдите в настройки модуля aero.test');
			}

			$this->_request = Application::getInstance()->getContext()->getRequest();
		
			//Определяем массив свойств
			$this->property_ids = array_column($this->arParams["DISPLAY_OPTIONS"], 'ID');

			//Тянем элементы из инфоблока
			$this->arResult["ITEMS"] = array();

			$arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "PREVIEW_PICTURE");
			$arFilter = Array("IBLOCK_ID" => $this->arParams["IBLOCK_ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
			$iblock_elements = \CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
			while ($element_ob = $iblock_elements->GetNextElement()) {
				$arFields = $element_ob->GetFields();
				if ((int)$arFields['PREVIEW_PICTURE'] > 0) {
					$arFields['PREVIEW_PICTURE'] = \CFile::GetPath($arFields['PREVIEW_PICTURE']);
				}
				$this->arResult["ITEMS"][$arFields["ID"]] = $arFields;
				$this->elementProps[$arFields["ID"]] = array();
			}

			// Тянем кастомные свойства для полученных элементов
			\CIBlockElement::GetPropertyValuesArray($this->elementProps, $this->arParams["IBLOCK_ID"], array(),
				$this->property_ids);

			// Перебираем айтемы
			foreach ($this->arResult["ITEMS"] as $id => &$item) {
				// Тащим цены для каждого айтема
				$arPrice = \CCatalogProduct::GetOptimalPrice($id, 1, array(), "N");
				if (!$arPrice["RESULT_PRICE"][0] && $arPrice["RESULT_PRICE"]["PRICE_TYPE_ID"] == $this->arParams["PRICE_TYPE"]) {
					//Если на сайте один тип цен и он сходится с настройками - просто положим его
					$item["PRICES"] = $arPrice["RESULT_PRICE"];
				} else {
					if ($arPrice["RESULT_PRICE"][0]) {
						//Иначе переберем массив с ценами и выведем нужную
						foreach ($arPrice["RESULT_PRICE"] as $price_item) {
							if ($price_item["PRICE_TYPE_ID"] == $this->arParams["PRICE_TYPE"]) {
								$item["PRICES"] = $price_item;
							}
						}
					}
				}

				// Объединяем филды и свойства
				$item["PROPERTIES"] = $this->elementProps[$id];
			}
			unset($item);
		}
		catch (\Exception $ex)
		{
			$this->arResult = ['ERROR_TEXT' => $ex->getMessage()];
		}

        $this->includeComponentTemplate();
    }
}

?>
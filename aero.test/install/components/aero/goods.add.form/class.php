<?php

use Aero\Test\Options;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class GoodsAddFormComponent extends CBitrixComponent
{
    private $_request;

    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    private function _checkModules()
	{
		if (!Loader::includeModule('aero.test')) 
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
		
		}
		catch (\Exception $ex)
		{
			$this->arResult = ['ERROR_TEXT' => $ex->getMessage()];
		}

        $this->includeComponentTemplate();
    }
}

?>
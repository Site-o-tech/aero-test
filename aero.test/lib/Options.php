<?
use Bitrix\Main;

namespace Aero\Test;

class Options{
    private $iblock_id;
    private $properties;
    private $price_type;
    private $accesible_types;

    public function GetAccesibleTypes(){
        $this->$accesible_types = array(
            "S" // Строка
        );
        return $this->$accesible_types;
    }
   
    public function GetIBlockId(){
        $this->$iblock_id = \Bitrix\Main\Config\Option::get("aero.test", "IBLOCK_ID", false, SITE_ID);
        return $this->$iblock_id;
    }

    public function GetProperties(){
        $this->$properties = explode(';', \Bitrix\Main\Config\Option::get("aero.test", "PROPERTIES", false, SITE_ID));
        return $this->$properties;
    }

    public function GetPropertiesFull(){
        $property_codes = $this->GetProperties();
        $iblock_id = $this->GetIBlockId();
        $properties_values = array();
        $properties = \Bitrix\Iblock\PropertyTable::getList([
			'filter' => [
				'IBLOCK_ID' => $iblock_id,
				'CODE' => $property_codes
			],
			'select' => [
                'ID', 'NAME', 'CODE', 'MULTIPLE', 'PROPERTY_TYPE', 'IS_REQUIRED'
            ]
		]);
		while ($prop_field = $properties->fetch()) {
			$properties_values[$prop_field["CODE"]] = $prop_field;
		}
        return $properties_values;
    }

    public function GetPriceType(){
        $this->$price_type = \Bitrix\Main\Config\Option::get("aero.test", "PRICE_TYPE", false, SITE_ID);
        return $this->$price_type;
    }
}
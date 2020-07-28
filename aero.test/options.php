<?
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock,
	Bitrix\Main\Localization\Loc;

if(!$USER->IsAdmin())
	return;

if (!\Bitrix\Main\Loader::includeModule('aero.test') || !\Bitrix\Main\Loader::includeModule('catalog'))
{
	return;
}

IncludeModuleLangFile(__FILE__);

$arAllOptions = array(
	array("ID" => "PRICE_TYPE", "NAME" => GetMessage("OPTION_PRICE_TYPE"), "TYPE" => "select"),
	array("ID" => "IBLOCK_ID", "NAME" => GetMessage("OPTION_IBLOCK_ID"), "TYPE" => "select"),
	array("ID" => "PROPERTIES", "NAME" => GetMessage("OPTION_PROPERTIES"), "TYPE" => "select_multiple"),
);


$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "ib_settings", "TITLE" => GetMessage("OPTION_SECTION_MAIN")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

// Тащим поддерживаемые типы свойств из модуля
$options_instance = new \Aero\Test\Options;
$accesible_types = $options_instance->GetAccesibleTypes();


if($_SERVER["REQUEST_METHOD"]=="POST" && ($_POST['Update'] || $_POST['Apply'])>0 && check_bitrix_sessid())
{
	foreach($arAllOptions as $arOption)
	{
		$name = $arOption["ID"];
		if(is_array($_REQUEST[$name])){
			$val = implode(';', $_REQUEST[$name]);
		} else {
			$val = $_REQUEST[$name];
		}

		COption::SetOptionString("aero.test", $name, $val);
	}

	if(strlen($_POST['Update'])>0 && strlen($_REQUEST["back_url_settings"])>0)
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"]));
} else if($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["action"] == "GetPropsForIBlock"){
	// Отдаем список свойств аяксу
	if($_POST["iblock_id"] == 'false'){
		echo 'false';
	} else {
		$properties_values = array();
		$properties = \Bitrix\Iblock\PropertyTable::getList([
			'filter' => [
				'IBLOCK_ID' => \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getPost('iblock_id'),
				'PROPERTY_TYPE' => $accesible_types
			],
			'select' => [
				'CODE', 'NAME', 'PROPERTY_TYPE'
			]
		]);
		while ($prop_field = $properties->fetch()) {
			$properties_values[] = array("ID" => $prop_field["CODE"], "NAME" => $prop_field["NAME"]);
		}

		echo json_encode($properties_values, JSON_UNESCAPED_UNICODE);
	}
	die();
}

// Выводим нужные значения для полей
$properties_values = array();
foreach($arAllOptions as &$option){
	switch($option["ID"]){
		case 'IBLOCK_ID':
			// Вытаскиваем инфоблоки торговых каталогов
			$val = COption::GetOptionString("aero.test", $option["ID"], false, SITE_ID);
			$res = CCatalog::GetList(Array("ID", "NAME"), Array("ACTIVE"=>"Y"), true);
			while($ar_res = $res->Fetch()){
				$option["VALUES"][] = array("ID" => $ar_res["IBLOCK_ID"], "NAME" => $ar_res["NAME"]);
				// Если инфоблок выбран - надо вытащить его свойства для поля "PROPERTIES"
				if($val == $ar_res["IBLOCK_ID"]){
					// Парсим по полученным параметрам и сторим в переменную для PROPERTIES
					$properties = \Bitrix\Iblock\PropertyTable::getList([
						'filter' => [
							'IBLOCK_ID' => $ar_res["IBLOCK_ID"],
							'PROPERTY_TYPE' => $accesible_types
						],
						'select' => [
							'CODE', 'NAME', 'PROPERTY_TYPE'
						]
					]);
					while ($prop_field = $properties->fetch()) {
						$properties_values[] = array("ID" => $prop_field["CODE"], "NAME" => $prop_field["NAME"]);
					}
				}
			}
		break;
		case 'PRICE_TYPE':
			$res = \Bitrix\Catalog\GroupTable::getList([
				'select' => [
					'ID', 'NAME'
				]
			]);
			while($ar_res = $res->Fetch()){
				$option["VALUES"][] = array("ID" => $ar_res["ID"], "NAME" => $ar_res["NAME"]);
			}
		break;
		case 'PROPERTIES':
			if($properties_values) {
				$option["VALUES"] = $properties_values;
			}
		break;
	}
}

$tabControl->Begin();
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?echo LANGUAGE_ID?>">
	<?$tabControl->BeginNextTab();?>
	<?
	foreach($arAllOptions as $arOption){
		$val = COption::GetOptionString("aero.test", $arOption["ID"], false, SITE_ID);
		?>
		<tr>
			<td width="20%" nowrap <?if($type[0]=="textarea") echo 'class="adm-detail-valign-top"'?>>
				<label for="<?echo htmlspecialcharsbx($arOption["ID"])?>"><?=$arOption["NAME"]?>:</label>
			<td width="80%">
				<?if($arOption["TYPE"]=="select"){?>
					<select name="<?echo htmlspecialcharsbx($arOption["ID"])?>">
						<option value="false">Не выбрано</option>
						<?
						foreach ($arOption["VALUES"] as $key => $value)
						{
							?><option value="<?= $value["ID"] ?>"<?= ($value["ID"] == $val) ? " selected" : "" ?>><?= $value["NAME"] ?></option><?
						}
						?>
					</select>
				<?} elseif($arOption["TYPE"]=="select_multiple"){
					$val = explode(';', $val)?>
					<select name="<?echo htmlspecialcharsbx($arOption["ID"])?>[]" multiple style="min-width: 200px">
						<?
						foreach ($arOption["VALUES"] as $key => $value)
						{
							?><option value="<?= $value["ID"] ?>"<?= (in_array($value["ID"], $val)) ? " selected" : "" ?>><?= $value["NAME"] ?></option><?
						}
						?>
					</select>
				<?}?>
			</td>
		</tr>
	<?}?>

	<?$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" class="adm-btn-save">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>">
	<?=bitrix_sessid_post();?>
	<?$tabControl->End();?>
</form>

<script>
	var ID_select = document.querySelector('[name="IBLOCK_ID"]');
	var PROPS_select = document.querySelector('[name="PROPERTIES[]"]');
	ID_select.onchange = function(e){
		const request = new XMLHttpRequest();
		const url = window.location.href;
		const params = "action=GetPropsForIBlock&iblock_id=" + ID_select.value;
		request.open("POST", url, true);
		request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		request.addEventListener("readystatechange", () => {
			if(request.readyState === 4 && request.status === 200) {     
				PROPS_select.innerHTML = false; 
				var optionsHtml = '';
				if(request.responseText != 'false'){
					JSON.parse(request.responseText).forEach(function(elem, index, array) {
						optionsHtml += '<option value="'+elem.ID+'">'+elem.NAME+'</option>';
					});
					PROPS_select.innerHTML = optionsHtml;
				}
			}
		});
		request.send(params);
	};

</script>
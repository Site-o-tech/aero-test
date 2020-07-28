<?if($arResult["ERROR_TEXT"])
{
   echo $arResult["ERROR_TEXT"];
} else {?>
<h2>Добавить новый товар</h2>
<form enctype="multipart/form-data" id="product_addform" method="post" action="<?=$componentPath?>/ajax.php">
   <?=bitrix_sessid_post()?>
   <div class="inputwrap">
      <label>Название товара*</label>
      <input type="text" name="NAME" required maxlength="50">
   </div>
   <div class="inputwrap">
      <label>Символьный код</label>
      <input type="text" name="CODE" maxlength="50">
   </div>
   <div class="inputwrap">
      <label>Базовая цена*</label>
      <input type="number" name="BASE_PRICE" required>
   </div>
   <div class="inputwrap">
      <label>Картинка для анонса</label>
      <input type="file" name="PREVIEW_PICTURE" accept="image/jpeg,image/png,image/gif">
   </div>
   <h4>Свойства:</h4>
   <?foreach($arParams["DISPLAY_OPTIONS"] as $option){?>
   <div class="inputwrap">
      <label><?=$option["NAME"]?><?echo $option["IS_REQUIRED"] == 'Y' ? '*' : '';?></label>
      <?switch($option["PROPERTY_TYPE"]){
         case 'S':
            // Строка
         ?>
            <input type="text" name="PROPERTIES[<?=$option["CODE"]?>]<?echo $option["MULTIPLE"] == 'Y' ? '[]' : '';?>" <?echo $option["IS_REQUIRED"] == 'Y' ? 'required' : '';?>>
         <?
         break;
      }
      if($option["MULTIPLE"] == "Y"){?>
         <a class="addinputbtn" data-role="addinput" data-target="<?=$option["CODE"]?>">Добавить вариант</a>
      <?}?>
   </div>
   <?}?>
   <input type="submit" value="Создать">
</form>
<?}?>
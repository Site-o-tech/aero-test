<?if($arResult["ERROR_TEXT"])
{
   echo $arResult["ERROR_TEXT"];
} else if(!$arResult["ITEMS"]) {
   echo 'Товаров нет';
} else {?>
<div class="products_block">
   <h2>Список товаров</h2>
   <?foreach($arResult["ITEMS"] as $item){?>
   <div class="products_item">
      <div class="products_item__image" style="background-image:url('<?=$item["PREVIEW_PICTURE"]?>')"></div>
      <div class="products_item__info">
         <h3><?=$item["NAME"]?></h3>
         <p class="price">
            <?if($item["PRICES"] && $item["PRICES"]["BASE_PRICE"] > $item["PRICES"]["DISCOUNT_PRICE"]){?>
            <span class="oldprice"><?=CurrencyFormat($item["PRICES"]["BASE_PRICE"], $item["PRICES"]["CURRENCY"])?></span>   
            <?}?>
            <span><?=CurrencyFormat($item["PRICES"]["DISCOUNT_PRICE"], $item["PRICES"]["CURRENCY"])?></span>
         </p>
         <h4>Свойства товара:</h4>
         <?foreach($item["PROPERTIES"] as $prop){
            if($prop["VALUE"]){?>
            <p class="product_item__property">
               <?
               if(is_array($prop["VALUE"])){
                  $prop_string = implode(', ', $prop["VALUE"]);?>
                  <?=$prop["NAME"]?>: <?=$prop_string?>
               <?} else {?>
                 <?=$prop["NAME"]?>: <?=$prop["VALUE"]?> 
               <?}?>
            </p>
            <?}
         }?>
      </div>
   </div>
   <?}?>
</div>
<?}?>
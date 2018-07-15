<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(array('ajax'));

if (empty($arResult['ERRORS']))
{
    // Подключаем файлы скриптов
    $APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/js/mibix.basket.available/script.js"></script>', true);

    // Подключаем разные стили по запросу из параметров
    switch ($arParams['COLOR_CHEMES'])
    {
        case "blue-bx":
            $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/blue-bx.css", false);
            break;
        case "green-bx":
            $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/green-bx.css", false);
            break;
        case "yellow-bx":
            $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/yellow-bx.css", false);
            break;
        case "red-bx":
            $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/red-bx.css", false);
            break;
        case "gray-blue":
            $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/gray-blue.css", false);
            break;
        case "gray-green":
            $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/gray-green.css", false);
            break;
        case "gray-yellow":
            $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/gray-yellow.css", false);
            break;
        default: // По умолчанию -> Черно-красная
            $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/gray-red.css", false);
    }
}
?>

<div class="mbx-basket_buffer-space"></div>
<div class="mbx-basket_bottom-panel" id="cartPanel">
    <div class="mbx-basket_bottom-panel-i">
        <div class="mbx-basket_inner mbx-basket_cleared">
            <div class="mbx-basket_bottom-panel-feedback">
                <?if (!$USER->IsAuthorized()):?>
                    <a href="<?=$arParams['PATH_TO_AUTH']?>" class="bt2 signin"><?=GetMessage("MIBIX_LOGIN")?></a>&nbsp;&nbsp;
                    <a href="<?=$arParams['PATH_TO_REGISTRATION']?>" class="signup"><?=GetMessage("MIBIX_REGISTRATION")?></a>
                <?else:?>
                    <a class="bt2" href="<?=$arParams['PATH_TO_PERSONAL']?>"><span class="b-button__i"><?=GetMessage("MIBIX_CABINET")?> [
                            <?
                            $name = trim($USER->GetFullName());
                            if (strlen($name) <= 0) $name = $USER->GetLogin();
                            echo htmlspecialcharsEx($name);
                            ?>]
                    </span></a>&nbsp;&nbsp;
                    <a href="<?=$APPLICATION->GetCurPageParam("logout=yes", Array("logout"))?>" class="logout"><?=GetMessage("AUTH_LOGOUT")?></a>
                <?endif;?>
            </div>
            <ul class="mbx-basket_cart-short mbx-basket_cleared">
                <li>
                    <div class="mbx-basket_cart mbx-basket_serv" id="compareLabel">
                        <div id="scrollNavArrow" class="scroll_arrow<?if($arParams["SHOW_SCROLL_LINK"]!="Y"){ echo " disabled"; }?>"></div>
                        <?
                        //Здесь Вы можете подключить компонент "Сравнения", чтобы выводить в панели кнопку и количество товара в списке сравнения. В шаблоне используйте следующий код:
                        /*<div id="compare">
                            <a class="active" href="/catalog/compare/" title="Сравнить выбранные товары">
                                <span>
                                    <span class="g-underline fade">Сравнение</span>
                                </span>
                                <strong class="count">0</strong>
                            </a>
                        </div>*/
                        ?>
                    </div>
                </li>
                <li>
                    <div id="mbasket_cart" class="cart_view"<?if($arResult["NUM_PRODUCTS"]<1){echo ' style="display: none;"';}?>>
                        <table id="mbasket_items" class="basket_positions">
                            <thead>
                            <tr>
                                <th><?=GetMessage("MIBIX_LIST_PHOTO")?></th>
                                <th><?=GetMessage("MIBIX_LIST_NAME")?></th>
                                <th><?=GetMessage("MIBIX_LIST_PRICE")?></th>
                                <th colspan="2"><?=GetMessage("MIBIX_LIST_COUNT")?></th>
                                <th><?=GetMessage("MIBIX_LIST_DELETE")?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?
                            if ($arResult["NUM_PRODUCTS"]>0)
                            {
                                foreach ($arResult["ITEMS"] as &$v)
                                {?>
                                    <tr id="MBITEM_<?=$v["ID"]?>">
                                        <td>
                                            <div class="photo_container">
                                                <?if (strlen($arItem["DETAIL_PAGE_URL"]) > 0):?><a href="<?=$arItem["DETAIL_PAGE_URL"] ?>"><?endif;?>
                                                    <div class="order_photo" style="background-image:url('<?=$v["IMAGE_SRC"]?>')"></div>
                                                <?if (strlen($arItem["DETAIL_PAGE_URL"]) > 0):?></a><?endif;?>
                                            </div>
                                        </td>
                                        <?
                                        if ('' != $v["DETAIL_PAGE_URL"])
                                        {
                                            ?><td><a href="<?echo $v["DETAIL_PAGE_URL"]; ?>"><?echo $v["NAME"]?></a></td><?
                                        }
                                        else
                                        {
                                            ?><td><?echo $v["NAME"]?></td><?
                                        }
                                        ?>
                                        <td class="price"><?echo $v["PRICE_FORMATED"]?></td>
                                        <td class="quan">
                                            <input type="text" class="quantity_item" id="MBASKET_QUANTITY_INPUT_<?=$v["ID"]?>" maxlength="18" min="0" step="0" value="<?=intval($v["QUANTITY"])?>" onchange="updateBasketAvailableQuantity('MBASKET_QUANTITY_INPUT_<?=$v["ID"]?>', '<?=$v["ID"]?>')" />
                                            <input type="hidden" id="MBASKET_QUANTITY_MBITEM_<?=$v["ID"]?>" name="MBASKET_QUANTITY_<?=$v["ID"]?>" value="<?=intval($v["QUANTITY"])?>">
                                        </td>
                                        <td class="quan_count">
                                            <div class="quantity_count">
                                                <a href="javascript:void(0);" class="plus" onclick="setBasketAvailableQuantity(<?=$v["ID"]?>, 'up');"></a>
                                                <a href="javascript:void(0);" class="minus" onclick="setBasketAvailableQuantity(<?=$v["ID"]?>, 'down');"></a>
                                            </div>
                                        </td>
                                        <td class="delete"><a href="javascript:void(0);" onclick="ajaxDeleteItemBasketAvailable(<?=$v["ID"]?>);"><img name="no-hide-cart-control" src="/bitrix/components/mibix/basket.available/templates/.default/images/delete_item.png" /></a></td>
                                    </tr>
                                <?}
                                if (isset($v)) unset($v);
                            }
                            ?>
                            </tbody>
                        </table>
                        <input type="hidden" id="MBASKET_QUANTITY_MBITEMS" value="">
                        <input type="hidden" id="MBASKET_TEXT_CART_EMPTY" value="<?=GetMessage("MIBIX_BASKET_YOUR_CART_EMPTY")?>">
                        <input type="hidden" id="MBASKET_TEXT_CART_FILL" value="<?=GetMessage("MIBIX_BASKET_YOUR_CART")?>">
                        <input type="hidden" id="MBASKET_TEXT_CART_BASKETEMPTY" value="<?=GetMessage("MIBIX_BASKET_EMPTY_CART")?>">
                        <input type="hidden" id="MBASKET_PARAM_CURRENCY" value="<?=$arResult["PARAM_CURRENCY"]?>">
                        <input type="hidden" id="MBASKET_PARAM_IMAGE" value="<?=$arResult["PARAM_IMAGE_SETTING"]?>">
                    </div>
                    <div class="mbx-basket_cart mbx-basket_serv">
                        <span id="mbasket_cart_line">
                            <a id="personal_cart_url" href="javascript:void(0);"<?if($arResult["NUM_PRODUCTS"]>0){echo ' class="active"';}?>>
                                <span id="MBASKET_NUM_TEXT" class="g-underline<?if($arResult["NUM_PRODUCTS"]<1){echo ' fade';}?>"><?if($arResult["NUM_PRODUCTS"]>0){echo GetMessage("MIBIX_BASKET_YOUR_CART");}else{echo GetMessage("MIBIX_BASKET_YOUR_CART_EMPTY");}?></span>
                                <strong class="count"><span id="MBASKET_NUM"><?=$arResult["NUM_PRODUCTS"]?></span></strong>
                            </a>
                            <span id="MBASKET_SUM" <?if($arResult["NUM_PRODUCTS"]>0){echo "class=\"num\">".$arResult["SUM_PRODUCTS"];}else{echo "class=\"empty\">".GetMessage("MIBIX_BASKET_EMPTY_CART");}?></span>
                            <a id="MBASKET_ORDER" href="<?=$arParams["PATH_TO_ORDER_MAKE"];?>" class="bt1 order<?if($arResult["NUM_PRODUCTS"]<1){echo " _disabled";}?>"><span></span><?=GetMessage("MIBIX_BASKET_CREATE_ORDER")?></a>
                        </span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>


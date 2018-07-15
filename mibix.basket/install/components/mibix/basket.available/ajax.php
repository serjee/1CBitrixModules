<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CComponentUtil::__IncludeLang(dirname($_SERVER["SCRIPT_NAME"]), "/ajax.php");

if (!CModule::IncludeModule("sale") || !CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog")) return;

global $USER, $APPLICATION;
if (!check_bitrix_sessid() || $_SERVER["REQUEST_METHOD"] != "POST") return;

CUtil::JSPostUnescape();

$num_products = 0;
$totalPrice = 0;
$arRes = array();
$newProductId = false;
$newBasketId = false;
$arErrors = array();

// Валюта из настроек компонента
if(isset($_POST["param_currency"])) $currencyPrice = $_POST["param_currency"];

// Устанавливаем картинку, в зависимости от выбранного в свойствах месторасположения
if(isset($_POST["param_image"])) $imageSetting = $_POST["param_image"];

// Проверка запрашиваемого действия
if (isset($_POST["action"]) && strlen($_POST["action"]) > 0)
{
    $siteId = isset($_POST["site_id"]) ? $_POST["site_id"] : SITE_ID;

    // Для безопасного удаления, - перебираем корзину пользователя, находим запрашиваемые элементы на обновление и удаления, производим действия
    $arTmpItems = array();
    $dbItems = CSaleBasket::GetList(
        array("PRICE" => "DESC"),
        array(
            "FUSER_ID" => CSaleBasket::GetBasketUserID(),
            "LID" => SITE_ID,
            "ORDER_ID" => "NULL"
        ),
        false,
        false,
        array(
            "ID", "NAME", "PRODUCT_PROVIDER_CLASS", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID",
            "PRICE", "QUANTITY", "DELAY", "CAN_BUY", "CURRENCY", "SUBSCRIBE", "TYPE", "SET_PARENT_ID"
        )
    );
    while ($arItem = $dbItems->Fetch())
    {
        if (CSaleBasketHelper::isSetItem($arItem))
            continue;

        $arTmpItems[] = $arItem;
    }

    // Перебираем массив с элементами корзины
    foreach ($arTmpItems as $arItem)
    {
        // Обновление количества товара
        if ($_POST["action"] == "quantity_update")
        {
            // Определяем формат поля
            $isFloatQuantity = (isset($arItem["MEASURE_RATIO"]) && floatval($arItem["MEASURE_RATIO"]) > 0 && $arItem["MEASURE_RATIO"] != 1) ? true : false;
            if (!isset($_POST["MBASKET_QUANTITY_MBITEM_".$arItem["ID"]]) || floatval($_POST["MBASKET_QUANTITY_MBITEM_".$arItem["ID"]]) <= 0)
            {
                $quantityTmp = ($isFloatQuantity === true) ? floatval($arItem["QUANTITY"]) : intval($arItem["QUANTITY"]);
            }
            else
            {
                $quantityTmp = ($isFloatQuantity === true) ? floatval($_POST["MBASKET_QUANTITY_MBITEM_".$arItem["ID"]]) : intval($_POST["MBASKET_QUANTITY_MBITEM_".$arItem["ID"]]);
            }

            // Обновляем позицию в корзине
            $arFields["QUANTITY"] = $quantityTmp;
            CSaleBasket::Update($arItem["ID"], $arFields);

            // Сумма корзины = как цена умноженная на новую информацию о количестве
            $totalPrice += $arItem["PRICE"] * intval($quantityTmp);
        }
        // Удаление товара
        elseif ( $_POST["action"] == "item_remove" )
        {
            if (isset($_POST["DELETE_".$arItem["ID"]]) && $_POST["DELETE_".$arItem["ID"]] == "Y")
            {
                if ($arItem["SUBSCRIBE"] == "Y" && is_array($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()]))
                {
                    unset($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()][$arItem["PRODUCT_ID"]]);
                }

                if ( $_POST["ITEM_COUNT"] == 1 )
                {
                    $arRes["CART_EMPTY"] = "Y";
                }

                CSaleBasket::Delete($arItem["ID"]);

                continue; // Чтоб не считал далее количество и стоимость корзины
            }

            // Сумма корзины = как цена умноженная на количество из базы (т.к. оно не меняется при удалении)
            $totalPrice += $arItem["PRICE"] * $arItem["QUANTITY"];
        }
        // По умолчанию считается сумма корзины (чтобы в случае Ajax-добавления товара не обнулялся счетчик общей суммы)
        else
        {
            // Сумма корзины = как цена умноженная на количество из базы (т.к. оно не меняется при удалении)
            $totalPrice += $arItem["PRICE"] * $arItem["QUANTITY"];
        }

        // Количество, цена
        $num_products++;
    }

    // Добавление элемента в корзину
    if ( $_POST["action"] == "item_add" )
    {
        if (isset($_POST["item_id"]) && isset($_POST["quantity"]))
        {
            $PRODUCT_ID = intval($_POST["item_id"]);
            $QUANTITY = intval($_POST["quantity"]);

            if ($PRODUCT_ID >0 && $QUANTITY > 0)
            {
                // Перебираем элементы корзины и ищем среди них товар, который пользователь пытается добавить в корзину
                $itemIsExist = false;
                $safeQuantity = 0;
                $baskedItemId = 0;
                foreach ($arTmpItems as $arItem)
                {
                    if ($arItem["PRODUCT_ID"] == $PRODUCT_ID)
                    {
                        $itemIsExist = true;
                        $baskedItemId = $arItem["ID"];
                        $safeQuantity = intval($arItem["QUANTITY"]) + $QUANTITY;
                    }
                }

                // Если элемент найден в корзине - обновляем только его количество
                if ($itemIsExist)
                {
                    // Обновляем позицию в корзине
                    $arFields["QUANTITY"] = $safeQuantity;
                    CSaleBasket::Update($baskedItemId, $arFields);

                    // Сумма корзины = как цена умноженная на новую информацию о количестве
                    $totalPrice += $arItem["PRICE"] * intval($arFields["QUANTITY"]);

                    // Поля для обновления полей
                    $arRes["ITEM_UPDATED_ID"] = $baskedItemId;
                    $arRes["ITEM_UPDATED_COUNT"] = $arFields["QUANTITY"];
                }
                else // Если элемен не найден, - добавляем его
                {
                    $NEW_PRODUCT_CODE = Add2BasketByProductID($PRODUCT_ID, $QUANTITY);
                    if($NEW_PRODUCT_CODE > 0)
                    {
                        $arNewItem = CSaleBasket::GetByID($NEW_PRODUCT_CODE);

                        // Название товара, цена, количество
                        $arRes["NEW_ITEM_NAME"] = $arNewItem["NAME"];
                        $arRes["NEW_ITEM_PRICE"] = $arNewItem["PRICE"];
                        $arRes["NEW_ITEM_QUANTITY"] = $arNewItem["QUANTITY"];

                        $photoItemSrc = "/bitrix/components/mibix/basket.available/templates/.default/images/no_photo.png";

                        $arElementResult = CIBlockElement::GetByID($arNewItem["PRODUCT_ID"]);
                        if($arElement = $arElementResult->GetNext())
                        {
                            // Вытаскиваем картинку из полей превью или детальной
                            if ($imageSetting == 'PREVIEW_PICTURE' || $imageSetting == 'DETAIL_PICTURE')
                            {
                                // Если в настройках выбрано PREVIEW_PICTURE и картинка в этом поле реально существует, определяем ссылку к ней
                                if ($imageSetting == 'PREVIEW_PICTURE' && isset($arElement["PREVIEW_PICTURE"]) && intval($arElement["PREVIEW_PICTURE"]) > 0)
                                {
                                    $arImage = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
                                    if ($arImage)
                                    {
                                        $arFileTmp = CFile::ResizeImageGet(
                                            $arImage,
                                            array("width" => "24", "height" =>"24"),
                                            BX_RESIZE_IMAGE_PROPORTIONAL,
                                            true
                                        );
                                        $photoItemSrc = $arFileTmp["src"];
                                    }
                                }
                                elseif ($imageSetting == 'DETAIL_PICTURE' && isset($arElement["DETAIL_PICTURE"]) && intval($arElement["DETAIL_PICTURE"]) > 0)
                                {
                                    $arImage = CFile::GetFileArray($arElement["DETAIL_PICTURE"]);
                                    if ($arImage)
                                    {
                                        $arFileTmp = CFile::ResizeImageGet(
                                            $arImage,
                                            array("width" => "24", "height" =>"24"),
                                            BX_RESIZE_IMAGE_PROPORTIONAL,
                                            true
                                        );
                                        $photoItemSrc = $arFileTmp["src"];
                                    }
                                }
                            }
                            else // Если картинку нужно брать из свойства
                            {
                                $IBLOCK_ID = CIBlockElement::GetIBlockByID($arOneItem["PRODUCT_ID"]);
                                $dbElProps = CIBlockElement::GetProperty($IBLOCK_ID, $arOneItem["PRODUCT_ID"], Array(), Array("CODE"=>$imageSetting));
                                if($obElement = $dbElProps->Fetch())
                                {
                                    $arImage = CFile::GetFileArray($obElement["VALUE"]);
                                    if ($arImage)
                                    {
                                        $arFileTmp = CFile::ResizeImageGet(
                                            $arImage,
                                            array("width" => "24", "height" =>"24"),
                                            BX_RESIZE_IMAGE_PROPORTIONAL,
                                            true
                                        );
                                        $photoItemSrc = $arFileTmp["src"];
                                    }
                                }
                            }
                        }
                        // --------------------------------------------------------------------------

                        // Если до этого товаров не было в корзине, то обновляем элементы управления, делая их активные
                        if (count($arTmpItems) == 0)
                        {
                            $arRes["NEW_FIRST_ITEM"] = "Y";
                        }

                        // Формируем новую ячейку товара для интерактивного списка
                        $newItemHtml = '<td><div class="photo_container"><div class="order_photo" style="background-image:url(\''.$photoItemSrc.'\')"></div></div></td>';
                        $newItemHtml .= '<td><a href="'.$arNewItem["DETAIL_PAGE_URL"].'">'.$arNewItem["NAME"].'</a></td>';
                        $newItemHtml .= '<td class="price">'.SaleFormatCurrency($arNewItem["PRICE"], $arNewItem["CURRENCY"]).'</td>';
                        $newItemHtml .= '<td class="quan"><input type="text" class="quantity_item" id="MBASKET_QUANTITY_INPUT_'.$arNewItem["ID"].'" maxlength="18" min="0" step="0" value="1" onchange="updateBasketAvailableQuantity(\'MBASKET_QUANTITY_INPUT_'.$arNewItem["ID"].'\', \''.$arNewItem["ID"].'\')"><input type="hidden" id="MBASKET_QUANTITY_MBITEM_'.$arNewItem["ID"].'" name="MBASKET_QUANTITY_'.$arNewItem["ID"].'" value="1"></td>';
                        $newItemHtml .= '<td class="quan_count"><div class="quantity_count"><a href="javascript:void(0);" class="plus" onclick="setBasketAvailableQuantity('.$arNewItem["ID"].', \'up\');"></a><a href="javascript:void(0);" class="minus" onclick="setBasketAvailableQuantity('.$arNewItem["ID"].', \'down\');"></a></div></td>';
                        $newItemHtml .= '<td class="delete"><a href="javascript:void(0);" onclick="ajaxDeleteItemBasketAvailable('.$arNewItem["ID"].');"><img name="no-hide-cart-control" src="/bitrix/components/mibix/basket.available/templates/.default/images/delete_item.png"></a></td>';
                        $arRes["NEW_ITEM_ID"] = $arNewItem["ID"];
                        $arRes["NEW_PRODUCT"] = $newItemHtml;

                        $totalPrice += $arNewItem["PRICE"] * $arNewItem["QUANTITY"];
                        $num_products++;
                    }
                }
            }
        }
    }

    // Информация о новой сумме корзины и количестве товара
    $arRes["SUM_PRODUCTS"] = SaleFormatCurrency($totalPrice, $currencyPrice);
    $arRes["NUM_PRODUCTS"] = $num_products;
}

$arRes["PARAMS"]["QUANTITY_FLOAT"] = (isset($_POST["quantity_float"]) && $_POST["quantity_float"] == "Y") ? "Y" : "N";

$APPLICATION->RestartBuffer();
header('Content-Type: application/json; charset='.LANG_CHARSET);
echo CUtil::PhpToJSObject($arRes);
die();

?>

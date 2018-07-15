<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(array('ajax','popup'));

// Подключаем разные стили по запросу из параметров
switch ($arParams['COLOR_CHEMES'])
{
    case "blue":
        $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/blue.css", false);
        break;
    case "green":
        $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/green.css", false);
        break;
    case "bluelight":
        $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/bluelight.css", false);
        break;
    case "yellow":
        $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/yellow.css", false);
        break;
    case "red":
        $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/red.css", false);
        break;
    default: // По умолчанию -> Серая
        $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/css-chemes/gray.css", false);
}

if($arResult['JQUERY_ENABLED'] == 'Y') {
    $APPLICATION->AddHeadString('
        if (typeof(jQuery) == \'undefined\') {
            document.write(\'\x3Cscript type="text/javascript" src="' . $this->GetFolder() . '/js/jquery-1.4.3.min.js">\x3C/script>\');
        }', true);
}
if($arResult['JQUERY_ENABLED'] == 'Y' && $arResult['FANCYBOX_ENABLED'] == 'Y') {
    $APPLICATION->AddHeadString('
    if(typeof $.fancybox != \'function\') {
        <script type="text/javascript" src="' . $this->GetFolder() . '/js/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
        <script type="text/javascript" src="' . $this->GetFolder() . '/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
        <link type="text/css"  rel="stylesheet" href="' . str_replace($_SERVER["DOCUMENT_ROOT"], "", dirname(__FILE__)) . '/js/fancybox/jquery.fancybox-1.3.4.css" />
    }', true);
}
?>

<div class="mbx-panel panel-battle-simple">
    <div class="panel-battle-heading"><?=$arResult["BATTLE_NAME"];?></div>
    <div class="panel-battle-body">
        <div class="panel-battle-body-timer">
            <?if(!$arResult["BATTLE_END"]):?>
            <div style="display:none;" id="BATTLE-TIME-<?=$arParams["CODE_GROUP"];?>" data-group='<?=$arParams["CODE_GROUP"];?>' data-now='<?=$arResult["TIME_CURRENT"];?>' data-timestamp='<?=$arResult["DATE_FINISH"];?>'></div>
            <table class="battle-timer-table" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td colspan="7" class="battle-timer-title"><?=GetMessage("MIBIX_BATTLE_TMP_TITLE_TIMER")?></td>
                    </tr>
                    <tr class="battle-timer-body">
                        <td><span id="<?=$arParams["CODE_GROUP"];?>-timer-days" class="battle-timer-body-num white">00</span></td>
                        <td><span class="battle-timer-body-sep">:</span></td>
                        <td><span id="<?=$arParams["CODE_GROUP"];?>-timer-hrs" class="battle-timer-body-num white">00</span></td>
                        <td><span class="battle-timer-body-sep">:</span></td>
                        <td><span id="<?=$arParams["CODE_GROUP"];?>-timer-min" class="battle-timer-body-num white">00</span></td>
                        <td><span class="battle-timer-body-sep">:</span></td>
                        <td><span id="<?=$arParams["CODE_GROUP"];?>-timer-sec" class="battle-timer-body-num black">00</span></td>
                    </tr>
                    <tr class="battle-timer-notes">
                        <td class="battle-timer-notes-mess"><?=GetMessage("MIBIX_BATTLE_TMP_TIMER_DAY")?></td>
                        <td>&nbsp;</td>
                        <td class="battle-timer-notes-mess"><?=GetMessage("MIBIX_BATTLE_TMP_TIMER_HOUR")?></td>
                        <td>&nbsp;</td>
                        <td class="battle-timer-notes-mess"><?=GetMessage("MIBIX_BATTLE_TMP_TIMER_MIN")?></td>
                        <td>&nbsp;</td>
                        <td class="battle-timer-notes-mess"><?=GetMessage("MIBIX_BATTLE_TMP_TIMER_SEC")?></td>
                    </tr>
                </tbody>
            </table>
            <?else:?>
                <div class="battle-end"><?=GetMessage("MIBIX_BATTLE_TMP_TIMER_BATTLE_END", Array("#BATTLE_NAME#"=>$arResult["BATTLE_NAME"]))?></div>
            <?endif;?>
        </div>
        <table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
        <tr>
        <?
        $group_cnt = 0;
        $scriptObject = ""; // для динамического js
        foreach($arResult["ELEMENTS"] as $elemId => $battleVal):?>
        <?if($group_cnt!=0 && $group_cnt%3==0):?></tr><tr><?endif;?>
        <td class="panel-battle-body-item-td" style="width: <?=$arResult["COLUMN_WIDTH"]?>%">
            <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
                <tr>
                    <td class="header"><h2><?=$battleVal["NAME"];?></h2></td>
                </tr>
                <tr>
                    <td class="block-center block-images">
                        <?
                        $i=0;
                        foreach($battleVal["IMAGES"] as $img_link) {
                            // картинки отображаемые только в fancybox (со 2й и дальше)
                            if ($i > 0) {
                                if($arResult['FANCYBOX_ENABLED'] == 'Y') { ?>
                                    <div style="display:none;"><a rel="bg_images_<?=$arParams["CODE_GROUP"];?>_<?=$group_cnt;?>" href="<?= $img_link; ?>"></a></div>
                                <?  }
                            } else {
                                if($arResult['FANCYBOX_ENABLED'] == 'Y') { ?>
                                    <a rel="bg_images_<?=$arParams["CODE_GROUP"];?>_<?=$group_cnt;?>" href="<?= $img_link; ?>"><img alt="<?= $battleVal["NAME"]; ?>" src="<?= $img_link; ?>"/></a>
                                <?  } elseif(strlen($battleVal["LINK"])) { ?>
                                    <a href="<?= $battleVal["LINK"]; ?>"><img alt="<?= $battleVal["NAME"]; ?>" src="<?= $img_link; ?>"/></a>
                                <?  } else { ?>
                                    <img alt="<?= $battleVal["NAME"]; ?>" src="<?= $img_link; ?>"/>
                                <?  }
                            }
                            $i++;
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?if(strlen($battleVal["DESC"])):?>
                        <div class="block-center block-desc"><?=$battleVal["DESC"];?></div>
                        <?endif;?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?if(strlen($battleVal["LINK"])):?>
                            <div class="block-center block-link"><a href="<?=$battleVal["LINK"];?>"><?=GetMessage("MIBIX_BATTLE_TMP_ITEM_MORE")?></a></div>
                        <?endif;?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?
                        if($arResult['FANCYBOX_ENABLED'] == 'Y') {
                            $APPLICATION->AddHeadString('<script type="text/javascript">
                        $(document).ready(function() {
                            $("a[rel=bg_images_' . $arParams["CODE_GROUP"] . '_' . $group_cnt . '").fancybox({
                                \'transitionIn\':\'none\',
                                \'transitionOut\':\'none\',
                                \'titlePosition\':\'over\'
                            });
                        });</script>', true);
                        }
                        ?>
                        <? // Формируем title
                        $arTitles = $arResult["AR_TITLES"];
                        unset($arTitles[$elemId]);
                        $strTitles = "\"" . implode("\", \"", $arTitles) . "\"";
                        // Генерация данных для шаринга в соц.сети
                        $uID = $arResult["BATTLE_ID"].$elemId;
                        $scriptObject .= 'texts.socdata['.$uID.'] = new Object();';
                        $scriptObject .= 'texts.socdata['.$uID.'].summary = \''.GetMessage("MIBIX_BATTLE_TMP_ITEM_SUMMARY", Array("#BRAND#"=>$battleVal["NAME"], "#URL#"=>$battleVal["LINK"])).'\';';
                        $scriptObject .= 'texts.socdata['.$uID.'].title = \''.GetMessage("MIBIX_BATTLE_TMP_ITEM_TITLE", Array("#BRAND#"=>$battleVal["NAME"], "#BRANDS#"=>$strTitles)).'\';'; //title
                        $scriptObject .= 'texts.socdata['.$uID.'].titletw = \''.GetMessage("MIBIX_BATTLE_TMP_ITEM_TITLE_TW", Array("#BRAND#"=>$battleVal["NAME"], "#BRANDS#"=>$strTitles)).'\';'; //title for TW
                        $scriptObject .= 'texts.socdata['.$uID.'].url = \''.$battleVal["LINK"].'\';'; //url
                        $scriptObject .= 'texts.socdata['.$uID.'].image = \''.$arResult["SITE_HOST"].$battleVal["IMAGES"][0].'\';'; //first image
                        ?>
                        <table border="0" cellpadding="0" cellspacing="0" class="block-bottom-table">
                            <tr>
                                <td class="ai_price"><? echo $battleVal["VOTES"] . " " . $battleVal["VOTES_STRING"];?></td>
                                <td class="ai_discounted"><a class="btn btn-primary<?if($arResult["BATTLE_END"]){echo " disabled";};?> vote-popup-open" href="javascript:void(0)" onclick="setBattleID(<?=$uID;?>)"><?=GetMessage("MIBIX_BATTLE_TMP_ITEM_VOTE")?></a></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
        <?
            $group_cnt++;
        endforeach;?>
        </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>

<div id="hideBlock" style="display:none;">
    <h1><?=GetMessage("MIBIX_BATTLE_TMP_ITEM_CHECK_TITLE")?>:</h1>
    <p style="text-align: center;">
        <?if($arResult["VK_ENABLED"]=="Y"):?>
        <a href="javascript:void(0)" onclick="voteSet('vk');"><img src="<?=$this->GetFolder()?>/images/vk.png" /></a>
        <?endif;?>
        <?if($arResult["TW_ENABLED"]=="Y"):?>
        <a href="javascript:void(0)" onclick="voteSet('tw');"><img src="<?=$this->GetFolder()?>/images/tw.png" /></a>
        <?endif;?>
        <?if($arResult["FB_ENABLED"]=="Y"):?>
        <a href="javascript:void(0)" onclick="voteSet('fb');"><img src="<?=$this->GetFolder()?>/images/fb.png" /></a>
        <?endif;?>
        <?if($arResult["OK_ENABLED"]=="Y"):?>
        <a href="javascript:void(0)" onclick="voteSet('ok');"><img src="<?=$this->GetFolder()?>/images/ok.png" /></a>
        <?endif;?>
        <?if($arResult["ML_ENABLED"]=="Y"):?>
        <a href="javascript:void(0)" onclick="voteSet('mm');"><img src="<?=$this->GetFolder()?>/images/mm.png" /></a>
        <?endif;?>
        <?if($arResult["PI_ENABLED"]=="Y"):?>
        <a href="javascript:void(0)" onclick="voteSet('pi');"><img src="<?=$this->GetFolder()?>/images/pi.png" /></a>
        <?endif;?>
    </p>
    <p><?=GetMessage("MIBIX_BATTLE_TMP_ITEM_CHECK_NOTE")?></p>
</div>

<script type="text/javascript">
    if (typeof texts ==="undefined")
        var texts = new Object();
    if (typeof texts.socdata ==="undefined")
    texts.socdata = new Array();
    <?=$scriptObject;?>
</script>
<script type="text/javascript">
    if(typeof voteSet != 'function') {
        document.write('\x3Cscript type="text/javascript" src="<?=$this->GetFolder().'/js/script.js';?>">\x3C/script>');
    }
    BX.ready(function() {
        time_tick(BX('BATTLE-TIME-<?=$arParams["CODE_GROUP"];?>'));
    });
</script>

<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(array('ajax','popup'));

if($arResult['JQUERY_ENABLED'] == 'Y') {
    $APPLICATION->AddHeadString('<script>
        if (typeof(jQuery) == \'undefined\') {
            document.write(\'\x3Cscript type="text/javascript" src="' . $this->GetFolder() . '/js/jquery-1.4.3.min.js">\x3C/script>\');
        }</script>', true);
}
if($arResult['JQUERY_ENABLED'] == 'Y' && $arResult['FANCYBOX_ENABLED'] == 'Y') {
    $APPLICATION->AddHeadString('<script>
    if(typeof $.fancybox != \'function\') {
        document.write(\'\x3Cscript type="text/javascript" src="' . $this->GetFolder() . '/js/fancybox/jquery.mousewheel-3.0.4.pack.js">\x3C/script>\');
        document.write(\'\x3Cscript type="text/javascript" src="' . $this->GetFolder() . '/js/fancybox/jquery.fancybox-1.3.4.pack.js">\x3C/script>\');
    }</script>', true);
    $APPLICATION->SetAdditionalCSS(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__))."/js/fancybox/jquery.fancybox-1.3.4.css", false);
}
?>

<div class="mbx-panel-battle-dis">
    <div class="container">

        <div class="row">
            <div class="panel panel-<?=$arParams['COLOR_PANEL_CHEMES'];?>">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="head-discount"><?=$arResult["BATTLE_NAME"];?></div>
                        </div>
                        <div class="col-lg-pull-4">
                            <div class="all-sale-progress">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-<?=$arParams['COLOR_PROGRESS_TOP_CHEMES'];?><?if($arResult['SHOW_PROGRESS_TOP_ACTIVE']=='Y') echo " progress-bar-striped active";?>" role="progressbar" aria-valuenow="<?=IntVal($arResult['DISCOUNT_ALL'])?>" aria-valuemin="0" aria-valuemax="100" style="min-width:40%; width:<?=IntVal($arResult['DISCOUNT_ALL'])?>%">
                                        <?=GetMessage("MIBIX_BATTLEDIS_TMP_TITLE_DISCOUNT", Array('#DISCOUNT#'=>IntVal($arResult['DISCOUNT_ALL'])))?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                    <?if(!$arResult["BATTLE_END"]):?>
                        <div class="col-lg-12">
                            <div style="display:none;" id="BATTLE-TIME-<?=$arParams["CODE_GROUP"];?>" data-group='<?=$arParams["CODE_GROUP"];?>' data-now='<?=$arResult["TIME_CURRENT"];?>' data-timestamp='<?=$arResult["DATE_FINISH"];?>'></div>
                            <table class="battle-timer-table" border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                <tr>
                                    <td colspan="7" class="battle-timer-title"><?=GetMessage("MIBIX_BATTLEDIS_TMP_TITLE_TIMER")?></td>
                                </tr>
                                <tr class="battle-timer-body">
                                    <td><span id="<?=$arParams["CODE_GROUP"];?>-dis-timer-days" class="battle-timer-body-num white">00</span></td>
                                    <td><span class="battle-timer-body-sep">:</span></td>
                                    <td><span id="<?=$arParams["CODE_GROUP"];?>-dis-timer-hrs" class="battle-timer-body-num white">00</span></td>
                                    <td><span class="battle-timer-body-sep">:</span></td>
                                    <td><span id="<?=$arParams["CODE_GROUP"];?>-dis-timer-min" class="battle-timer-body-num white">00</span></td>
                                    <td><span class="battle-timer-body-sep">:</span></td>
                                    <td><span id="<?=$arParams["CODE_GROUP"];?>-dis-timer-sec" class="battle-timer-body-num black">00</span></td>
                                </tr>
                                <tr class="battle-timer-notes">
                                    <td class="battle-timer-notes-mess"><?=GetMessage("MIBIX_BATTLEDIS_TMP_TIMER_DAY")?></td>
                                    <td>&nbsp;</td>
                                    <td class="battle-timer-notes-mess"><?=GetMessage("MIBIX_BATTLEDIS_TMP_TIMER_HOUR")?></td>
                                    <td>&nbsp;</td>
                                    <td class="battle-timer-notes-mess"><?=GetMessage("MIBIX_BATTLEDIS_TMP_TIMER_MIN")?></td>
                                    <td>&nbsp;</td>
                                    <td class="battle-timer-notes-mess"><?=GetMessage("MIBIX_BATTLEDIS_TMP_TIMER_SEC")?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?else:?>
                        <div class="col-lg-12">
                            <div class="battle-end-message"><?=GetMessage("MIBIX_BATTLEDIS_TMP_TIMER_BATTLE_END", Array("#BATTLE_NAME#"=>$arResult["BATTLE_NAME"]))?></div>
                        </div>
                    <?endif;?>
                    </div>
                    <div class="row">
                        <?
                        $group_cnt = 0;
                        $scriptObject = ""; // для динамического js
                        foreach($arResult["ELEMENTS"] as $elemId => $battleVal):?>
                        <div class="col-md-<?=$arResult["COUNT_COL"]?> col-sm-<?=$arResult["COUNT_COL"]?>">
                            <div class="thumbnail-container">
                                <div class="thumbnail thumbnail-<?=$arParams['COLOR_THUMBNAIL_CHEMES'];?>">
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
                                    <div class="caption">
                                        <h3 id="thumbnail-label"><?=$battleVal["NAME"];?></h3>
                                        <?if(strlen($battleVal["DESC"])):?>
                                        <p class="description-text"><?=strip_tags($battleVal["DESC"]);?></p>
                                        <?endif;?>
                                        <?if(!empty($arResult["IS_PRICE"])):?>
                                            <div class="dis-price">
                                                <div class="dsc-old-price"><?=GetMessage("MIBIX_BATTLEDIS_TMP_ITEM_PRICE_OLD", Array("#PRICE_OLD#"=>$battleVal["PRICE"]))?></div>
                                                <div class="dsc-new-price"><span><?=$battleVal["PRICE_DISCOUNT"]?></span></div>
                                                <div style="clear:both;"></div>
                                            </div>
                                        <?endif;?>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-<?=$arParams['COLOR_PROGRESS_CHEMES'];?><?if($arResult['SHOW_PROGRESS_ACTIVE']=='Y') echo " progress-bar-striped active";?>" role="progressbar" aria-valuenow="<?=IntVal($battleVal["DISCOUNT_VIEW"])?>" aria-valuemin="0" aria-valuemax="100" style="min-width:4em; width: <?=IntVal($battleVal["DISCOUNT_VIEW"])?>%;">
                                                <?=$battleVal["DISCOUNT"]?>%
                                            </div>
                                        </div>
                                        <div class="progress-note">
                                            <?=GetMessage("MIBIX_BATTLEDIS_TMP_ITEM_PROGRESS_NOTE")?>
                                        </div>
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
                                        $scriptObject .= 'texts.socdata['.$uID.'].summary = \''.GetMessage("MIBIX_BATTLEDIS_TMP_ITEM_SUMMARY", Array("#BRAND#"=>$battleVal["NAME"], "#URL#"=>$battleVal["LINK"])).'\';';
                                        $scriptObject .= 'texts.socdata['.$uID.'].title = \''.GetMessage("MIBIX_BATTLEDIS_TMP_ITEM_TITLE", Array("#BRAND#"=>$battleVal["NAME"], "#BRANDS#"=>$strTitles)).'\';'; //title
                                        $scriptObject .= 'texts.socdata['.$uID.'].titletw = \''.GetMessage("MIBIX_BATTLEDIS_TMP_ITEM_TITLE_TW", Array("#BRAND#"=>$battleVal["NAME"], "#BRANDS#"=>$strTitles)).'\';'; //title for TW
                                        $scriptObject .= 'texts.socdata['.$uID.'].url = \''.$battleVal["LINK"].'\';'; //url
                                        $scriptObject .= 'texts.socdata['.$uID.'].image = \''.$arResult["SITE_HOST"].$battleVal["IMAGES"][0].'\';'; //first image
                                        ?>
                                        <div>
                                            <div class="vote-count-text"><? echo $battleVal["VOTES"] . " " . $battleVal["VOTES_STRING"];?></div>
                                            <div class="vote-button"><a class="btn btn-<?=$arParams['COLOR_BUTTON_CHEMES'];?><?if($arResult["BATTLE_END"]){echo " disabled";};?> vote-popup-open" href="javascript:void(0)" onclick="setDisBattleID(<?=$uID;?>)"><?=GetMessage("MIBIX_BATTLEDIS_TMP_ITEM_VOTE")?></a></div>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?
                        $group_cnt++;
                        endforeach;?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="hideBlock" style="display:none;">
    <h1><?=GetMessage("MIBIX_BATTLEDIS_TMP_ITEM_CHECK_TITLE")?>:</h1>
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
    <p><?=GetMessage("MIBIX_BATTLEDIS_TMP_ITEM_CHECK_NOTE")?></p>
</div>
<div id="accessDenyBlock" style="display:none;"><p><?=GetMessage("MIBIX_BATTLEDIS_TMP_ITEM_VOTE_DENY_BODY")?></p></div>

<script type="text/javascript">
    if (typeof texts ==="undefined")
        var texts = new Object();
    if (typeof texts.socdata ==="undefined")
    texts.socdata = new Array();
    <?=$scriptObject;?>
</script>
<script type="text/javascript">
    BX.ready(function() {
        dis_time_tick(BX('BATTLE-TIME-<?=$arParams["CODE_GROUP"];?>'));
    });
    var accessDialog = new BX.PopupWindow('vote_already', window.body, {
        autoHide : true,
        offsetTop : 1,
        offsetLeft : 0,
        zIndex : 9999,
        lightShadow : false,
        closeIcon : true,
        closeByEsc : false,
        overlay: {
            backgroundColor: 'gray', opacity: '80'
        }
    });
    if(typeof voteSet != 'function') {
        document.write('\x3Cscript type="text/javascript" src="<?=$this->GetFolder().'/js/script.js';?>">\x3C/script>');
    }
</script>

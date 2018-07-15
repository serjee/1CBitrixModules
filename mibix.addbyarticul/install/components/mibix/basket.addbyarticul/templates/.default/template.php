<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="form-group-addbyarticul">
    <label class="control-label-addbyarticul" for="idsToAdd"><?=GetMessage("MIBIX_ABA_HEAD_TO_CART")?></label>
    <div class="in" id="wrap1" style="height: auto;">
        <form method="post" action="<?=POST_FORM_ACTION_URI?>" name="addbyarticul_form" id="addbyarticul_form">
            <?=bitrix_sessid_post()?>
            <div class="input-group-addbyarticul">
                <input class="form-control-addbyarticul" type="text" id="idsToAdd" name="id_articuls" value="">
                <span class="input-group-btn-addbyarticul"><button class="btn-addbyarticul" type="submit"><?=GetMessage("MIBIX_ABA_ADD_TO_CART")?></button></span>
            </div>
            <div class="form-hint-addbyarticul"><?=GetMessage("MIBIX_ABA_FOOTER_TO_CART")?></div>
        </form>
    </div>
</div>
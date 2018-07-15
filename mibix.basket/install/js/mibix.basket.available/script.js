BX.ready(function()
{
    BX('MBASKET_QUANTITY_MBITEMS').value = BX('mbasket_items').rows.length;

    BX.bind(document.body, 'click', function(e)
    {
        if(!e) e = window.event;
        if(BX.findParent(BX(e.target), {"class" : "mbx-basket_bottom-panel"}) == null)
        {
            if(BX.style(BX('mbasket_cart'), 'display') != 'none' && e.target.name != 'no-hide-cart-control')
            {
                BX.hide(BX('mbasket_cart'));
            }
        }
    });

    BX.bind(BX('personal_cart_url'), 'click', function()
    {
        if(BX.hasClass(BX('personal_cart_url'),'active'))
        {
            if(BX.style(BX('mbasket_cart'), 'display') == 'none')
            {
                BX.show(BX('mbasket_cart'));
            }
            else
            {
                BX.hide(BX('mbasket_cart'));
            }
        }
    });

    if(!BX.hasClass(BX('scrollNavArrow'),'disabled'))
    {
        BX.bind(window, 'scroll', function() {
            if(BX.GetWindowScrollPos().scrollTop > 37) {
                BX.show(BX('scrollNavArrow'));
            } else {
                BX.hide(BX('scrollNavArrow'));
            }
        });

        BX.bind(BX('scrollNavArrow'), 'mouseover', function () {
            BX.style(this, 'opacity', 1);
        });

        BX.bind(BX('scrollNavArrow'), 'mouseout', function () {
            BX.style(this, 'opacity', 0.7);
        });

        BX.bind(BX('scrollNavArrow'), 'click', function () {
            window.scrollTo(0,0);
        });
    }
});

// update basket panel
function updateBasketPanel(res)
{
    // update cart to active when first item added from ajax
    if(res['NEW_FIRST_ITEM'] == "Y")
    {
        // panel style as active
        BX.addClass(BX('personal_cart_url'),'active');

        BX.removeClass(BX('MBASKET_SUM'),'empty');
        BX.addClass(BX('MBASKET_SUM'),'num');

        BX.removeClass(BX('MBASKET_NUM_TEXT'),'fade');
        BX.removeClass(BX('MBASKET_ORDER'),'_disabled');

        BX('MBASKET_NUM_TEXT').innerHTML = BX('MBASKET_TEXT_CART_FILL').value;
    }

    // num and sum update
    BX('MBASKET_NUM').innerHTML = res['NUM_PRODUCTS'];
    BX('MBASKET_SUM').innerHTML = res['SUM_PRODUCTS'];

    if(res['CART_EMPTY'] == "Y")
    {
        // hide window of items
        BX.hide(BX('mbasket_cart'));

        //  panel style as empty
        BX.removeClass(BX('personal_cart_url'),'active');

        BX.removeClass(BX('MBASKET_SUM'),'num');
        BX.addClass(BX('MBASKET_SUM'),'empty');

        BX.addClass(BX('MBASKET_NUM_TEXT'),'fade');
        BX.addClass(BX('MBASKET_ORDER'),'_disabled');

        BX('MBASKET_NUM_TEXT').innerHTML = BX('MBASKET_TEXT_CART_EMPTY').value;
        BX('MBASKET_SUM').innerHTML = BX('MBASKET_TEXT_CART_BASKETEMPTY').value;
    }

    if(res['NEW_PRODUCT'] != null && res['NEW_PRODUCT'] != "")
    {
        var tableNode = BX.findChild(BX('mbasket_items'), {"tag" : "tbody"});
        tableNode.appendChild(BX.create('TR', {
            props: {id: 'MBITEM_'+res['NEW_ITEM_ID']},
            html: res['NEW_PRODUCT']
        }));

        BX('MBASKET_QUANTITY_MBITEMS').value++; // counter for right ajax
    }

    // update quantity in case of add exist item
    if(res['ITEM_UPDATED_ID'] > 0 && res['ITEM_UPDATED_COUNT'] > 0)
    {
        BX("MBASKET_QUANTITY_INPUT_" + res['ITEM_UPDATED_ID']).value = res['ITEM_UPDATED_COUNT'];
        BX("MBASKET_QUANTITY_INPUT_" + res['ITEM_UPDATED_ID']).defaultValue = res['ITEM_UPDATED_COUNT'];
        BX("MBASKET_QUANTITY_MBITEM_" + res['ITEM_UPDATED_ID']).value = res['ITEM_UPDATED_COUNT'];
    }
}

// check if quantity is valid and update values
function updateBasketAvailableQuantity(controlId, basketId)
{
    var bUseFloatQuantity = false;
    var bIsQuantityFloat = false;
    var newVal = parseFloat(BX(controlId).value) || 0;

    if (parseInt(newVal) != parseFloat(newVal))
    {
        bIsQuantityFloat = true;
    }

    newVal = (bUseFloatQuantity === false && bIsQuantityFloat === false) ? parseInt(newVal) : parseFloat(newVal).toFixed(2);

    BX(controlId).defaultValue = newVal;
    BX(controlId).value = newVal;

    // set hidden real quantity value (will be used in actual calculation)
    BX("MBASKET_QUANTITY_MBITEM_" + basketId).value = newVal;
    ajaxUpdateQuantityBasketAvailable();
}

// used when quantity is changed by clicking on arrows
function setBasketAvailableQuantity(basketId, sign)
{
    var curVal = parseFloat(BX("MBASKET_QUANTITY_INPUT_" + basketId).value),
        newVal;

    newVal = (sign == 'up') ? curVal+1 : curVal-1;

    if (newVal < 1) newVal = 1;

    newVal = newVal.toFixed(2);

    BX("MBASKET_QUANTITY_INPUT_" + basketId).value = newVal;
    BX("MBASKET_QUANTITY_INPUT_" + basketId).defaultValue = newVal;

    updateBasketAvailableQuantity('MBASKET_QUANTITY_INPUT_' + basketId, basketId);
}

// ajax update information about quantity for basket
function ajaxUpdateQuantityBasketAvailable()
{
    BX.showWait();

    var postData = {
        'sessid': BX.bitrix_sessid(),
        'site_id': BX.message('SITE_ID'),
        'param_currency': BX("MBASKET_PARAM_CURRENCY").value,
        'param_image': BX("MBASKET_PARAM_IMAGE").value,
        'action': 'quantity_update'
    };

    var items = BX('mbasket_items');
    if (!!items && items.rows.length > 0)
    {
        for (var i = 1; items.rows.length > i; i++)
        {
            postData['MBASKET_QUANTITY_' + items.rows[i].id] = BX('MBASKET_QUANTITY_' + items.rows[i].id).value;
        }
    }

    BX.ajax({
        url: '/bitrix/components/mibix/basket.available/ajax.php',
        method: 'POST',
        data: postData,
        dataType: 'json',
        onsuccess: function(result)
        {
            BX.closeWait();
            updateBasketPanel(result);
        }
    });
}

// ajax update information about quantity for basket
function ajaxDeleteItemBasketAvailable(id)
{
    BX.showWait();

    var postData = {
        'sessid': BX.bitrix_sessid(),
        'site_id': BX.message('SITE_ID'),
        'param_currency': BX("MBASKET_PARAM_CURRENCY").value,
        'param_image': BX("MBASKET_PARAM_IMAGE").value,
        'action': 'item_remove'
    };

    var items = BX('mbasket_items');
    if (!!items && items.rows.length > 0)
    {
        for (var i = 1; items.rows.length > i; i++)
        {
            // Если пункт совпал с удаляемым, то визуально убираем его
            if(items.rows[i].id == ('MBITEM_' + id))
            {
                postData["DELETE_" + id] = "Y";
                BX.remove(BX(items.rows[i].id));

                var iCount = BX('MBASKET_QUANTITY_MBITEMS').value - 1;
                BX('MBASKET_QUANTITY_MBITEMS').value = iCount;
                postData["ITEM_COUNT"] = iCount;
            }
        }
    }

    BX.ajax({
        url: '/bitrix/components/mibix/basket.available/ajax.php',
        method: 'POST',
        data: postData,
        dataType: 'json',
        onsuccess: function(result)
        {
            BX.closeWait();
            updateBasketPanel(result);
        }
    });
}

// function for add item to cart (
function ajaxAddItemToBasketAvailable(id, quantity)
{
    BX.showWait();

    var postData = {
        'sessid': BX.bitrix_sessid(),
        'site_id': BX.message('SITE_ID'),
        'param_currency': BX("MBASKET_PARAM_CURRENCY").value,
        'param_image': BX("MBASKET_PARAM_IMAGE").value,
        'action': 'item_add',
        'item_id': id,
        'quantity': quantity
    };

    BX.ajax({
        url: '/bitrix/components/mibix/basket.available/ajax.php',
        method: 'POST',
        data: postData,
        dataType: 'json',
        onsuccess: function(result)
        {
            BX.closeWait();
            updateBasketPanel(result);
        }
    });
}

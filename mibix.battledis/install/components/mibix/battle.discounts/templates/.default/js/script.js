window.BXDEBUG = true;
BX.ready(function() {

    var oPopupDis = new BX.PopupWindow('call_feedback', window.body, {
        autoHide : true,
        offsetTop : 1,
        offsetLeft : 0,
        lightShadow : true,
        closeIcon : true,
        closeByEsc : true,
        overlay: {
            backgroundColor: 'gray', opacity: '80'
        }
    });
    oPopupDis.setContent(BX('hideBlock').innerHTML);

    BX.bindDelegate(
        document.body, 'click', {className: 'vote-popup-open' },
        BX.proxy(function(e){
            if(!e) e = window.event;
            oPopupDis.show();
            return BX.PreventDefault(e);
        }, oPopupDis)
    );
});

// обратный отчет времени
function dis_time_tick(timeblock) {
    if (timeblock) {
        var gra, grc, grd;
        var gr_group_name = timeblock.getAttribute('data-group');
        var gr_timestamp = new Date(timeblock.getAttribute('data-timestamp') * 1000);
        var gr_timenow = (new Date(timeblock.getAttribute('data-now') * 1000)).valueOf();
        var gr_date = new Date();
        setInterval (function () {
            grd = gr_timenow + (new Date() - gr_date);
            gra = (gr_timestamp - grd).valueOf();
            if (gra < 0) {
                //timeblock.innerHTML = "Голосование закончено!";
            } else {
                grc = disapp.time(gra);
                //timeblock.innerHTML = grc.days + " дн. " + grc.hours + " ч. " + grc.min + " мин. " + grc.sec + " сек.";
                BX(gr_group_name+'-dis-timer-days').innerHTML = grc.days;
                BX(gr_group_name+'-dis-timer-hrs').innerHTML = grc.hours;
                BX(gr_group_name+'-dis-timer-min').innerHTML = grc.min;
                BX(gr_group_name+'-dis-timer-sec').innerHTML = grc.sec;
            }
        }, 1000);
    }
}

disapp = {
    time: function (a) {
        left = a / 1000;
        days = Math.floor(left / 86400) % 86400;
        hrs = Math.floor(left / 3600) % 24;
        min = Math.floor(left / 60) % 60;
        sec = Math.floor(left) % 60;
        return {
            days: (days + "").length < 2 ? "0" + days : days,
            hours: (hrs + "").length < 2 ? "0" + hrs : hrs,
            min: (min + "").length < 2 ? "0" + min : min,
            sec: (sec + "").length < 2 ? "0" + sec : sec
        }
    }
};

// ID бренда после того, как нажали "Голосовать"
var id_branddis = 0;

// Голосовать через Фейсбук (вызывается при клике)
function voteSet(socid) {

    // всплывающее окно для постинга в соц.сеть
    var url;
    switch (socid)
    {
        case 'fb':
            //var fb_app_id = 0; // Чтобы активировать данный способ, нужно в создать приложение facebook и указать его ID в этой переменной
            //url = "https://www.facebook.com/dialog/feed?app_id="+fb_app_id+"&display=popup&name="+encodeURIComponent(texts.socdata[id_branddis].title)+"&description="+encodeURIComponent(texts.socdata[id_branddis].summary)+"&link="+encodeURIComponent(texts.socdata[id_branddis].url)+"&picture="+encodeURIComponent(texts.socdata[id_branddis].image);
            url = "http://www.facebook.com/sharer.php?s=100&p[title]="+encodeURIComponent(texts.socdata[id_branddis].title)+"&p[summary]="+encodeURIComponent(texts.socdata[id_branddis].summary)+"&p[url]="+encodeURIComponent(texts.socdata[id_branddis].url)+"&p[images][0]="+encodeURIComponent(texts.socdata[id_branddis].image);
            break;
        case 'tw':
            url = "http://twitter.com/share?text="+encodeURIComponent(texts.socdata[id_branddis].titletw)+"&amp;url="+encodeURIComponent(texts.socdata[id_branddis].url);
            break;
        case 'vk':
            url = "http://vk.com/share.php?url="+encodeURIComponent(texts.socdata[id_branddis].url)+"&title="+encodeURIComponent(texts.socdata[id_branddis].title)+"&description="+encodeURIComponent(texts.socdata[id_branddis].summary)+"&image="+encodeURIComponent(texts.socdata[id_branddis].image);
            break;
        case 'ok':
            url = "http://www.ok.ru/dk?st.cmd=addShare&st.s=1&st._surl="+encodeURIComponent(texts.socdata[id_branddis].url)+"&st.comments="+encodeURIComponent(texts.socdata[id_branddis].summary);
            //url = "http://www.ok.ru/dk?st.cmd=addShare&st.s=1&st._surl="+encodeURIComponent(texts.socdata[id_branddis].url)+"&title="+encodeURIComponent(texts.socdata[id_branddis].title)+"&description="+encodeURIComponent(texts.socdata[id_branddis].summary);
            break;
        case 'mm':
            //url = "http://connect.mail.ru/share?url="+encodeURIComponent(texts.socdata[id_branddis].url);
            url = "http://connect.mail.ru/share?url="+encodeURIComponent(texts.socdata[id_branddis].url)+"&title="+encodeURIComponent(texts.socdata[id_branddis].title)+"&description="+encodeURIComponent(texts.socdata[id_branddis].summary)+"&image_url="+encodeURIComponent(texts.socdata[id_branddis].image);
            break;
        case 'pi':
            url = "http://pinterest.com/pin/create/button/?url="+encodeURIComponent(texts.socdata[id_branddis].url)+"&description="+encodeURIComponent(texts.socdata[id_branddis].summary)+"&media="+encodeURIComponent(texts.socdata[id_branddis].image);
            break;
        default:
            url = '';
            break;
    }

    if(url.length > 0)
    {
        modalwin = window.open(url,'','toolbar=0,status=0,width=626,height=436');

        var postData = {
            'sessid': BX.bitrix_sessid(),
            'action': 'vote_check',
            'socid': socid,
            'battleid': id_branddis
        };
        BX.ajax({
            url: '/bitrix/components/mibix/battle.discounts/battle_ajax.php',
            method: 'POST',
            data: postData,
            dataType: 'json',
            onsuccess: function(voted)
            {
                if(voted) {
                    modalwin.close();
                    accessDialog.setContent(BX('accessDenyBlock').innerHTML);
                    accessDialog.show();
                }
            }
        });
    }
}

// Запоминаем ID битвы, за которую нажали кнопку "Голосовать"
function setDisBattleID(b) {
    id_branddis = b;
    return false;
}
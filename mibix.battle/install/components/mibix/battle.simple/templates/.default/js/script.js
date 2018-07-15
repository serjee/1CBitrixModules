window.BXDEBUG = true;
BX.ready(function() {

    var oPopup = new BX.PopupWindow('call_feedback', window.body, {
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
    oPopup.setContent(BX('hideBlock').innerHTML);

    BX.bindDelegate(
        document.body, 'click', {className: 'vote-popup-open' },
        BX.proxy(function(e){
            if(!e) e = window.event;
            oPopup.show();
            return BX.PreventDefault(e);
        }, oPopup)
    );

});

// обратный отчет времени
function time_tick(timeblock) {
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
                grc = app.time(gra);
                //timeblock.innerHTML = grc.days + " дн. " + grc.hours + " ч. " + grc.min + " мин. " + grc.sec + " сек.";
                BX(gr_group_name+'-timer-days').innerHTML = grc.days;
                BX(gr_group_name+'-timer-hrs').innerHTML = grc.hours;
                BX(gr_group_name+'-timer-min').innerHTML = grc.min;
                BX(gr_group_name+'-timer-sec').innerHTML = grc.sec;
            }
        }, 1000);
    }
}

app = {
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
var id_brand = 0;

// Голосовать через Фейсбук (вызывается при клике)
function voteSet(socid) {

    // всплывающее окно для постинга в соц.сеть
    var url;
    switch (socid)
    {
        case 'fb':
            //var fb_app_id = 0; // Чтобы активировать данный способ, нужно в создать приложение facebook и указать его ID в этой переменной
            //url = "https://www.facebook.com/dialog/feed?app_id="+fb_app_id+"&display=popup&name="+encodeURIComponent(texts.socdata[id_brand].title)+"&description="+encodeURIComponent(texts.socdata[id_brand].summary)+"&link="+encodeURIComponent(texts.socdata[id_brand].url)+"&picture="+encodeURIComponent(texts.socdata[id_brand].image);
            url = "http://www.facebook.com/sharer.php?s=100&p[title]="+encodeURIComponent(texts.socdata[id_brand].title)+"&p[summary]="+encodeURIComponent(texts.socdata[id_brand].summary)+"&p[url]="+encodeURIComponent(texts.socdata[id_brand].url)+"&p[images][0]="+encodeURIComponent(texts.socdata[id_brand].image);
            break;
        case 'tw':
            url = "http://twitter.com/share?text="+encodeURIComponent(texts.socdata[id_brand].titletw)+"&amp;url="+encodeURIComponent(texts.socdata[id_brand].url);
            break;
        case 'vk':
            url = "http://vk.com/share.php?url="+encodeURIComponent(texts.socdata[id_brand].url)+"&title="+encodeURIComponent(texts.socdata[id_brand].title)+"&description="+encodeURIComponent(texts.socdata[id_brand].summary)+"&image="+encodeURIComponent(texts.socdata[id_brand].image);
            break;
        case 'ok':
            url = "http://www.ok.ru/dk?st.cmd=addShare&st.s=1&st._surl="+encodeURIComponent(texts.socdata[id_brand].url)+"&st.comments="+encodeURIComponent(texts.socdata[id_brand].summary);
            //url = "http://www.ok.ru/dk?st.cmd=addShare&st.s=1&st._surl="+encodeURIComponent(texts.socdata[id_brand].url)+"&title="+encodeURIComponent(texts.socdata[id_brand].title)+"&description="+encodeURIComponent(texts.socdata[id_brand].summary);
            break;
        case 'mm':
            //url = "http://connect.mail.ru/share?url="+encodeURIComponent(texts.socdata[id_brand].url);
            url = "http://connect.mail.ru/share?url="+encodeURIComponent(texts.socdata[id_brand].url)+"&title="+encodeURIComponent(texts.socdata[id_brand].title)+"&description="+encodeURIComponent(texts.socdata[id_brand].summary)+"&image_url="+encodeURIComponent(texts.socdata[id_brand].image);
            break;
        case 'pi':
            url = "http://pinterest.com/pin/create/button/?url="+encodeURIComponent(texts.socdata[id_brand].url)+"&description="+encodeURIComponent(texts.socdata[id_brand].summary)+"&media="+encodeURIComponent(texts.socdata[id_brand].image);
            break;
        default:
            url = '';
            break;
    }

    if(url.length > 0)
    {
        modalwin = window.open(url,'','toolbar=0,status=0,width=626,height=436');
    }
}

// Запоминаем ID битвы, за которую нажали кнопку "Голосовать"
function setBattleID(b) {
    id_brand = b;
    return false;
}
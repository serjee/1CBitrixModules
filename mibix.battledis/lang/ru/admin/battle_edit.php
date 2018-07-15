<?
$MESS['MIBIX_BATTLEDIS_MODULE_NOT_FOUND'] = "Установленный модуль не найден";
$MESS['MIBIX_BATTLEDIS_MODULE_DEMO'] = "Вы используете ДЕМО-версию модуля";
$MESS['MIBIX_BATTLEDIS_MODULE_DEMO_EXPIRED'] = "Истек период работы ДЕМО-версии модуля";
$MESS["MIBIX_BATTLEDIS_TAB_BATTLE"] = "Настройки битвы";
$MESS["MIBIX_BATTLEDIS_TAB_BATTLE_TITLE"] = "Основные настройки голосования";
$MESS["MIBIX_BATTLEDIS_TAB_BATTLE_SOC"] = "Социальные сети";
$MESS["MIBIX_BATTLEDIS_TAB_BATTLE_SOC_TITLE"] = "Настройка социальных сетей для голосования";
$MESS["MIBIX_BATTLEDIS_BATTLE_TITLE"] = "Основные параметры битвы (голосования)";
$MESS["MIBIX_BATTLEDIS_BATTLE_EDIT_TITLE"] = "Редактирование битвы";
$MESS["MIBIX_BATTLEDIS_BATTLE_ADD_TITLE"] = "Создание новой битвы";
$MESS["MIBIX_BATTLEDIS_BATTLE_LIST"] = "Список битв";
$MESS["MIBIX_BATTLEDIS_BATTLE_LIST_TEXT"] = "Список";
$MESS["MIBIX_BATTLEDIS_BATTLE_SAVE_ERROR"] = "При сохранении \"битвы\" возникли ошибки:";
$MESS["MIBIX_BATTLEDIS_BATTLE_DATE_ADD"] = "Дата добавления";
$MESS["MIBIX_BATTLEDIS_BATTLE_DATE_UPD"] = "Дата модификации";
$MESS["MIBIX_BATTLEDIS_BATTLE_ACTIVE"] = "Активен";
$MESS["MIBIX_BATTLEDIS_BATTLE_DATE_START"] = "Дата и время начала";
$MESS["MIBIX_BATTLEDIS_BATTLE_DATE_FINISH"] = "Дата и время окончания";
$MESS["MIBIX_BATTLEDIS_BATTLE_NAME"] = "Название битвы";
$MESS["MIBIX_BATTLEDIS_BATTLE_ITEMS"] = "Элементы битвы";
$MESS["MIBIX_BATTLEDIS_BATTLE_IS_CRON_COUNT"] = "Подсчет голосов на стороне CRON";
$MESS["MIBIX_BATTLEDIS_BATTLE_IS_CRON_COUNT_NOTE"] = "Рекомендуется использовать вызов специального скрипта #SCRIPT# через CRON для подсчета голосов с целью оптимизации нагрузки на сайт (сервер). Иначе голоса будут подсчитываться каждный раз, на каждом хите страницы, на которой вызывается компонент модуля.<br />Пример правила для запуска через cron (ежедневно в 10.00):<br /><b>00 10 * * * /usr/bin/php -c /etc/php.ini -q #ROOT#/#SCRIPT# > /dev/null 2>&1</b>.";
$MESS["MIBIX_BATTLEDIS_BATTLE_IS_PRICE"] = "Отображать цену на товар";
$MESS["MIBIX_BATTLEDIS_BATTLE_IS_INDICATOR"] = "Выводить индикатор скидки";
$MESS["MIBIX_BATTLEDIS_BATTLE_DISCOUNT_ALL"] = "Общая скидка для распределения на все товары";
$MESS["MIBIX_BATTLEDIS_BATTLE_DISCOUNT_ALL_NOTE"] = "Здесь вы устанавливаете тот общий размер скидки (от 1 до 100 процентов), за которую будут бороться товары, учавствующие в битве путем голосования. Чем больше голосов будет у товара, тем больше он будет иметь процент скидки от общей.";
$MESS["MIBIX_BATTLEDIS_BATTLE_DISCOUNT_MIN"] = "Максимально допустимая скидка на один товар (%)";
$MESS["MIBIX_BATTLEDIS_BATTLE_DISCOUNT_MIN_NOTE"] = "Это ограничение позволяет не влететь в убыток, если вдруг один товар окажется настолько популярным, что практически вся общая скидка может перейти к нему. В итоге продажа такого товара может оказаться не рентабельной. Для этого установите максимально допустимый процент скидки для товара, и его скидка не будет превышать это значение.";
$MESS["MIBIX_BATTLEDIS_BATTLE_IS_PROTECTION"] = "Защита от повторных голосований";
$MESS["MIBIX_BATTLEDIS_BATTLE_IS_PROTECTION_NOTE"] = "Запрет пользователю голосовать с одного IP для каждой социальной сети больше одного раза в сутки.";

$MESS["MIBIX_BATTLEDIS_BATTLE_SOCNET_BLOCK"] = "Выбор социальных сетей доступных для голосования";
$MESS["MIBIX_BATTLEDIS_BATTLE_SOCNET_VK_TITLE"] = "Голосование через \"Вконтакте\"";
$MESS["MIBIX_BATTLEDIS_BATTLE_SOCNET_FB_TITLE"] = "Голосование через \"Фейсбук\"";
$MESS["MIBIX_BATTLEDIS_BATTLE_SOCNET_TW_TITLE"] = "Голосование через \"Твиттер\"";
$MESS["MIBIX_BATTLEDIS_BATTLE_SOCNET_OK_TITLE"] = "Голосование через \"Однокласники\"";
$MESS["MIBIX_BATTLEDIS_BATTLE_SOCNET_ML_TITLE"] = "Голосование через \"Мой Круг\"";
$MESS["MIBIX_BATTLEDIS_BATTLE_SOCNET_PI_TITLE"] = "Голосование через \"Pinterest\"";

$MESS["MIBIX_BATTLEDIS_BATTLE_DATA_TITLE"] = "Выбор расположения данных для битвы (голосования)";
$MESS["MIBIX_BATTLEDIS_BATTLE_IBLOCK_ID"] = "Инфоблок для выбора данных";
$MESS["MIBIX_BATTLEDIS_BATTLE_SITE_ID"] = "Сайт";
$MESS["MIBIX_BATTLEDIS_BATTLE_TITLE_NAME"] = "Расположение названия";
$MESS["MIBIX_BATTLEDIS_BATTLE_TEXT"] = "Расположение описания";
$MESS["MIBIX_BATTLEDIS_BATTLE_LINKS"] = "Ссылка на бренд";
$MESS["MIBIX_BATTLEDIS_BATTLE_SITE"] = "Адрес сайта для ссылок (пример: http://yoursite.com)";
$MESS["MIBIX_BATTLEDIS_BATTLE_PICTURES"] = "Расположение изображений";
$MESS["MIBIX_BATTLEDIS_BATTLE_GROUP"] = "Группа";

$MESS["MIBIX_BATTLEDIS_BATTLE_ADD_TEXT"] = "Добавить";
$MESS["MIBIX_BATTLEDIS_BATTLE_DEL_TEXT"] = "Удалить";
$MESS["MIBIX_BATTLEDIS_BATTLE_MNU_ADD"] = "Добавить нововую битву";
$MESS["MIBIX_BATTLEDIS_BATTLE_MNU_DEL"] = "Удалить эту битву";
$MESS["MIBIX_BATTLEDIS_BATTLE_MNU_DEL_CONF"] = "Удалить битву?";
$MESS["MIBIX_BATTLEDIS_BATTLE_SAVED"] = "Битва успешно сохранена.";
?>
<?php
$MESS["MIBIX_PP_TAB_MAIN"] = "Общие настройки";
$MESS["MIBIX_PP_TAB_MAIN_TITLE"] = "Общие настройки для экспорта в социальных сетях";
$MESS["MIBIX_PP_TAB_SOC"] = "Социальные сети";
$MESS["MIBIX_PP_TAB_SOC_TITLE"] = "Параметры экспорта в социальных сетях";

$MESS["MIBIX_PP_MAIN_SECTION"] = "Источник выгрузки картинок";
$MESS["MIBIX_PP_MAIN_IBLOCK_ID"] = "Инфоблок с товарами";
$MESS["MIBIX_PP_MAIN_SECTIONS_INC"] = "Выбрать разделы";
$MESS["MIBIX_PP_MAIN_SECTIONS_INC_NOTE"] = "при выборе учитываются и подразделы";
$MESS["MIBIX_PP_MAIN_SECTIONS_EXC"] = "Исключить разделы";
$MESS["MIBIX_PP_MAIN_SECTIONS_EXC_NOTE"] = "для выбранных разделов";
$MESS["MIBIX_PP_MAIN_TEXT"] = "Текст для описания фото";
$MESS["MIBIX_PP_MAIN_PICTURES"] = "Изображения для выгрузки";

$MESS["MIBIX_PP_PUBLISH"] = "Параметры выгрузки";
$MESS["MIBIX_PP_PUBLISH_LINK_POST"] = "Публиковать в описании фото ссылки на товары";
$MESS["MIBIX_SP_PUBLISH_SELECT_SITE"] = "Выбор сайта для формирования ссылок";
$MESS["MIBIX_PP_PUBLISH_DIFF_ITEMS"] = "Выбирать разные товары при выгрузке для каждой соц.сети";

$MESS["MIBIX_PP_EVENTS"] = "Выгрузка и обновление по событиям";
$MESS["MIBIX_PP_EVENTS_ITEM_ADD"] = "Экспорт картинок при добавлении нового товара";

$MESS["MIBIX_PP_TIME_SECTION"] = "Расписание для выгрузки";
$MESS["MIBIX_PP_TIME_METHOD"] = "Метод запуска:";
$MESS["MIBIX_PP_TIME_METHOD_AGENT"] = "через агента";
$MESS["MIBIX_PP_TIME_METHOD_CRON"] = "через cron запуск скрипта #SCRIPT#";
$MESS["MIBIX_PP_TIME_SPENT"] = "Время выгрузки";
$MESS["MIBIX_PP_TIME_PERIODITY"] = "Периодичность";
$MESS["MIBIX_PP_TIME_PER_1"] = "каждый день";
$MESS["MIBIX_PP_TIME_PER_2"] = "через день";
$MESS["MIBIX_PP_TIME_PER_3"] = "раз в 3 дня";
$MESS["MIBIX_PP_TIME_PER_4"] = "еженедельно";
$MESS["MIBIX_PP_AGENT"] = "Доступен только в случае, если системные агенты на Вашем сайте выполняются через cron (неважно, только непериодические или все). Иначе для автоматического создания резервных копий необходимо использовать метод запуска \"через cron\".";
$MESS["MIBIX_PP_CRON_SET"] = "Файл #SCRIPT# расположен в корне сайта. Путь к нему нужно указать в правиле CRON на хостинге или веб-сервере.<br />Пример правила для запуска через cron (ежедневно в 10.00):<br /><b>00 10 * * * /usr/bin/php -c /etc/php.ini -q #ROOT#/#SCRIPT# > /dev/null 2>&1</b>";
$MESS["MIBIX_PP_TOKEN_NOTE"] = "Процесс получения токена и ID описаны <a href=\"http://mibix.ru/docs/module_photoposter.pdf\" target=\"_blank\">в документации</a> модуля.";

$MESS["MIBIX_PP_SP_SECTION"] = "Интеграция с модулем \"Авто публикация товаров в соц.сетях\"";
$MESS["MIBIX_PP_SP_USESETTING"] = "Использовать аналогичные параметры доступа";

$MESS["MIBIX_PP_VK_SECTION"] = "Параметры доступа для соц.сети \"Вконтакте\"";
$MESS["MIBIX_PP_VK_ENABLE"] = "Включить экспорт для \"Вконтакте\"";
$MESS["MIBIX_PP_VK_TOKEN"] = "Токен для Вашего приложения";
$MESS["MIBIX_PP_VK_GROUP"] = "Группа для экспорта картинок (имя или ссылка)";

$MESS["MIBIX_PP_VK_ALB_SECTION"] = "Параметры альбомов для выгрузки \"Вконтакте\"";
$MESS["MIBIX_PP_VK_ALB_CHECK"] = "При выгрузке изображений";
$MESS["MIBIX_PP_VK_ALB_CHECK_EXIST"] = "Выбрать существующий альбом";
$MESS["MIBIX_PP_VK_ALB_CHECK_NEW"] = "Создавать новые альбомы для каждой категории товара";
$MESS["MIBIX_PP_VK_ALB_EXIST_SELECT"] = "Альбом для выгрузки";
$MESS["MIBIX_PP_VK_ALB_NEW_DESC"] = "Заполнять описание альбома из описания разделов";
$MESS["MIBIX_PP_VK_ALB_NEW_COMMENT"] = "Отключать комментарии для новых альбомов";

$MESS["MIBIX_PP_FB_ALB_SECTION"] = "Параметры альбомов для выгрузки \"Фейсбук\"";
$MESS["MIBIX_PP_FB_ALB_CHECK"] = "При выгрузке изображений";
$MESS["MIBIX_PP_FB_ALB_CHECK_EXIST"] = "Выбрать существующий альбом";
$MESS["MIBIX_PP_FB_ALB_CHECK_NEW"] = "Создавать новые альбомы для каждой категории товара";
$MESS["MIBIX_PP_FB_ALB_EXIST_SELECT"] = "Альбом для выгрузки";
$MESS["MIBIX_PP_FB_ALB_NEW_DESC"] = "Заполнять описание альбома из описания разделов";

$MESS["MIBIX_PP_FB_SECTION"] = "Параметры доступа для соц.сети \"Фейсбук\"";
$MESS["MIBIX_PP_FB_ENABLE"] = "Включить экспорт для \"Фейсбук\"";
$MESS["MIBIX_PP_FB_TOKEN"] = "Токен доступа к странице";
$MESS["MIBIX_PP_FB_TOKEN_NOTE"] = "Процесс получения токена описан <a href=\"http://mibix.ru/docs/module_photoposter.pdf\" target=\"_blank\">в документации</a> модуля.";
$MESS["MIBIX_PP_FB_WALL"] = "ID страницы для экспорта картинок";

$MESS["MIBIX_PP_MAIN_SAVED"] = "Настройки модуля успешно сохранены";
$MESS["MIBIX_PP_MAIN_ERROR_TITLE"] = "Во время сохранения настроек возникли ошибки:";

$MESS["MIBIX_PP_INC_SELECT_ALBUM"] = "Выберите альбом";
$MESS["MIBIX_PP_INC_SELECT_IBLOCK"] = "Выберите инфоблок";
$MESS["MIBIX_PP_INC_WITHOUT_TEXT"] = "Без текста";
$MESS["MIBIX_PP_INC_PREVIEW_TEXT"] = "Текст для анонса";
$MESS["MIBIX_PP_INC_DETAIL_TEXT"] = "Детальное описание";
$MESS["MIBIX_PP_INC_PREVIEW_PICTURE"] = "Картинка для анонса";
$MESS["MIBIX_PP_INC_DETAIL_PICTURE"] = "Детальная картинка";
$MESS["MIBIX_PP_ERR_IBLOCK_EMPTY"] = "Инфоблок не выбран";
$MESS["MIBIX_PP_ERR_TEXT_LIMIT"] = "Поле \"Текст для публикации\" содержит слишком много символов";
$MESS["MIBIX_PP_ERR_TEXT_OR_PICTURE"] = "Нужно выбрать хотя бы одно из полей: \"Текст для публикации\" или \"Изображения для публикации\"";
$MESS["MIBIX_PP_ERR_RUNTIME_LIMIT"] = "Поле \"Время публикации\" имеет неверный формат. Нужно указывать в виде: 00:00 или выбрать из таймера";
$MESS["MIBIX_PP_ERR_RUN_PERIOD"] = "Период запуска через Агента не выбран";
$MESS["MIBIX_PP_ERR_SP_TABLE_EMPTY"] = "Нельзя использовать настройки из модуля \"Авто постинг товаров в соц.сети\". Он не установлен.";
$MESS["MIBIX_PP_ERR_SP_BOTH_DISABLED"] = "Нет активных подключений в модуле \"Авто постинг товаров в соц.сети\".";
$MESS["MIBIX_PP_ERR_SP_BOTH_TOKEN_EMPTY"] = "Не указан ни один токен в модуле \"Авто постинг товаров в соц.сети\".";
$MESS["MIBIX_PP_ERR_SP_BOTH_WALL_EMPTY"] = "Не указана ни одна группа в модуле \"Авто постинг товаров в соц.сети\".";
$MESS["MIBIX_PP_ERR_VK_TOKEN_EMPTY"] = "Поле токена для \"Вконтакте\" нужно заполнить";
$MESS["MIBIX_PP_ERR_VK_TOKEN_LIMIT"] = "Поле токена для \"Вконтакте\" имеет слишком большое значение";
$MESS["MIBIX_PP_ERR_VK_WALL_EMPTY"] = "Поле \"Ссылка на группу Вконтакте\" нужно заполнить";
$MESS["MIBIX_PP_ERR_VK_WALL_LIMIT"] = "Поле \"сылка на группу Вконтакте\" имеет слишком большое значение";
$MESS["MIBIX_PP_ERR_FB_TOKEN_EMPTY"] = "Поле ID приложения (App ID) для \"Фейсбук\" нужно заполнить";
$MESS["MIBIX_PP_ERR_FB_TOKEN_LIMIT"] = "Поле ID приложения (App ID) для \"Фейсбук\" имеет слишком большое значение";
$MESS["MIBIX_PP_ERR_FB_WALL_EMPTY"] = "Поле \"Ссылка на группу Фейсбук\" нужно заполнить";
$MESS["MIBIX_PP_ERR_FB_WALL_LIMIT"] = "Поле \"Сылка на группу Фейсбук\" имеет слишком большое значение";
?>
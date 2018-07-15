<?php
$MESS["MIBIX_SP_TAB_MAIN"] = "Общие настройки";
$MESS["MIBIX_SP_TAB_MAIN_TITLE"] = "Общие настройки для публикации в социальных сетях";
$MESS["MIBIX_SP_TAB_SOC"] = "Социальные сети";
$MESS["MIBIX_SP_TAB_SOC_TITLE"] = "Параметры публикации в социальных сетях";

$MESS["MIBIX_SP_MAIN_SECTION"] = "Источник публикации товаров";
$MESS["MIBIX_SP_MAIN_IBLOCK_ID"] = "Инфоблок с товарами";
$MESS["MIBIX_SP_MAIN_SECTIONS_INC"] = "Выбрать разделы";
$MESS["MIBIX_SP_MAIN_SECTIONS_INC_NOTE"] = "при выборе учитываются и подразделы";
$MESS["MIBIX_SP_MAIN_SECTIONS_EXC"] = "Исключить разделы";
$MESS["MIBIX_SP_MAIN_SECTIONS_EXC_NOTE"] = "для выбранных разделов";
$MESS["MIBIX_SP_MAIN_TEXT"] = "Текст для публикации";
$MESS["MIBIX_SP_MAIN_PICTURES"] = "Изображения для публикации";

$MESS["MIBIX_SP_PUBLISH"] = "Параметры публикации";
$MESS["MIBIX_SP_PUBLISH_LINK_POST"] = "Публиковать ссылки на товары";
$MESS["MIBIX_SP_PUBLISH_SELECT_SITE"] = "Выбор сайта для формирования ссылок";
$MESS["MIBIX_SP_PUBLISH_DIFF_ITEMS"] = "Произвольные товары для каждой соц.сети";
$MESS["MIBIX_SP_PUBLISH_REPEAT"] = "Публиковать снова, когда все товары опубликованы";

$MESS["MIBIX_SP_EVENTS"] = "Публикация товаров по событиям";
$MESS["MIBIX_SP_EVENTS_ITEM_ADD"] = "Публикация нового товара при добавлении";

$MESS["MIBIX_SP_TIME_SECTION"] = "Расписание публикации";
$MESS["MIBIX_SP_TIME_METHOD"] = "Метод запуска:";
$MESS["MIBIX_SP_TIME_METHOD_AGENT"] = "через агента";
$MESS["MIBIX_SP_TIME_METHOD_CRON"] = "через cron запуск скрипта #SCRIPT#";
$MESS["MIBIX_SP_TIME_SPENT"] = "Время публикации";
$MESS["MIBIX_SP_TIME_PERIODITY"] = "Периодичность";
$MESS["MIBIX_SP_TIME_PER_1"] = "каждый день";
$MESS["MIBIX_SP_TIME_PER_2"] = "через день";
$MESS["MIBIX_SP_TIME_PER_3"] = "раз в 3 дня";
$MESS["MIBIX_SP_TIME_PER_4"] = "еженедельно";
$MESS["MIBIX_SP_AGENT"] = "Доступен только в случае, если системные агенты на Вашем сайте выполняются через cron (неважно, только непериодические или все). Иначе для автоматического создания резервных копий необходимо использовать метод запуска \"через cron\".";
$MESS["MIBIX_SP_CRON_SET"] = "Файл #SCRIPT# расположен в корне сайта. Путь к нему нужно указать в правиле CRON на хостинге или веб-сервере.<br />Пример правила для запуска через cron (ежедневно в 10.00):<br /><b>00 10 * * * /usr/bin/php -c /etc/php.ini -q #ROOT#/#SCRIPT# > /dev/null 2>&1</b>";
$MESS["MIBIX_SP_TOKEN_NOTE"] = "Процесс получения токена и ID описаны <a href=\"http://mibix.ru/docs/module_socposter.pdf\" target=\"_blank\">в документации</a> модуля.";

$MESS["MIBIX_SP_VK_SECTION"] = "Параметры для публикации в соц.сети \"Вконтакте\"";
$MESS["MIBIX_SP_VK_ENABLE"] = "Включить публикацию \"Вконтакте\"";
$MESS["MIBIX_SP_VK_TOKEN"] = "Токен для Вашего приложения";
$MESS["MIBIX_SP_VK_GROUP"] = "Группа для публикации (имя или ссылка)";

$MESS["MIBIX_SP_FB_SECTION"] = "Настройки для публикации в соц.сети \"Фейсбук\"";
$MESS["MIBIX_SP_FB_ENABLE"] = "Включить публикацию \"Фейсбук\"";
$MESS["MIBIX_SP_FB_TOKEN"] = "Токен доступа к странице";
$MESS["MIBIX_SP_FB_TOKEN_NOTE"] = "Процесс получения токена описан <a href=\"http://mibix.ru/docs/module_socposter.pdf\" target=\"_blank\">в документации</a> модуля.";
$MESS["MIBIX_SP_FB_WALL"] = "ID страницы для публикации";

$MESS["MIBIX_SP_MAIN_SAVED"] = "Настройки модуля успешно сохранены";
$MESS["MIBIX_SP_MAIN_ERROR_TITLE"] = "Во время сохранения настроек возникли ошибки:";

$MESS["MIBIX_SP_INC_SELECT_IBLOCK"] = "Выберите инфоблок";
$MESS["MIBIX_SP_INC_WITHOUT_TEXT"] = "Без текста";
$MESS["MIBIX_SP_INC_PREVIEW_TEXT"] = "Текст для анонса";
$MESS["MIBIX_SP_INC_DETAIL_TEXT"] = "Детальное описание";
$MESS["MIBIX_SP_INC_PREVIEW_PICTURE"] = "Картинка для анонса";
$MESS["MIBIX_SP_INC_DETAIL_PICTURE"] = "Детальная картинка";
$MESS["MIBIX_SP_ERR_IBLOCK_EMPTY"] = "Инфоблок не выбран";
$MESS["MIBIX_SP_ERR_TEXT_LIMIT"] = "Поле \"Текст для публикации\" содержит слишком много символов";
$MESS["MIBIX_SP_ERR_TEXT_OR_PICTURE"] = "Нужно выбрать хотя бы одно из полей: \"Текст для публикации\" или \"Изображения для публикации\"";
$MESS["MIBIX_SP_ERR_RUNTIME_LIMIT"] = "Поле \"Время публикации\" имеет неверный формат. Нужно указывать в виде: 00:00 или выбрать из таймера";
$MESS["MIBIX_SP_ERR_RUN_PERIOD"] = "Период запуска через Агента не выбран";
$MESS["MIBIX_SP_ERR_VK_TOKEN_EMPTY"] = "Поле токена для \"Вконтакте\" нужно заполнить";
$MESS["MIBIX_SP_ERR_VK_TOKEN_LIMIT"] = "Поле токена для \"Вконтакте\" имеет слишком большое значение";
$MESS["MIBIX_SP_ERR_VK_WALL_EMPTY"] = "Поле \"Ссылка на группу Вконтакте\" нужно заполнить";
$MESS["MIBIX_SP_ERR_VK_WALL_LIMIT"] = "Поле \"сылка на группу Вконтакте\" имеет слишком большое значение";
$MESS["MIBIX_SP_ERR_FB_TOKEN_EMPTY"] = "Поле ID приложения (App ID) для \"Фейсбук\" нужно заполнить";
$MESS["MIBIX_SP_ERR_FB_TOKEN_LIMIT"] = "Поле ID приложения (App ID) для \"Фейсбук\" имеет слишком большое значение";
$MESS["MIBIX_SP_ERR_FB_WALL_EMPTY"] = "Поле \"Ссылка на группу Фейсбук\" нужно заполнить";
$MESS["MIBIX_SP_ERR_FB_WALL_LIMIT"] = "Поле \"Сылка на группу Фейсбук\" имеет слишком большое значение";
?>
<?php

include_once('Vkontakte.php');

$groups = array('15014694', '15014695');    //ID групп
$message = "Текст сообщения";               //Текст сообщения
/*
Пути к изображениям
*/
$images = array(        
    'http://company.yandex.ru/i/kr7.jpg', 
    'http://company.yandex.ru/i/datacenters/_MG_3279.jpg'
);  

$application_id = '*******';    //ID приложения 
$secret_application_key = '************';   //Защищенный ключ
$login_or_email = 'sam3434@mail.ru';                //логин или email вконтакте
$vk_password = '************';                    //пароль вконтакте

$public = new Vkontakte($application_id, $secret_application_key, $login_or_email, $vk_password);
$public->vkrepost($groups, $message, $images);


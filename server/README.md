# Серверная часть meow-mkh.ru

Хостинг reg.ru, nginx + Apache + PHP 8.2, база — SQLite.

## Раскладка

    /var/www/u3618984/data/
      config/meow.php        конфиг с токеном бота и хешем пароля (вне веб-корня, 0600)
      config/meow.sqlite     база: события, настройки, заявки
      www/meow-mkh.ru/       сайт
        api/_boot.php        общий бутстрап
        api/schedule.php     отдаёт расписание в JSON
        api/lead.php         принимает заявку, шлёт в телеграм
      www/admin.meow-mkh.ru/ админка
        index.php            вход, расписание, заявки, настройки
        chatid.php           определение chat_id телеграма

## Первая установка

1. Положить `config.example.php` как `config/meow.php`, подставив токен и хеш пароля:
   `php -r 'echo password_hash("пароль", PASSWORD_DEFAULT);'`
2. Права: `chmod 700 config && chmod 600 config/meow.php`
3. База создастся сама при первом обращении.

## Смена пароля админки

    php -r 'echo password_hash("новый-пароль", PASSWORD_DEFAULT), "\n";'

Полученный хеш вписать в `admin_hash` в `config/meow.php`.

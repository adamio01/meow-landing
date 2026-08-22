<?php
/* Отдаёт активное расписание для блока на сайте. */
require __DIR__ . '/_boot.php';

header('Access-Control-Allow-Origin: *');

$rows = array_map(static function (array $e): array {
    return [
        'title' => $e['title'],
        'sub'   => $e['subtitle'],
        'kind'  => $e['kind'],
        'when'  => $e['when_text'],
        'age'   => $e['age'],
        'note'  => $e['note'],
    ];
}, events_active());

json_out(['ok' => true, 'events' => $rows, 'whatsapp' => setting('whatsapp', '79882938008')]);

<?php
/* Показывает chat_id всех, кто писал боту. Только для авторизованных. */
require '/var/www/u3618984/data/www/meow-mkh.ru/api/_boot.php';
session_name('meowadm'); session_start();
if (empty($_SESSION['auth'])) { http_response_code(403); exit('Сначала войдите в админку'); }

header('Content-Type: text/plain; charset=utf-8');
$token = cfg()['bot_token'];
$body  = @file_get_contents("https://api.telegram.org/bot{$token}/getUpdates");
$d     = json_decode((string)$body, true);
$found = [];
foreach (($d['result'] ?? []) as $u) {
    $m = $u['message'] ?? $u['my_chat_member'] ?? [];
    $c = $m['chat'] ?? null;
    if ($c) $found[$c['id']] = trim(($c['title'] ?? '') . ' ' . ($c['first_name'] ?? '') . ' @' . ($c['username'] ?? ''));
}
if (!$found) { echo "Пока никто не писал боту.\n\nОткройте @meowleadsbot в телеграме и нажмите «Начать» (/start), затем обновите эту страницу."; exit; }
echo "Найденные чаты — скопируйте нужный id в настройки:\n\n";
foreach ($found as $id => $who) echo "  $id   $who\n";

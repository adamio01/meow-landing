<?php
/* Приём заявки с сайта -> запись в базу -> отправка в телеграм. */
require __DIR__ . '/_boot.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'method'], 405);

$name    = trim((string)($_POST['name']    ?? ''));
$phone   = trim((string)($_POST['phone']   ?? ''));
$comment = trim((string)($_POST['comment'] ?? ''));
$source  = trim((string)($_POST['source']  ?? 'сайт'));
$honey   = trim((string)($_POST['website'] ?? ''));   // ловушка для ботов

if ($honey !== '') json_out(['ok' => true]);          // молча глотаем спам

$digits = preg_replace('/\D+/', '', $phone);
if (mb_strlen($name) < 2)  json_out(['ok' => false, 'error' => 'Укажите имя'], 422);
if (strlen($digits) < 10)  json_out(['ok' => false, 'error' => 'Укажите телефон'], 422);

$name    = mb_substr($name, 0, 80);
$comment = mb_substr($comment, 0, 500);
$source  = mb_substr($source, 0, 60);
$ip      = (string)($_SERVER['REMOTE_ADDR'] ?? '');

/* Не чаще 5 заявок с одного IP за 10 минут. */
$q = db()->prepare("SELECT COUNT(*) FROM leads WHERE ip = ? AND created_at > datetime('now', '-10 minutes')");
$q->execute([$ip]);
if ((int)$q->fetchColumn() >= 5) json_out(['ok' => false, 'error' => 'Слишком много заявок, попробуйте позже'], 429);

$ins = db()->prepare('INSERT INTO leads (name, phone, comment, source, ip, created_at)
                      VALUES (?, ?, ?, ?, ?, datetime("now", "+3 hours"))');
$ins->execute([$name, $phone, $comment, $source, $ip]);
$id = (int)db()->lastInsertId();

$e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$text = "🐱 <b>Новая заявка с сайта</b>\n\n"
      . "👤 <b>Имя:</b> " . $e($name) . "\n"
      . "📞 <b>Телефон:</b> " . $e($phone) . "\n"
      . ($comment !== '' ? "💬 <b>Комментарий:</b> " . $e($comment) . "\n" : '')
      . "📍 <b>Откуда:</b> " . $e($source);

$res = tg_send($text);
if (!empty($res['ok'])) db()->prepare('UPDATE leads SET delivered = 1 WHERE id = ?')->execute([$id]);

/* Заявка сохранена в любом случае — даже если телеграм не ответил. */
json_out(['ok' => true]);

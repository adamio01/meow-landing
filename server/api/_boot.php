<?php
/* Общий бутстрап: конфиг, база, вспомогательные функции. */
declare(strict_types=1);

const CFG_PATH = '/var/www/u3618984/data/config/meow.php';

function cfg(): array {
    static $c = null;
    if ($c === null) {
        if (!is_file(CFG_PATH)) { http_response_code(500); exit('config missing'); }
        $c = require CFG_PATH;
    }
    return $c;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . cfg()['db'], null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('CREATE TABLE IF NOT EXISTS events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            kind TEXT NOT NULL DEFAULT "weekly",
            when_text TEXT NOT NULL DEFAULT "",
            age TEXT NOT NULL DEFAULT "",
            note TEXT NOT NULL DEFAULT "",
            sort INTEGER NOT NULL DEFAULT 0,
            active INTEGER NOT NULL DEFAULT 1
        )');
        $pdo->exec('CREATE TABLE IF NOT EXISTS settings (k TEXT PRIMARY KEY, v TEXT NOT NULL)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS leads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL DEFAULT "",
            phone TEXT NOT NULL DEFAULT "",
            comment TEXT NOT NULL DEFAULT "",
            source TEXT NOT NULL DEFAULT "",
            ip TEXT NOT NULL DEFAULT "",
            created_at TEXT NOT NULL DEFAULT "",
            delivered INTEGER NOT NULL DEFAULT 0
        )');
    }
    return $pdo;
}

function setting(string $k, string $default = ''): string {
    $s = db()->prepare('SELECT v FROM settings WHERE k = ?');
    $s->execute([$k]);
    $v = $s->fetchColumn();
    return $v === false ? $default : (string)$v;
}

function set_setting(string $k, string $v): void {
    $s = db()->prepare('INSERT INTO settings (k, v) VALUES (?, ?)
                        ON CONFLICT(k) DO UPDATE SET v = excluded.v');
    $s->execute([$k, $v]);
}

function events_active(): array {
    return db()->query('SELECT * FROM events WHERE active = 1 ORDER BY sort, id')->fetchAll();
}

function json_out($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function tg_send(string $text): array {
    $token = cfg()['bot_token'];
    $chat  = setting('chat_id');
    if ($chat === '') return ['ok' => false, 'error' => 'chat_id не задан'];
    $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'chat_id'    => $chat,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]),
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($body === false) return ['ok' => false, 'error' => $err];
    $r = json_decode($body, true);
    return is_array($r) ? $r : ['ok' => false, 'error' => 'bad response'];
}

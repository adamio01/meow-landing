<?php
/* Админка МЯУ: расписание, тексты, фото, заявки. */
declare(strict_types=1);
require '/var/www/u3618984/data/www/meow-mkh.ru/api/render.php';

const SITE_DIR   = '/var/www/u3618984/data/www/meow-mkh.ru';
const UPLOAD_DIR = SITE_DIR . '/photos/uploads';
const SECTIONS   = '/var/www/u3618984/data/config/cms-sections.json';

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
$https = (isset($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off' && $_SERVER['HTTPS'] !== '')
      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
      || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
if ($https) ini_set('session.cookie_secure', '1');
session_name('meowadm');
session_start();

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function back(string $msg = '', string $tab = 'content', string $extra = ''): never {
    $_SESSION['flash'] = $msg;
    header('Location: ?tab=' . urlencode($tab) . $extra);
    exit;
}
function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400); exit('Страница устарела. Обновите её и попробуйте снова.');
    }
}
function sections(): array {
    static $s = null;
    if ($s === null) $s = json_decode((string)file_get_contents(SECTIONS), true) ?: [];
    return $s;
}
/** Снимок текущего состояния текстов — чтобы можно было вернуть назад. */
function snapshot(string $note): void {
    $cur = [];
    foreach (db()->query('SELECT k, v FROM content') as $r) $cur[$r['k']] = $r['v'];
    db()->prepare('INSERT INTO revisions (created_at, note, data) VALUES (?, ?, ?)')
        ->execute([date('d.m.Y H:i'), $note, json_encode($cur, JSON_UNESCAPED_UNICODE)]);
    db()->exec('DELETE FROM revisions WHERE id NOT IN (SELECT id FROM revisions ORDER BY id DESC LIMIT 20)');
}
/** Текущее значение поля: правка либо то, что в шаблоне. */
function value_of(array $f, array $ov): string {
    if (isset($ov[$f['key']]) && $ov[$f['key']] !== '') return $ov[$f['key']];
    return $f['kind'] === 'image' ? $f['orig'] : cms_to_text($f['orig']);
}

/* ---------- вход ---------- */
if (isset($_GET['logout'])) { session_destroy(); header('Location: ?'); exit; }
$err = '';
if (($_POST['do'] ?? '') === 'login') {
    usleep(300000);
    if (hash_equals(cfg()['admin_user'], (string)($_POST['user'] ?? ''))
        && password_verify((string)($_POST['pass'] ?? ''), cfg()['admin_hash'])) {
        session_regenerate_id(true);
        $_SESSION['auth'] = true;
    } else { $err = 'Неверный логин или пароль'; }
}
if (empty($_SESSION['auth'])) { $GLOBALS['login_err'] = $err; require __DIR__ . '/_login.php'; exit; }

/* ---------- действия ---------- */
$act = $_POST['do'] ?? '';
if ($act !== '' && $act !== 'login') {
    check_csrf();

    if ($act === 'save_content') {
        $fields = [];
        foreach (cms_fields() as $f) $fields[$f['key']] = $f;
        $posted  = (array)($_POST['f'] ?? []);
        $changed = 0;
        $pending = [];
        foreach ($posted as $k => $v) {
            if (!isset($fields[$k])) continue;
            $v    = trim((string)$v);
            $orig = cms_to_text($fields[$k]['orig']);
            $cur  = db()->prepare('SELECT v FROM content WHERE k = ?');
            $cur->execute([$k]);
            $now  = (string)($cur->fetchColumn() ?: $orig);
            if ($v === $now) continue;
            $pending[$k] = ($v === '' || $v === $orig) ? null : $v;
            $changed++;
        }
        if ($changed) {
            snapshot('правка текстов');
            $del = db()->prepare('DELETE FROM content WHERE k = ?');
            $set = db()->prepare('INSERT INTO content (k, v) VALUES (?, ?)
                                  ON CONFLICT(k) DO UPDATE SET v = excluded.v');
            foreach ($pending as $k => $v) { $v === null ? $del->execute([$k]) : $set->execute([$k, $v]); }
            $r = cms_render();
            back($r['ok'] ? "Сохранено. Изменено полей: {$changed}. Сайт обновлён."
                          : 'Ошибка сборки страницы: ' . ($r['error'] ?? '?'),
                 'content', '&s=' . urlencode((string)($_POST['sec'] ?? '')));
        }
        back('Ничего не изменилось', 'content', '&s=' . urlencode((string)($_POST['sec'] ?? '')));
    }

    if ($act === 'undo') {
        $rev = db()->query('SELECT * FROM revisions ORDER BY id DESC LIMIT 1')->fetch();
        if (!$rev) back('Возвращать нечего — правок ещё не было', 'content');
        $data = json_decode($rev['data'], true) ?: [];
        db()->exec('DELETE FROM content');
        $ins = db()->prepare('INSERT INTO content (k, v) VALUES (?, ?)');
        foreach ($data as $k => $v) $ins->execute([$k, $v]);
        db()->prepare('DELETE FROM revisions WHERE id = ?')->execute([$rev['id']]);
        cms_render();
        back('Вернул как было в ' . $rev['created_at'], 'content');
    }

    if ($act === 'reset_all') {
        snapshot('сброс всех правок');
        db()->exec('DELETE FROM content');
        cms_render();
        back('Все тексты и фото вернулись к исходным', 'content');
    }

    if ($act === 'upload') {
        $key = (string)($_POST['key'] ?? '');
        $file = $_FILES['photo'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) back('Файл не загрузился', 'photos');
        if ($file['size'] > 8 * 1024 * 1024) back('Файл больше 8 МБ — уменьшите его', 'photos');
        $info = @getimagesize($file['tmp_name']);
        $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
        if (!$ext) back('Подходят только JPG, PNG и WEBP', 'photos');
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $name = $key . '-' . substr(bin2hex(random_bytes(4)), 0, 6) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) back('Не смог сохранить файл', 'photos');
        snapshot('замена фото');
        db()->prepare('INSERT INTO content (k, v) VALUES (?, ?)
                       ON CONFLICT(k) DO UPDATE SET v = excluded.v')
            ->execute([$key, 'photos/uploads/' . $name]);
        cms_render();
        back('Фото заменено, сайт обновлён', 'photos');
    }

    /* ---- расписание ---- */
    if ($act === 'save') {
        $title = trim((string)$_POST['title']);
        if ($title === '') back('Название не может быть пустым', 'events');
        $args = [$title, trim((string)($_POST['subtitle'] ?? '')),
                 in_array($_POST['kind'] ?? '', ['weekly','special'], true) ? $_POST['kind'] : 'weekly',
                 trim((string)$_POST['when_text']), trim((string)$_POST['age']),
                 trim((string)$_POST['note']), (int)($_POST['sort'] ?? 0),
                 isset($_POST['active']) ? 1 : 0];
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $args[] = $id;
            db()->prepare('UPDATE events SET title=?,subtitle=?,kind=?,when_text=?,age=?,note=?,sort=?,active=? WHERE id=?')->execute($args);
            back('Событие обновлено', 'events');
        }
        db()->prepare('INSERT INTO events (title,subtitle,kind,when_text,age,note,sort,active) VALUES (?,?,?,?,?,?,?,?)')->execute($args);
        back('Событие добавлено', 'events');
    }
    if ($act === 'toggle') { db()->prepare('UPDATE events SET active = 1 - active WHERE id = ?')->execute([(int)$_POST['id']]); back('Готово', 'events'); }
    if ($act === 'delete') { db()->prepare('DELETE FROM events WHERE id = ?')->execute([(int)$_POST['id']]); back('Событие удалено', 'events'); }
    if ($act === 'move') {
        $id = (int)$_POST['id']; $dir = $_POST['dir'] === 'up' ? -1 : 1;
        $cur = db()->query('SELECT id FROM events ORDER BY sort, id')->fetchAll();
        $pos = null;
        foreach ($cur as $i => $r) if ((int)$r['id'] === $id) $pos = $i;
        if ($pos !== null) {
            $new = $pos + $dir;
            if ($new >= 0 && $new < count($cur)) {
                [$cur[$pos], $cur[$new]] = [$cur[$new], $cur[$pos]];
                $up = db()->prepare('UPDATE events SET sort = ? WHERE id = ?');
                foreach ($cur as $i => $r) $up->execute([$i, $r['id']]);
            }
        }
        back('Порядок изменён', 'events');
    }
}

$tab   = $_GET['tab'] ?? 'content';
$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$ov    = cms_overrides();
$revCount = (int)db()->query('SELECT COUNT(*) FROM revisions')->fetchColumn();
$changedCount = count($ov);
require __DIR__ . '/_layout.php';

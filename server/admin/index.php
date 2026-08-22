<?php
/* Админка МЯУ: расписание, настройки, журнал заявок. */
declare(strict_types=1);
require '/var/www/u3618984/data/www/meow-mkh.ru/api/_boot.php';

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
/* Apache отдаёт HTTPS="off" на обычном соединении — !empty() тут не годится. */
$https = (isset($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off' && $_SERVER['HTTPS'] !== '')
      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
      || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
if ($https) ini_set('session.cookie_secure', '1');
session_name('meowadm');
session_start();

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function back(string $msg = '', string $tab = 'events'): never {
    $_SESSION['flash'] = $msg;
    header('Location: ?tab=' . urlencode($tab));
    exit;
}
function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400); exit('Сессия устарела, обновите страницу');
    }
}

/* ---------- вход / выход ---------- */
if (isset($_GET['logout'])) { session_destroy(); header('Location: ?'); exit; }

$err = '';
if (($_POST['do'] ?? '') === 'login') {
    $u = (string)($_POST['user'] ?? '');
    $p = (string)($_POST['pass'] ?? '');
    /* Пауза против перебора. */
    usleep(300000);
    if (hash_equals(cfg()['admin_user'], $u) && password_verify($p, cfg()['admin_hash'])) {
        session_regenerate_id(true);
        $_SESSION['auth'] = true;
    } else {
        $err = 'Неверный логин или пароль';
    }
}

if (empty($_SESSION['auth'])) {
    ?><!doctype html><html lang="ru"><head><meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Вход — админка МЯУ</title><style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;
    background:#12121a;color:#fff;font:16px/1.5 -apple-system,Segoe UI,Roboto,sans-serif}
    form{background:#1c1c28;padding:32px;border-radius:16px;width:min(360px,92vw)}
    h1{margin:0 0 24px;font-size:20px}
    label{display:block;font-size:13px;opacity:.7;margin:14px 0 6px}
    input{width:100%;padding:12px 14px;border-radius:10px;border:1px solid #33334a;
    background:#12121a;color:#fff;font-size:16px}
    button{width:100%;margin-top:22px;padding:13px;border:0;border-radius:10px;
    background:#ffd400;color:#12121a;font-weight:700;font-size:15px;cursor:pointer}
    .err{margin-top:16px;color:#ff6b8a;font-size:14px}</style></head><body>
    <form method="post"><h1>Админка МЯУ</h1>
    <input type="hidden" name="do" value="login">
    <label>Логин</label><input name="user" autocomplete="username" autofocus>
    <label>Пароль</label><input name="pass" type="password" autocomplete="current-password">
    <button>Войти</button>
    <?php if ($err) echo '<div class="err">' . h($err) . '</div>'; ?>
    </form></body></html><?php
    exit;
}

/* ---------- действия ---------- */
$act = $_POST['do'] ?? '';
if ($act !== '' && $act !== 'login') {
    check_csrf();
    if ($act === 'save') {
        $id    = (int)($_POST['id'] ?? 0);
        $title = trim((string)$_POST['title']);
        if ($title === '') back('Название не может быть пустым');
        $args = [
            $title,
            in_array($_POST['kind'] ?? '', ['weekly', 'special'], true) ? $_POST['kind'] : 'weekly',
            trim((string)$_POST['when_text']),
            trim((string)$_POST['age']),
            trim((string)$_POST['note']),
            (int)($_POST['sort'] ?? 0),
            isset($_POST['active']) ? 1 : 0,
        ];
        if ($id > 0) {
            $args[] = $id;
            db()->prepare('UPDATE events SET title=?,kind=?,when_text=?,age=?,note=?,sort=?,active=?
                           WHERE id=?')->execute($args);
            back('Событие обновлено');
        }
        db()->prepare('INSERT INTO events (title,kind,when_text,age,note,sort,active)
                       VALUES (?,?,?,?,?,?,?)')->execute($args);
        back('Событие добавлено');
    }
    if ($act === 'toggle') {
        db()->prepare('UPDATE events SET active = 1 - active WHERE id = ?')->execute([(int)$_POST['id']]);
        back('Показ переключён');
    }
    if ($act === 'delete') {
        db()->prepare('DELETE FROM events WHERE id = ?')->execute([(int)$_POST['id']]);
        back('Событие удалено');
    }
    if ($act === 'move') {
        $id  = (int)$_POST['id'];
        $dir = $_POST['dir'] === 'up' ? -1 : 1;
        $cur = db()->query('SELECT id, sort FROM events ORDER BY sort, id')->fetchAll();
        foreach ($cur as $i => $r) if ((int)$r['id'] === $id) $pos = $i;
        if (isset($pos)) {
            $new = $pos + $dir;
            if ($new >= 0 && $new < count($cur)) {
                [$cur[$pos], $cur[$new]] = [$cur[$new], $cur[$pos]];
                $up = db()->prepare('UPDATE events SET sort = ? WHERE id = ?');
                foreach ($cur as $i => $r) $up->execute([$i, $r['id']]);
            }
        }
        back('Порядок изменён');
    }
    if ($act === 'settings') {
        set_setting('chat_id',  trim((string)$_POST['chat_id']));
        set_setting('whatsapp', preg_replace('/\D+/', '', (string)$_POST['whatsapp']));
        back('Настройки сохранены', 'settings');
    }
    if ($act === 'testtg') {
        $r = tg_send('✅ Проверка связи из админки МЯУ. Заявки будут приходить сюда.');
        back(!empty($r['ok']) ? 'Тестовое сообщение отправлено' :
            'Не отправилось: ' . ($r['description'] ?? $r['error'] ?? 'неизвестная ошибка'), 'settings');
    }
}

$tab    = $_GET['tab'] ?? 'events';
$flash  = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$events = db()->query('SELECT * FROM events ORDER BY sort, id')->fetchAll();
$edit   = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare('SELECT * FROM events WHERE id = ?');
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch() ?: null;
}
?><!doctype html><html lang="ru"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Админка МЯУ</title><style>
*{box-sizing:border-box}
body{margin:0;background:#12121a;color:#e9e9f2;font:15px/1.55 -apple-system,Segoe UI,Roboto,sans-serif}
.wrap{max-width:920px;margin:0 auto;padding:24px 16px 64px}
header{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:24px}
h1{font-size:20px;margin:0}
a{color:inherit}
.tabs{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap}
.tabs a{padding:8px 16px;border-radius:999px;background:#1c1c28;text-decoration:none;font-size:14px}
.tabs a.on{background:#ffd400;color:#12121a;font-weight:700}
.card{background:#1c1c28;border-radius:14px;padding:18px;margin-bottom:14px}
.flash{background:#1d3a2a;border:1px solid #2f6a49;padding:12px 16px;border-radius:10px;margin-bottom:18px}
table{width:100%;border-collapse:collapse}
td,th{padding:11px 8px;text-align:left;border-bottom:1px solid #2a2a3c;vertical-align:top;font-size:14px}
th{font-size:12px;text-transform:uppercase;letter-spacing:.06em;opacity:.55;font-weight:600}
.off{opacity:.4}
.tag{display:inline-block;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:700}
.tag.w{background:#28407a;color:#bcd0ff}
.tag.s{background:#7a2848;color:#ffc0d4}
label{display:block;font-size:12px;opacity:.65;margin:12px 0 5px}
input[type=text],input[type=number],select,textarea{width:100%;padding:10px 12px;border-radius:9px;
border:1px solid #33334a;background:#12121a;color:#fff;font-size:15px;font-family:inherit}
textarea{min-height:64px;resize:vertical}
.row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
button{padding:10px 16px;border:0;border-radius:9px;background:#ffd400;color:#12121a;
font-weight:700;font-size:14px;cursor:pointer}
button.ghost{background:#2a2a3c;color:#e9e9f2}
button.danger{background:#4a1d2a;color:#ff9db3}
.acts{display:flex;gap:6px;flex-wrap:wrap}
.acts form{display:inline}
.acts button{padding:6px 11px;font-size:13px}
.hint{font-size:13px;opacity:.6;margin-top:8px}
@media(max-width:640px){.row{grid-template-columns:1fr}
 table,thead,tbody,th,td,tr{display:block}
 thead{display:none}
 tr{border-bottom:1px solid #2a2a3c;padding:10px 0}
 td{border:0;padding:4px 0}}
</style></head><body><div class="wrap">
<header><h1>🐱 Админка МЯУ</h1><a href="?logout=1">Выйти</a></header>
<div class="tabs">
  <a href="?tab=events"   class="<?= $tab === 'events'   ? 'on' : '' ?>">Расписание</a>
  <a href="?tab=leads"    class="<?= $tab === 'leads'    ? 'on' : '' ?>">Заявки</a>
  <a href="?tab=settings" class="<?= $tab === 'settings' ? 'on' : '' ?>">Настройки</a>
</div>
<?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>

<?php if ($tab === 'events'): ?>
  <div class="card">
    <b><?= $edit ? 'Изменить событие' : 'Новое событие' ?></b>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf() ?>">
      <input type="hidden" name="do" value="save">
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <label>Название</label>
      <input type="text" name="title" value="<?= h($edit['title'] ?? '') ?>" required>
      <div class="row">
        <div><label>Когда</label>
          <input type="text" name="when_text" placeholder="Каждую пятницу и субботу"
                 value="<?= h($edit['when_text'] ?? '') ?>"></div>
        <div><label>Возраст</label>
          <input type="text" name="age" placeholder="3+" value="<?= h($edit['age'] ?? '') ?>"></div>
      </div>
      <div class="row">
        <div><label>Тип</label>
          <select name="kind">
            <option value="weekly"  <?= ($edit['kind'] ?? '') === 'weekly'  ? 'selected' : '' ?>>Постоянное</option>
            <option value="special" <?= ($edit['kind'] ?? '') === 'special' ? 'selected' : '' ?>>Раз в месяц</option>
          </select></div>
        <div><label>Порядок</label>
          <input type="number" name="sort" value="<?= (int)($edit['sort'] ?? count($events)) ?>"></div>
      </div>
      <label>Описание (необязательно)</label>
      <textarea name="note"><?= h($edit['note'] ?? '') ?></textarea>
      <label style="display:flex;gap:8px;align-items:center;margin-top:14px">
        <input type="checkbox" name="active" style="width:auto"
               <?= (!$edit || $edit['active']) ? 'checked' : '' ?>> Показывать на сайте
      </label>
      <div style="margin-top:16px" class="acts">
        <button><?= $edit ? 'Сохранить' : 'Добавить' ?></button>
        <?php if ($edit): ?><a href="?tab=events"><button type="button" class="ghost">Отмена</button></a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card">
    <table><thead><tr><th>Событие</th><th>Когда</th><th>Возраст</th><th></th></tr></thead><tbody>
    <?php foreach ($events as $e): ?>
      <tr class="<?= $e['active'] ? '' : 'off' ?>">
        <td><b><?= h($e['title']) ?></b>
          <span class="tag <?= $e['kind'] === 'special' ? 's' : 'w' ?>">
            <?= $e['kind'] === 'special' ? 'раз в месяц' : 'постоянное' ?></span>
          <?php if (!$e['active']): ?> <span class="tag" style="background:#3a3a4c">скрыто</span><?php endif; ?>
          <?php if ($e['note']): ?><div class="hint"><?= h($e['note']) ?></div><?php endif; ?></td>
        <td><?= h($e['when_text']) ?></td>
        <td><?= h($e['age']) ?></td>
        <td><div class="acts">
          <a href="?tab=events&edit=<?= (int)$e['id'] ?>"><button type="button" class="ghost">Изменить</button></a>
          <form method="post"><input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="do" value="toggle"><input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
            <button class="ghost"><?= $e['active'] ? 'Скрыть' : 'Показать' ?></button></form>
          <form method="post"><input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="do" value="move"><input type="hidden" name="dir" value="up">
            <input type="hidden" name="id" value="<?= (int)$e['id'] ?>"><button class="ghost">↑</button></form>
          <form method="post"><input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="do" value="move"><input type="hidden" name="dir" value="down">
            <input type="hidden" name="id" value="<?= (int)$e['id'] ?>"><button class="ghost">↓</button></form>
          <form method="post" onsubmit="return confirm('Удалить «<?= h($e['title']) ?>»?')">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
            <button class="danger">Удалить</button></form>
        </div></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$events): ?><tr><td colspan="4">Пока пусто — добавьте первое событие выше.</td></tr><?php endif; ?>
    </tbody></table>
  </div>

<?php elseif ($tab === 'leads'):
  $leads = db()->query('SELECT * FROM leads ORDER BY id DESC LIMIT 200')->fetchAll(); ?>
  <div class="card">
    <table><thead><tr><th>Когда</th><th>Имя</th><th>Телефон</th><th>Откуда</th><th>ТГ</th></tr></thead><tbody>
    <?php foreach ($leads as $l): ?>
      <tr><td><?= h($l['created_at']) ?></td><td><?= h($l['name']) ?></td>
        <td><a href="tel:<?= h($l['phone']) ?>"><?= h($l['phone']) ?></a></td>
        <td><?= h($l['source']) ?><?php if ($l['comment']): ?><div class="hint"><?= h($l['comment']) ?></div><?php endif; ?></td>
        <td><?= $l['delivered'] ? '✅' : '—' ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$leads): ?><tr><td colspan="5">Заявок пока нет.</td></tr><?php endif; ?>
    </tbody></table>
  </div>

<?php else: ?>
  <div class="card">
    <b>Куда слать заявки</b>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf() ?>">
      <input type="hidden" name="do" value="settings">
      <label>Telegram chat_id</label>
      <input type="text" name="chat_id" value="<?= h(setting('chat_id')) ?>" placeholder="например 123456789">
      <div class="hint">Напишите боту @meowleadsbot команду /start, затем нажмите «Определить chat_id» ниже.</div>
      <label>Номер WhatsApp для записи (только цифры)</label>
      <input type="text" name="whatsapp" value="<?= h(setting('whatsapp', '79882938008')) ?>">
      <div style="margin-top:16px"><button>Сохранить</button></div>
    </form>
  </div>
  <div class="card">
    <b>Проверка связи</b>
    <div class="hint">Отправит тестовое сообщение в указанный чат.</div>
    <form method="post" style="margin-top:12px">
      <input type="hidden" name="csrf" value="<?= csrf() ?>">
      <input type="hidden" name="do" value="testtg">
      <button class="ghost">Отправить тест</button>
    </form>
  </div>
  <div class="card">
    <b>Определить chat_id</b>
    <div class="hint">Сначала напишите боту /start в телеграме, потом откройте:
      <a href="chatid.php" target="_blank">chatid.php</a></div>
  </div>
<?php endif; ?>
</div></body></html>

<!doctype html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Панель МЯУ</title>
<style>
@font-face{font-family:'Mont';src:url('fonts/Mont-Regular.woff2') format('woff2');font-weight:400;font-display:swap}
@font-face{font-family:'Mont';src:url('fonts/Mont-SemiBold.woff2') format('woff2');font-weight:600;font-display:swap}
@font-face{font-family:'Mont';src:url('fonts/Mont-Bold.woff2') format('woff2');font-weight:700;font-display:swap}
@font-face{font-family:'Mont';src:url('fonts/Mont-Heavy.woff2') format('woff2');font-weight:800;font-display:swap}
@font-face{font-family:'Riffic';src:url('fonts/Riffic-Bold.ttf') format('truetype');font-weight:700;font-display:swap}
:root{--ink:#0A0A0F;--paper:#F4EFE6;--white:#fff;--gray:#6F6878;--line:rgba(10,10,15,.12);
--pink:#FC1751;--yellow:#F9C930;--green:#1E9E63}
*{box-sizing:border-box}
body{margin:0;background:var(--paper);color:var(--ink);font-family:'Mont',system-ui,sans-serif;
font-size:15px;line-height:1.5;padding-bottom:110px}
h1,h2,h3{font-family:'Riffic','Mont',sans-serif;text-transform:uppercase;letter-spacing:-.02em;margin:0}
a{color:inherit}
.top{position:sticky;top:0;z-index:50;background:var(--paper);border-bottom:1.5px solid var(--line)}
.wrap{max-width:1100px;margin:0 auto;padding:0 22px}
.top__in{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:18px 0}
.top h1{font-size:22px}
.top a.out{font-size:13px;font-weight:700;color:var(--gray);text-decoration:none}
.tabs{display:flex;gap:8px;overflow-x:auto;padding-bottom:14px}
.tabs a{white-space:nowrap;padding:10px 20px;border-radius:999px;background:var(--white);
border:1.5px solid var(--line);text-decoration:none;font-size:14px;font-weight:700}
.tabs a.on{background:var(--ink);color:var(--white);border-color:var(--ink)}
main{padding-top:26px}
.flash{margin-bottom:22px;padding:15px 20px;border-radius:14px;background:#E4F6EC;
border:1.5px solid #A8DDC2;color:#14603C;font-weight:600}
.card{background:var(--white);border:1.5px solid var(--line);border-radius:20px;padding:26px;margin-bottom:18px}
.card>h2{font-size:19px;margin-bottom:6px}
.card>.note{color:var(--gray);font-size:14px;margin-bottom:20px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:18px}
label{display:block;font-size:11px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;
color:var(--gray);margin-bottom:7px}
input[type=text],input[type=number],select,textarea{width:100%;padding:13px 16px;border-radius:12px;
border:1.5px solid var(--line);background:var(--paper);color:var(--ink);
font-family:inherit;font-size:15px;font-weight:500}
textarea{min-height:82px;resize:vertical;line-height:1.5}
input:focus,textarea:focus,select:focus{outline:none;border-color:var(--pink);background:var(--white)}
.field{margin-bottom:18px}
.field.dirty input,.field.dirty textarea{border-color:var(--yellow);background:#FFFBEC}
.btn{display:inline-flex;align-items:center;gap:8px;padding:13px 22px;border:0;border-radius:12px;
background:var(--ink);color:var(--white);font-family:inherit;font-weight:800;font-size:13px;
letter-spacing:.05em;text-transform:uppercase;cursor:pointer;text-decoration:none}
.btn.ghost{background:var(--white);color:var(--ink);border:1.5px solid var(--line)}
.btn.danger{background:#FFE8ED;color:#C8113F}
.btn:disabled{opacity:.45;cursor:default}
.btn.sm{padding:8px 14px;font-size:11px}
.bar{position:fixed;left:0;right:0;bottom:0;z-index:60;background:var(--white);
border-top:1.5px solid var(--line);box-shadow:0 -8px 24px rgba(10,10,15,.06)}
.bar__in{display:flex;align-items:center;gap:14px;flex-wrap:wrap;padding:16px 0}
.bar__txt{font-size:14px;color:var(--gray);margin-right:auto}
.bar__txt b{color:var(--ink)}
table{width:100%;border-collapse:collapse}
th,td{padding:13px 10px;text-align:left;border-bottom:1px solid var(--line);vertical-align:top;font-size:14px}
th{font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:var(--gray);font-weight:800}
.tag{display:inline-block;padding:3px 10px;border-radius:999px;font-size:10px;font-weight:800;
letter-spacing:.05em;text-transform:uppercase}
.tag.w{background:#E8EEFF;color:#2A68E1}.tag.s{background:#FFF3CC;color:#8A6A00}
.tag.off{background:#EEE;color:#777}
.off td{opacity:.45}
.acts{display:flex;gap:6px;flex-wrap:wrap}.acts form{display:inline}
.ph{display:flex;gap:16px;align-items:flex-start;background:var(--white);
border:1.5px solid var(--line);border-radius:16px;padding:14px}
.ph img{width:112px;height:84px;object-fit:cover;border-radius:10px;flex:none;background:var(--paper)}
.ph__b{min-width:0;flex:1}
.ph__n{font-weight:700;font-size:14px;margin-bottom:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ph__p{font-size:12px;color:var(--gray);margin-bottom:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ph input[type=file]{font-size:12px;width:100%}
.hint{font-size:12px;color:var(--gray);margin-top:6px}
.secnav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px}
.secnav a{padding:8px 15px;border-radius:999px;background:var(--white);border:1.5px solid var(--line);
text-decoration:none;font-size:13px;font-weight:700}
.secnav a.on{background:var(--pink);color:var(--white);border-color:var(--pink)}
@media(max-width:700px){.wrap{padding:0 14px}.card{padding:18px}
 table,thead,tbody,th,td,tr{display:block}thead{display:none}
 tr{border-bottom:1.5px solid var(--line);padding:12px 0}td{border:0;padding:4px 0}}
</style></head><body>

<div class="top"><div class="wrap">
  <div class="top__in"><h1>🐱 Панель МЯУ</h1>
    <div><a href="https://meow-mkh.ru/" target="_blank" class="btn ghost sm">Открыть сайт</a>
    <a href="?logout=1" class="out">Выйти</a></div></div>
  <div class="tabs">
    <a href="?tab=content" class="<?= $tab==='content'?'on':'' ?>">Тексты</a>
    <a href="?tab=photos"  class="<?= $tab==='photos' ?'on':'' ?>">Фото</a>
    <a href="?tab=events"  class="<?= $tab==='events' ?'on':'' ?>">Расписание</a>
    <a href="?tab=leads"   class="<?= $tab==='leads'  ?'on':'' ?>">Заявки</a>
  </div>
</div></div>

<main class="wrap">
<?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
<?php
$fields = cms_fields();
$names  = sections();

if ($tab === 'content'):
    $groups = [];
    foreach ($fields as $f) if ($f['kind'] !== 'image') $groups[$f['section']][] = $f;
    $cur = $_GET['s'] ?? array_key_first($groups);
    if (!isset($groups[$cur])) $cur = array_key_first($groups);
?>
  <div class="secnav">
    <?php foreach ($groups as $sec => $list): ?>
      <a href="?tab=content&s=<?= urlencode((string)$sec) ?>" class="<?= $sec===$cur?'on':'' ?>">
        <?= h($names[$sec] ?? $sec) ?></a>
    <?php endforeach; ?>
  </div>
  <form method="post" id="cform">
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <input type="hidden" name="do" value="save_content">
    <input type="hidden" name="sec" value="<?= h((string)$cur) ?>">
    <div class="card">
      <h2><?= h($names[$cur] ?? $cur) ?></h2>
      <div class="note">Перенос строки — просто Enter. Чтобы выделить слово цветом, поставьте вокруг него звёздочки: *слово*.</div>
      <div class="grid">
      <?php foreach ($groups[$cur] as $f): $v = value_of($f, $ov); $edited = isset($ov[$f['key']]); ?>
        <div class="field <?= $edited?'dirty':'' ?>">
          <label><?= h($f['label']) ?><?= $edited ? ' · изменено' : '' ?></label>
          <?php if ($f['kind'] === 'line'): ?>
            <input type="text" name="f[<?= $f['key'] ?>]" value="<?= h($v) ?>">
          <?php else: ?>
            <textarea name="f[<?= $f['key'] ?>]"><?= h($v) ?></textarea>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </form>
  <div class="bar"><div class="wrap"><div class="bar__in">
    <div class="bar__txt">Изменённых полей на сайте: <b><?= $changedCount ?></b>.
      <?= $revCount ? 'Можно вернуть последнюю правку.' : 'Правок ещё не было.' ?></div>
    <form method="post" onsubmit="return confirm('Вернуть последнюю сохранённую правку?')">
      <input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="do" value="undo">
      <button class="btn ghost" <?= $revCount ? '' : 'disabled' ?>>← Вернуть назад</button></form>
    <form method="post" onsubmit="return confirm('Вернуть ВСЕ тексты и фото к исходным? Это отменит все правки.')">
      <input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="do" value="reset_all">
      <button class="btn danger" <?= $changedCount ? '' : 'disabled' ?>>Сбросить всё</button></form>
    <button class="btn" form="cform">Сохранить изменения</button>
  </div></div></div>

<?php elseif ($tab === 'photos'):
    $imgs = array_values(array_filter($fields, fn($f) => $f['kind'] === 'image'));
    $groups = [];
    foreach ($imgs as $f) $groups[$f['section']][] = $f;
?>
  <div class="card">
    <h2>Фотографии сайта</h2>
    <div class="note">Выберите новый файл — он заменит фото сразу. JPG, PNG или WEBP, до 8 МБ.
      Лучше брать снимок такого же вытянутого или квадратного вида, как старый, иначе кадр обрежется.</div>
  </div>
  <?php foreach ($groups as $sec => $list): ?>
    <div class="card"><h2 style="font-size:16px;margin-bottom:16px"><?= h($names[$sec] ?? $sec) ?></h2>
      <div class="grid">
      <?php foreach ($list as $f): $src = value_of($f, $ov); ?>
        <form method="post" enctype="multipart/form-data" class="ph">
          <img src="https://meow-mkh.ru/<?= h($src) ?>" alt="" loading="lazy">
          <div class="ph__b">
            <div class="ph__n"><?= h($f['label']) ?><?= isset($ov[$f['key']]) ? ' · заменено' : '' ?></div>
            <div class="ph__p"><?= h(basename($src)) ?></div>
            <input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="do" value="upload">
            <input type="hidden" name="key" value="<?= $f['key'] ?>">
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                   onchange="this.form.submit()">
          </div>
        </form>
      <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

<?php elseif ($tab === 'events'):
    $events = db()->query('SELECT * FROM events ORDER BY sort, id')->fetchAll();
    $edit = null;
    if (isset($_GET['edit'])) { $s = db()->prepare('SELECT * FROM events WHERE id=?'); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch() ?: null; }
?>
  <div class="card">
    <h2><?= $edit ? 'Изменить событие' : 'Добавить событие' ?></h2>
    <div class="note">То, что здесь появится, сразу видно в блоке «Расписание» на сайте.</div>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf() ?>">
      <input type="hidden" name="do" value="save">
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="grid">
        <div class="field"><label>Название (тема)</label>
          <input type="text" name="title" value="<?= h($edit['title'] ?? '') ?>" placeholder="Лапка-табалапка" required></div>
        <div class="field"><label>Что это</label>
          <input type="text" name="subtitle" value="<?= h($edit['subtitle'] ?? '') ?>" placeholder="Мастер-класс"></div>
        <div class="field"><label>Когда</label>
          <input type="text" name="when_text" value="<?= h($edit['when_text'] ?? '') ?>" placeholder="Каждое воскресенье"></div>
        <div class="field"><label>Возраст</label>
          <input type="text" name="age" value="<?= h($edit['age'] ?? '') ?>" placeholder="3+"></div>
        <div class="field"><label>Тип</label>
          <select name="kind">
            <option value="weekly"  <?= ($edit['kind'] ?? '')==='weekly' ?'selected':'' ?>>Постоянное</option>
            <option value="special" <?= ($edit['kind'] ?? '')==='special'?'selected':'' ?>>Большое событие месяца</option>
          </select></div>
        <div class="field"><label>Порядок</label>
          <input type="number" name="sort" value="<?= (int)($edit['sort'] ?? count($events)) ?>"></div>
      </div>
      <div class="field"><label>Пояснение (необязательно)</label>
        <textarea name="note"><?= h($edit['note'] ?? '') ?></textarea></div>
      <label style="display:flex;gap:9px;align-items:center;text-transform:none;font-size:14px;color:var(--ink)">
        <input type="checkbox" name="active" style="width:auto" <?= (!$edit || $edit['active'])?'checked':'' ?>> Показывать на сайте</label>
      <div style="margin-top:20px" class="acts">
        <button class="btn"><?= $edit ? 'Сохранить' : 'Добавить' ?></button>
        <?php if ($edit): ?><a href="?tab=events" class="btn ghost">Отмена</a><?php endif; ?>
      </div>
    </form>
  </div>
  <div class="card">
    <table><thead><tr><th>Событие</th><th>Когда</th><th>Возраст</th><th></th></tr></thead><tbody>
    <?php foreach ($events as $e): ?>
      <tr class="<?= $e['active']?'':'off' ?>">
        <td><b><?= h($e['title']) ?></b>
          <span class="tag <?= $e['kind']==='special'?'s':'w' ?>"><?= $e['kind']==='special'?'событие месяца':'постоянное' ?></span>
          <?php if(!$e['active']):?><span class="tag off">скрыто</span><?php endif;?>
          <?php if($e['subtitle']):?><div class="hint"><?= h($e['subtitle']) ?></div><?php endif;?></td>
        <td><?= h($e['when_text']) ?></td><td><?= h($e['age']) ?></td>
        <td><div class="acts">
          <a href="?tab=events&edit=<?= (int)$e['id'] ?>" class="btn ghost sm">Изменить</a>
          <?php foreach ([['toggle','',$e['active']?'Скрыть':'Показать'],['move','up','↑'],['move','down','↓']] as [$d,$dir,$lbl]): ?>
            <form method="post"><input type="hidden" name="csrf" value="<?= csrf() ?>">
              <input type="hidden" name="do" value="<?= $d ?>"><input type="hidden" name="dir" value="<?= $dir ?>">
              <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
              <button class="btn ghost sm"><?= $lbl ?></button></form>
          <?php endforeach; ?>
          <form method="post" onsubmit="return confirm('Удалить «<?= h($e['title']) ?>»?')">
            <input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="do" value="delete">
            <input type="hidden" name="id" value="<?= (int)$e['id'] ?>"><button class="btn danger sm">Удалить</button></form>
        </div></td></tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>

<?php else:
    $leads = db()->query('SELECT * FROM leads ORDER BY id DESC LIMIT 300')->fetchAll(); ?>
  <div class="card">
    <h2>Заявки с сайта</h2>
    <div class="note">Дублируются в телеграм-группу. Галочка — значит сообщение туда ушло.</div>
    <table><thead><tr><th>Когда</th><th>Имя</th><th>Телефон</th><th>Откуда</th><th>ТГ</th></tr></thead><tbody>
    <?php foreach ($leads as $l): ?>
      <tr><td><?= h($l['created_at']) ?></td><td><b><?= h($l['name']) ?></b></td>
        <td><a href="tel:<?= h($l['phone']) ?>"><?= h($l['phone']) ?></a></td>
        <td><?= h($l['source']) ?><?php if($l['comment']):?><div class="hint"><?= h($l['comment']) ?></div><?php endif;?></td>
        <td><?= $l['delivered'] ? '✅' : '—' ?></td></tr>
    <?php endforeach; ?>
    <?php if(!$leads):?><tr><td colspan="5">Заявок пока нет.</td></tr><?php endif;?>
    </tbody></table>
  </div>
<?php endif; ?>
</main>
<script>
/* Подсвечиваем поля, которые изменили, но ещё не сохранили. */
document.querySelectorAll('#cform input[type=text], #cform textarea').forEach(el => {
  const start = el.value;
  el.addEventListener('input', () => el.closest('.field').classList.toggle('dirty', el.value !== start));
});
</script>
</body></html>

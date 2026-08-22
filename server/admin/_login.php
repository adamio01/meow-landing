<!doctype html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Вход — МЯУ</title>
<style>
@font-face{font-family:'Mont';src:url('fonts/Mont-Regular.woff2') format('woff2');font-weight:400;font-display:swap}
@font-face{font-family:'Mont';src:url('fonts/Mont-Bold.woff2') format('woff2');font-weight:700;font-display:swap}
@font-face{font-family:'Mont';src:url('fonts/Mont-Heavy.woff2') format('woff2');font-weight:800;font-display:swap}
@font-face{font-family:'Riffic';src:url('fonts/Riffic-Bold.ttf') format('truetype');font-weight:700;font-display:swap}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:grid;place-items:center;background:#F4EFE6;color:#0A0A0F;
font-family:'Mont',system-ui,sans-serif;padding:20px}
form{background:#fff;padding:40px;border-radius:24px;width:min(400px,100%);border:1.5px solid rgba(10,10,15,.1)}
h1{font-family:'Riffic','Mont',sans-serif;font-size:30px;margin:0 0 6px;text-transform:uppercase;letter-spacing:-.02em}
.sub{color:#6F6878;font-size:14px;margin-bottom:28px}
label{display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#6F6878;margin:18px 0 7px}
input{width:100%;padding:15px 18px;border-radius:12px;border:1.5px solid rgba(10,10,15,.12);background:#F4EFE6;font-size:16px;font-family:inherit;font-weight:600}
input:focus{outline:none;border-color:#FC1751}
button{width:100%;margin-top:26px;padding:17px;border:0;border-radius:12px;background:#0A0A0F;color:#fff;
font-weight:800;font-size:14px;letter-spacing:.05em;text-transform:uppercase;cursor:pointer;font-family:inherit}
.err{margin-top:18px;padding:12px 16px;background:#FFE8ED;color:#C8113F;border-radius:10px;font-size:14px;font-weight:600}
</style></head><body>
<form method="post">
  <h1>Панель МЯУ</h1>
  <div class="sub">Управление сайтом meow-mkh.ru</div>
  <input type="hidden" name="do" value="login">
  <label>Логин</label><input name="user" autocomplete="username" autofocus>
  <label>Пароль</label><input name="pass" type="password" autocomplete="current-password">
  <button>Войти</button>
  <?php if (!empty($GLOBALS['login_err'])) echo '<div class="err">' . h($GLOBALS['login_err']) . '</div>'; ?>
</form></body></html>

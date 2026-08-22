<?php
/* Пересобирает index.html из шаблона и правок в базе. */
declare(strict_types=1);
require_once __DIR__ . '/_boot.php';

const TPL_PATH    = '/var/www/u3618984/data/www/meow-mkh.ru/index.template.html';
const OUT_PATH    = '/var/www/u3618984/data/www/meow-mkh.ru/index.html';
const FIELDS_PATH = '/var/www/u3618984/data/config/cms-fields.json';

function cms_fields(): array {
    static $f = null;
    if ($f === null) $f = json_decode((string)file_get_contents(FIELDS_PATH), true) ?: [];
    return $f;
}

/** Правки: ключ => значение. */
function cms_overrides(): array {
    $out = [];
    foreach (db()->query('SELECT k, v FROM content') as $r) $out[$r['k']] = $r['v'];
    return $out;
}

/**
 * Текст из редактора -> HTML.
 *   перенос строки  -> <br>
 *   *слово*         -> жирный
 *   ~слово~         -> выделение цветом
 *   [текст](ссылка) -> ссылка
 */
function cms_to_html(string $t): string {
    $t = str_replace(["\r\n", "\r"], "\n", $t);
    $t = htmlspecialchars($t, ENT_NOQUOTES, 'UTF-8');

    $t = preg_replace_callback('~\[([^\]\n]+)\]\(([^)\s]+)\)~u', static function (array $m): string {
        $url = $m[2];
        /* Пускаем только безопасные схемы. */
        if (!preg_match('~^(https?://|tel:|mailto:|/|#)~i', $url)) return $m[1];
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $m[1] . '</a>';
    }, $t);

    $t = preg_replace('~\*([^*\n]+)\*~u', '<b>$1</b>', $t);
    $t = preg_replace('#\x7E([^\x7E\n]+)\x7E#u', '<em>$1</em>', $t);
    return nl2br($t, false);
}

/** HTML из шаблона -> текст для редактора (обратное преобразование). */
function cms_to_text(string $h): string {
    $h = preg_replace('~<br\s*/?>~i', "\n", $h);
    $h = preg_replace('~<a[^>]*href="([^"]*)"[^>]*>(.*?)</a>~is', '[$2]($1)', $h);
    $h = preg_replace('~<(?:b|strong)[^>]*>(.*?)</(?:b|strong)>~is', '*$1*', $h);
    $h = preg_replace('~<em[^>]*>(.*?)</em>~is', '~$1~', $h);
    $h = strip_tags($h);
    return trim(html_entity_decode($h, ENT_QUOTES, 'UTF-8'));
}

function cms_render(): array {
    $tpl = (string)file_get_contents(TPL_PATH);
    if ($tpl === '') return ['ok' => false, 'error' => 'шаблон не найден'];

    $ov      = cms_overrides();
    $applied = 0;
    $missed  = [];

    foreach (cms_fields() as $f) {
        $key = $f['key'];
        if (!isset($ov[$key]) || $ov[$key] === '') continue;

        $anchor = 'data-cms="' . $key . '"';
        $at = strpos($tpl, $anchor);
        if ($at === false) { $missed[] = $key; continue; }

        if ($f['kind'] === 'image') {
            /* Меняем src внутри самого тега. */
            $tagStart = strrpos(substr($tpl, 0, $at), '<');
            $tagEnd   = strpos($tpl, '>', $at);
            $tag      = substr($tpl, $tagStart, $tagEnd - $tagStart + 1);
            $newTag   = preg_replace('~src="[^"]*"~', 'src="' . htmlspecialchars($ov[$key], ENT_QUOTES, 'UTF-8') . '"', $tag, 1);
            $tpl      = substr($tpl, 0, $tagStart) . $newTag . substr($tpl, $tagEnd + 1);
            $applied++;
            continue;
        }

        /* Текст: содержимое лежит ровно за закрывающей скобкой тега. */
        $gt   = strpos($tpl, '>', $at);
        $orig = $f['orig'];
        if ($gt === false || substr($tpl, $gt + 1, strlen($orig)) !== $orig) { $missed[] = $key; continue; }

        $new = ($f['kind'] === 'line')
             ? htmlspecialchars($ov[$key], ENT_NOQUOTES, 'UTF-8')
             : cms_to_html($ov[$key]);
        $tpl = substr($tpl, 0, $gt + 1) . $new . substr($tpl, $gt + 1 + strlen($orig));
        $applied++;
    }

    /* Служебные метки в публичную страницу не отдаём. */
    $tpl = preg_replace('~\s*data-cms="[a-z0-9]+"~', '', $tpl);

    if (file_put_contents(OUT_PATH, $tpl) === false) return ['ok' => false, 'error' => 'не смог записать index.html'];
    return ['ok' => true, 'applied' => $applied, 'missed' => $missed];
}

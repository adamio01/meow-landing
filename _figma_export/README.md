# MEOW Landing — для импорта в Figma

В Figma НЕТ способа сгенерировать `.fig` файл вне самой программы — формат закрытый.
Используйте один из этих плагинов в Figma, который импортирует HTML в виде слоёв:

## Вариант 1 — html.to.design (бесплатный, лучший)
1. Откройте Figma → правая панель → Plugins → найти "html.to.design"
2. Запустите плагин в любом файле
3. Выберите "Import from URL" или "Import from file"
   - Если "Import from URL": загрузите эту папку на любой статичный хостинг (Netlify Drop, GitHub Pages) и вставьте URL
   - Если "Import from file": перетащите `index.html` в окно плагина
4. Плагин создаст слои в Figma. Все стили, фотографии и шрифты будут перенесены

## Вариант 2 — Anima (платный, но мощный)
- Figma плагин "Anima" → "Import design from URL or HTML"

## Вариант 3 — Builder.io
- builder.io/cli или плагин builder.io в Figma
- Поддерживает прямой импорт HTML с сохранением структуры

## Структура архива
- `index.html`        — основная вёрстка
- `fonts/`            — Riffic Bold + все веса Mont (бренд)
- `photos/`           — реальные фото MEOW + котик-маскот + Pexels

## Где открыть локально
- Открыть `index.html` в браузере двойным кликом, ИЛИ
- В терминале: `python3 -m http.server 8000` в этой папке → `http://localhost:8000/`

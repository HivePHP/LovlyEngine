# HivePHP Realtime (Socket.IO) — настройка уведомлений

Уведомления в HivePHP работают в два слоя:

1. **Данные** — всегда из PHP-БД. Колокольчик в шапке, список и счётчики
   непрочитанных рендератся на сервере и обновляются через `GET /api/notifications`.
   Этот слой работает **всегда**, даже если Node-сервер не запущен.
2. **Реалтайм** — маленький standalone **Node.js + Socket.IO** сервер
   (`realtime/server.js`), который доставляет «живые» события (заявка в друзья,
   принятие дружбы) открытым вкладкам без перезагрузки страницы.

> Слой 2 — *опциональный*. Без него система деградирует в «подтягивание при
> открытии колокольчика»: бейджи появятся при следующем открытии страницы или
> дропдауна, но обновления в реальном времени не будет.

---

## 1. Предварительные требования

Нужен **Node.js (>= 16)** и **npm**. Проверить:

```bash
node --version
npm --version
```

На этой машине Node **не установлен** — убедитесь, что он появился в `PATH`,
прежде чем запускать сервер. Установить можно с https://nodejs.org
(LTS-версию) или через пакетный менеджер вашей ОС.

---

## 2. Настройка PHP-стороны

Файлы конфигурации уже созданы:

- `config/realtime.php` — весь конфиг реалтайма;
- переменные `REALTIME_*` / `NOTIFICATION_*` — в `.env` (и `.env.example`).

Проверьте/задайте в `.env`:

```dotenv
REALTIME_ENABLED=true
REALTIME_HOST=127.0.0.1
REALTIME_PORT=3001
REALTIME_PATH=/socket.io
# Если PHP и браузер на разных хостах — задайте явный публичный URL:
# REALTIME_PUBLIC_URL=http://127.0.0.1:3001

REALTIME_SECRET=обязательно-замените-длинной-случайной-строкой
REALTIME_TOKEN_TTL=300
REALTIME_HTTP_TIMEOUT=2000

NOTIFICATION_MAX_ITEMS=30
NOTIFICATION_TTL_DAYS=90
```

### ⚠️ ОБЯЗАТЕЛЬНО: секрет должен совпадать

Значение `REALTIME_SECRET` должно быть **одинаковым**:

- в `.env` PHP-приложения, **и**
- в переменной окружения Node-сервера.

Если секреты различаются, сокеты не заведутся и `/emit` будет отклоняться
(постоянно `bad_signature`). Не оставляйте дефолтное значение
`change-me-put-a-long-random-string-here` — Node при старте выдаст предупреждение.

Секрет лучше сгенерировать случайно, например:

```bash
node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"
# или
openssl rand -hex 32
```

---

## 3. Установка Node-сервера

Перейдите в каталог `realtime/` и выполните:

```bash
cd realtime
npm install                 # ставит express + socket.io (и клиентский пакет)
npm run install:client      # копирует socket.io.js в public/assets/socketio/
```

`install:client` копирует UMD-бандл клиента
`node_modules/socket.io/client-dist/socket.io.js` в
`public/assets/socketio/socket.io.js`. Это нужно сделать **один раз** — браузер
скачивает `socket.io.js` с того же origin, что и приложение (без внешних CDN).

Если этот шаг пропустить, страница загрузится, но колокольчик останется
«пассивным» (без живого socket-подключения); HTTP-слой уведомлений продолжит
работать.

### Переменные окружения Node

Задайте секрет и порт перед запуском. Можно через переменные окружения:

```bash
REALTIME_SECRET=<тот-же-секрет-что-в-.env> REALTIME_PORT=3001 npm start
```

Доступные переменные (соответствуют `config/realtime.php`):

| Переменная       | По умолчанию                           | Описание                                |
|------------------|----------------------------------------|-----------------------------------------|
| `REALTIME_PORT`  | `3001`                                 | Порт HTTP/Socket.IO                     |
| `REALTIME_PATH`  | `/socket.io`                           | Путь Socket.IO (совпадает с PHP)        |
| `REALTIME_SECRET`| `change-me-put-a-long-random-string-here` | Общий HMAC-секрет (должен совпадать с PHP) |
| `REALTIME_TOKEN_TTL` | `300`                              | TTL сокет-токена (сек)                  |
| `REALTIME_CORS`  | `*`                                    | Разрешённые origin для сокетов (список через запятую, напр. `http://hivephp.local`) |

### Запуск

```bash
npm start
```

Ожидаемый вывод:

```
[realtime] listening on http://0.0.0.0:3001 path=/socket.io
```

Проверка, что сервер поднялся:

```bash
curl http://127.0.0.1:3001/health
# -> {"ok":true,"uptime":...}
```

---

## 4. Как это работает

### Поток события (пример — заявка в друзья)

```
User A отправляет заявку User B
   │
   ▼  PHP (FriendsController::add)
NotificationService::friendRequest(B, A)
   │  ├── NotificationRepository::create(...)   ← запись в БД (всегда)
   │  └── RealtimeService::push('friend.request', [B], {...})
   │         POST http://127.0.0.1:3001/emit  (HMAC-подпись)
   ▼
Node server.js — проверяет подпись, ts, лимит
   │  io.to('user:B').emit('realtime:event', {...})
   ▼
Браузер User B (shell.js → NotificationBell) получил событие
   │  GET /api/notifications  ← перечитал авторитетные данные с сервера
   ▼
Обновление колокольчика + бейджа «Друзья (N)»
```

`NotificationBell` на клиенте **никогда не верит** полю `payload` из вебсокета
для разметки: при любом событии он запрашивает свежий список через
`GET /api/notifications` и рендерит из ответа PHP. Так исключается подделка
содержимого через канал реального времени. Весь DOM строится через
`textContent`/DOM API — XSS-безопасно.

### Безопасность

- **Сокет-токен** — `base64url(JSON {uid, exp}) . base64url(HMAC-SHA256(payload, secret))`.
  Выдаётся PHP на короткий срок (`token_ttl`, дефолт 300 c). Node проверяет
  подпись и срок действия; подделать без секрета нельзя.
- **POST /emit** — подписан: `sig = HMAC-SHA256(secret, "<ts>\n<event>\n<userIds>\n<payload>")`.
  Node сверяет подпись в constant-time (`timingSafeEqual`) и отклоняет запросы с
  `|ts - now| > 30 c` (анти-реплей). Секрет и данные БД на Node **не передаются** —
  Node не имеет доступа к БД, каждое сообщение санкционировано PHP.
- **Rate-limit** — по IP для подключений и `/emit` (в памяти).
- **CSRF** — PHP-эндпоинты `POST /api/notifications/*` за middleware `auth` и
  принимают `X-CSRF-Token` (JS шлёт автоматически).

---

## 5. Что происходит, когда реалтайм выключен/недоступен

- `REALTIME_ENABLED=false` → `clientConfig()` возвращает `null`, браузер не
  пытается коннектиться; колокольчик работает по HTTP (рендер на странице +
  обновление при открытии дропдауна).
- Node поднят, но PHP `REALTIME_SECRET` не совпадает → `bad_signature` в
  `storage/logs/realtime.log`; уведомления всё равно пишутся в БД.
- Сетевая ошибка push → пишется в `storage/logs/realtime.log`, запрос не падает.

---

## 6. Поиск неисправностей

| Симптом | Причина / решение |
|---------|-------------------|
| `bad_signature` в логе | `REALTIME_SECRET` в `.env` и в env Node не совпадают. |
| Консоль браузера: `socket.io.js: 404` | Не выполнен `npm run install:client` (или удалён `public/assets/socketio/socket.io.js`). |
| `Error: unauthorized` при коннекте сокета | Просрочен/подменён сокет-токен. Обновите страницу (PHP выдаст новый). |
| Бейджи не «живые», но при открытии колокольчика есть | Сокет не подключён (нет Node, CORS, порт). HTTP-слой работает — это нормальная деградация. |
| `CORS` блок в браузере | Задайте `REALTIME_CORS` с вашим origin (напр. `http://hivephp.local`). |
| Порт занят | Смените `REALTIME_PORT` в `.env` (PHP) И в env Node — должны совпадать. |

---

## 7. Структура

```
realtime/
├── package.json   # зависимости (express, socket.io) и скрипты
├── server.js      # Socket.IO сервер + POST /emit + /health
└── install.js     # копирует socket.io.js в public/assets/socketio/

config/realtime.php            # конфиг PHP-стороны
app/Services/RealtimeService.php     # HMAC-токены + push /emit
app/Services/NotificationService.php # высокоуровневые уведомления (friend.*)
app/Repositories/NotificationRepository.php # CRUD/счётчики в БД
app/Http/Controllers/NotificationsController.php # /api/notifications*
resources/assets/js/core/NotificationBell.js     # колокольчик + socket-клиент
```

---

## 8. Быстрый старт (шпаргалка)

```bash
# 1. Убедиться, что Node установлен
node --version

# 2. Один раз: установка клиента для PHP
cd realtime
npm install
npm run install:client

# 3. Задать ОДИНАКОВЫЙ секрет в .env (PHP) и в env Node

# 4. Запустить Node
REALTIME_SECRET=<секрет> npm start

# 5. Запустить PHP-приложение (обычно через веб-сервер). Готово.
```

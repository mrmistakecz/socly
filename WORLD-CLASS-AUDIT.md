# SOCLY v3 — Co ještě chybí

> Pouze nedokončené položky. Vše hotové bylo odstraněno.
> Poslední aktualizace: 5. 5. 2026

---

## ✅ Implementováno (5. 5. 2026)

### Bezpečnost
- ✅ CSP — odstraněn `unsafe-eval` ze `SecurityHeaders.php`
- ✅ Brute-force login — `RateLimiter::for('login')` s klíčem `email|ip` v `AppServiceProvider`
- ✅ Admin deleteUser — mazání souborů (avatar, cover, posty) + `forceDelete()`

### Backend
- ✅ Změna hesla — `PUT /settings/password` + formulář v `Settings.vue`
- ✅ Změna emailu — `PUT /settings/email` + migrace `pending_email`
- ✅ GDPR export — `GET /settings/export` (ZIP s profil, posty, zprávy)
- ✅ Tipy — `POST /users/{user}/tip` s 80/20 split + notifikace
- ✅ Story views — `story_views` tabulka + `POST /stories/{story}/view`
- ✅ Cron: `subscriptions:expire` (daily), `stories:cleanup` (hourly), `posts:sync-counts` (hourly)
- ✅ Queue async — `ShouldBroadcast` místo `ShouldBroadcastNow` (kromě NewMessage)

### Logické chyby
- ✅ WallController N+1 konverzace — přepsáno na single SQL s JOIN + subquery
- ✅ ProfileController likes — `(int) $p->likes_count` místo `K` formátu
- ✅ PostInteraction — per-post channel `post.{id}` místo public `posts`
- ✅ Whisper typing — opraveno na server-side broadcasting
- ✅ Admin deleteUser — konzistentní `forceDelete()`

### Frontend
- ✅ Infinite scroll (IntersectionObserver) — již existoval
- ✅ Pull-to-refresh — `usePullToRefresh` composable
- ✅ Image lazy loading — `loading="lazy"` na všech `<img>`
- ✅ Video přehrávač v modalu — `<video>` s `controls playsinline`
- ✅ Swipe gesta pro tab switching — touchstart/touchend v `AuthenticatedLayout`
- ✅ a11y — `aria-label`, `role="dialog"`, `aria-modal`, `aria-current`
- ✅ EmptyState + ConfirmModal komponenty
- ✅ `useAction` composable (loading stavy)

### Databáze
- ✅ Performance indexy — migrace pro messages, posts, likes, follows, subscriptions

### Nové migrace (ke spuštění na VPS)
- `add_pending_email_to_users_table`
- `create_story_views_table`
- `add_performance_indexes`

---

## Zbývá (vyžaduje API klíče / VPS přístup)

### Admin 2FA (TOTP)
- Balíček: `composer require pragmarx/google2fa-laravel`
- Přidat `google2fa_secret` sloupec, middleware `Verify2FA`, Vue stránku.

### Live streaming (LiveKit)
- `composer require agence104/livekit-server-sdk`
- Vyžaduje: `LIVEKIT_API_KEY`, `LIVEKIT_API_SECRET`, `LIVEKIT_URL`

### AI moderace obsahu
- Vyžaduje: `OPENAI_API_KEY`

### i18n
- `npm install vue-i18n`, `locales/cs.json`, `createI18n()` v `app.js`

### CDN — Cloudflare R2
- Přesměrovat DNS, média na R2 s `cdn.socly.eu`

---

## Infrastruktura (VPS — po deployi)

### Redis aktivace
```
apt install redis-server php-redis
```
V `.env`: `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`

### Sentry instalace
```
composer require sentry/sentry-laravel
```
V `.env`: `SENTRY_LARAVEL_DSN=xxx`

### Zálohy databáze (cron)
```
0 3 * * * pg_dump socly > /backups/socly_$(date +\%F).sql && find /backups -mtime +30 -delete
```

### Firewall + SSL
```
ufw default deny incoming && ufw allow 22,80,443,8080/tcp && ufw enable
certbot renew --dry-run
```

---

## API klíče k obstarání

| Služba | K čemu | Env proměnné |
|--------|--------|--------------|
| **Resend** | Transakční emaily | `RESEND_KEY` |
| **Cloudflare R2** | Media storage | `R2_ACCESS_KEY_ID`, `R2_SECRET_ACCESS_KEY`, `R2_BUCKET`, `R2_ENDPOINT` |
| **Sentry** | Error tracking | `SENTRY_LARAVEL_DSN` |
| **LiveKit** | Live streaming | `LIVEKIT_API_KEY`, `LIVEKIT_API_SECRET`, `LIVEKIT_URL` |
| **OpenAI** | AI moderace | `OPENAI_API_KEY` |

---

## Testy (post-deploy)

### PHPUnit feature testy
- Registrace/login/logout, CRUD postů, follow, subscribe, like, zprávy, admin.

### Playwright E2E testy
- Registrační flow, vytvoření postu, profil → follow → zpráva.

---

*Tento soubor obsahuje pouze to, co JEŠTĚ NEBYLO implementováno.*

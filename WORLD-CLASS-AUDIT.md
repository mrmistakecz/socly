# SOCLY v3 — Co ještě chybí

> Pouze nedokončené položky. Vše hotové bylo odstraněno.
> Poslední aktualizace: 4. 5. 2026

---

## Bezpečnost

### CSP — odstranit unsafe-inline / unsafe-eval
- **Soubor:** `app/Http/Middleware/SecurityHeaders.php`
- Nahradit `unsafe-inline` nonce-based CSP. Minimálně odstranit `unsafe-eval`.
- Použít `spatie/laravel-csp` nebo ručně vygenerovat nonce a předat do Vite.

### Admin 2FA (TOTP)
- Balíček: `composer require pragmarx/google2fa-laravel`
- Přidat `google2fa_secret` sloupec (migrace), middleware `Verify2FA`, Vue stránku `Pages/Auth/TwoFactor.vue`.

### Brute-force ochrana loginu (IP+email klíč)
- `RateLimiter::for('login', ...)` v `AppServiceProvider` s klíčem `$email . '|' . $ip`.

### Admin smazání uživatele nemaže soubory
- **Soubor:** `AdminController.php`
- Před `$user->delete()` smazat avatar, cover, obrázky postů ze storage.

---

## Backend

### Změna hesla v nastavení
- `PUT /settings/password` — validovat `current_password`, hash nového hesla.
- Přidat formulář do `Settings.vue`.

### Změna emailu
- `PUT /settings/email` — uložit do `pending_email`, odeslat verifikační link, po potvrzení přepsat.
- Migrace: přidat `pending_email` sloupec.

### Export dat (GDPR)
- `GET /settings/export` — ZIP se všemi daty uživatele (profil, posty, zprávy).
- `throttle:1,60`. Frontend: tlačítko "Stáhnout má data" v Settings.vue.

### Expirování předplatného (cron)
- Artisan command `subscriptions:expire` — `Subscription::where('expires_at','<',now())->delete()`.
- Registrovat v `routes/console.php`: `Schedule::command('subscriptions:expire')->daily()`.

### Stories — cron cleanup
- Artisan command `stories:cleanup` — mazat vypršelé stories + soubory.
- `Schedule::command('stories:cleanup')->hourly()`.

### Stories — views tracking
- `story_views` tabulka (story_id, user_id, viewed_at).
- `StoryController::view()` endpoint pro zaznamenání zhlédnutí.

### Live streaming (LiveKit)
- `composer require agence104/livekit-server-sdk`
- `LiveStreamController`: `start()` + `join()` pro token generování.
- Frontend: `npm install livekit-client`, nahradit mockup v `LiveScreen.vue`.

### Tipy za kredity (dysko)
- Endpoint `POST /users/{user}/tip` s body `{ amount: 100 }`.
- 80/20 split, `NewNotification` s typem `tip`.

### Synchronizace likes_count
- Artisan command `posts:sync-counts` — raw SQL UPDATE synchronizace.
- `Schedule::command('posts:sync-counts')->hourly()`.

### AI moderace obsahu
- `OPENAI_API_KEY` — moderace uploadů, auto-popisky.

---

## Frontend

### Infinite scroll na feedu
- `IntersectionObserver` na sentinel div v `FeedScreen.vue`.
- Volat `/api/posts?last_id=X`, přidávat posty do pole.

### Pull to refresh
- Touch event handler (`touchstart`/`touchmove`/`touchend`) na feed kontejneru.
- Pokud `scrollTop === 0` a tah > 60px → spinner + `router.reload()`.

### Image lazy loading
- Přidat `loading="lazy"` na všechny `<img>` tagy mimo viewport.

### Video přehrávač v post modalu
- **Soubor:** `Profile.vue` post modal
- `<video v-if="post.isVideo" :src="post.image" controls playsinline />` / `<img v-else />`.

### Swipe gesta pro přepínání tabů
- `touchstart`/`touchend` na content kontejneru v `Wall.vue`.
- CSS `transform: translateX()` transition.

### Přístupnost (a11y)
- `aria-label` na ikonková tlačítka, `role="dialog"` na modaly, `useFocusTrap()` pro focus management.

### i18n
- `npm install vue-i18n`, vytvořit `locales/cs.json`, `createI18n()` v `app.js`.

### EmptyState komponenta
- `EmptyState.vue` s propsy `icon`, `title`, `description`, `actionLabel`.
- Použít v Messages, Discover, Profile saved tab.

### Loading stavy na tlačítkách
- Follow, Like, Subscribe — přidat disabled + spinner během API volání.
- Composable `useAction(fn)`.

### Potvrzovací dialogy
- `ConfirmModal.vue` — nahradit nativní `confirm()` pro destruktivní akce.

### Animace a mikro-interakce
- Page transitions: `<Transition name="fade">` na tab obsah.
- Staggered list: `:style="{transitionDelay: i*50+'ms'}"`.
- Skeleton shimmer: CSS gradient animation.

---

## Logické chyby

### Konverzace query — N+1
- **Soubor:** `WallController.php` řádky ~95-130
- Přepsat na jeden SQL dotaz s `GROUP BY` + subquery pro unread count.

### ProfileController — likes formátování
- **Soubor:** `ProfileController.php`
- Změnit `'likes' => round($p->likes_count / 1000, 1)` na `'likes' => (int) $p->likes_count`.

### Whisper typing na nesprávném kanálu
- **Soubory:** `useRealtime.js`, `MessagesScreen.vue`
- Přidat presence channel `chat.{pairId}` nebo HTTP-based typing s debounce + Cache.

### PostInteraction broadcast na public channel
- **Soubor:** `PostInteraction.php`
- Změnit channel na `new Channel('post.'.$this->postId)`.
- `FeedCard.vue`: `Echo.channel('post.'+post.id).listen(...)` při mount, `Echo.leave()` při unmount.

### Admin deleteUser — SoftDeletes nekonzistence
- **Soubor:** `AdminController.php`
- Rozhodnout: buď `forceDelete()` všude, nebo soft-delete + cleanup command po 30 dnech.

---

## Databáze a výkon

### Ověřit DB indexy
- `messages` — index na `(sender_id, receiver_id, created_at)`
- `posts` — index na `(user_id, created_at)`
- `likes` — unique index na `(user_id, post_id)`
- `follows` — unique index na `(follower_id, following_id)`
- `subscriptions` — index na `(subscriber_id, creator_id, expires_at)`
- Ověřit: `DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'likes'")`.

### Queue worker — přepnout na async
- Změnit `ShouldBroadcastNow` na `ShouldBroadcast` v `NewNotification.php` + `PostInteraction.php`.
- `NewMessage` nechat `Now` pro okamžitost.

### CDN — Cloudflare
- Přesměrovat DNS na Cloudflare (nameservery), aktivovat Proxy na A záznamu.
- Média na R2 s custom doménou `cdn.socly.eu`.

---

## Infrastruktura (VPS — po deployi)

### Redis aktivace
```
apt install redis-server php-redis
```
V `.env`:
```
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Cloudflare R2 klíče
```env
FILESYSTEM_DISK=r2
R2_ACCESS_KEY_ID=xxx
R2_SECRET_ACCESS_KEY=xxx
R2_BUCKET=socly-media
R2_ENDPOINT=https://xxx.r2.cloudflarestorage.com
```
Pak: `rclone sync /var/www/socly-v3/storage/app/public r2:socly-media`

### Sentry instalace
```
composer require sentry/sentry-laravel
```
V `.env`: `SENTRY_LARAVEL_DSN=xxx`

### Zálohy databáze (cron)
```
mkdir -p /backups
crontab -e → 0 3 * * * pg_dump socly > /backups/socly_$(date +\%F).sql && find /backups -mtime +30 -delete
```

### SSL ověření
```
certbot renew --dry-run
```

### Firewall
```
ufw default deny incoming && ufw allow 22,80,443,8080/tcp && ufw enable
```

### Produkční .env hodnoty
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://socly.eu
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=socly.eu
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

### GitHub Actions — přidat secrets
- `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY` do GitHub Secrets pro `deploy.yml`.

---

## Testy (post-deploy)

### PHPUnit feature testy
- Registrace/login/logout, CRUD postů, follow, subscribe, like, zprávy, admin.
- `php artisan make:test RegisterTest`

### Playwright E2E testy
- Registrační flow, vytvoření postu, profil → follow → zpráva.
- `npm install -D @playwright/test`

---

## API klíče k obstarání

| Služba | K čemu | Env proměnné |
|--------|--------|--------------|
| **Resend** | Transakční emaily (verifikace, reset hesla) | `RESEND_KEY` |
| **Cloudflare R2** | Offshore media storage | `R2_ACCESS_KEY_ID`, `R2_SECRET_ACCESS_KEY`, `R2_BUCKET`, `R2_ENDPOINT` |
| **Sentry** | Error tracking | `SENTRY_LARAVEL_DSN` |
| **LiveKit** | Live streaming | `LIVEKIT_API_KEY`, `LIVEKIT_API_SECRET`, `LIVEKIT_URL` |
| **OpenAI** | AI moderace | `OPENAI_API_KEY` |
| **UptimeRobot** | Monitoring dostupnosti | (žádné env, web dashboard) |

---

## Migrace ke spuštění na VPS

```bash
php artisan migrate
```

Nové migrace (spustit poprvé):
- `add_balance_to_users_table`
- `create_transactions_table`
- `create_reports_table`
- `create_blocks_table`
- `create_stories_table`
- `add_thumbnail_to_posts_table`
- `add_onboarding_to_users_table`

---

*Tento soubor obsahuje pouze to, co JEŠTĚ NEBYLO implementováno.*

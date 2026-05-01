# SOCLY v3 — Premium Content Platform

> Where exclusivity meets desire. Premium content from elite creators.

## Tech Stack

- **Backend:** Laravel 13, PHP 8.4, SQLite
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS, Lucide Icons
- **Real-time:** Laravel Reverb (WebSockets)
- **Storage:** Cloudflare R2 (S3-compatible)
- **PWA:** Service Worker, Web App Manifest

## Quick Start

```bash
# Install dependencies
composer install
npm install

# Setup database
php artisan migrate:fresh --seed

# Create storage symlink
php artisan storage:link

# Run development server
composer dev
```

## Demo Login

- **Email:** `demo@socly.app`
- **Password:** `password`

## Features

- **Feed** — Hlavní zeď s příspěvky od tvůrců
- **Discover** — Objevte nové tvůrce, live streamy, trending obsah
- **Messages** — DM konverzace s tvůrci (VIP priorita)
- **Profile** — Profily tvůrců s grid příspěvků
- **Live Stream** — Živé vysílání s chatem a Dysko (tipy)
- **Create Post** — Tvorba příspěvků s exkluzivním obsahem
- **Follow/Like/Bookmark** — Plné sociální interakce
- **Settings** — Nastavení profilu, notifikací, soukromí
- **Auth** — Registrace, přihlášení, odhlášení
- **PWA** — Instalovatelná jako nativní aplikace
- **Responsive** — Mobile-first, tablet, desktop

## Project Structure

```
app/
├── Http/Controllers/    # WallController, ProfileController, PostController, ...
├── Models/              # User, Post, Like, Comment, Follow, Subscription, ...
database/
├── migrations/          # Users, Posts, Likes, Comments, Follows, Subscriptions, ...
├── seeders/             # Demo data seeder
resources/
├── js/
│   ├── Pages/           # Wall, Profile, Settings, Auth/Login, Auth/Register
│   ├── Layouts/         # AuthenticatedLayout (mobile + desktop)
│   └── Components/Socly/
│       ├── Screens/     # FeedScreen, DiscoverScreen, MessagesScreen, LiveScreen, ...
│       ├── Header.vue   # Top navigation bar
│       ├── BottomNav.vue# Bottom tab bar (mobile)
│       └── CreatePostModal.vue
├── css/app.css          # SOCLY premium dark theme
└── views/app.blade.php  # Inertia root template + PWA meta
```

## License

Proprietary — © 2026 SOCLY. All rights reserved.

# Laravel Discord Programming News Bot

Bot Laravel untuk memantau security advisories dan feature updates dari berbagai framework/library programming, dengan notifikasi otomatis ke Discord.

## Features

- 🔒 **Security Monitoring**: Memantau security advisories dari GitHub Advisory Database
- 🚀 **Feature Updates**: Tracking release terbaru dari framework populer
- 💬 **Discord Commands**: Slash commands untuk trigger manual updates
- 📊 **Status Dashboard**: Web dashboard untuk melihat status bot
- 🤖 **Automated Notifications**: Notifikasi otomatis via Discord webhook

## Discord Slash Commands

Bot mendukung slash commands berikut:

- `/security-update` - Trigger manual scan untuk security advisories
- `/feature-update` - Trigger manual scan untuk feature updates
- `/status` - Lihat status bot dan informasi update terakhir

## Requirements

- PHP 8.2 atau lebih tinggi
- Laravel 11
- MySQL/SQLite database
- Discord Bot Account

## Installation

### 1. Clone Repository

```bash
git clone <repository-url>
cd laravel-discord-programming-news
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

Copy `.env.example` ke `.env`:

```bash
cp .env.example .env
```

Update konfigurasi database dan Discord:

```env
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_DATABASE=your-database-name
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Discord Configuration
DISCORD_APP_ID=your-app-id
DISCORD_PUBLIC_KEY=your-public-key
DISCORD_BOT_TOKEN=your-bot-token
DISCORD_SECURITY_WEBHOOK_URL=your-webhook-url
```

### 4. Database Setup

```bash
php artisan key:generate
php artisan migrate
```

### 5. Setup Discord Bot

#### A. Create Discord Application

1. Kunjungi [Discord Developer Portal](https://discord.com/developers/applications)
2. Click "New Application" dan beri nama bot Anda
3. Navigate ke tab "Bot" dan click "Add Bot"
4. Copy **Bot Token** dan simpan ke `.env` sebagai `DISCORD_BOT_TOKEN`

#### B. Get Application Credentials

1. Navigate ke tab "General Information"
2. Copy **Application ID** → `DISCORD_APP_ID`
3. Copy **Public Key** → `DISCORD_PUBLIC_KEY`

#### C. Create Webhook

1. Buka Discord server Anda
2. Klik kanan pada channel yang ingin menerima notifikasi → "Edit Channel"
3. Navigate ke "Integrations" → "Webhooks" → "New Webhook"
4. Copy webhook URL → `DISCORD_SECURITY_WEBHOOK_URL`

#### D. Enable Interactions

1. Di Discord Developer Portal, navigate ke tab "General Information"
2. Scroll ke "Interactions Endpoint URL"
3. Masukkan URL: `https://your-domain.com/api/discord/interactions`
4. Click "Save Changes"

> **Note**: Discord akan melakukan verification request. Pastikan aplikasi Anda sudah deploy dan route `/api/discord/interactions` dapat diakses.

#### E. Install Bot to Server

1. Navigate ke tab "OAuth2" → "URL Generator"
2. Select scopes: `applications.commands` dan `bot`
3. Select permissions: `Send Messages`, `Embed Links`
4. Copy generated URL dan buka di browser
5. Pilih server dan authorize bot

### 6. Register Discord Commands

Setelah bot terinstall, register slash commands:

```bash
php artisan discord:register-commands
```

Untuk testing lebih cepat, gunakan guild-specific registration:

```bash
php artisan discord:register-commands --guild=YOUR_GUILD_ID
```

## Configuration

### Security Frameworks

Edit `config/security.php` untuk menambah/mengurangi framework yang dimonitor:

```php
'frameworks' => [
    'laravel/framework',
    'next',
    'react',
    'vue',
    // tambahkan framework lainnya
],
```

### Feature Repositories

Edit `config/features.php` untuk tracking releases:

```php
'repos' => [
    'Laravel' => 'laravel/framework',
    'Node.js' => 'nodejs/node',
    // tambahkan repository lainnya
],
```

## Cron Job Setup

Bot memerlukan Laravel scheduler untuk automated scans. Tambahkan ke crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Untuk shared hosting (cPanel), gunakan path absolut ke PHP:

```bash
* * * * * /usr/local/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1
```

## Usage

### Automated Scanning

Schedule sudah dikonfigurasi untuk:
- Security scan: Setiap 6 jam
- Feature scan: Setiap 12 jam

### Manual Scanning via Discord

Gunakan slash commands di Discord:
- `/security-update` - Scan security advisories sekarang
- `/feature-update` - Scan feature updates sekarang
- `/status` - Lihat status dan statistik bot

### Manual Scanning via Artisan

```bash
# Security scan
php artisan security:scan

# Feature scan  
php artisan feature:scan
```

## Development

Run development server:

```bash
composer run dev
```

atau jalankan services secara terpisah:

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Queue worker
php artisan queue:listen

# Terminal 3: Logs
php artisan pail

# Terminal 4: Vite
npm run dev
```

## Troubleshooting

### Discord Interactions Not Working

1. Pastikan Interactions Endpoint URL sudah diset dengan benar di Discord Developer Portal
2. Verify bahwa route `/api/discord/interactions` dapat diakses publik
3. Check logs di `storage/logs/laravel.log` untuk error signature verification
4. Pastikan `DISCORD_PUBLIC_KEY` di `.env` sesuai dengan Public Key di Discord Developer Portal

### Commands Not Appearing

1. Jalankan ulang `php artisan discord:register-commands`
2. Untuk testing, gunakan guild-specific: `php artisan discord:register-commands --guild=YOUR_GUILD_ID`
3. Global commands bisa memakan waktu hingga 1 jam untuk propagate

### Webhook Not Sending

1. Verify `DISCORD_SECURITY_WEBHOOK_URL` valid
2. Check webhook masih aktif di Discord channel settings
3. Review logs untuk HTTP errors

## License

MIT License

## Contributing

Pull requests are welcome! For major changes, please open an issue first.

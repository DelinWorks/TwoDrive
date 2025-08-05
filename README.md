# 📂 TwoDrive – Laravel-Based Personal Cloud Storage

TwoDrive is a self-hosted OneDrive-like file manager built with Laravel. It includes a virtual filesystem with folder hierarchy, file uploads, previews, folder sharing, etc. Designed to be simple, fast, and clean — ideal for personal use or internal deployment.

---

## 🚀 Features

- 🗂 **Hierarchical Virtual File System** – Files and folders managed in a tree structure, not dumped flat.
- 📁 **Upload / Download / Preview**
- 📁 **Share** - Share a folder and it's contents using a link.
- 🧾 **File Metadata** – Size, type, last updated info shown inline.
- 🧠 **Built with Laravel** – Clean structure, Eloquent-based models, SQLite-powered.

---

## 🛠 Tech Stack

- **Backend**: Laravel 9
- **Database**: SQLite
- **Frontend**: Alpine.js, TailwindCSS

---

## 📦 Requirements

- PHP 8.1+
- Composer
- SQLite
- Node.js + npm

---

## 🧰 Getting Started

### 1. Clone the Repo

```bash
git clone https://github.com/your-username/twodrive.git
cd twodrive
````

---

### 2. Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
```

If you're using frontend tooling:

```bash
npm install && npm run build
```

---

### 3. Setup Environment

Copy `.env.example` to `.env` and configure it:

```bash
cp .env.example .env
php artisan key:generate
```

Basic `.env` values:

```env
APP_NAME=TwoDrive
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

FILESYSTEM_DRIVER=local
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

> **Note**: Create an empty SQLite file:

```bash
touch database/database.sqlite
```

---

### 4. Run Migrations

```bash
php artisan migrate --force
php artisan storage:link
```

---

### 5. Run the App (Local Dev)

```bash
php artisan serve
```

Visit `http://localhost:8000`

---

## ⚙️ Recommended Production Setup

> You **must not** use `php artisan serve` in production.

Instead:

* Deploy via **Nginx** + **PHP-FPM**
* Use **Cloudflare Tunnel** to secure access or manually host it on your VPS
* Point Cloudflare to your internal server (localhost:80)

Example Nginx config:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/twodrive/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
---

## 📁 File Structure (Brief Overview)

```
.
├── app/
│   ├── Models/            # File, Folder models with hierarchy logic
│   ├── Http/Controllers/  # Upload, Download, Browse logic
├── database/
│   └── migrations/
├── public/                # Entry point (index.php)
├── resources/
│   └── views/             # Blade templates
├── routes/
│   └── web.php            # Main routes
└── .env                   # Your config
```

---

## 👷‍♂️ To-Do / Improvements

* [X] Public sharing links
* [ ] File thumbnails for images/videos
* [ ] Expiring/signed download links
* [ ] Folder-level permissions (future multi-user)
* [ ] Chunked large file uploads

---

## 📝 License

This project is open source and MIT licensed.

---

## 🤝 Credits

Built with ❤️ using Laravel, Tailwind, and Alpine. Inspired by the usability of OneDrive and the self-hosting of Nextcloud.

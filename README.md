# Laravel + Vue.js Monorepo

This is a monorepo project combining **Laravel (PHP Backend)** and **Vue.js (JavaScript Frontend)** in a single codebase.

---


> The Vue.js frontend lives in `resources/js` and is compiled using Laravel Mix.

---

## 🛠 Prerequisites

- PHP >= 7.4 (or per Laravel version)
- Composer
- Node.js & npm
- MySQL or equivalent
- Git

---

## ⚙️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/kenzi-ugene/sc.git
cd your-laravel-vue-monorepo

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

npm install
npm run dev    # For development
# or
npm run build  # For production

# Laravel tests
php artisan test





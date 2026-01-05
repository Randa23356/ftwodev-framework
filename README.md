# FTwoDev Framework

**FTwoDev** is a modern, lightweight, and native PHP 8+ framework designed for creative developers. It prioritizes convention over configuration, speed, and simplicity.

![Banner](https://via.placeholder.com/800x200.png?text=FTwoDev+Framework)

## 🚀 Key Features

*   **Native Power**: Built on PHP 8+ with zero bloat.
*   **Magic Routing**: Automatic URL-to-Controller mapping. `/users/profile` -> `UsersController::profile()`.
*   **Creative CLI**: Built-in `ftwo` tool to `ignite` your server and `craft` your code.
*   **Engine & Modular**: Separate core engine from your project logic.
*   **Secure by Design**: Auto CSRF protection and output escaping.

## 📦 Installation

To get started with FTwoDev:

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/your-repo/ftwo-framework.git my-app
    cd my-app
    ```

2.  **Ignite the Installer**
    Run the installer to set up permissions and your unique App Key.
    ```bash
    php install.php
    ```

3.  **Generate Autoloader**
    ```bash
    composer dump-autoload
    ```

4.  **Ignite Server**
    Start the development engine.
    ```bash
    php ftwo ignite
    ```
    Open `http://localhost:8000` in your browser.

## 🔥 CLI Usage: The `ftwo` Tool

FTwoDev comes with a powerful CLI tool to speed up your workflow.

| Command | Description |
| :--- | :--- |
| `php ftwo ignite` | 🔥 Start the development server |
| `php ftwo craft:controller Name` | 🔨 Craft a new Controller |
| `php ftwo craft:model Name` | 📦 Craft a new Model |
| `php ftwo craft:view name` | 📄 Craft a new View file |
| `php ftwo craft:service Name` | 🔧 Craft a new Service class |

## 📁 Directory Structure

The framework is organized to keep your code clean:

```
/FDFramework
├─ /engine                 # The heart of the framework (Core)
├─ /config                 # App, Database, Routes, and Middleware config
├─ /core-modules           # Built-in modules (Auth, Logger, CLI)
├─ /projects               # YOUR Application Code
│    ├─ Controllers        # Logic
│    ├─ Models             # Data
│    ├─ Middlewares        # Filters
│    ├─ Migrations         # Database Schema
│    ├─ Services           # Business Logic
│    └─ Views              # Templates (.ftwo.php)
├─ /public                 # Entry point
└─ ftwo                    # CLI Executable
```

## 🛣️ Routing System

FTwoDev uses **Magic Routing** by default. You don't need to register every route!

*   URL: `/dashboard`
    *   Target: `Projects\Controllers\DashboardController::index()`
*   URL: `/users/edit/5`
    *   Target: `Projects\Controllers\UsersController::edit(5)`

**Manual Routes:**
You can still define custom routes in `config/routes.php` if you need to override the magic.

```php
Router::get('/admin', 'AdminController@index')->middleware('auth');
```

## 🛡️ Middleware

Filter requests before they reach your controller. Global middlewares run on every request, while named middlewares can be assigned to specific routes.

**Config:** `config/middleware.php`
**Create:** `php ftwo craft:born` (just kidding, use `craft:controller` for now or create manually in `projects/Middlewares`).

## 🗄️ Database Migrations

Manage your schema via code. 

1. **Craft:** `php ftwo craft:migration create_posts_table`
2. **Edit:** Open the file in `projects/Migrations` and add your SQL.
3. **Migrate:** `php ftwo ignite:migrate`
4. **Rollback:** `php ftwo ignite:rollback`


## 🎨 Template Engine

Views use the `.ftwo.php` extension. They are standard PHP files but with superpowers.

**Controller:**
```php
return $this->view('profile', ['name' => 'Arman']);
```

**View (`projects/Views/profile.ftwo.php`):**
```php
<?php $this->extends('layout'); ?>

<?php $this->section('content'); ?>
    <h1>Hello, <?= $this->e($name) ?></h1>
<?php $this->endSection(); ?>
```

## 🔒 Security

*   **CSRF Protection**: Use `<?= csrf_field() ?>` in your forms.
*   **Escaping**: Use `$this->e($var)` in views to prevent XSS.

---

<p align="center">
  Built with ❤️ by FTwoDev Team
</p>

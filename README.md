# PHP Assessment Exam (v2.0)

A simple task management application built with Laravel and Vue.js.

## Demo URL: https://powderblue-bear-757602.hostingersite.com/login

## Tech Stack

- **Backend**: Laravel 12+, PHP 8.2+
- **Frontend**: Vue 3, Inertia.js, TypeScript
- **Database**: MySQL
- **UI**: Tailwind CSS, shadcn-vue
- **Code Quality**: Laravel Pint

## Features

- Create, edit, show and delete tasks
- Task status management (to-do, in-progress, done)
- Subtasks support
- Image attachments
- Draft/published tasks
- Search and filtering
- Pagination
- Trash system with 30-day auto cleanup

## Installation

1. **Clone the repository**

```bash
git clone https://github.com/robertfederico/php-assessment-exam.git
cd php-assessment-exam
```

2. **Install dependencies**

```bash
composer install
npm install
```

3. **Environment setup**

```bash
cp .env.example .env
```

Edit `.env` file with your database credentials.

4. **Run installation command**

```bash
php artisan app:install
```

5. **Build frontend & Start the server**

```bash
composer run dev
```

## Important Commands

**Cleanup trashed tasks (runs automatically daily)**

```bash
php artisan tasks:cleanup-trash
```

**Code formatting**

```bash
composer run pint
```

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL

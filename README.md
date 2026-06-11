# SkillBridge — Freelancer & Client Matchmaking Platform

SkillBridge is a complete, lightweight, role-based matchmaking platform connecting **Clients** (people who need work done) with **Freelancers** (people offering skills/services) using skill tags, budget ranges, and availability.

---

## 🎯 Key Features

1. **Role-Based Authentication** — Secure login/register flow separating Client and Freelancer experiences.
2. **Dashboard Statistics** — Custom dashboards displaying jobs posted, applications sent/received, and matchmaking metrics.
3. **Dynamic Skill Tagging** — Pill-based tag inputs for profiles and jobs, complete with autocomplete suggestions for common skills.
4. **Smart Matchmaking** — Matching engine ranked by overlapping skill sets using SQLite queries.
5. **Application Pipeline** — Freelancers apply with custom cover letters, and Clients accept or reject applications instantly from their dashboard.
6. **Robust Validation** — Synchronized JavaScript and PHP server-side form validations.

---

## 🛠️ Tech Stack

- **Frontend**: Vanilla HTML5, Responsive CSS3 (Mobile-First Layout), Vanilla JavaScript
- **Backend**: PHP 8+ (No external frameworks)
- **Database**: SQLite 3 (PDO driver, WAL concurrency mode, automated schema migration on launch)

---

## 🚀 Quick Start Guide

### 1. Enable SQLite Extension in PHP
Ensure `extension=pdo_sqlite` is uncommented in your `php.ini` file.

### 2. Run the Development Server
Since XAMPP is installed locally, you can start the server using the XAMPP PHP executable directly:

```powershell
# Open terminal inside the skillbridge directory and run:
C:\xampp\php\php.exe -S localhost:8000
```

### 3. Open the App
Visit **[http://localhost:8000](http://localhost:8000)** in your browser. The SQLite database `skillbridge.db` will be automatically created and seeded with tables on the first page load.

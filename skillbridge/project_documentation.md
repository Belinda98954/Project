# SkillBridge — Project Documentation

## 1. Distinctiveness and Complexity

SkillBridge is a custom-built, lightweight freelancer-to-client matchmaking platform. It is designed to be highly responsive, responsive to user roles (client vs. freelancer), and uses SQLite for persistent storage.

### Core Complexities:
- **Role-Based Workflows**: Clients and Freelancers have different interfaces, permissions, and available navigation items. Pages are guarded by session checks (e.g. `requireRole()`).
- **Dynamic Skill Tag Management**: Users can dynamically add and remove skill badges using an interactive frontend pill-badge input. It synchronizes with hidden form fields to save changes in a single HTTP request.
- **Automated Skill Matchmaking**: The platform uses SQL aggregations to calculate overlapping skill counts between posted jobs and freelancer profiles. This creates a match score (e.g., "3/4 skills matched") dynamically for both roles.
- **Strict Data Consistency**: Employs SQLite constraints like foreign key cascades and restricted ENUM checks (`CHECK(role IN ('freelancer', 'client'))`).

---

## 2. Design Approach and Reasoning

- **Clean Aesthetic**: Color scheme uses Deep Blue (`#1a1a2e`) for layouts, Accent Teal (`#00b4d8`) for active states, indicators, and buttons, and White cards against a light grey background to preserve high contrast and scanning readability.
- **No Unnecessary Elements**: Kept simple, avoiding distracting visual clutter, external icon libraries, and gradients.
- **Card-Based Mobile-First Layout**: Adapts gracefully from mobile dimensions up to 1100px desktop monitors using standard CSS Media Queries.
- **Micro-interactions**: Hover effects on cards, active link styling, and quick button feedback create a snappy user experience.

---

## 3. File Breakdown

| File Name | Role / Description | Owner |
|---|---|---|
| [db.php](file:///c:/Users/USER/Desktop/Project/skillbridge/db.php) | SQLite connection, tables schema creation, session helpers, and authentication gatekeepers. | Nelly |
| [register.php](file:///c:/Users/USER/Desktop/Project/skillbridge/register.php) | Registration form with client/freelancer role selection and auto-login on successful registration. | Nelly |
| [login.php](file:///c:/Users/USER/Desktop/Project/skillbridge/login.php) | Secure credential check using `password_verify` and session creation. | Nelly |
| [logout.php](file:///c:/Users/USER/Desktop/Project/skillbridge/logout.php) | Destroys current session variables and redirects user to landing page. | Nelly |
| [index.php](file:///c:/Users/USER/Desktop/Project/skillbridge/index.php) | Interactive landing page highlighting features and onboarding CTAs. | Gideon |
| [dashboard.php](file:///c:/Users/USER/Desktop/Project/skillbridge/dashboard.php) | Role-tailored dashboard presenting counts/stats, quick actions, and applications management. | Gideon |
| [profile.php](file:///c:/Users/USER/Desktop/Project/skillbridge/profile.php) | Allows users to view/edit user details and dynamically manage their skill tags. | Gideon |
| [matches.php](file:///c:/Users/USER/Desktop/Project/skillbridge/matches.php) | Displays matching freelancers (for clients) or matching jobs (for freelancers) ordered by skill overlap score. | Gideon |
| [post_job.php](file:///c:/Users/USER/Desktop/Project/skillbridge/post_job.php) | Client form for posting new jobs with details, budget ranges, deadlines, and skill tag requirements. | Belinda |
| [browse_jobs.php](file:///c:/Users/USER/Desktop/Project/skillbridge/browse_jobs.php) | Allows freelancers to view open jobs, see matching skills highlighted, and apply directly. | Belinda |
| [browse_freelancers.php](file:///c:/Users/USER/Desktop/Project/skillbridge/browse_freelancers.php) | Allows clients to view all freelancers registered on the platform and highlights matching skills. | Belinda |
| [style.css](file:///c:/Users/USER/Desktop/Project/skillbridge/style.css) | Custom styling rules including colors, buttons, card grids, layout rules, and media queries. | Gideon |
| [app.js](file:///c:/Users/USER/Desktop/Project/skillbridge/app.js) | Dynamic form validation, skill badge interactions, and mobile navigation toggler. | Gideon |

---

## 4. How to Run (Setup Steps)

### Prerequisites:
- Ensure **PHP 8.0+** is installed on your local machine.
- SQLite support must be enabled in your php.ini configuration (`extension=pdo_sqlite` or `extension=sqlite3`).

### Launch Steps:
1. Open your terminal/command prompt.
2. Navigate to the project directory:
   ```bash
   cd c:\Users\USER\Desktop\Project\skillbridge
   ```
3. Start the PHP built-in web server:
   ```bash
   php -S localhost:8000
   ```
4. Open your web browser and visit: [http://localhost:8000](http://localhost:8000).
5. The SQLite database `skillbridge.db` will be auto-created on first load!

---

## 5. Team Reflections


/**
 * app.js — Frontend Validation & Dynamic UI
 * Owner: Gideon
 *
 * Handles client-side form validation, dynamic skill tag input,
 * mobile navigation toggle, and cover letter form toggling.
 */

// ─── Mobile Navigation Toggle ──────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.querySelector('.navbar-toggle');
    var navLinks = document.querySelector('.navbar-links');
    if (toggleBtn && navLinks) {
        toggleBtn.addEventListener('click', function () {
            navLinks.classList.toggle('open');
        });
    }

    // ─── Form Validation Setup ─────────────────────────────────
    setupFormValidation();

    // ─── Skill Tag Input Setup ─────────────────────────────────
    setupSkillTagInput();

    // ─── Cover Letter Toggle ───────────────────────────────────
    setupApplyToggles();
});

// ═══════════════════════════════════════════════════════════════
// FORM VALIDATION
// ═══════════════════════════════════════════════════════════════

function setupFormValidation() {
    // Registration form
    var regForm = document.getElementById('register-form');
    if (regForm) {
        regForm.addEventListener('submit', function (e) {
            if (!validateRegistration()) {
                e.preventDefault();
            }
        });
    }

    // Login form
    var loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            if (!validateLogin()) {
                e.preventDefault();
            }
        });
    }

    // Post job form
    var jobForm = document.getElementById('post-job-form');
    if (jobForm) {
        jobForm.addEventListener('submit', function (e) {
            if (!validatePostJob()) {
                e.preventDefault();
            }
        });
    }

    // Profile form
    var profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            if (!validateProfile()) {
                e.preventDefault();
            }
        });
    }
}

/**
 * Show an error message under a form field.
 */
function showError(inputId, message) {
    var errorEl = document.getElementById(inputId + '-error');
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('visible');
    }
}

/**
 * Clear all visible form errors.
 */
function clearErrors() {
    var errors = document.querySelectorAll('.form-error');
    for (var i = 0; i < errors.length; i++) {
        errors[i].textContent = '';
        errors[i].classList.remove('visible');
    }
}

/**
 * Validate email format.
 */
function isValidEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate registration form.
 */
function validateRegistration() {
    clearErrors();
    var valid = true;
    var name = document.getElementById('name');
    var email = document.getElementById('email');
    var password = document.getElementById('password');
    var confirmPassword = document.getElementById('confirm_password');
    var role = document.getElementById('role');

    if (!name || name.value.trim() === '') {
        showError('name', 'Name is required.');
        valid = false;
    }

    if (!email || email.value.trim() === '') {
        showError('email', 'Email is required.');
        valid = false;
    } else if (!isValidEmail(email.value.trim())) {
        showError('email', 'Please enter a valid email address.');
        valid = false;
    }

    if (!password || password.value.length < 6) {
        showError('password', 'Password must be at least 6 characters.');
        valid = false;
    }

    if (!confirmPassword || confirmPassword.value !== password.value) {
        showError('confirm_password', 'Passwords do not match.');
        valid = false;
    }

    if (!role || role.value === '') {
        showError('role', 'Please select a role.');
        valid = false;
    }

    return valid;
}

/**
 * Validate login form.
 */
function validateLogin() {
    clearErrors();
    var valid = true;
    var email = document.getElementById('email');
    var password = document.getElementById('password');

    if (!email || email.value.trim() === '') {
        showError('email', 'Email is required.');
        valid = false;
    } else if (!isValidEmail(email.value.trim())) {
        showError('email', 'Please enter a valid email address.');
        valid = false;
    }

    if (!password || password.value === '') {
        showError('password', 'Password is required.');
        valid = false;
    }

    return valid;
}

/**
 * Validate post job form.
 */
function validatePostJob() {
    clearErrors();
    var valid = true;
    var title = document.getElementById('title');
    var description = document.getElementById('description');
    var budgetMin = document.getElementById('budget_min');
    var budgetMax = document.getElementById('budget_max');
    var deadline = document.getElementById('deadline');
    var skillsHidden = document.getElementById('skills_hidden');

    if (!title || title.value.trim() === '') {
        showError('title', 'Job title is required.');
        valid = false;
    }

    if (!description || description.value.trim() === '') {
        showError('description', 'Job description is required.');
        valid = false;
    }

    if (!budgetMin || budgetMin.value === '' || parseFloat(budgetMin.value) < 0) {
        showError('budget_min', 'Please enter a valid minimum budget.');
        valid = false;
    }

    if (!budgetMax || budgetMax.value === '' || parseFloat(budgetMax.value) < 0) {
        showError('budget_max', 'Please enter a valid maximum budget.');
        valid = false;
    }

    if (budgetMin && budgetMax && parseFloat(budgetMin.value) >= parseFloat(budgetMax.value)) {
        showError('budget_max', 'Maximum budget must be greater than minimum budget.');
        valid = false;
    }

    if (!deadline || deadline.value === '') {
        showError('deadline', 'Deadline is required.');
        valid = false;
    }

    if (!skillsHidden || skillsHidden.value.trim() === '') {
        showError('skill_input', 'Add at least one required skill.');
        valid = false;
    }

    return valid;
}

/**
 * Validate profile form.
 */
function validateProfile() {
    clearErrors();
    var valid = true;
    var name = document.getElementById('name');

    if (!name || name.value.trim() === '') {
        showError('name', 'Name is required.');
        valid = false;
    }

    var hourlyRate = document.getElementById('hourly_rate');
    if (hourlyRate && hourlyRate.value !== '' && parseFloat(hourlyRate.value) < 0) {
        showError('hourly_rate', 'Hourly rate cannot be negative.');
        valid = false;
    }

    return valid;
}

// ═══════════════════════════════════════════════════════════════
// DYNAMIC SKILL TAG INPUT
// ═══════════════════════════════════════════════════════════════

function setupSkillTagInput() {
    var skillInput = document.getElementById('skill_input');
    var addSkillBtn = document.getElementById('add-skill-btn');
    var skillContainer = document.getElementById('skill-tags-container');
    var skillsHidden = document.getElementById('skills_hidden');

    if (!skillInput || !skillContainer || !skillsHidden) return;

    // Initialize existing skills from hidden field
    var currentSkills = [];
    if (skillsHidden.value.trim() !== '') {
        currentSkills = skillsHidden.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    }

    function renderSkills() {
        skillContainer.innerHTML = '';
        for (var i = 0; i < currentSkills.length; i++) {
            var pill = document.createElement('span');
            pill.className = 'skill-pill';
            pill.textContent = currentSkills[i];

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-skill';
            removeBtn.textContent = '\u00d7';
            removeBtn.setAttribute('data-index', i);
            removeBtn.addEventListener('click', function () {
                var idx = parseInt(this.getAttribute('data-index'));
                currentSkills.splice(idx, 1);
                syncAndRender();
            });

            pill.appendChild(removeBtn);
            skillContainer.appendChild(pill);
        }
    }

    function syncAndRender() {
        skillsHidden.value = currentSkills.join(',');
        renderSkills();
    }

    function addSkill() {
        var val = skillInput.value.trim();
        if (val === '') return;

        // Prevent duplicates (case-insensitive)
        var lower = val.toLowerCase();
        for (var i = 0; i < currentSkills.length; i++) {
            if (currentSkills[i].toLowerCase() === lower) {
                skillInput.value = '';
                return;
            }
        }

        currentSkills.push(val);
        skillInput.value = '';
        syncAndRender();
        skillInput.focus();
    }

    if (addSkillBtn) {
        addSkillBtn.addEventListener('click', function (e) {
            e.preventDefault();
            addSkill();
        });
    }

    skillInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSkill();
        }
    });

    // Initial render
    renderSkills();
}

// ═══════════════════════════════════════════════════════════════
// APPLY FORM TOGGLE (Browse Jobs)
// ═══════════════════════════════════════════════════════════════

function setupApplyToggles() {
    var applyBtns = document.querySelectorAll('.apply-toggle-btn');
    for (var i = 0; i < applyBtns.length; i++) {
        applyBtns[i].addEventListener('click', function () {
            var jobId = this.getAttribute('data-job-id');
            var form = document.getElementById('apply-form-' + jobId);
            if (form) {
                form.classList.toggle('open');
            }
        });
    }
}

/**
 * Validate a cover letter before application submit.
 */
function validateApplication(jobId) {
    var textarea = document.getElementById('cover-letter-' + jobId);
    var errorEl = document.getElementById('cover-letter-error-' + jobId);
    if (!textarea || textarea.value.trim() === '') {
        if (errorEl) {
            errorEl.textContent = 'Please write a cover letter.';
            errorEl.classList.add('visible');
        }
        return false;
    }
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.remove('visible');
    }
    return true;
}

<?php
require_once __DIR__ . '/../includes/auth.php';
start_session();
redirect_if_logged_in();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<title>24-HR Food Recall | FNRI-DOST</title>
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="login-page">

<div class="login-split">
  <!-- Brand panel -->
  <div class="login-brand">
    <div class="brand-content">
      <div class="brand-icon">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="24" cy="24" r="22" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
          <path d="M24 10C24 10 14 18 14 26C14 31.5 18.5 36 24 36C29.5 36 34 31.5 34 26C34 18 24 10 24 10Z" fill="rgba(255,255,255,0.9)"/>
          <path d="M24 20V32M20 26H28" stroke="#1A3A5C" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <h1>Philippine 24-Hour<br>Food Recall System</h1>
      <p>Intake24-style dietary assessment<br>with FNRI Food Composition Table</p>
      <div class="brand-badges">
        <span class="badge">NNS 2024</span>
        <span class="badge">FNRI-DOST</span>
        <span class="badge">5-Pass Method</span>
      </div>
    </div>
  </div>

  <!-- Form panel -->
  <div class="login-form-panel">
    <div class="login-form-wrap">
      <h2>Sign In</h2>
      <p class="subtitle">Enter your credentials to continue</p>

      <form id="loginForm">
        <div class="field">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="e.g. interviewer01"
                 autocomplete="username" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter password"
                 autocomplete="current-password" required>
        </div>
        <div id="loginError" class="alert-error" hidden></div>
        <button type="submit" class="btn-primary btn-full" id="loginBtn">
          Sign In
        </button>
      </form>

      <p class="login-hint">
        Demo: <code>supervisor01</code> / <code>interviewer01</code> / <code>interviewer02</code><br>
        Password: <code>password123</code>
      </p>
    </div>
  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('loginBtn');
  const err = document.getElementById('loginError');
  btn.disabled = true;
  btn.textContent = 'Signing in…';
  err.hidden = true;

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const res  = await fetch('api/auth.php?action=login', {
      method: 'POST',
      headers: {'Content-Type':'application/json', 'X-CSRF-Token': csrfToken},
      body: JSON.stringify({
        username: document.getElementById('username').value.trim(),
        password: document.getElementById('password').value,
      }),
    });
    const data = await res.json();
    if (data.success) {
      window.location.href = data.redirect;
    } else {
      err.textContent = data.error || 'Login failed';
      err.hidden = false;
    }
  } catch {
    err.textContent = 'Connection error. Please try again.';
    err.hidden = false;
  }

  btn.disabled = false;
  btn.textContent = 'Sign In';
});
</script>
</body>
</html>

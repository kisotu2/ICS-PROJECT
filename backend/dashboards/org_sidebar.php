
<style>
    .org-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 220px;
  height: 100vh;
  background-color: #003366;
  padding: 20px 15px;
  box-sizing: border-box;
  color: white;
  display: flex;
  flex-direction: column;
  gap: 15px;
  z-index: 1000;
}

.org-sidebar .logo {
  text-align: center;
  margin-bottom: 30px;
}

.org-sidebar .logo img {
  max-width: 160px;
  height: auto;
}

.org-sidebar a {
  color: white;
  text-decoration: none;
  padding: 10px 12px;
  border-radius: 6px;
  transition: background 0.2s ease;
  font-size: 14px;
  display: block;
}

.org-sidebar a:hover {
  background-color: #004080;
}

.main-content {
  margin-left: 240px; /* leave space for fixed sidebar */
  padding: 30px;
}
</style>

<div class="org-sidebar">
  <div class="logo">
   <img src="../../Asset/logo.png" alt="Logo">
  </div>
  <a href="organisation_dashboard.php">🏠 Dashboard</a>
  <a href="edit_organisation_profile.php">🏢 Organisation Profile</a>
  <a href="interests.php">🤝 My Interests</a>
  <a href="interests_sent.php">📤 Interests Sent</a>
  <a href="interviews.php">📅 Interview Requests</a>
  <a href="../logout.php">🚪 Logout</a>
</div>

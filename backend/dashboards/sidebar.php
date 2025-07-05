
<style>
  .sidebar {
    width: 220px;
    background: #003366;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .sidebar-logo {
    margin-bottom: 20px;
  }

  .sidebar-logo img {
    max-width: 100%;
    height: 60px;
    object-fit: contain;
  }

  .sidebar h2 {
    color: #fff;
    margin-bottom: 30px;
    font-size: 18px;
    text-align: center;
  }

  .sidebar a {
    color: #fff;
    text-decoration: none;
    margin-bottom: 15px;
    padding: 10px 12px;
    border-radius: 6px;
    transition: background 0.2s ease;
    font-size: 14px;
    width: 100%;
    text-align: left;
  }

  .sidebar a:hover {
    background: #004080;
  }
</style>

<div class="sidebar">
  <div class="sidebar-logo">
    <img src="../../Asset/logo.png" alt="Logo">
  </div>
  <h2>Job Portal</h2>
  <a href="users_dashboard.php">🏠 Dashboard</a>
  <a href="edit_profile.php">✏️ Edit Profile</a>
  <a href="job_offers.php">📩 Job Offers</a>
  <a href="../logout.php">🚪 Logout</a>
</div>

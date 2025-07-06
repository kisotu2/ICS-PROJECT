<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Job Marketplace Dashboard</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #f2f6fc;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .container {
      display: flex;
      flex: 1;
    }

    .sidebar {
      width: 260px;
      background: #1e88e5;
      color: white;
      padding: 30px 20px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 20px;
      min-height: 100vh;
      position: sticky;
      top: 0;
    }

    .sidebar h2 {
      font-size: 1.5rem;
      margin-bottom: 20px;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      background: #1565c0;
      padding: 12px 18px;
      width: 100%;
      border-radius: 6px;
      font-size: 16px;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: background 0.3s ease;
    }

    .sidebar a:hover {
      background: #0d47a1;
    }

    .main-content {
      flex: 1;
      padding: 40px;
      animation: fadeIn 1.2s ease-in;
    }

    header {
      text-align: center;
      margin-bottom: 30px;
    }

    header h1 {
      font-size: 2.5rem;
      margin-bottom: 20px;
    }

    header img {
      width: 100%;
      max-width: 700px;
      height: auto;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      animation: fadeIn 1.5s ease-in;
    }

    .bottom-section {
      margin-top: 60px;
      text-align: center;
    }

    .bottom-section img {
      width: 90%;
      max-width: 900px;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    .bottom-caption {
      margin-top: 12px;
      font-size: 18px;
      color: #555;
    }

    footer {
      background: #e3f2fd;
      padding: 20px;
      color: #555;
      font-size: 14px;
      text-align: center;
      margin-top: auto;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .sidebar {
        width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        padding: 20px 10px;
        position: static;
      }

      .sidebar a {
        flex: 1 1 45%;
        margin: 5px;
        justify-content: center;
      }

      .main-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="sidebar">
    <h2>Get Started</h2>
    <a href="signup.php"><i class="fas fa-user-plus"></i>  Signup</a>
    <a href="login.php"><i class="fas fa-user-lock"></i> Login</a>
  </div>

  <div class="main-content">
    <header>
      <h1>Welcome to the Job Marketplace System</h1>
      <img src="/job_seeker/Asset/successful.avif" alt="Job Seeker Header Image">
    </header>

    <div class="bottom-section">
      <img src="/job_seeker/Asset/successful.avif" alt="Success Celebration">
      <img src="/job_seeker/Asset/search.webp" alt="Success Celebration">
      <p class="bottom-caption">Thousands of job seekers have found success using our platform!</p>
    </div>
  </div>
</div>

<footer>
  &copy; 2025 Job Marketplace System. All Rights Reserved.
</footer>

<script>
  const hours = new Date().getHours();
  const greeting =
    hours < 12 ? "Good Morning" :
    hours < 18 ? "Good Afternoon" : "Good Evening";
  document.querySelector("h1").textContent += ` - ${greeting}!`;
</script>

</body>
</html>

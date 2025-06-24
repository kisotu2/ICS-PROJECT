<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$org_id = $_SESSION['org_id'] ?? 1; // Replace with real session logic

// Database connection
$conn = new mysqli('localhost', 'root', '', 'jobseekers');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch organisation name
$org_name = 'Organisation';
$stmt = $conn->prepare("SELECT name FROM organisation WHERE id = ?");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$stmt->bind_result($org_name);
$stmt->fetch();
$stmt->close();

// Get stats
$interview_count = $conn->query("SELECT COUNT(*) as count FROM interviews WHERE organisation_id = $org_id")->fetch_assoc()['count'];
$viewed_seekers = $conn->query("SELECT COUNT(DISTINCT jobseeker_id) as count FROM interests WHERE organisation_id = $org_id")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Organisation Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #1e1e2f;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 30px;
        }

        .sidebar a button {
    width: 100%;
    background: none;
    border: none;
    color: white;
    padding: 10px 0;
    text-align: left;
    font-size: 16px;
    cursor: pointer;
}
.sidebar a button:hover {
    background-color: #33334d;
}


        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .card {
            background-color: #f2f2f2;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .logout {
            margin-top: 30px;
            display: block;
            color: red;
            text-decoration: none;
        }

        input[type="text"] {
            padding: 10px;
            width: 250px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><?= htmlspecialchars($org_name) ?></h2>

    <a href="organisation_dashboard.php"><button>Dashboard</button></a>
    <a href="./backend/talent_pool.php"><button>Talent Pool</button></a>
    <a href="./backend/interviews.php"><button>Interviews</button></a>
    <a href="./backend/profile.php"><button>Profile</button></a>

    <a class="logout" href="../logout.php">Logout</a>
</div>


<div class="main-content">
    <h2>Dashboard</h2>

    <div class="card">Interviews Scheduled: <?= $interview_count ?></div>
    <div class="card">Job Seekers Viewed: <?= $viewed_seekers ?></div>

    <div class="card">
        <form method="GET" action="organisation_dashboard.php">
            <input type="text" name="search" placeholder="Search Job Seekers..." />
            <button type="submit">Search</button>
        </form>
    </div>

    <?php if (!empty($_GET['search'])): ?>
        <div class="card">
            <h3>Search Results for: <?= htmlspecialchars($_GET['search']) ?></h3>
            <?php
            $search = "%" . $conn->real_escape_string($_GET['search']) . "%";
            $stmt = $conn->prepare("SELECT id, name, email, cv FROM users WHERE name LIKE ?");
            $stmt->bind_param("s", $search);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0):
                while ($row = $res->fetch_assoc()): ?>
                    <div style="margin-bottom: 10px;">
                        <strong>Name:</strong> <?= htmlspecialchars($row['name']) ?><br>
                        <strong>Email:</strong> <?= htmlspecialchars($row['email']) ?><br>
                        <?php if ($row['cv']): ?>
                            <a href="../<?= $row['cv'] ?>" target="_blank">View CV</a>
                        <?php else: ?>
                            <em>No CV uploaded</em>
                        <?php endif; ?>
                        <form method="POST" action="show_interest.php">
                            <input type="hidden" name="jobseeker_id" value="<?= $row['id'] ?>">
                            <button type="submit">Show Interest</button>
                        </form>
                    </div>
                <?php endwhile;
            else:
                echo "<p>No job seekers found.</p>";
            endif;
            $stmt->close();
            ?>
        </div>
    <?php endif; ?>
</div>


<script>
    const ORG_ID = <?= $org_id ?>;
    const INTERVIEWS = <?= $interview_count ?>;
    const VIEWED = <?= $viewed_seekers ?>;
</script>

<script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
<script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>


<script type="text/babel">
    const root = ReactDOM.createRoot(document.getElementById('main-content'));

    function Dashboard() {
        return (
            <>
                <h2>Dashboard</h2>
                <div className="card">Interviews Scheduled: {INTERVIEWS}</div>
                <div className="card">Job Seekers Viewed: {VIEWED}</div>
                <div className="card">
                    <form method="GET" action="organisation_dashboard.php">
                        <input type="text" name="search" placeholder="Search Job Seekers..." />
                        <button type="submit">Search</button>
                    </form>
                </div>
                <a href="#talent-pool" onClick={() => navigate('talentPool')}>View Talent Pool</a>
            </>
        );
    }

    function TalentPool() {
        return (
            <>
                <h2>Talent Pool</h2>
                <iframe src="talent_pool.php" width="100%" height="600px" frameBorder="0"></iframe>
            </>
        );
    }

    function Interviews() {
        return (
            <>
                <h2>Interviews</h2>
                <iframe src="interviews.php" width="100%" height="600px" frameBorder="0"></iframe>
            </>
        );
    }

    function Profile() {
        return (
            <>
                <h2>Organisation Profile</h2>
                <iframe src="profile.php" width="100%" height="600px" frameBorder="0"></iframe>
            </>
        );
    }

    const views = {
        dashboard: <Dashboard />,
        talentPool: <TalentPool />,
        interviews: <Interviews />,
        profile: <Profile />
    };

    function navigate(view) {
        root.render(views[view] || <Dashboard />);
    }

    // Initial load
    navigate('dashboard');
</script>

</body>
</html>

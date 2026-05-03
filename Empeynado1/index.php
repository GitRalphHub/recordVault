<?php
session_start();
require_once 'db.php';

// Session guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Stats
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$recent         = $conn->query("SELECT * FROM students ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – RecordVault</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand"> RecordVault</div>
    <div class="nav-user">
         <strong><?= $_SESSION['username'] ?></strong>
        &nbsp;|&nbsp;
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="page-header">
        <div>
            <h2>Dashboard</h2>
            <p>Welcome back, <strong><?= $_SESSION['username'] ?></strong>!</p>
        </div>
        <a class="btn btn-primary" href="add_new_record.php">+ Add New Student</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $total_students ?></div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>

    <div class="card">
        <h3>Recent Records</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year Level</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recent->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['course']) ?></td>
                    <td><?= htmlspecialchars($row['year_level']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if ($total_students == 0): ?>
                <tr><td colspan="5" style="text-align:center;color:#999;">No student records yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>

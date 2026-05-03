<?php
session_start();
require_once 'db.php';

// Session guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors  = [];
$success = "";
$data    = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize all inputs
    $data = array_map('trim', $_POST);

    // Required field validation
    $required = ['first_name', 'last_name', 'email', 'course', 'year_level'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[$field] = "This field is required.";
        }
    }

    // Email format validation
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    // Year level must be 1–6
    if (!empty($data['year_level']) && (!is_numeric($data['year_level']) || $data['year_level'] < 1 || $data['year_level'] > 6)) {
        $errors['year_level'] = "Year level must be between 1 and 6.";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO students (first_name, last_name, email, course, year_level, address)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssssss",
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['course'],
                $data['year_level'],
                $data['address']
            );
            $stmt->execute();
            $success = "Student record added successfully!";
            $data = []; // Clear form on success
        } catch (mysqli_sql_exception $e) {
            if ($conn->errno === 1062) {
                $errors['email'] = "This email address is already registered.";
            } else {
                $errors['general'] = "A database error occurred. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student – RecordVault</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">🗂 RecordVault</div>
    <div class="nav-user">
        👤 <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
        &nbsp;|&nbsp;
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="page-header">
        <div>
            <h2>Add New Student</h2>
            <p>Fill in the form below to add a new student record.</p>
        </div>
        <a class="btn btn-secondary" href="index.php">← Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="add_new_record.php" novalidate>

            <div class="form-row">
                <div class="form-group <?= isset($errors['first_name']) ? 'has-error' : '' ?>">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name"
                           value="<?= htmlspecialchars($data['first_name'] ?? '') ?>"
                           placeholder="e.g. Juan">
                    <?php if (isset($errors['first_name'])): ?>
                        <span class="field-error"><?= $errors['first_name'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($errors['last_name']) ? 'has-error' : '' ?>">
                    <label for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name"
                           value="<?= htmlspecialchars($data['last_name'] ?? '') ?>"
                           placeholder="e.g. Dela Cruz">
                    <?php if (isset($errors['last_name'])): ?>
                        <span class="field-error"><?= $errors['last_name'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($data['email'] ?? '') ?>"
                       placeholder="e.g. juan@example.com">
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= $errors['email'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group <?= isset($errors['course']) ? 'has-error' : '' ?>">
                    <label for="course">Course <span class="required">*</span></label>
                    <select id="course" name="course">
                        <option value="">-- Select Course --</option>
                        <?php
                        $courses = ['BSIT', 'BSCS', 'BSCE', 'BSA', 'BSBA', 'BSED', 'BSME', 'BSEE'];
                        foreach ($courses as $c):
                            $sel = (($data['course'] ?? '') === $c) ? 'selected' : '';
                        ?>
                            <option value="<?= $c ?>" <?= $sel ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['course'])): ?>
                        <span class="field-error"><?= $errors['course'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($errors['year_level']) ? 'has-error' : '' ?>">
                    <label for="year_level">Year Level <span class="required">*</span></label>
                    <select id="year_level" name="year_level">
                        <option value="">-- Select Year --</option>
                        <?php for ($y = 1; $y <= 4; $y++):
                            $sel = (($data['year_level'] ?? '') == $y) ? 'selected' : '';
                        ?>
                            <option value="<?= $y ?>" <?= $sel ?>>Year <?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <?php if (isset($errors['year_level'])): ?>
                        <span class="field-error"><?= $errors['year_level'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"
                          placeholder="Street, Barangay, City, Province"><?= htmlspecialchars($data['address'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Student Record</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>

        </form>
    </div>

</div>
</body>
</html>

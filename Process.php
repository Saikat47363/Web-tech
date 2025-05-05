<?php
session_start();

// CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$data = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF check
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token mismatch.");
    }

    // Get values safely
    $data['name'] = htmlspecialchars(trim($_POST['reg_name']));
    $data['email'] = htmlspecialchars(trim($_POST['reg_email']));
    $data['password'] = $_POST['reg_password'];
    $data['confirm'] = $_POST['reg_confirm_password'];
    $data['dob'] = $_POST['reg_dob'];
    $data['country'] = htmlspecialchars(trim($_POST['reg_country']));
    $data['gender'] = $_POST['reg_gender'] ?? '';
    $data['terms'] = isset($_POST['reg_terms']);

    // Validation
    if (strlen($data['password']) < 8) $errors[] = "Password must be at least 8 characters.";
    if (preg_match('/\s/', $data['password'])) $errors[] = "Password cannot contain spaces.";
    if ($data['password'] !== $data['confirm']) $errors[] = "Passwords do not match.";

    $age = 0;
    if ($data['dob']) {
        $dob = new DateTime($data['dob']);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 18) $errors[] = "You must be at least 18 years old.";
    } else {
        $errors[] = "Date of birth is required.";
    }

    if (!$data['gender']) $errors[] = "Please select gender.";
    if (!$data['terms']) $errors[] = "You must accept the terms.";

    if (empty($errors)) {
        $_SESSION['submitted_data'] = $data;
        header("Location: register.php?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Form PHP Output</title>
    <style>
        .error {color: red;}
        .success {color: green;}
    </style>
</head>
<body>
<h2>PHP Registration Form</h2>

<?php
if (isset($_GET['success']) && isset($_SESSION['submitted_data'])) {
    $data = $_SESSION['submitted_data'];
    echo "<p class='success'>Registration successful!</p>";
    echo "<p><strong>Name:</strong> {$data['name']}</p>";
    echo "<p><strong>Email:</strong> {$data['email']}</p>";
    echo "<p><strong>Country:</strong> {$data['country']}</p>";
    echo "<p><strong>Date of Birth:</strong> {$data['dob']} (Age: {$age})</p>";
    echo "<p><strong>Gender:</strong> {$data['gender']}</p>";
    unset($_SESSION['submitted_data']);
} else {
?>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $err) echo "<li>$err</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php">
        <label>Full Name:</label><br>
        <input type="text" name="reg_name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="reg_email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="reg_password" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="reg_confirm_password" required><br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="reg_dob" required><br><br>

        <label>Country:</label><br>
        <input type="text" name="reg_country" required><br><br>

        <label>Gender:</label><br>
        <input type="radio" name="reg_gender" value="male" required> Male
        <input type="radio" name="reg_gender" value="female"> Female<br><br>

        <input type="checkbox" name="reg_terms" value="1" required> Accept Terms<br><br>

        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <input type="submit" value="Register">
    </form>
<?php } ?>
</body>
</html>

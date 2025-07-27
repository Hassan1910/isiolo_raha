<!DOCTYPE html>
<html>
<head>
    <title>Test Form</title>
</head>
<body>
    <h1>Simple Test Form</h1>
    
    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <h2>Form Submitted Successfully!</h2>
        <pre><?php print_r($_POST); ?></pre>
    <?php endif; ?>
    
    <form action="test_form.php" method="post">
        <p>
            <label>Name: <input type="text" name="name" required></label>
        </p>
        <p>
            <label>Email: <input type="email" name="email" required></label>
        </p>
        <p>
            <button type="submit">Submit Test</button>
        </p>
    </form>
    
    <p><a href="index.php">Back to Home</a></p>
</body>
</html>
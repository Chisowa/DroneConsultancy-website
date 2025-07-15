<?php
// Database configuration
$host = 'localhost';
$dbname = 'drone_consultancy';
$username = 'root';
$password = '';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'error' => "Database connection failed: " . $e->getMessage()
    ]));
}

// Set response header
header('Content-Type: application/json');

// Process form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING);
        $service_type = filter_input(INPUT_POST, 'service_type', FILTER_SANITIZE_STRING);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        $newsletter = isset($_POST['newsletter']) ? 1 : 0;
        
        // Basic validation (fixed syntax errors in if statements)
        if (empty($name)) {
            throw new Exception('Name is required');
        }
        if (empty($email)) {
            throw new Exception('Email is required');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        if (empty($service_type)) {
            throw new Exception('Service type is required');
        }
        if (empty($message)) {
            throw new Exception('Message is required');
        }
        
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO service_requests 
                              (name, email, phone, company, service_type, message, newsletter, status, date) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', CURDATE())");
        
        $stmt->execute([$name, $email, $phone, $company, $service_type, $message, $newsletter]);
        
        // Send confirmation email (optional)
        $to = $email;
        $subject = "SkyVision: Thank you for your service request";
        $email_message = "Dear $name,\n\nThank you for contacting SkyVision about our $service_type services.\n\n";
        $email_message .= "We have received the following details:\n\n";
        $email_message .= "Service: $service_type\n";
        $email_message .= "Message: $message\n\n";
        $email_message .= "Our team will review your request and contact you within 24 hours.\n\n";
        $email_message .= "Best regards,\nSkyVision Team";
        $headers = "From: info@skyvision.com";
        
        // Try to send email (but don't fail if email doesn't send)
        @mail($to, $subject, $email_message, $headers);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Your request has been submitted successfully!'
        ]);
        
    } catch (Exception $e) {
        // Return error response
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method. Please submit the form.'
    ]);
}
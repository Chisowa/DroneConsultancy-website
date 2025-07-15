<?php
require_once 'db_config.php';
verifyAdminSession();

// Get request ID
$requestId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$requestId) {
    die("Invalid request ID");
}

try {
    // Fetch request details
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM service_requests WHERE id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        die("Request not found");
    }
    
    // Output the details in HTML format
    echo '<div class="request-details">';
    echo '<h6>Basic Information</h6>';
    echo '<table class="table table-bordered">';
    echo '<tr><th width="30%">ID</th><td>' . htmlspecialchars($request['id']) . '</td></tr>';
    echo '<tr><th>Name</th><td>' . htmlspecialchars($request['name']) . '</td></tr>';
    echo '<tr><th>Email</th><td>' . htmlspecialchars($request['email']) . '</td></tr>';
    echo '<tr><th>Phone</th><td>' . htmlspecialchars($request['phone']) . '</td></tr>';
    echo '<tr><th>Company</th><td>' . htmlspecialchars($request['company']) . '</td></tr>';
    echo '<tr><th>Date</th><td>' . htmlspecialchars($request['date']) . '</td></tr>';
    echo '<tr><th>Status</th><td><span class="badge badge-' . 
         ($request['status'] == 'approved' ? 'success' : ($request['status'] == 'rejected' ? 'danger' : 'warning')) . '">' . 
         htmlspecialchars(ucfirst($request['status'])) . '</span></td></tr>';
    echo '</table>';
    
    echo '<h6 class="mt-4">Service Details</h6>';
    echo '<table class="table table-bordered">';
    echo '<tr><th width="30%">Service Type</th><td>' . htmlspecialchars($request['service_type']) . '</td></tr>';
    echo '</table>';
    
    echo '</div>';
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error fetching request details: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
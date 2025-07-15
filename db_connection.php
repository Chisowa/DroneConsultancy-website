<?php
require_once 'db_connection.php';
session_start();

// Handle login
if (isset($_POST['login'])) {
    $enteredPassword = $_POST['adminPassword'];
    $adminPassword = "SkyVision@2023"; // In production, store this securely hashed
    
    if ($enteredPassword === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $loginError = "Incorrect password. Please try again.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Check if admin is logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Get service requests from database
$serviceRequests = [];
if ($isLoggedIn) {
    try {
        $stmt = $pdo->query("SELECT * FROM service_requests ORDER BY date DESC");
        $serviceRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching service requests: " . $e->getMessage());
    }
}

// Handle status updates
if (isset($_POST['update_status'])) {
    $requestId = $_POST['request_id'];
    $newStatus = $_POST['new_status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE service_requests SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $requestId]);
        header("Location: admin.php");
        exit;
    } catch (PDOException $e) {
        die("Error updating status: " . $e->getMessage());
    }
}

// Handle delete request
if (isset($_POST['delete_request'])) {
    $requestId = $_POST['request_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM service_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        header("Location: admin.php");
        exit;
    } catch (PDOException $e) {
        die("Error deleting request: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Your existing head content remains the same -->
</head>
<body>
  <!-- Login Screen (shown by default) -->
  <div id="loginScreen" <?php if ($isLoggedIn) echo 'style="display: none;"'; ?>>
    <div class="container">
      <div class="login-container text-center">
        <h2 class="mb-4"><i class="fas fa-lock"></i> Admin Login</h2>
        <?php if (isset($loginError)): ?>
          <div class="alert alert-danger"><?php echo $loginError; ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="form-group">
            <input type="password" class="form-control" name="adminPassword" placeholder="Enter admin password" required>
          </div>
          <button type="submit" name="login" class="btn btn-primary btn-block mb-3">Login</button>
          <a href="index.html" class="btn btn-outline-secondary btn-block">
            <i class="fas fa-arrow-left mr-2"></i> Return to Home
          </a>
        </form>
      </div>
    </div>
  </div>

  <!-- Admin Dashboard (hidden until login) -->
  <div id="adminDashboard" <?php if (!$isLoggedIn) echo 'style="display: none;"'; ?>>
    <!-- Your existing dashboard HTML remains the same until the requests table -->
    
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Client</th>
              <th>Service Type</th>
              <th>Submitted</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="requestsTable">
            <?php foreach ($serviceRequests as $request): ?>
              <tr>
                <td><?php echo htmlspecialchars($request['id']); ?></td>
                <td>
                  <strong><?php echo htmlspecialchars($request['name']); ?></strong><br>
                  <small class="text-muted"><?php echo htmlspecialchars($request['email']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($request['service_type']); ?></td>
                <td><?php echo htmlspecialchars($request['date']); ?></td>
                <td>
                  <?php if ($request['status'] === 'pending'): ?>
                    <span class="badge badge-pending">Pending</span>
                  <?php elseif ($request['status'] === 'approved'): ?>
                    <span class="badge badge-approved">Approved</span>
                  <?php else: ?>
                    <span class="badge badge-rejected">Rejected</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button class="btn btn-sm btn-info action-btn view-details" data-id="<?php echo $request['id']; ?>">
                    <i class="fas fa-eye"></i> View
                  </button>
                  <?php if ($request['status'] === 'pending'): ?>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                    <input type="hidden" name="new_status" value="approved">
                    <button type="submit" name="update_status" class="btn btn-sm btn-success action-btn">
                      <i class="fas fa-check"></i> Approve
                    </button>
                  </form>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                    <input type="hidden" name="new_status" value="rejected">
                    <button type="submit" name="update_status" class="btn btn-sm btn-danger action-btn">
                      <i class="fas fa-times"></i> Reject
                    </button>
                  </form>
                  <?php endif; ?>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                    <button type="submit" name="delete_request" class="btn btn-sm btn-secondary action-btn" onclick="return confirm('Are you sure you want to delete this request?');">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Update the statistics cards with PHP -->
    <script>
    $(document).ready(function() {
      // Update stats with PHP counts
      $("#totalRequests").text(<?php echo count($serviceRequests); ?>);
      $("#pendingRequests").text(<?php echo count(array_filter($serviceRequests, function($r) { return $r['status'] === 'pending'; })); ?>);
      $("#approvedRequests").text(<?php echo count(array_filter($serviceRequests, function($r) { return $r['status'] === 'approved'; })); ?>);
      $("#rejectedRequests").text(<?php echo count(array_filter($serviceRequests, function($r) { return $r['status'] === 'rejected'; })); ?>);

      // Handle logout
      $("#logoutBtn").click(function() {
        window.location.href = "?logout=1";
      });

      // View details button
      $(".view-details").click(function() {
        const requestId = $(this).data("id");
        // You could implement a modal here or keep the simple alert
        alert("Viewing details for request #" + requestId);
      });
    });
    </script>
  </div>
</body>
</html>
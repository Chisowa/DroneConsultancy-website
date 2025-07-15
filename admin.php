<?php
// Database configuration and session handling
session_start();
require_once 'db_config.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$host = 'localhost';
$dbname = 'drone_consultancy';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle login
if (isset($_POST['login']) && isset($_POST['csrf_token'])) {
    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }
    
    $enteredPassword = $_POST['adminPassword'] ?? '';
    // NOTE: In production, use password_hash() and password_verify()
    $adminPassword = "SkyVision@2023";
    
    if ($enteredPassword === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_activity'] = time();
        // Regenerate session ID after login
        session_regenerate_id(true);
    } else {
        $loginError = "Incorrect password. Please try again.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    // Clear all session data
    $_SESSION = array();
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Check login status and session timeout (30 minutes)
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
if ($isLoggedIn && isset($_SESSION['last_activity'])) {
    $inactive = 1800; // 30 minutes in seconds
    if (time() - $_SESSION['last_activity'] > $inactive) {
        session_unset();
        session_destroy();
        $isLoggedIn = false;
    } else {
        $_SESSION['last_activity'] = time(); // Update last activity time
    }
}

// Fetch data if logged in
$serviceRequests = [];
$stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

if ($isLoggedIn) {
    try {
        // Get all requests
        $stmt = $pdo->prepare("SELECT * FROM service_requests ORDER BY date DESC");
        $stmt->execute();
        $serviceRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM service_requests GROUP BY status");
        $stmt->execute();
        $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($statusCounts as $row) {
            $stats[$row['status']] = $row['count'];
            $stats['total'] += $row['count'];
        }
    } catch (PDOException $e) {
        error_log("Error fetching service requests: " . $e->getMessage());
        die("Error fetching service requests. Please try again later.");
    }
}

// Handle status updates
if (isset($_POST['update_status']) && $isLoggedIn && isset($_POST['csrf_token'])) {
    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }
    
    $requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    $newStatus = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);
    
    if ($requestId && in_array($newStatus, ['pending', 'approved', 'rejected'])) {
        try {
            $stmt = $pdo->prepare("UPDATE service_requests SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $requestId]);
            header("Location: admin.php");
            exit;
        } catch (PDOException $e) {
            error_log("Error updating status: " . $e->getMessage());
            die("Error updating status. Please try again.");
        }
    }
}

// Handle delete request
if (isset($_POST['delete_request']) && $isLoggedIn && isset($_POST['csrf_token'])) {
    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }
    
    $requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    
    if ($requestId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM service_requests WHERE id = ?");
            $stmt->execute([$requestId]);
            header("Location: admin.php");
            exit;
        } catch (PDOException $e) {
            error_log("Error deleting request: " . $e->getMessage());
            die("Error deleting request. Please try again.");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SkyVision Admin Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    :root {
      --primary: #2a5bd7;
      --secondary: #00c3ff;
      --dark: #1a1a2e;
      --light: #f8f9fa;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fa;
    }
    
    .login-container {
      max-width: 400px;
      margin: 100px auto;
      padding: 30px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .admin-nav {
      background: var(--dark);
      min-height: 100vh;
      padding-top: 20px;
    }
    
    .admin-nav .nav-link {
      color: rgba(255,255,255,0.8);
      padding: 10px 15px;
      border-radius: 5px;
      margin-bottom: 5px;
    }
    
    .admin-nav .nav-link:hover,
    .admin-nav .nav-link.active {
      background: rgba(255,255,255,0.1);
      color: white;
    }
    
    .admin-nav .nav-link i {
      width: 20px;
      margin-right: 10px;
      text-align: center;
    }
    
    .dashboard-header {
      background: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      padding: 15px 0;
      margin-bottom: 30px;
    }
    
    .request-card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      margin-bottom: 20px;
      transition: all 0.3s;
    }
    
    .request-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .badge-pending {
      background-color: #ffc107;
      color: #212529;
    }
    
    .badge-approved {
      background-color: #28a745;
    }
    
    .badge-rejected {
      background-color: #dc3545;
    }
    
    .action-btn {
      padding: 5px 10px;
      font-size: 0.8rem;
      margin-right: 5px;
    }
    
    .status-select {
      width: 120px;
      display: inline-block;
    }
  </style>
</head>
<body>
  <!-- Login Screen -->
  <div id="loginScreen" <?php if ($isLoggedIn) echo 'style="display: none;"'; ?>>
    <div class="container">
      <div class="login-container text-center">
        <h2 class="mb-4"><i class="fas fa-lock"></i> Admin Login</h2>
        <?php if (isset($loginError)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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

  <!-- Admin Dashboard -->
  <div id="adminDashboard" <?php if (!$isLoggedIn) echo 'style="display: none;"'; ?>>
    <div class="row no-gutters">
      <!-- Sidebar -->
      <div class="col-md-2 admin-nav">
        <div class="text-center mb-4">
          <h4 class="text-white">SkyVision</h4>
          <p class="text-muted small">Admin Dashboard</p>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link active" href="#">
              <i class="fas fa-tasks"></i> Service Requests
            </a>
          </li>
          <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="?logout=1" id="logoutBtn">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </li>
        </ul>
      </div>
      
      <!-- Main Content -->
      <div class="col-md-10">
        <div class="dashboard-header">
          <div class="container">
            <div class="row align-items-center">
              <div class="col">
                <h4 class="mb-0">Service Requests Management</h4>
              </div>
              <div class="col-auto">
                <span class="badge badge-primary">Last updated: <?php echo date('Y-m-d H:i:s'); ?></span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="container">
          <!-- Stats Cards -->
          <div class="row mb-4">
            <div class="col">
              <div class="card request-card">
                <div class="card-body p-3">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="d-flex align-items-center">
                        <div class="bg-primary rounded-circle p-3 mr-3 text-white">
                          <i class="fas fa-inbox fa-lg"></i>
                        </div>
                        <div>
                          <h6 class="mb-0">Total Requests</h6>
                          <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="d-flex align-items-center">
                        <div class="bg-warning rounded-circle p-3 mr-3 text-white">
                          <i class="fas fa-clock fa-lg"></i>
                        </div>
                        <div>
                          <h6 class="mb-0">Pending</h6>
                          <h3 class="mb-0"><?php echo $stats['pending']; ?></h3>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="d-flex align-items-center">
                        <div class="bg-success rounded-circle p-3 mr-3 text-white">
                          <i class="fas fa-check fa-lg"></i>
                        </div>
                        <div>
                          <h6 class="mb-0">Approved</h6>
                          <h3 class="mb-0"><?php echo $stats['approved']; ?></h3>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="d-flex align-items-center">
                        <div class="bg-danger rounded-circle p-3 mr-3 text-white">
                          <i class="fas fa-times fa-lg"></i>
                        </div>
                        <div>
                          <h6 class="mb-0">Rejected</h6>
                          <h3 class="mb-0"><?php echo $stats['rejected']; ?></h3>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Requests Table -->
          <div class="row">
            <div class="col">
              <div class="card request-card">
                <div class="card-header">
                  <div class="row align-items-center">
                    <div class="col">
                      <h5 class="mb-0">Recent Service Requests</h5>
                    </div>
                    <div class="col-auto">
                      <select class="form-control form-control-sm status-select" onchange="window.location.href='?filter='+this.value">
                        <option value="all" <?= !isset($_GET['filter']) || $_GET['filter'] == 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="pending" <?= isset($_GET['filter']) && $_GET['filter'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= isset($_GET['filter']) && $_GET['filter'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= isset($_GET['filter']) && $_GET['filter'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Client</th>
                          <th>Service Type</th>
                          <th>Company</th>
                          <th>Submitted</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $filter = $_GET['filter'] ?? 'all';
                        $filteredRequests = $filter == 'all' 
                            ? $serviceRequests 
                            : array_filter($serviceRequests, fn($r) => $r['status'] == $filter);
                        
                        foreach ($filteredRequests as $request): 
                            $badgeClass = [
                                'pending' => 'badge-pending',
                                'approved' => 'badge-approved',
                                'rejected' => 'badge-rejected'
                            ][$request['status']];
                        ?>
                        <tr>
                          <td><?= htmlspecialchars($request['id']) ?></td>
                          <td>
                            <strong><?= htmlspecialchars($request['name']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($request['email']) ?></small>
                          </td>
                          <td><?= htmlspecialchars($request['service_type']) ?></td>
                          <td><?= htmlspecialchars($request['company']) ?></td>
                          <td><?= htmlspecialchars($request['date']) ?></td>
                          <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($request['status']) ?></span></td>
                          <td>
                            <button class="btn btn-sm btn-info action-btn view-details" data-id="<?= $request['id'] ?>">
                              <i class="fas fa-eye"></i> View
                            </button>
                            <?php if ($request['status'] === 'pending'): ?>
                            <form method="POST" style="display:inline">
                              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                              <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                              <input type="hidden" name="new_status" value="approved">
                              <button type="submit" name="update_status" class="btn btn-sm btn-success action-btn">
                                <i class="fas fa-check"></i> Approve
                              </button>
                            </form>
                            <form method="POST" style="display:inline">
                              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                              <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                              <input type="hidden" name="new_status" value="rejected">
                              <button type="submit" name="update_status" class="btn btn-sm btn-danger action-btn">
                                <i class="fas fa-times"></i> Reject
                              </button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" style="display:inline">
                              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                              <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                              <button type="submit" name="delete_request" class="btn btn-sm btn-secondary action-btn" onclick="return confirm('Are you sure?')">
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
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Details Modal -->
  <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="detailsModalLabel">Request Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="requestDetails">
          <!-- Details will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  
  <script>
    $(document).ready(function() {
      // Handle view button click
      $('.view-details').click(function() {
        const requestId = $(this).data('id');
        
        // Show loading state
        $('#requestDetails').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading details...</p></div>');
        
        // Fetch data via AJAX
        $.ajax({
          url: 'get_request_details.php',
          type: 'GET',
          data: { id: requestId },
          dataType: 'html',
          success: function(data) {
            $('#requestDetails').html(data);
            $('#detailsModal').modal('show');
          },
          error: function() {
            $('#requestDetails').html('<div class="alert alert-danger">Failed to load details. Please try again.</div>');
            $('#detailsModal').modal('show');
          }
        });
      });
    });
  </script>
</body>
</html>
<?php
// Add this at the very top of the file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The form submission will be handled by the AJAX call to submit_booking.php
    // This is just here in case JavaScript is disabled
    require_once 'submit_booking.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact SkyVision | Drone Services Inquiry</title>
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
      background-color: #f8f9fa;
      background-image: url('https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      background-blend-mode: overlay;
      background-color: rgba(248, 249, 250, 0.9);
    }
    
    .navbar {
      background: rgba(26, 26, 46, 0.9) !important;
      backdrop-filter: blur(10px);
    }
    
    .form-container {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      padding: 40px;
      margin: 50px 0;
      position: relative;
      z-index: 1;
      border: 1px solid rgba(0,0,0,0.1);
    }
    
    .btn-gradient {
      background: linear-gradient(45deg, var(--primary), var(--secondary));
      border: none;
      color: white;
      transition: all 0.3s;
      background-size: 200% auto;
      padding: 12px 30px;
    }
    
    .btn-gradient:hover {
      background-position: right center;
      color: white;
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    .form-control {
      height: 50px;
      border-radius: 5px;
      border: 1px solid #e0e0e0;
      padding-left: 20px;
      background-color: rgba(255,255,255,0.8);
    }
    
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(42, 91, 215, 0.25);
      background-color: white;
    }
    
    textarea.form-control {
      height: auto;
      min-height: 150px;
    }
    
    .contact-info-card {
      background: rgba(255,255,255,0.95);
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      padding: 30px;
      height: 100%;
      transition: all 0.3s;
      border: 1px solid rgba(0,0,0,0.05);
    }
    
    .contact-info-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .contact-icon {
      font-size: 2rem;
      color: var(--primary);
      margin-bottom: 20px;
    }
    
    /* Modal Styles */
    .modal-confirmation {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.7);
      animation: fadeIn 0.3s;
    }
    
    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity: 1;}
    }
    
    .modal-content {
      background-color: #fefefe;
      margin: 10% auto;
      padding: 40px;
      border-radius: 10px;
      width: 90%;
      max-width: 600px;
      box-shadow: 0 5px 30px rgba(0,0,0,0.3);
      animation: slideDown 0.4s;
      background-image: linear-gradient(rgba(255,255,255,0.98), rgba(255,255,255,0.98));
    }
    
    @keyframes slideDown {
      from {transform: translateY(-50px); opacity: 0;}
      to {transform: translateY(0); opacity: 1;}
    }
    
    .close-modal-btn {
      background: var(--primary);
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .close-modal-btn:hover {
      background: #1a4ab6;
    }
    
    .page-header {
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                  url('https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 100px 0;
      margin-bottom: 30px;
      text-align: center;
    }
    
    footer {
      background: rgba(0,0,0,0.9);
      color: white;
      padding: 50px 0 20px;
      position: relative;
      z-index: 1;
    }
    
    footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 100%;
      background: url('https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
      background-size: cover;
      background-position: center;
      opacity: 0.2;
      z-index: -1;
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="index.html">
        <i class="fas fa-drone-alt mr-2"></i> SkyVision
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
          <li class="nav-item ml-lg-3">
            <a href="tel:+15551234567" class="btn btn-outline-light btn-sm">
              <i class="fas fa-phone mr-2"></i> Call Us
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <div class="container">
      <h1 class="display-4 font-weight-bold">Contact Our Drone Experts</h1>
      <p class="lead">Get in touch to discuss your aerial project requirements</p>
    </div>
  </div>

  <!-- Contact Form -->
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="form-container">
          <h2 class="mb-4 font-weight-bold">Request a Quote</h2>
          <p class="mb-5 text-muted">Complete the form below and we'll contact you within 24 hours</p>
          
          <form id="bookingForm" onsubmit="return submitForm(event)">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="name">Full Name *</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="form-group col-md-6">
                <label for="email">Email Address *</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="phone">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone">
              </div>
              <div class="form-group col-md-6">
                <label for="company">Company (Optional)</label>
                <input type="text" class="form-control" id="company" name="company">
              </div>
            </div>
            
            <div class="form-group">
              <label for="service_type">Service Needed *</label>
              <select class="form-control" id="service_type" name="service_type" required>
                <option value="" disabled selected>Select a service</option>
                <option>Aerial Surveying & Mapping</option>
                <option>Infrastructure Inspection</option>
                <option>Construction Progress Monitoring</option>
                <option>Agricultural Analysis</option>
                <option>Cinematic Aerial Videography</option>
                <option>Real Estate Photography</option>
                <option>Other (Specify Below)</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="message">Project Details *</label>
              <textarea class="form-control" id="message" name="message" rows="5" placeholder="Tell us about your project, location, timeline, and any special requirements" required></textarea>
            </div>
            
            <div class="form-group form-check">
              <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter" checked>
              <label class="form-check-label" for="newsletter">Subscribe to our newsletter for drone industry updates</label>
            </div>
            
            <button type="submit" class="btn btn-gradient btn-lg mt-3">
              <i class="fas fa-paper-plane mr-2"></i> Submit Request
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Contact Info Section -->
  <section class="py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="contact-info-card text-center">
            <div class="contact-icon">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <h3>Our Location</h3>
            <p class="mb-0">123 Chilanga<br>Lusaka, Zambia</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="contact-info-card text-center">
            <div class="contact-icon">
              <i class="fas fa-phone-alt"></i>
            </div>
            <h3>Call Us</h3>
            <p class="mb-0">
              <a href="tel:+26097000000">+260 97000000</a><br>
              Mon-Fri: 8am-6pm EST
            </p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="contact-info-card text-center">
            <div class="contact-icon">
              <i class="fas fa-envelope"></i>
            </div>
            <h3>Email Us</h3>
            <p class="mb-0">
              <a href="mailto:info@skyvision.com">info@skyvision.com</a><br>
              Response within 24 hours
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Confirmation Modal -->
  <div id="confirmationModal" class="modal-confirmation">
    <div class="modal-content">
      <div class="text-center mb-4">
        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
      </div>
      <h3 class="text-center mb-4">Thank You for Your Request!</h3>
      <div id="submissionDetails" class="mb-4"></div>
      <div class="text-center">
        <p class="text-muted mb-4">A confirmation has been sent to your email. Our team will contact you shortly.</p>
        <button onclick="closeModal()" class="close-modal-btn">Close</button>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-6 mb-4 mb-md-0">
          <h3 class="h4 mb-4"><i class="fas fa-drone-alt mr-2"></i> SkyVision</h3>
          <p>Pioneering drone technology solutions. Certified and fully insured.</p>
          <div class="mt-4">
            <a href="#" class="text-white mr-3"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="text-white mr-3"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-white mr-3"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="text-white mr-3"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
        <div class="col-md-3 mb-4 mb-md-0">
          <h4 class="h5 mb-4">Quick Links</h4>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="index.html" class="text-white">Home</a></li>
            <li class="mb-2"><a href="services.html" class="text-white">Services</a></li>
            <li class="mb-2"><a href="about.html" class="text-white">About Us</a></li>
            <li class="mb-2"><a href="contact.html" class="text-white">Contact</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h4 class="h5 mb-4">Legal</h4>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="#" class="text-white">Privacy Policy</a></li>
            <li class="mb-2"><a href="#" class="text-white">Terms of Service</a></li>
            <li class="mb-2"><a href="#" class="text-white">FAQ</a></li>
          </ul>
        </div>
      </div>
      <hr class="my-4 bg-light">
      <div class="text-center">
        <p class="mb-0 small">&copy; 2025 SkyVision Drone Solutions. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    // Form submission with enhanced modal
    function submitForm(event) {
      event.preventDefault();
      
      // Get form data
      const formData = new FormData(document.getElementById('bookingForm'));
      const data = Object.fromEntries(formData.entries());
      
      // Show submission details in modal
      document.getElementById('submissionDetails').innerHTML = `
        <div class="submission-detail">
          <p><strong>Name:</strong> ${data.name}</p>
          <p><strong>Email:</strong> ${data.email}</p>
          ${data.phone ? `<p><strong>Phone:</strong> ${data.phone}</p>` : ''}
          ${data.company ? `<p><strong>Company:</strong> ${data.company}</p>` : ''}
          <p><strong>Service:</strong> ${data.service_type}</p>
          ${data.message ? `<p><strong>Details:</strong> ${data.message}</p>` : ''}
        </div>
      `;
      
      // Show modal with animation
      document.getElementById('confirmationModal').style.display = 'block';
      document.body.style.overflow = 'hidden';
      
      // Submit to server (AJAX)
      fetch('submit_booking.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(formData)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Submission successful');
        } else {
          console.error('Server error:', data.error);
        }
      })
      .catch(error => console.error('Error:', error));
      
      return false;
    }
    
    function closeModal() {
      document.getElementById('confirmationModal').style.display = 'none';
      document.body.style.overflow = 'auto';
      document.getElementById('bookingForm').reset();
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('confirmationModal');
      if (event.target == modal) {
        closeModal();
      }
    }
    
    // Navbar scroll effect
    $(window).scroll(function() {
      if ($(this).scrollTop() > 100) {
        $('.navbar').addClass('scrolled');
      } else {
        $('.navbar').removeClass('scrolled');
      }
    });
  </script>
</body>
</html>
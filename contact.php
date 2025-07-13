<?php
session_start();
if (!isset($_SESSION["user"])) {
   header("Location: login.php");
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Contact Us - Fabrique</title>
    <style>
        
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
          
        }
        
        .hero-content h1 {
            font-size: 3em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .hero-content p {
            font-size: 1.2em;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .sisters-intro {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 40px;
            align-items: center;
        }
        
        .sisters-text h2 {
            color: #4CAF50;
            margin-bottom: 20px;
            font-size: 2.2em;
        }
        
        .sisters-text p {
            line-height: 1.7;
            color: #333;
            margin-bottom: 15px;
        }
        
        .sisters-image {
            text-align: center;
        }
        
        .sisters-image img {
            width: 100%;
            max-width: 280px;
            height: 350px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }
        
        .sisters-image img:hover {
            transform: scale(1.05);
        }
        
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }
        
        .contact-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 4px solid #4CAF50;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .contact-card h3 {
            font-size: 1.3em;
            margin-bottom: 15px;
            color: #4CAF50;
        }
        
        .contact-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .contact-form-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 40px;
            align-items: start;
        }
        
        .form-image img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .contact-form h3 {
            color: #4CAF50;
            font-size: 1.8em;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            position: relative;
            margin: 0 auto;
            margin-right: 8px;
            width: fit-content;
            height: fit-content;
            padding: 12px;
        }
        
        .submit-btn:hover {
            color: white;
              transition: transform 0.3s ease;
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }
        
        @media (max-width: 768px) {
            .sisters-intro,
            .contact-form-section {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-content h1 {
                font-size: 2em;
            }
        }
    </style>
</head>

<body>
    <div class="hero-section">
        <div class="hero-content">
            <h1>Get in Touch</h1>
            <p>We'd love to hear from you! Contact the Fabrique sisters today.</p>
        </div>
    </div>
    
    <nav>
        <button onclick="goToPage()">Log out</button>
        <script>
            function goToPage() {
                window.location.href = "logout.php";  
            }
        </script>
        <a href="home.php">Back to Home</a>
        <a href="about.php">About us</a>
    </nav>
    
    <div class="contact-container">
        <div class="sisters-intro">
            <div class="sisters-text">
                <h2>Meet the Fabrique Sisters</h2>
                <p>Hello! We're Sabita and Aruna, the sister duo behind Fabrique. What started as our shared love for beautiful textiles has blossomed into a thriving business that we're incredibly proud of.</p>
                
                <p>From our cozy workshop in Nepal, we carefully curate and design premium bedsheets, blankets, tote bags, and home accessories. Every product reflects our commitment to quality, comfort, and style.</p>
                
                <p>We believe that your home should be your sanctuary, and we're here to help you create that perfect space. Whether you have questions about our products, need styling advice, or just want to chat about textiles, we're always here to help!</p>
            </div>
            <div class="sisters-image">
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="The Fabrique Sisters">
            </div>
        </div>
        
        <section>
            <div class="contact-info">
                <div class="contact-card">
                    <h3>📍 Our Shop</h3>
                    <p>Gabahal<br>
                    Patan, Lalitpur<br>
                    Bagmati Province, Nepal<br><br>
                    <em>Visit us to see our latest collections!</em></p>
                </div>
                
                <div class="contact-card">
                    <h3>📞 Call Us</h3>
                    <p><strong>Sabita:</strong> 9860308373<br>
                    <strong>Aruna:</strong> 9852001092<br>
                    <em>We're usually available 11 AM - 7 PM</em></p>
                </div>
                
                <div class="contact-card">
                    <h3>✉️ Email Us</h3>
                    <p><strong>General:</strong> hello@fabrique.com<br>
                    <strong>Orders:</strong> orders@fabrique.com<br>
                    <em>We reply within 24 hours!</em></p>
                </div>
                
                <div class="contact-card">
                    <h3>🕒 Workshop Hours</h3>
                    <p><strong>Mon - Fri:</strong> 9:00 AM - 7:00 PM<br>
                    <strong>Saturday:</strong> 10:00 AM - 5:00 PM<br>
                    <strong>Sunday:</strong> By appointment<br><br>
                    <em>We love visitors - just give us a call!</em></p>
                </div>
            </div>
            
            <div class="contact-form-section">
                <div class="form-image">
                    <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Beautiful bedsheets and home textiles">
                </div>
                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <form action="contact_handler.php" method="POST">
                        <div class="form-group">
                            <label for="name">Your Name:</label>
                            <input type="text" id="name" name="name" placeholder="What should we call you?" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" id="email" name="email" placeholder="your@email.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" id="phone" name="phone" placeholder="+977-98-XXXXXXXX (optional)">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">What's this about?</label>
                            <input type="text" id="subject" name="subject" placeholder="Product inquiry, custom order, etc." required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Your Message:</label>
                            <textarea id="message" name="message" placeholder="Tell us about your needs, ask questions, or just say hello! We love hearing from our customers." required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
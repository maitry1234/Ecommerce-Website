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
    <title>About Us - Fabrique</title>
    <style>

        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1631452180539-96aca7d48617?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            height: 200px; /* Reduced height */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .hero-content h1 {
            font-size: 3em;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-content p {
            font-size: 1.2em;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            max-width: 500px;
        }

        nav {
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }

       
        nav button {
            border: none;
            background: none;
            cursor: pointer;
        }

        .sisters-story {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 0 auto 40px;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            align-items: center;
        }

        .story-text h2 {
            color: #4CAF50;
            font-size: 2.2em;
            margin-bottom: 20px;
            position: relative;
        }

        .story-text h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            border-radius: 2px;
        }

        .story-text p {
            line-height: 1.7;
            color: #333;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .sisters-photo img {
            width: 100%;
            height: 450px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }

        .sisters-photo img:hover {
            transform: scale(1.03);
        }

        .products-showcase {
            max-width: 1200px;
            margin: 0 auto 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-card-content {
            padding: 20px;
        }

        .product-card h3 {
            color: #4CAF50;
            font-size: 1.3em;
            margin-bottom: 10px;
        }

        .product-card p {
            color: #666;
            line-height: 1.6;
            font-size: 0.95em;
        }

        .about-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 1200px;
            margin: 0 auto 40px;
        }

        .about-section h2 {
            color: #4CAF50;
            margin-bottom: 20px;
            font-size: 2em;
            position: relative;
        }

        .about-section h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            border-radius: 2px;
        }

        .about-section p {
            line-height: 1.7;
            color: #333;
            font-size: 1.1em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 0.95em;
            opacity: 0.9;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .value-card {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            border-top: 4px solid #4CAF50;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .value-card h4 {
            color: #4CAF50;
            font-size: 1.2em;
            margin: 10px 0;
        }

        .value-card p {
            color: #666;
            line-height: 1.6;
            font-size: 0.95em;
        }

        .workshop-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 1200px;
            margin: 0 auto 40px;
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 40px;
            align-items: center;
        }

        .workshop-image img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .workshop-text h2 {
            color: #4CAF50;
            font-size: 2em;
            margin-bottom: 20px;
            position: relative;
        }

        .workshop-text h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            border-radius: 2px;
        }

        .workshop-text p {
            line-height: 1.7;
            color: #333;
            font-size: 1.1em;
            margin-bottom: 15px;
        }

       

        @media (max-width: 768px) {
            .sisters-story,
            .workshop-section {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-content h1 {
                font-size: 2em;
            }

            .sisters-photo img {
                height: 350px;
            }

            .workshop-image img {
                height: 250px;
            }
        }
    </style>
</head>

<body>
    <div class="hero-section">
        <div class="hero-content">
            <h1>Our Story</h1>
            <p>Two sisters, one dream, and a passion for creating beautiful homes</p>
        </div>
    </div>

    <nav>
        <button onclick="goToPage()">Log out</button>
        <script>
            function goToPage() {
                window.location.href = "logout.php";  
            }
        </script>
        <a href="<?php echo $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : 'index.php'; ?>">Back to Home</a>
        <a href="#products">Products</a>
        <a href="contact.php">Contact</a>
    </nav>

    <section class="sisters-story">
        <div class="story-text">
            <h2>Meet Sabita & Aruna</h2>
            <p>We're Sabita and Aruna Sharma, sisters who transformed our childhood passion for textiles into Fabrique – Nepal's cherished home textile brand. Inspired by our grandmother, a skilled seamstress, we grew up immersed in the art of fabric creation.</p>
            <p>Sabita, the elder sister, honed her craft in fashion design in Delhi, while Aruna mastered business management in Kathmandu. After years apart, our shared love for creating beautiful home essentials brought us back together in Nepal.</p>
            <p>In 2020, we launched Fabrique from a small workshop in Thamel, starting with bedsheets and growing to include blankets, tote bags, and more. Each piece reflects our commitment to craftsmanship and comfort.</p>
            <p>Our products are more than textiles – they’re stories of sisterhood, tradition, and the joy of making your home a sanctuary.</p>
        </div>
        <div class="sisters-photo">
            <img src="https://images.unsplash.com/photo-1494790108755-2616c60e1d9d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Sabita and Aruna - The Fabrique Sisters">
        </div>
    </section>

    <section class="products-showcase">
        <div class="product-card">
            <img src="https://images.unsplash.com/photo-1505693314120-0d443867891c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Premium Bedsheets">
            <div class="product-card-content">
                <h3>Premium Bedsheets</h3>
                <p>Soft cotton and silk bedsheets designed for ultimate comfort, available in classic and vibrant patterns to elevate your bedroom.</p>
            </div>
        </div>
        <div class="product-card">
            <img src="assets/blanket1.jpg" alt="Cozy Blankets">
            <div class="product-card-content">
                <h3>Cozy Blankets</h3>
                <p>Hand-selected wool and cotton blankets tailored for Nepal’s climate, perfect for any season.</p>
            </div>
        </div>
        <div class="product-card">
            <img src="assets/tot1.jpg" alt="Stylish Tote Bags">
            <div class="product-card-content">
                <h3>Eco-Friendly Tote Bags</h3>
                <p>Sustainable tote bags made from organic cotton and hemp, ideal for daily adventures.</p>
            </div>
        </div>
    </section>

    <section class="about-section">
        <h2>Our Mission</h3>
        <p>We aim to craft premium home textiles that bring comfort, beauty, and sustainability to every home, supporting local artisans and creating lasting memories.</p>
    </section>

    <section class="workshop-section">
        <div class="workshop-image">
            <img src="https://images.unsplash.com/photo-1558618427-f1e3b8c76ca-a2f5f9d09?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Our Workshop in Thamel">
        </div>
        <div class="workshop-text">
            <h3>Our Workshop</h3>
            <p>In Thamel’s vibrant heart, we collaborate with local artisans to bring our designs to life. Our workshop is filled with the hum of creativity as we select fabrics and perfect each piece.</p>
            <p>We prioritize local suppliers and sustainable materials to support our community and planet. It’s where tradition meets modern design.</p>
        </div>
    </section>

    <section class="about-section">
        <h3>Our Achievements</h4>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number">8000+</span>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-card">
                <span class="stat-number">160</span>
                <div class="stat-label">Product Designs</div>
            </div>
            <div class="stat-card">
                <span class="stat-number">4.9</span>
                <div class="stat-label">Customer Rating</div>
            </div>
            <div class="stat-card">
                <span class="stat-number">5</span>
                <div class="stat-label">Years of Craft</div>
            </div>
        </div>
    </section>

    <section class="about-section">
        <h3>What Makes Us Special</h4>
        <div class="values-grid">
            <div class="value-card">
                <h4>Sisterly Synergy</h4>
                <p>Sabita’s design flair and Aruna’s meticulous planning create products that are both stunning and practical.</p>
            </div>
            <div class="value-card">
                <h4>Eco-Conscious</h4>
                <p>We use sustainable materials and minimal packaging to protect our environment.</p>
            </div>
            <div class="value-card">
                <h4>Nepali Roots</h4>
                <p>As a Nepali brand, we celebrate local craftsmanship and traditions.</p>
            </div>
            <div class="value-card">
                <h4>Personal Care</h4>
                <p>We oversee every detail, ensuring each product is made with love.</p>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>
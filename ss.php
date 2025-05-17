<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Bedsheet Store</title>
      
</head>
<body>
    <header>
        <h1>Fabrique</h1>
        <p>Your one-stop shop for premium bedsheets, blankets and sleeping dress.</p>
    </header>
    <nav >
        <button onclick="goToPage()">Sign up</button>
        <script>
            function goToPage() {
                window.location.href = "login.php";  
            }
        </script>
        <a href="#products">Products</a>
        <a href="#contact">Contact</a>
        <a href="#aboutus">About us</a>
    </nav>
    <div class="container">
        <section id="products">
            <h2>Our Products</h2>
            <div class="products">
                <div class="product">
                    <img src="assets/bed1.jpg" alt="Bedsheet 1">
                    <h3>Bedsheets</h3>
                    <p>Variety of designs</p>
                    <p><strong></strong></p>
                
                </div>

                <div class="product">
                    <img src="assets/Totebag.jpg" alt="Bedsheet 2">
                    <h3>Totebag</h3>
                    <p>Hand Painted</p>
                    <p><strong></strong></p>
                </div>

                <div class="product">
                    <img src="assets/blanket1.jpg" alt="Bedsheet 3">
                    <h3>Blankets</h3>
                    <p>Nepali pure cotton</p>
                    <p><strong></strong></p>
                </div>
              
            </div>

         </section>   
     </div>
     
     <div class="container">
        <section id="products">
            <h2>Best Sellers</h2>
            <div class="products">
                <div class="product">
                    <img src="assets/bed1.jpg" alt="Bedsheet 1">
                    <h3>Nepali Cotton Bedsheet</h3>
                    <p>Rs 2500</p>
                
                </div>

                <div class="product">
                    <img src="assets/Totebag.jpg" alt="Bedsheet 2">
                    <h3>Hand Painted Totebag</h3>
                    <p>Rs 800</p>
                    <p><strong></strong></p>
                </div>

                <div class="product">
                    <img src="assets/blanket1.jpg" alt="Bedsheet 3">
                    <h3>Summer Quills</h3>
                    <p>Rs 1599</p>
                    <p><strong></strong></p>
                </div>
              
            </div>

         </section>   
     </div>

     <?php include 'footer.php'; ?>
         
</body>
</html>


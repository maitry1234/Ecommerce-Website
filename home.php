
<?php
session_start();
if (!isset($_SESSION["user"])) {
   header("Location: login.php");
   exit();
}

require_once "database.php";

$successMessage = "";
if (isset($_SESSION["login_success"])) {
    $successMessage = $_SESSION["login_success"];
    unset($_SESSION["login_success"]);
}

$paymentSuccessMessage = "";
if (isset($_SESSION["payment_success"])) {
    $paymentSuccessMessage = $_SESSION["payment_success"];
    unset($_SESSION["payment_success"]);
}

// Fetch categories
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
$categories = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Fabrique - Bedsheet Store</title>
    <style>
        /* Toast notification styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast-message {
            background: #ffffff;
            color: #333;
            padding: 16px 20px;
            margin-bottom: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-left: 4px solid #4CAF50;
            max-width: 350px;
            min-width: 280px;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            transform: translateX(400px);
            opacity: 0;
            animation: slideInRight 0.4s ease-out forwards;
        }
        .toast-message::before {
            content: "✓";
            display: inline-block;
            width: 20px;
            height: 20px;
            background: #4CAF50;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 12px;
            vertical-align: middle;
        }
        .toast-message .close-btn {
            position: absolute;
            top: 8px;
            right: 12px;
            background: none;
            border: none;
            font-size: 18px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toast-message .close-btn:hover {
            color: #666;
        }
        .toast-message.success {
            border-left-color: #4CAF50;
        }
        .toast-message.success::before {
            background: #4CAF50;
        }
        @keyframes slideInRight {
            0% { transform: translateX(400px); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            0% { transform: translateX(0); opacity: 1; }
            100% { transform: translateX(400px); opacity: 0; }
        }
        .toast-message.hiding {
            animation: slideOutRight 0.3s ease-in forwards;
        }
        /* Search bar styles */
          .search-container {
            position: relative;
            width: 300px;
            margin: 1px;
            display: inline-block;
            padding: auto;
        }
        .search-input {
            width: 100%;
            padding: 10px 40px 10px 35px;
            border: 2px solid #e1e1e1;
            border-radius: 25px;
            font-size: 14px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: rgba(255, 255, 255, 0.3);
        }
        .search-input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 8px rgba(76, 175, 80, 0.2);
        }
        .search-icon {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #666;
            font-size: 16px;
        }
        .clear-search {
            position: absolute;
            top: 10%;
            right: 10px;
            background: none;
            border: none;
            color: #999;
            font-size: 16px;
            cursor: pointer;
            display: none;
        }
       
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            margin-top: 5px;
            display: none;
        }
        .search-results.active {
            display: block;
        }
        .search-results h2 {
            color: #4CAF50;
            margin: 15px;
            font-size: 1.4em;
        }
        .search-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
        }
        .search-product {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 8px;
            text-align: left;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .search-product:hover {
            background: #e8f5e9;
            transform: translateX(5px);
        }
        .search-product img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .search-product h3 {
            color: #333;
            font-size: 1em;
            margin: 0;
            flex: 1;
        }
        .search-product p {
            color: #666;
            font-size: 0.85em;
            margin: 0;
        }
        .search-product .price {
            color: #4CAF50;
            font-weight: bold;
            font-size: 0.9em;
        }
        .search-message {
            color: #666;
            font-size: 0.9em;
            text-align: center;
            padding: 15px;
            margin: 0;
        }
/* Responsive adjustments */
@media (max-width: 768px) {
    .search-results {
        max-height: 350px;
        margin-top: 6px;
    }
    .search-grid {
        gap: 10px;
        padding: 12px;
    }
    .search-product {
        padding: 10px;
    }
    .search-product img {
        width: 60px;
        height: 60px;
    }
    .search-product h3 {
        font-size: 1em;
    }
    .search-product p {
        font-size: 0.85em;
    }
    .search-product .price {
        font-size: 0.95em;
    }
    .search-message {
        font-size: 0.9em;
        padding: 15px;
    }

        }
        /* Category and container styles */
        .container {
            text-align: center;
            padding: 10px;
        }
        .product {
            cursor: pointer;
            transition: transform 0.3s;
        }
        .product:hover {
            transform: translateY(-5px);
        }
        h2 {
            text-align: left;
            padding: 10px;
            color: #000000ff;
            font-size: 1.8em;
        }
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 10px;
        }
        .product {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-align: center;
        }
        .product img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .product h3 {
            color: #333;
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        .product p {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .search-container {
                width: 100%;
                max-width: 400px;
            }
        }
    </style>
</head>

<body>

<?php if (!empty($successMessage) || !empty($paymentSuccessMessage)) : ?>
    <div class="toast-container">
        <?php if (!empty($successMessage)) : ?>
            <div class="toast-message success" id="toastMessageLogin">
                <?php echo htmlspecialchars($successMessage); ?>
                <button class="close-btn" onclick="closeToast('toastMessageLogin')">×</button>
            </div>
        <?php endif; ?>
        <?php if (!empty($paymentSuccessMessage)) : ?>
            <div class="toast-message success" id="toastMessagePayment">
                <?php echo htmlspecialchars($paymentSuccessMessage); ?>
                <button class="close-btn" onclick="closeToast('toastMessagePayment')">×</button>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function closeToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.add('hiding');
                setTimeout(() => {
                    toast.parentElement.remove();
                }, 300);
            }
        }

        // Auto-hide toasts after 5 seconds
        setTimeout(function() {
            <?php if (!empty($successMessage)) : ?>
                closeToast('toastMessageLogin');
            <?php endif; ?>
            <?php if (!empty($paymentSuccessMessage)) : ?>
                closeToast('toastMessagePayment');
            <?php endif; ?>
        }, 5000);

        // Click anywhere on toast to close
        <?php if (!empty($successMessage)) : ?>
            document.getElementById('toastMessageLogin').addEventListener('click', function(e) {
                if (e.target.classList.contains('close-btn')) return;
                closeToast('toastMessageLogin');
            });
        <?php endif; ?>
        <?php if (!empty($paymentSuccessMessage)) : ?>
            document.getElementById('toastMessagePayment').addEventListener('click', function(e) {
                if (e.target.classList.contains('close-btn')) return;
                closeToast('toastMessagePayment');
            });
        <?php endif; ?>
    </script>
<?php endif; ?>

<header>
    <h1>Welcome to Fabrique<?php echo isset($_SESSION["full_name"]) ? ', ' . $_SESSION["full_name"] : ''; ?>!</h1>
    <p>Your one-stop shop for premium bedsheets, blankets and sleeping dress.</p>
</header>
<nav>
     <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" class="search-input" placeholder="Search for bedsheets, blankets..." autocomplete="off">
        <button class="clear-search" id="clearSearch">×</button>
        <div class="search-results" id="searchResults"></div>
    </div>
    <button onclick="goToPage()">Log out</button>
    <script>
        function goToPage() {
            window.location.href = "logout.php";  
        }
    </script>
    <a href="#products">Products</a>
    <a href="contact.php">Contact</a>
    <a href="about.php">About us</a>
   
</nav>
<div class="container">
    <section id="products">
        <h2>Product Categories</h2>
        <div class="products">
            <?php foreach ($categories as $category): ?>
            <div class="product" onclick="window.location.href='category.php?id=<?php echo $category['category_id']; ?>'">
                <img src="<?php echo $category['category_image']; ?>" alt="<?php echo $category['category_name']; ?>">
                <h3><?php echo $category['category_name']; ?></h3>
                <p><?php echo $category['category_description']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<script>
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const clearSearch = document.getElementById('clearSearch');
    const searchContainer = document.querySelector('.search-container');
    let searchTimeout = null;

    // Show/hide clear button
    searchInput.addEventListener('input', function() {
        clearSearch.style.display = this.value.trim() ? 'block' : 'none';
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Debounce search
        searchTimeout = setTimeout(() => {
            const searchTerm = this.value.trim();
            if (searchTerm.length === 0) {
                searchResults.innerHTML = '';
                searchResults.classList.remove('active');
                return;
            }

            fetch(`search_products.php?search=${encodeURIComponent(searchTerm)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.success && data.products.length > 0) {
                        const header = document.createElement('h2');
                        header.textContent = `Search Results for "${searchTerm}"`;
                        searchResults.appendChild(header);
                        const grid = document.createElement('div');
                        grid.className = 'search-grid';
                        data.products.forEach(product => {
                            const productDiv = document.createElement('div');
                            productDiv.className = 'search-product';
                            productDiv.onclick = () => window.location.href = `product.php?id=${product.product_id}`;
                            productDiv.innerHTML = `
                                <img src="${product.product_image}" alt="${product.product_name}">
                                <div>
                                    <h3>${product.product_name}</h3>
                                    <p>${product.category_name}</p>
                                    <p class="price">Rs ${product.price}</p>
                                </div>
                            `;
                            grid.appendChild(productDiv);
                        });
                        searchResults.appendChild(grid);
                        searchResults.classList.add('active');
                    } else {
                        searchResults.innerHTML = `
                            <h2>No Results Found</h2>
                            <p class="search-message">No products match your search for "${searchTerm}". Try another term!</p>
                        `;
                        searchResults.classList.add('active');
                    }
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                    searchResults.innerHTML = `
                        <h2>Search Error</h2>
                        <p class="search-message">An error occurred while searching: ${error.message}. Please try again.</p>
                    `;
                    searchResults.classList.add('active');
                });
        }, 300);
    });

    // Clear search input
    clearSearch.addEventListener('click', () => {
        searchInput.value = '';
        searchResults.innerHTML = '';
        searchResults.classList.remove('active');
        clearSearch.style.display = 'none';
        searchInput.focus();
    });

    // Close search results when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchContainer.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.innerHTML = '';
            searchResults.classList.remove('active');
        }
    });
</script>
<?php include 'footer.php'; ?>     
</body>
</html>
const products = [
    { id: 1, name: "Wireless Headphones", price: "$99", image: "https://via.placeholder.com/250x200?text=Headphones" },
    { id: 2, name: "Smart Watch", price: "$149", image: "https://via.placeholder.com/250x200?text=Smart+Watch" },
    { id: 3, name: "Gaming Mouse", price: "$59", image: "https://via.placeholder.com/250x200?text=Gaming+Mouse" },
    { id: 4, name: "Mechanical Keyboard", price: "$129", image: "https://via.placeholder.com/250x200?text=Keyboard" },
    { id: 5, name: "4K Monitor", price: "$299", image: "https://via.placeholder.com/250x200?text=4K+Monitor" },
    { id: 6, name: "Wireless Earbuds", price: "$79", image: "https://via.placeholder.com/250x200?text=Earbuds" },
    { id: 7, name: "Portable Speaker", price: "$89", image: "https://via.placeholder.com/250x200?text=Speaker" },
    { id: 8, name: "Laptop Stand", price: "$39", image: "https://via.placeholder.com/250x200?text=Laptop+Stand" },
    { id: 9, name: "External SSD", price: "$149", image: "https://via.placeholder.com/250x200?text=SSD" },
    { id: 10, name: "Smartphone Tripod", price: "$29", image: "https://via.placeholder.com/250x200?text=Tripod" }
];


// Function to Populate Product Cards
function generateProductCards() {
    const productContainer = document.getElementById("overviewContainer");

    // Generate product cards using template literals
    productContainer.innerHTML = products.map(product => `
        <div class="product-card swiper-slide">
        
        <div class = "imageContainer">
            <img src="${product.image}" alt="${product.name}" class="product-image">
        </div>
        <div class="product-title">
            <h2 >${product.name}</h2>
        </div>
            <p class="product-price">${product.price}</p>
            <button class="product-button">Add to Cart</button>
        </div>
    `).join('');
}

// Call function to generate products
window.onload = generateProductCards;

console.log("this from product card");

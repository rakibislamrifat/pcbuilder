//UPDATE PARTS COUNT/TOTAL IN HEADER 
window.addEventListener('DOMContentLoaded', () => {
    const partsCountEl = document.getElementById('parts_count');
    const partsTotalEl = document.getElementById('parts_total_price');

    const parts = localStorage.getItem('cartPartsCount') || 0;
    const total = localStorage.getItem('cartTotal') || 0;

    if (partsCountEl) partsCountEl.textContent = parts;
    if (partsTotalEl) partsTotalEl.textContent = `$${parseFloat(total).toFixed(2)}`;
});
  
// ADD TO BUILDER FUNCTIONALITY
document.querySelectorAll(".add-to-builder").forEach(button => {
    button.addEventListener("click", () => {
        const category = button.dataset.category?.toLowerCase() || 'other';

        const productData = {
            title: button.dataset.title,
            image: button.dataset.image,
            base: button.dataset.base,
            shipping: button.dataset.shipping,
            availability: button.dataset.availability,
            price: button.dataset.price,
            affiliateUrl: button.dataset.affiliateUrl,
            asin: button.dataset.asin,
            features: button.dataset.features,
            rating: button.dataset.rating,
            socket: button.dataset.socket,
            chipset: button.dataset.chipset,
            category: button.dataset.category
        };

        // Save product to localStorage
        localStorage.setItem(`pcbuild_${category}`, JSON.stringify(productData));

        // Category-specific logic
        switch (category) {
            case 'cpu':
                localStorage.setItem('selected_cpu_socket', productData.socket);
                localStorage.setItem('pcbuild_cpu', JSON.stringify(productData));
                break;
            case 'cpu cooler':
                localStorage.setItem('selected_cpu_cooler_socket', productData.socket);
                break;
            case 'motherboard':
                localStorage.setItem('selected_motherboard_socket', productData.socket);
                localStorage.setItem('selected_motherboard_chipset', productData.chipset);
                localStorage.setItem('pcbuild_motherboard', JSON.stringify(productData));
                break;
            case 'memory':
            case 'ram': // just in case you use either
                localStorage.setItem('selected_ram_type', productData.ram_type);
                localStorage.setItem('selected_ram_speed', productData.ram_speed);
                localStorage.setItem('pcbuild_ram', JSON.stringify(productData));
                break;
            default:
                break;
        }

        // UI update or redirect
        if (window.location.pathname.includes("/pcbuildparts/pc-build-parts/")) {
            if (typeof updateRow === "function") {
                updateRow(category, productData);
            }
        } else {
            window.location.href = "/pcbuildparts/pc-build-parts/";
        }
    });
});


// SCROLL TO TABLE ON PAGINATION
const params = new URLSearchParams(window.location.search);
if (params.has('pcbuild_page')) {
    const tableElement = document.getElementById("pcbuild-table");
    if (tableElement) {
        tableElement.scrollIntoView({ behavior: "smooth" });
    }
}

//MANUFATURER FILTER CHECKBOX
document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.filter-toggle');

    toggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const filterGroup = this.closest('.filter-group');
            const options = filterGroup.querySelector('.filter-options');

            if (options.style.display === 'none') {
                options.style.display = 'block';
                this.textContent = '−'; // minus sign
            } else {
                options.style.display = 'none';
                this.textContent = '+'; // plus sign
            }
        });
    });
});
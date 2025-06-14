<?php
if (!defined('ABSPATH')) {
    exit;
}

// Shortcode to render full PC Builder UI
function pcbuild_render_ui_shortcode() {
    ob_start();
    $cards = [
        'base',
        'shipping',
        'availability',
        'price',
        'where',
        'buy',
        'cancel'
    ];
  ?>

  <!-- Start of the PC Builder section which contains the UI for selecting and reviewing PC components -->
  <section id="buildOverview">

          <!-- Header section that includes tabs for navigating between component selection and overview -->
          <div class="partsHeader">
              <h3>Choose Your Parts</h3>
              <!-- <div class="navNtab">
                <ol>
                    <li>
                        <button class="tab-btn" onclick="openTab(event, 'tab1')">Choose Component</button>
                    </li>
                    <li>
                        <button class="tab-btn active" onclick="openTab(event, 'tab2')">Overview</button>
                    </li>
                </ol>
              </div> -->
          </div>
          
          <div class="container">

              <div class="tab_Content_Warpper">
                
                <!-- Wrapper for all the tabbed content, including component selection and build overview -->
                <div id="tab1" class="tab-content active">
                    
                    <div class="pcbuild-scroll-wrapper">
                        <div class="cardWarpper">
                            <!-- Header row that labels each column in the component selection table -->
                            <div class="row" id="row-th">
                                <div class="comp card"><span class="rowHeading">Component</span></div>
                                <div class="selection card"><span class="rowHeading">Selection</span></div>
                                <div class="base card"><span class="rowHeading">Base</span></div>
                                <div class="shipping card"><span class="rowHeading">Shipping</span></div>
                                <div class="availability card"><span class="rowHeading">Availability</span></div>
                                <div class="price card"><span class="rowHeading">Price</span></div>
                                <div class="where card"><span class="rowHeading">Where</span></div>
                                <div class="buy card"></div>
                                <div class="cancel card"></div>
                            </div>

                            <!-- Row for CPU selection and its associated pricing and availability information -->
                            <div class="row">
                              <div class="comp card">
                                  <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/cpu'); ?>">
                                      <span class="componentName">CPU</span>
                                  </a>
                              </div>

                              <div class="selection card">
                                  <button class="selectionBTN" data-redirect="<?php echo site_url('/products/cpu'); ?>">
                                      <span style="font-size:20px; ">&#43;</span>
                                      <span class="pc-part">Choose a CPU</span>
                                  </button>
                              </div>
                              <?php 
                              function isMobile() {
                                return preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']);
                              }
                              ?>
                              <?php if (!isMobile()): ?>
                                <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                              <?php endif; ?>
                            </div>

                            <!-- Row for CPU Cooler selection with dynamic pricing and vendor details -->
                            <div class="row">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/cpu-cooler'); ?>">
                                      <span class="componentName">CPU Cooler</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style="font-size:20px;">&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/cpu-cooler'); ?>">
                                            <span>Choose A CPU Cooler</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                  <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Row for selecting the motherboard and displaying related data -->
                            <div class="row">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/motherboard'); ?>">
                                        <span class="componentName">Motherboard</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style="font-size:20px;">&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/motherboard'); ?>">
                                            <span>Choose A Motherboard</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                  <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Memory selection row including price, promo, tax, etc. -->
                            <div class="row">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/memory'); ?>">
                                    <span class="componentName" data-key="ram">Memory</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style='font-size:20px;'>&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/memory'); ?>">
                                            <span>Choose A Memory</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                  <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Storage device selection row with associated info and controls -->
                            <div class="row">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/storage'); ?>">
                                        <span class="componentName">Storage</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style='font-size:20px;'>&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/storage'); ?>">
                                            <span>Choose A Storage</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                  <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Row for choosing a GPU or video card with details like price, tax, and source -->
                            <div class="row">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/video-card'); ?>">
                                        <span class="componentName">Video Card</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style='font-size:20px;'>&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/video-card'); ?>">
                                            <span>Choose A Video Card</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                  <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            <!-- PC case selection row along with its pricing and availability -->
                            <div class="row">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/case'); ?>">
                                        <span class="componentName">Case</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style='font-size:20px;'>&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/case'); ?>">
                                            <span>Choose A Case</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                  <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Row for selecting a PSU (Power Supply Unit) and its metadata -->
                            <div class="row">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/power-supply'); ?>">
                                        <span class="componentName">Power Supply</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style='font-size:20px;'>&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/power-supply'); ?>">
                                            <span>Choose A Power Supply</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                  <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Operating System selection row with related information -->
                            <div class="row">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/operating-system'); ?>">
                                        <span class="componentName">Operating System</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style='font-size:20px;'>&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/operating-system'); ?>">
                                            <span>Choose A Operating System</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                  <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Monitor selection row showing pricing, availability, and purchase options -->
                            <div class="row">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/monitor'); ?>">
                                        <span class="componentName">Monitor</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style='font-size:20px;'>&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/monitor'); ?>">
                                            <span>Choose A Monitor</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                  <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Sound Card selection row showing pricing, availability, and purchase options -->
                            <div class="row" style="display:none">
                                <div class="comp card">
                                    <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/sound-card'); ?>">
                                        <span class="componentName">Sound Cards</span>
                                    </a>
                                </div>
                                <div class="selection card">
                                    <button class="selectionBTN">
                                        <span style='font-size:20px;'>&#43;</span>
                                        <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/sound-card'); ?>">
                                            <span>Choose A Sound Card</span>
                                        </a>
                                    </button>
                                </div>
                                <?php if (!isMobile()): ?>
                                    <?php foreach ($cards as $card) echo "<div class='{$card} card'></div>"; ?>
                                <?php endif; ?>
                            </div>

                            
                            <div class="category-row">
                              <div class="category-label">Expansion Cards /<br> Networking</div>
                              <div class="category-items">
                                <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/sound-cards'); ?>">
                                    <span class="componentName">Sound Cards</span>
                                </a>
                                <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/wired-network-adapters'); ?>">
                                    <span class="componentName">Wired Network Adapters</span>
                                </a>
                                <a href="javascript:void(0)" class="pc-part" data-redirect="<?php echo site_url('/products/wireless-network-adapters'); ?>">
                                    <span class="componentName">Wireless Network Adapters</span>
                                </a>
                              </div>
                            </div>

                            <div style="width: 100%;"></div>

                            <div class="category-row">
                              <div class="category-label">Peripherals</div>
                              <div class="category-items">
                                <a href="#">Headphones</a>
                                <a href="#">Keyboards</a>
                                <a href="#">Mice</a>
                                <a href="#">Speakers</a>
                                <a href="#">Webcams</a>
                              </div>
                            </div>

                            <div class="category-row">
                              <div class="category-label">Accessories / Other</div>
                              <div class="category-items">
                                <a href="#">Case Accessories</a>
                                <a href="#">Case Fans</a>
                                <a href="#">Fan Controllers</a>
                                <a href="#">Thermal Compound</a>
                                <a href="#">External Storage</a>
                                <a href="#">Optical Drives</a>
                                <a href="#">UPS Systems</a>
                              </div>
                            </div>

                        </div>
                    </div>

                    <div id="products_total_price"></div>

                    <div id="checkoutWrapper" style="margin-top: 30px; text-align: right;">
                      <button id="checkoutAllBtn"
                              style="padding: 12px 24px; background: #ff9900; color: #fff; font-weight: bold; font-size: 16px; border: none; border-radius: 8px; cursor: pointer;">
                        Checkout All on Amazon
                      </button>
                    </div>

                </div>

                <div id="tab2" class="tab-content">
                  <div class="cardContiner" id="overviewContainer">
                    <!-- Selected product images will be injected here -->
                  </div>

                  <!-- Add this new div for product details -->
                  <div id="overviewProductDetails" style="margin-top: 30px;"></div>
                </div>

              </div>

          </div>

      </div>
  </section>

<style>
 .category-row {
  display: grid;
  grid-template-columns: 180px auto;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #ccc;
  width: 100%;
}

.category-row:not(:first-child) {
  display: block;
  clear: both;
}

.category-label {
  font-weight: bold;
  color: #0f9d8e;
  white-space: nowrap;
  padding-left: 4px;
  display: inline-block;
  width: 180px;
  vertical-align: middle;
}

.category-items {
  display: inline-block;
  vertical-align: middle;
  color: #003399;
  font-size: 14px;
}

.category-items a {
  margin-right: 20px;
  text-decoration: none;
  white-space: nowrap;
}

.category-items a:hover {
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 600px) {
  .category-row {
    display: block !important;
    clear: none;
    margin-top: 20px;
  }
  .category-label {
    width: 100%;
    margin-bottom: 6px;
    padding-left: 0;
  }
  .category-items {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }
}
</style>

<style>
  @media screen and (max-width: 900px) {
  .row {
    display: flex;
    flex-direction: column;
    padding: 12px;
    border-bottom: 1px solid #444;
  }

  .row .card {
    width: 100%;
    margin-bottom: 8px;
  }

  .row .selection,
  .row .base,
  .row .shipping,
  .row .availability,
  .row .price,
  .row .where,
  .row .buy,
  .row .cancel {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  #row-th {
    display: none !important;
  }

  .componentName {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 8px;
  }

  .selection img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
    margin-right: 12px;
  }

  .selection strong {
    font-size: 14px;
  }
}
</style>

<style>	
	@media (max-width: 768px) {
		#row-th {
			display: none !important;
		}
		#tab1 {
			padding: 0 !important;
		}
		.partsHeader {
			padding-top: 15px;
			padding-bottom: 5px;
		}
		.comp {
			margin-right: 30px!important;
		}
	}
</style>

<script>

document.addEventListener("DOMContentLoaded", function () {

  // Build checkout URL and open cart in Amazon
  document.getElementById("checkoutAllBtn").addEventListener("click", function () {
      const rows = document.querySelectorAll(".row");
      let asins = [];
      const associateTag = pcbuild_ajax_object.associate_tag;

      rows.forEach(row => {
        const categorySpan = row.querySelector(".componentName");
        if (categorySpan) {
          const category = categorySpan.textContent.trim().toLowerCase();
          const storedData = localStorage.getItem(`pcbuild_${category}`);
          if (storedData) {
            try {
              const product = JSON.parse(storedData);
              if (product.asin) {
                asins.push(product.asin);
              }
            } catch (e) {
              console.error(`Invalid JSON for ${category}`, e);
            }
          }
        }
      });

      if (asins.length === 0) {
        alert("Please select some parts before checking out.");
        return;
      }

      let cartUrl = `https://www.amazon.com/gp/aws/cart/add.html?AssociateTag=${associateTag}`;
      asins.forEach((asin, index) => {
        const num = index + 1;
        cartUrl += `&ASIN.${num}=${asin}&Quantity.${num}=1`;
      });

      window.open(cartUrl, "_blank");
    });
    
    // Redicting function
    document.querySelectorAll('[data-redirect]').forEach(el => {
        el.addEventListener("click", function () {
            const target = this.getAttribute("data-redirect");
            if (target) window.location.href = target;
        });
    });

});

document.addEventListener("DOMContentLoaded", function () {
  const partTriggers = document.querySelectorAll(".pc-part");
  const partModal = document.getElementById("cpuModal");
  const modalOverlay = document.getElementById("modalOverlay");
  const popupContent = document.getElementById("popupContent");

  // Restore selected parts from localStorage on page load
  const rows = document.querySelectorAll(".row");
  rows.forEach(row => {
    const categorySpan = row.querySelector(".componentName");
    if (categorySpan) {
      const category = categorySpan.textContent.trim().toLowerCase();
      const savedData = localStorage.getItem(`pcbuild_${category}`);
      if (savedData) {
        const parsedData = JSON.parse(savedData);
        updateRow(category, parsedData);
      }
    }
  });

  // Handle clicking on a component part to open modal
  if (partTriggers.length && partModal && modalOverlay && popupContent) {
    partTriggers.forEach(trigger => {
      trigger.addEventListener("click", function () {
        const row = trigger.closest(".row");
        const categorySpan = row.querySelector(".componentName");
        const category = categorySpan ? categorySpan.textContent.trim() : "CPU";

        // Save category for modal context
        partModal.setAttribute('data-current-category', category);

        // Show modal and overlay
        partModal.style.display = "block";
        modalOverlay.style.display = "block";

        // Load modal product list dynamically via AJAX
        fetch(pcbuild_ajax_object.ajax_url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'action=load_pcbuild_parts&category=' + encodeURIComponent(category)
        })
        .then(response => response.text())
        .then(html => {
          popupContent.innerHTML = html;
        });
      });
    });

    // Close modal if overlay is clicked
    modalOverlay.addEventListener("click", function () {
      closePartModal();
    });

  }

  // Handle "Add to Builder" button clicks
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("add-to-builder")) {
      const button = e.target;

      const productData = {
        title: button.dataset.title,
        image: button.dataset.image,
        base: button.dataset.base,
        //promo: button.dataset.promo,
        shipping: button.dataset.shipping,
        //tax: button.dataset.tax,
        availability: button.dataset.availability,
        price: button.dataset.price,
        affiliateUrl: button.dataset.affiliateUrl,
        asin: button.dataset.asin,
        rating: button.dataset.rating || ''
      };

      const category = button.dataset.category.toLowerCase();
      localStorage.setItem(`pcbuild_${category}`, JSON.stringify(productData));

      updateRow(category, productData);
      closePartModal();
    }
  });

  function updateRow(category, data) {
  
  const rows = document.querySelectorAll(".row");

  // Improved mobile detection with landscape support
  const isMobile = /Mobi|Android|iPhone/i.test(navigator.userAgent) ||
    (window.innerWidth <= 900 && window.innerHeight <= 500) ||
    (screen.orientation && screen.orientation.type.includes("landscape") && window.innerHeight < 500);

  rows.forEach(row => {
    const categorySpan = row.querySelector(".componentName");
    if (categorySpan && categorySpan.textContent.trim().toLowerCase() === category.toLowerCase()) {

      const base = data.base || '';
      const shipping = data.shipping || '';
      const availability = data.availability || '';
      const price = data.price || '';
      const affiliateUrl = data.affiliateUrl || '#';
      const title = data.title || '';
      const image = data.image || '';

      const truncatedTitle = title.length > 70 ? title.slice(0, 70) + "..." : title;
      const escapedTitle = truncatedTitle.replace(/</g, "&lt;").replace(/>/g, "&gt;");

      const logoUrl = `${pcbuild_ajax_object.uploads_url}/2025/04/amazon-logo.png`;

      if (isMobile) {
		const specialCases = {
			"cpu": "CPU",
			"cpu cooler": "CPU Cooler"
		};

		const formattedCategory = specialCases[category.toLowerCase()] ||
			category
				.split(" ")
				.map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
				.join(" ");

		document.getElementById("row-th").style.display = "none";
		document.getElementById("tab1").style.padding = "0";

		// MOBILE LAYOUT: Each label-value in its own row, label left, value right
		row.innerHTML = `
		<div class="componentName" style="font-size:20px; margin-top: 10px; margin-bottom: 20px;">
		  <strong>${formattedCategory}</strong>
		</div>

		<div class="selection" style="margin-bottom: 20px;">
		  <div style="display:flex; align-items:center; gap:12px;">
			<img src="${image}" alt="${escapedTitle}" style="width:50px; height:50px; object-fit:cover; border-radius:6px;">
			<div style="flex:1;"><strong style="font-size:14px;">${escapedTitle}</strong></div>
		  </div>
		</div>

		<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
		  <span style="color: #CCC;">Base</span>
		  <span>${base}</span>
		</div>
		<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
		  <span style="color: #CCC;">Shipping</span>
		  <span>${shipping}</span>
		</div>
		<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
		  <span style="color: #CCC;">Availability</span>
		  <span>${availability}</span>
		</div>
		<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
		  <span style="color: #CCC;">Price</span>
		  <span>${price}</span>
		</div>
		<div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: center;">
		  <span style="color: #CCC;">Where</span>
		  <a href="${affiliateUrl}" target="_blank" rel="nofollow noopener" style="display: inline-flex; align-items: center;">
			<img src="${logoUrl}" alt="Buy on Amazon" style="width:80px; height:auto;" />
		  </a>
		</div>
		<div style="display: flex; justify-content: space-between; margin-top: 10px; margin-bottom: 10px;">
		  <div class="buy" style="flex: 1;">
			<a href="${affiliateUrl}" target="_blank" rel="nofollow noopener">
			  <button style="background:#28a745; color:#fff; border:none; padding:6px 25px; border-radius:6px; cursor:pointer; height:36px; width: 100%;">Buy</button>
			</a>
		  </div>
		  <div class="cancel" style="flex: 1; margin-left: 10px;">
			<button class="remove-from-builder" data-category="${category}" style="background:none; border:1px solid #CCC; font-weight:bold; cursor:pointer; color:#ccc; border-radius:6px; padding:5px 10px; height:36px; width: 100%;">
			  <span style="font-size:20px; line-height:1;">&times;</span> Remove
			</button>
		  </div>
		</div>
		`;
	} else {
        // DESKTOP LAYOUT
        if (row.querySelector(".selection")) {
          row.querySelector(".selection").innerHTML = `
            <div class="product-selected" style="display: flex; align-items: center; gap: 12px;">
              <img src="${image}" alt="${escapedTitle}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
              <div style="flex: 1;"><strong style="font-size: 14px; display: block;">${escapedTitle}</strong></div>
            </div>`;
        }

        if (row.querySelector(".base")) row.querySelector(".base").textContent = base;
        if (row.querySelector(".shipping")) row.querySelector(".shipping").textContent = shipping;
        if (row.querySelector(".availability")) row.querySelector(".availability").textContent = availability;
        if (row.querySelector(".price")) row.querySelector(".price").textContent = price;

        if (row.querySelector(".where")) {
          row.querySelector(".where").innerHTML = `
            <a href="${affiliateUrl}" target="_blank" rel="nofollow noopener">
              <img src="${logoUrl}" alt="Buy on Amazon" style="width:80px; height:auto;" />
            </a>`;
        }

        if (row.querySelector(".buy")) {
          row.querySelector(".buy").innerHTML = `
            <a href="${affiliateUrl}" target="_blank" rel="nofollow noopener">
              <button style="background:#28a745; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer;">Buy</button>
            </a>`;
        }

        if (row.querySelector(".cancel")) {
          row.querySelector(".cancel").innerHTML = `
            <button class="remove-from-builder" data-category="${category}"
              style="background:none; border:none; font-size:30px; font-weight:bold; cursor:pointer; color:#ccc;">&times;</button>`;
        }
      }
    }
  });

  calculateTotalPrice();
}


  function calculateTotalPrice() {
    let total = 0;
    let parts = 0;

    const priceElements = document.querySelectorAll('.row .price');

    priceElements.forEach(priceEl => {
      const priceText = priceEl.textContent.replace(/[^0-9.]/g, ''); // Remove $ and commas
      const priceValue = parseFloat(priceText);
      if (!isNaN(priceValue)) {
        total += priceValue;
        parts++;
      }
    });

    // Store total in localStorage
    localStorage.setItem('cartTotal', total.toFixed(2));
    localStorage.setItem('cartPartsCount', parts);

    // Update builder list total (if exists)
    const totalDiv = document.getElementById('products_total_price');
    if (totalDiv) {
      totalDiv.style.cssText = 'margin-top: 20px; font-size: 18px; font-weight: bold; text-align: right;';
      totalDiv.textContent = `Total: $${total.toFixed(2)}`;
    }

    // Update parts count and total on another page
    const partsCountEl = document.getElementById('parts_count');
    const partsTotalEl = document.getElementById('parts_total_price');

    if (partsCountEl) partsCountEl.textContent = parts;
    if (partsTotalEl) partsTotalEl.textContent = `$${total.toFixed(2)}`;
  }


  // Remove item from builder and refresh
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-from-builder")) {
      const category = e.target.dataset.category.toLowerCase();
      localStorage.removeItem(`pcbuild_${category}`);
      location.reload(); // Optional: Use more elegant UI clearing
    }
  });
  
});
</script>


<script>
  // Function to handle tab switching
  function openTab(evt, tabId) {
    // Hide all tab contents and deactivate all tab buttons
    document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
    document.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("active"));

    // Show the selected tab content and activate the clicked tab button
    document.getElementById(tabId).classList.add("active");
    evt.currentTarget.classList.add("active");

    // If switching to the "Overview" tab, load stored product overview images
    if (tabId === "tab2") {
      loadOverviewImagesOnly();
    }
  }

  // Load only the overview image cards for each saved product in localStorage
  function loadOverviewImagesOnly() {
    const container = document.getElementById("overviewContainer");
    container.innerHTML = "";

    // Filter localStorage keys for saved PC build items
    const keys = Object.keys(localStorage).filter(key => key.startsWith("pcbuild_"));

    keys.forEach(key => {
      try {
        const data = JSON.parse(localStorage.getItem(key));
        const category = key.replace("pcbuild_", "");

        // Create image card for each category
        const imgCard = `
          <div onclick="showProductDetails('${category}')" style="
            width: 120px; height: 120px; border: 1px solid #ccc; border-radius: 10px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            margin: 10px; cursor: pointer; transition: 0.3s;" 
            onmouseover="this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'"
            onmouseout="this.style.boxShadow='none'">
            <img src="${data.image}" alt="${data.title}" style="width: 60px; height: 60px; object-fit: contain;">
            <p style="font-size: 12px; margin-top: 5px;">${category}</p>
          </div>
          <div id="details_${category}" class="product-details" style="margin-top:10px;"></div>
        `;
        container.insertAdjacentHTML("beforeend", imgCard);
      } catch (e) {
        console.error("Invalid localStorage data", key, e);
      }
    });
  }

  // Display detailed information about a selected product
  function showProductDetails(category) {
    const data = JSON.parse(localStorage.getItem(`pcbuild_${category}`));
    const detailsWrapper = document.getElementById('overviewProductDetails');

    if (!data || !detailsWrapper) return;

    // Build the product detail layout
    detailsWrapper.innerHTML = `
      <div class="product-detail-card" style="
        display: flex;
        flex-direction: row;
        gap: 24px;
        background: #ffffff;
        padding: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      ">
        <div class="product-detail-img" style="flex: 1; max-width: 320px;">
          <img src="${data.image}" alt="${data.title}" style="width: 100%; border-radius: 8px; object-fit: contain;">
        </div>

        <div class="product-detail-content" style="flex: 2;">
          <h2 style="font-size: 22px; color: #111; margin-bottom: 12px;">${data.title}</h2>

          <table class="product-specs" style="width: 100%; border-collapse: collapse; font-size: 14px; color: #333;">
            ${
              Object.entries(data).map(([key, val]) => {
                // Skip non-display keys
                if (['image', 'title', 'affiliateUrl', 'asin', 'promo', 'tax'].includes(key)) return '';

                // Special handling for "Rating"
                if (key === 'rating') {
                  return `
                    <tr style="border-bottom: 1px solid #f5f5f5;">
                      <td style="padding: 6px 10px; font-weight: 600; color: #444; width: 160px;">Seller Rating</td>
                      <td style="padding: 6px 10px; color: #f39c12;">${val}</td>
                    </tr>
                  `;
                }

                // Default product detail rows
                const formattedKey = key
                  .replace(/([A-Z])/g, ' $1')
                  .replace(/^./, str => str.toUpperCase())
                  .replace('About', 'About This Item');

                return `
                  <tr style="border-bottom: 1px solid #f5f5f5;">
                    <td style="padding: 6px 10px; font-weight: 600; color: #444; width: 160px;">${formattedKey}</td>
                    <td style="padding: 6px 10px; color: #222;">${val}</td>
                  </tr>
                `;
              }).join('')
            }
          </table>
        </div>
      </div>
    `;
  }

</script>

  <?php
  return ob_get_clean();
}
add_shortcode('pcbuild_ui', 'pcbuild_render_ui_shortcode');

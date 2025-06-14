<?php
function aawp_pcbuild_display_parts_case($atts) {
    $atts = shortcode_atts(array('category' => 'case'), $atts);
    $input_category = sanitize_title($atts['category']);
    
    // Define the category mapping
    $category_map = [
        'case' => 'Case',
        'pc-case' => 'Case',
    ];
    
    $category = $category_map[$input_category] ?? 'Case';
    
    // Create the transient cache key
    $transient_key = 'aawp_pcbuild_cache_' . md5($category);
    
    // Clear cache if admin and ?clear_cache=1 in URL
    if (is_user_logged_in() && current_user_can('manage_options') && isset($_GET['clear_cache'])) {
        delete_transient($transient_key);
    }
    
    // Try to get products from cache
    $products = get_transient($transient_key);
    
    // If not cached, fetch and cache the products
    if ($products === false) {
        $products = aawp_pcbuild_get_products($category);
        set_transient($transient_key, $products, 12 * HOUR_IN_SECONDS);
    }
    
    // If still no products, show error
    if (!is_array($products) || empty($products['SearchResult']['Items'])) {
        return '<p class="aawp-error">No products found or error fetching data. Please try again later.</p>';
    }    

    $all_items = $products['SearchResult']['Items'];
    $total_items = count($all_items);
    $items_per_page = 25;
    $current_page = isset($_GET['pcbuild_page']) ? max(1, intval($_GET['pcbuild_page'])) : 1;
    $total_pages = ceil($total_items / $items_per_page);
    $start = ($current_page - 1) * $items_per_page;
    $display_items = array_slice($all_items, $start, $items_per_page);

    ob_start();
    //include('parts-header.php');
    ?>
    <div style="background-color:#41466c; padding:20px; color:#fff; font-size:24px; font-weight:bold; text-align:center; margin-bottom:40px">
        Choose A <?php echo esc_html($category); ?>
    </div>
    
    <div style="width:90%; margin:0 auto; font-family:sans-serif;">
        <div class="pcbuilder-container" style="display:flex; gap:20px; margin-top:20px;">
           <!-- Sidebar -->

           <button class="pcbuild-sidebar-toggle">Filters</button>

            <div class="pcbuild-sidebar pcbuild-sidebar-mobile" style="width:250px; background:#f9f9f9; padding:20px; border-radius:8px;">
                <div style="margin-bottom:20px;"><strong>Part</strong> | <strong>List</strong></div>
                <div style="margin-bottom:20px;">
                    <div>PARTS: <strong id="parts_count"></strong></div>
                    <div>TOTAL: <strong id="parts_total_price"></strong></div>
                </div>
                <div class="filter-group">
                    <div class="filter-header">
                        <strong>PRICE</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="price-filter" style="display: block;">
                        <div id="price-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="price-min-label">$0</span>
                            <span id="price-max-label">$0</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>MANUFACTURER</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="manufacturer-filter">
                        <label><input type="checkbox" id="manufacturer-all" checked> All</label><br/>
                        <!-- Checkboxes will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>SELLER RATING</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="rating-filter">
                        <!-- Filters will be injected here -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>TYPE</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="type-filter">
                        <label><input type="checkbox" id="type-all" checked> All</label><br/>
                        <!-- Checkboxes for different types will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Color</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="color-filter">
                        <label><input type="checkbox" id="color-all" checked> All</label><br/>
                        <!-- Checkboxes for different colors will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Power Supply</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="powersupply-filter">
                        <label><input type="checkbox" id="powersupply-all" checked> All</label><br/>
                        <!-- Checkboxes for different power supplies will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Side Panel</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="sidepanel-filter">
                        <label><input type="checkbox" id="sidepanel-all" checked> All</label><br/>
                        <!-- Checkboxes for different side panels will be inserted here by JS -->
                    </div>
                </div>

            </div>

            <!-- Main Section -->
            <div class="pcbuilder-main" style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div id="total_products" style="font-weight:bold;"><?php echo $total_items; ?> Products</div>
                    <div>
                        <input type="text" id="pcbuild-search" placeholder="Search..."
                            style="padding:6px 10px; border-radius:6px; border:1px solid #ccc; margin-bottom: 15px" /><br>
                    </div>
                </div>

                <table id="pcbuild-table">
                    <thead>
                        <tr>
                            <th data-key="image">
                            </th>
                            <th class="sortable-header" data-key="name">
                                <span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Name</span>
                            </th>
                            <th class="sortable-header" data-key="type"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Type</span></th>
                            <th class="sortable-header" data-key="color"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Color</span></th>
                            <th class="sortable-header" data-key="power_supply"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Power Supply</span></th>
                            <th class="sortable-header" data-key="side_panel"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Side Panel</span></th>
                            <!-- <th class="sortable-header" data-key="volume"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> External Volume</span></th>
                            <th class="sortable-header" data-key="drive_bays"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Internal 3.5" Bays</span></th> -->
                            <th class="sortable-header" data-key="rating"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span>Seller Rating</span></th>
                            <th class="sortable-header" data-key="price"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Price</span></th>
                            <th style="padding:10px;">Action</th>
                        </tr>
                    </thead>
                    <?php include('rating-count.php'); ?>
                    <tbody>
                        <?php foreach ($display_items as $index => $item):
                            $row_bg = ($index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                            $asin = $item['ASIN'] ?? '';
                            $full_title = $item['ItemInfo']['Title']['DisplayValue'] ?? 'Unknown Product';
                            $title = esc_html(implode(' ', array_slice(explode(' ', $full_title), 0, 4)));
                            $raw_title = esc_attr($full_title);
                            $image = $item['Images']['Primary']['Large']['URL'] ?? $item['Images']['Primary']['Medium']['URL'] ?? $item['Images']['Primary']['Small']['URL'] ?? '';
                            $raw_image = esc_url($image);
                            $price = $item['Offers']['Listings'][0]['Price']['DisplayAmount'] ?? 'N/A';
                            $base_price = $price;
                            $availability = $item['Offers']['Listings'][0]['Availability']['Message'] ?? 'In Stock';
                            $product_url = $item['DetailPageURL'] ?? '#';
                            $features = $item['ItemInfo']['Features']['DisplayValues'] ?? [];
                            $features_string = implode(' ', $features);
                            $manufacturer = $item['ItemInfo']['ByLineInfo']['Manufacturer']['DisplayValue'] ?? 'Unknown';
                            $sellerCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? 'Unknown';
                            $sellerRating = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackRating'] ?? 'Unknown';

                            preg_match('/(ATX\s?Mid\s?Tower|Full\s?Tower|Mini\s?ITX|Micro\s?ATX|Small\s?Form\s?Factor|ITX|E-ATX|Cube|Tower)/i', $full_title . ' ' . $features_string, $type_match);
                            preg_match('/(Black|White|Red|Blue|Gray|Silver|ARGB|RGB)/i', $features_string, $color_match);
                            
                            $power_supply = '-';
                            $known_mounts = ['Bottom Mount', 'Rear Mount', 'Top Mount', 'Front Mount', 'Mid Tower', 'Side Mount'];

                            foreach ($known_mounts as $mount) {
                                if (stripos($features_string, $mount) !== false) {
                                    $power_supply = $mount;
                                    break;
                                }
                            }

                            preg_match('/(Tempered Glass|Mesh|Acrylic|Window)/i', $features_string, $panel_match);
                            preg_match('/(\d+(\.\d+)?)\s?(L|liters|cu\.?\s?ft)/i', $features_string, $volume_match);
                            preg_match('/(\d+)[\s-]?bay/i', $features_string, $bay_match);

                            $type = isset($type_match[1]) ? ucwords(strtolower($type_match[1])) : '-';
                            $color = $color_match[1] ?? '-';
                            $side_panel = $panel_match[1] ?? '-';
                            //$volume = isset($volume_match[1]) ? $volume_match[1] . ' L' : '-';
                            //$drive_bays = $bay_match[1] ?? '-';
                            $rating_count = display_rating_and_count($sellerRating, $sellerCount);
                        ?>
                        <!-- Mobile-responsive row structure to match the image exactly -->
                        <tr class="product-row" style="background-color: <?php echo $row_bg; ?>">
                        <!-- Regular desktop view -->
                        <?php if(true): // Always show desktop version, it will be hidden via CSS on mobile ?>
                            <td style="padding: 10px 0 10px 10px; width: 150px!important" title="<?php echo $raw_title; ?>">
                                <img src="<?php echo $raw_image; ?>" alt="<?php echo $title; ?>" style="width:125px; height:125px; border-radius:4px;" />
                            </td>
                            <td style="font-weight:800;"><?php echo $title; ?></td>
                            <td style="padding:10px;"><?php echo esc_html($type); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($color); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($power_supply); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($side_panel); ?></td>
                            <!-- <td style="padding:10px;"><?php //echo esc_html($volume); ?></td>
                            <td style="padding:10px;"><?php //echo esc_html($drive_bays); ?></td> -->
                            <td style="padding:10px;" data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"><?php echo $rating_count; ?></td>
                            <td style="padding:10px;"><?php echo esc_html($price); ?></td>
                            <td style="padding:10px;">
                                <button class="add-to-builder"
                                    data-asin="<?php echo esc_attr($asin); ?>"
                                    data-title="<?php echo esc_attr($full_title); ?>"
                                    data-image="<?php echo esc_url($image); ?>"
                                    data-base="<?php echo esc_attr($base_price); ?>"
                                    data-promo=""
                                    data-shipping="FREE"
                                    data-tax=""
                                    data-availability="<?php echo esc_attr($availability); ?>"
                                    data-price="<?php echo esc_attr($base_price); ?>"
                                    data-category="<?php echo esc_attr($category); ?>"
                                    data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                    data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                    data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"
                                    data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                    data-type="<?php echo esc_attr($type); ?>"
                                    data-color="<?php echo esc_attr($color); ?>"
                                    data-power-supply="<?php echo esc_attr($power_supply); ?>"
                                    data-side-panel="<?php echo esc_attr($side_panel); ?>"
                                    style="padding:10px 18px; background-color:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer;">
                                    <?php _e('Add', 'aawp-pcbuild'); ?>
                                </button>
                            </td>
                            <?php endif; ?>
                         
                         <!-- Mobile view structure - will be shown via CSS media queries -->
                         <td class="product-cell mobile-only" style="display:none;">
                             <div class="image-container">
                                 <img src="<?php echo $raw_image; ?>" alt="<?php echo $title; ?>" class="product-image">
                             </div>
                             <div class="product-info">
                                 <div class="product-name"><?php echo $title; ?></div>
                                 <div class="star-rating"><span class="review-count"><?php echo $rating_count; ?></span></div>
                     
                                 <!-- Specs container - exactly matching the image layout -->
                                 <div class="specs-container">
                                     <div class="spec-group">
                                         <div class="spec-label">Core Count</div>
                                         <div class="spec-value"><?php echo esc_html($core_count); ?></div>
                                     </div>
                                     <div class="spec-group">
                                         <div class="spec-label">Base Clock</div>
                                         <div class="spec-value"><?php echo $base_clock !== '-' ? $base_clock . ' GHz' : '-'; ?></div>
                                     </div>
                                     <div class="spec-group">
                                         <div class="spec-label">Boost Clock</div>
                                         <div class="spec-value"><?php echo $boost_clock !== '-' ? $boost_clock . ' GHz' : '-'; ?></div>
                                     </div>
                                     <div class="spec-group">
                                         <div class="spec-label">Microarchitecture</div>
                                         <div class="spec-value"><?php echo esc_html($microarch); ?></div>
                                     </div>
                                     <div class="spec-group">
                                        <div class="spec-label">Price</div>
                                        <div class="spec-value"><?php echo esc_html($price); ?></div>
                                    </div>
                                 </div>
                             </div>
                         </td>
                         
                         <!-- Price and Add button row for mobile -->
                         <td class="price-action-row mobile-only" style="display:none;">
                             <div class="action-cell">
                             <button class="add-to-builder"
                                    data-asin="<?php echo esc_attr($asin); ?>"
                                    data-title="<?php echo esc_attr($full_title); ?>"
                                    data-image="<?php echo esc_url($image); ?>"
                                    data-base="<?php echo esc_attr($base_price); ?>"
                                    data-promo=""
                                    data-shipping="FREE"
                                    data-tax=""
                                    data-availability="<?php echo esc_attr($availability); ?>"
                                    data-price="<?php echo esc_attr($base_price); ?>"
                                    data-category="<?php echo esc_attr($category); ?>"
                                    data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                    data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                    data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"
                                    data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                    data-type="<?php echo esc_attr($type); ?>"
                                    data-color="<?php echo esc_attr($color); ?>"
                                    data-power-supply="<?php echo esc_attr($power_supply); ?>"
                                    data-side-panel="<?php echo esc_attr($side_panel); ?>">
                                    <?php echo esc_html__('Add', 'aawp-pcbuild'); ?>
                                </button>
 
                             </div>
                         </td>
                     </tr>
                 <?php endforeach; ?>
             </tbody>
         </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div style="margin-top: 20px; text-align: center;">
                        <?php for ($i = 1; $i <= $total_pages; $i++):
                            $url = add_query_arg('pcbuild_page', $i);
                            $is_active = ($i === $current_page);
                        ?>
                            <a href="<?php echo esc_url($url); ?>"
                                style="margin: 0 5px; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none;
                                <?php echo $is_active ? 'background-color: #007bff; color: white;' : 'color: #007bff;'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<style>
    .spec-label {
    font-weight: 700!important;
    color: #000;
    font-size: 14px!important;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const ratingRanges = {
        "5": { min: 4.5, max: 5.0 },
        "4": { min: 3.5, max: 4.4 },
        "3": { min: 2.5, max: 3.4 },
        "unrated": "unrated"
    };

    const ratingFilterContainer = document.getElementById("rating-filter");
    const productRows = document.querySelectorAll("#pcbuild-table tbody tr");

    // Define rating filter options
    const ratingOptions = [
        { value: "all", label: "All" },
        { value: "5", label: "★★★★★" },
        { value: "4", label: "★★★★☆" },
        { value: "3", label: "★★★☆☆" },
        { value: "unrated", label: "Unrated" }
    ];

    // Inject rating checkboxes
    ratingFilterContainer.innerHTML = "";
    ratingOptions.forEach(opt => {
        const label = document.createElement("label");
        const input = document.createElement("input");
        input.type = "checkbox";
        input.name = "rating";
        input.value = opt.value;
        if (opt.value === "all") input.checked = true;
        label.style.display = "block";
        label.style.margin = "4px 0";
        label.appendChild(input);
        label.insertAdjacentHTML("beforeend", ` ${opt.label}`);
        ratingFilterContainer.appendChild(label);
    });

    const ratingFilterInputs = document.querySelectorAll('#rating-filter input[type="checkbox"]');

    function applyZebraStriping() {
        let visibleIndex = 0;
        productRows.forEach(row => {
            if (row.style.display !== "none") {
                row.style.backgroundColor = (visibleIndex % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                visibleIndex++;
            } else {
                row.style.backgroundColor = ""; // Reset background for hidden rows
            }
        });
    }

    function applyRatingFilter() {
        const selected = Array.from(ratingFilterInputs)
            .filter(input => input.checked && input.value !== "all")
            .map(input => input.value);

        const isAllChecked = document.querySelector('#rating-filter input[value="all"]').checked;
        let visibleCount = 0;

        productRows.forEach((row) => {
            const ratingAttr = row.querySelector(".add-to-builder")?.getAttribute("data-rating");
            const rating = parseFloat(ratingAttr);
            const isRated = !isNaN(rating);
            let visible = false;

            if (isAllChecked) {
                visible = true;
            } else if (selected.includes("unrated") && !isRated) {
                visible = true;
            } else if (isRated) {
                for (const value of selected) {
                    const range = ratingRanges[value];
                    if (range && typeof range === "object" && rating >= range.min && rating <= range.max) {
                        visible = true;
                        break;
                    }
                }
            }

            row.style.display = visible ? "" : "none";

            // Zebra striping
            if (visible) {
                visibleCount++;
            }
        });

        applyZebraStriping(); // Apply zebra striping after filtering
    }

    // Handle 'All' checkbox
    document.querySelector('#rating-filter input[value="all"]').addEventListener("change", function () {
        if (this.checked) {
            ratingFilterInputs.forEach(input => {
                if (input.value !== "all") input.checked = false;
            });
        }
        applyRatingFilter();
    });

    // Handle other checkboxes
    ratingFilterInputs.forEach(input => {
        if (input.value !== "all") {
            input.addEventListener("change", function () {
                if (this.checked) {
                    document.querySelector('#rating-filter input[value="all"]').checked = false;
                }
                applyRatingFilter();
            });
        }
    });

    applyRatingFilter(); // Initial render
});
</script>


<script>
// Side Panel Filtering for Storage Page
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("sidepanel-filter");
    const allCheckbox = document.getElementById("sidepanel-all");
    const sidePanelSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique side panels (case-insensitive) from data-side-panel attribute
    tableRows.forEach(row => {
        const sidePanel = row.querySelector("button.add-to-builder")?.dataset.sidePanel || "Unknown";
        sidePanelSet.add(sidePanel.trim().toLowerCase());
    });

    const sidePanels = Array.from(sidePanelSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    sidePanels.forEach(sidePanel => {
        const label = document.createElement("label");
        const displayName = sidePanel.charAt(0).toUpperCase() + sidePanel.slice(1);
        label.innerHTML = `<input type="checkbox" name="sidepanel" value="${sidePanel}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none';
        filterContainer.appendChild(el);
    });

    // Toggle link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='sidepanel']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applySidePanelFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='sidepanel']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const sidePanel = row.querySelector("button.add-to-builder")?.dataset.sidePanel.trim().toLowerCase();
            row.style.display = selected.includes(sidePanel) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='sidepanel']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applySidePanelFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "sidepanel") {
            applySidePanelFilter();
        }
    });

    // Show more/less toggle
    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) el.style.display = expanded ? "block" : "none";
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filter application
    applySidePanelFilter();
});
</script>

<script>
// Power Supply Filtering for Storage Page
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("powersupply-filter");
    const allCheckbox = document.getElementById("powersupply-all");
    const powersupplySet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique power supplies (case-insensitive) from data-power-supply attribute
    tableRows.forEach(row => {
        const powerSupply = row.querySelector("button.add-to-builder")?.dataset.powerSupply || "Unknown";
        powersupplySet.add(powerSupply.trim().toLowerCase());
    });

    const powerSupplies = Array.from(powersupplySet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    powerSupplies.forEach(powerSupply => {
        const label = document.createElement("label");
        const displayName = powerSupply.charAt(0).toUpperCase() + powerSupply.slice(1);
        label.innerHTML = `<input type="checkbox" name="powersupply" value="${powerSupply}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none';
        filterContainer.appendChild(el);
    });

    // Toggle link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='powersupply']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyPowerSupplyFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='powersupply']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const powerSupply = row.querySelector("button.add-to-builder")?.dataset.powerSupply.trim().toLowerCase();
            row.style.display = selected.includes(powerSupply) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='powersupply']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyPowerSupplyFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "powersupply") {
            applyPowerSupplyFilter();
        }
    });

    // Show more/less toggle
    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) el.style.display = expanded ? "block" : "none";
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filter application
    applyPowerSupplyFilter();
});
</script>

<script>
// Color Filtering for Storage Page
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("color-filter");
    const allCheckbox = document.getElementById("color-all");
    const colorSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique colors (case-insensitive)
    tableRows.forEach(row => {
        const color = row.querySelector("button.add-to-builder")?.dataset.color || "Unknown";
        colorSet.add(color.trim().toLowerCase());
    });

    const colors = Array.from(colorSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    colors.forEach(color => {
        const label = document.createElement("label");
        const displayName = color.charAt(0).toUpperCase() + color.slice(1);
        label.innerHTML = `<input type="checkbox" name="color" value="${color}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none';
        filterContainer.appendChild(el);
    });

    // Toggle link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='color']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyColorFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='color']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const color = row.querySelector("button.add-to-builder")?.dataset.color.trim().toLowerCase();
            row.style.display = selected.includes(color) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='color']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyColorFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "color") {
            applyColorFilter();
        }
    });

    // Show more/less toggle
    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) el.style.display = expanded ? "block" : "none";
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filter application
    applyColorFilter();
});
</script>


<script>
// Type Filtering for Storage Page
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("type-filter");
    const allCheckbox = document.getElementById("type-all");
    const typeSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique types (case-insensitive)
    tableRows.forEach(row => {
        const type = row.querySelector("button.add-to-builder")?.dataset.type || "Unknown";
        typeSet.add(type.trim().toLowerCase());
    });

    const types = Array.from(typeSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    types.forEach(type => {
        const label = document.createElement("label");
        const displayName = type.charAt(0).toUpperCase() + type.slice(1);
        label.innerHTML = `<input type="checkbox" name="type" value="${type}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none';
        filterContainer.appendChild(el);
    });

    // Toggle link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='type']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyTypeFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='type']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const type = row.querySelector("button.add-to-builder")?.dataset.type.trim().toLowerCase();
            row.style.display = selected.includes(type) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='type']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyTypeFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "type") {
            applyTypeFilter();
        }
    });

    // Show more/less toggle
    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) el.style.display = expanded ? "block" : "none";
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filter application
    applyTypeFilter();
});
</script>


<script>
// Manufacturer Filtering for Storage Page
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("manufacturer-filter");
    const allCheckbox = document.getElementById("manufacturer-all");
    const manufacturerSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique manufacturers (case-insensitive)
    tableRows.forEach(row => {
        const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer || "Unknown";
        manufacturerSet.add(manufacturer.trim().toLowerCase());
    });

    const manufacturers = Array.from(manufacturerSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    manufacturers.forEach(manufacturer => {
        const label = document.createElement("label");
        const displayName = manufacturer.charAt(0).toUpperCase() + manufacturer.slice(1);
        label.innerHTML = `<input type="checkbox" name="manufacturer" value="${manufacturer}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none';
        filterContainer.appendChild(el);
    });

    // Toggle link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='manufacturer']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyManufacturerFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='manufacturer']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer.trim().toLowerCase();
            row.style.display = selected.includes(manufacturer) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='manufacturer']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyManufacturerFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "manufacturer") {
            applyManufacturerFilter();
        }
    });

    // Show more/less toggle
    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) el.style.display = expanded ? "block" : "none";
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filter application
    applyManufacturerFilter();
});
</script>

<script>
// Price Filtering
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("price-slider");
    const minLabel = document.getElementById("price-min-label");
    const maxLabel = document.getElementById("price-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));
    const prices = rows.map(row => {
        // Assuming price is in the 6th column (index 7)
        const priceText = row.querySelector("td:nth-child(7)")?.textContent.replace(/[^0-9.]/g, '') || "0";
        return parseFloat(priceText) || 0;
    });

    const minPrice = Math.floor(Math.min(...prices));
    const maxPrice = Math.ceil(Math.max(...prices));
    let currentMin = minPrice;
    let currentMax = maxPrice;

    // Set default labels
    minLabel.textContent = `$${minPrice}`;
    maxLabel.textContent = `$${maxPrice}`;

    // Create 2 sliders
    sliderContainer.innerHTML = `
        <input type="range" class="min-range-bg" id="min-price" min="${minPrice}" max="${maxPrice}" value="${minPrice}" step="1" style="width: 100%;">
        <input type="range" class="max-range-bg" id="max-price" min="${minPrice}" max="${maxPrice}" value="${maxPrice}" step="1" style="width: 100%; margin-top: 10px;">
    `;

    const minSlider = document.getElementById("min-price");
    const maxSlider = document.getElementById("max-price");

    function applyZebraStripes() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function filterByPrice() {
        const minVal = parseFloat(minSlider.value);
        const maxVal = parseFloat(maxSlider.value);
        currentMin = minVal;
        currentMax = maxVal;

        minLabel.textContent = `$${minVal}`;
        maxLabel.textContent = `$${maxVal}`;

        rows.forEach(row => {
            const priceText = row.querySelector("td:nth-child(7)")?.textContent.replace(/[^0-9.]/g, '') || "0";
            const price = parseFloat(priceText) || 0;

            row.style.display = (price >= minVal && price <= maxVal) ? "" : "none";
        });

        applyZebraStripes();
    }

    minSlider.addEventListener("input", () => {
        if (parseFloat(minSlider.value) > parseFloat(maxSlider.value)) {
            minSlider.value = maxSlider.value;
        }
        filterByPrice();
    });

    maxSlider.addEventListener("input", () => {
        if (parseFloat(maxSlider.value) < parseFloat(minSlider.value)) {
            maxSlider.value = minSlider.value;
        }
        filterByPrice();
    });

    // Initial filter apply
    filterByPrice();
});
</script>


<script>
    // Sorting Logic
    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById("pcbuild-table");
        const headers = table.querySelectorAll(".sortable-header");

        let currentSort = { key: null, direction: 'asc' };

        headers.forEach(header => {
            header.addEventListener('click', function () {
                const key = this.dataset.key;
                currentSort.direction = (currentSort.key === key && currentSort.direction === 'asc') ? 'desc' : 'asc';
                currentSort.key = key;

                // Reset header icons
                headers.forEach(h => {
                    h.innerHTML = `&#9654; ${h.textContent.trim().replace(/^▲|▼|\▶/, '')}`;
                });

                // Show arrow direction on clicked header
                this.innerHTML = `${currentSort.direction === 'asc' ? '▲' : '▼'} ${this.textContent.trim().replace(/^▲|▼|\▶/, '')}`;

                // Sort rows based on clicked column
                sortTableByKey(key, currentSort.direction);
            });
        });

        function sortTableByKey(key, direction) {
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((a, b) => {
                const getText = (row, key) => {
                    const index = getColumnIndex(key);
                    const cell = row.querySelector(`td:nth-child(${index})`);
                    if (!cell) return '';

                    if (key === 'rating') {
                        return parseFloat(cell.dataset.rating || '0');
                    }

                    if (key === 'price') {
                        const num = parseFloat(cell.innerText.replace(/[^0-9.]/g, ''));
                        return isNaN(num) ? 0 : num;
                    }

                    return cell.innerText.trim().toLowerCase();
                };

                const valA = getText(a, key);
                const valB = getText(b, key);

                if (typeof valA === 'number' && typeof valB === 'number') {
                    return direction === 'asc' ? valA - valB : valB - valA;
                }

                return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            });

            // Zebra striping
            rows.forEach((row, i) => {
                row.style.backgroundColor = (i % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                tbody.appendChild(row);
            });
        }

        function getColumnIndex(key) {
            const mapping = {
                name: 2,
                type: 3,
                color: 4,
                power_supply: 5,
                side_panel: 6,
                rating: 7,
                price: 8
                // Column 8 (Action) is not sortable
            };
            return mapping[key];
        }
    });
</script>

<script>
    // Searching logic with zebra striping
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("pcbuild-search");
        const tableRows = document.querySelectorAll("#pcbuild-table tbody tr");

        function applyZebraStriping() {
            const visibleRows = Array.from(tableRows).filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        searchInput.addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();

            tableRows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });

            applyZebraStriping();
        });

        // Initial stripe in case all are visible initially
        applyZebraStriping();
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const sidebarToggle = document.querySelector('.pcbuild-sidebar-toggle');
  const sidebar = document.querySelector('.pcbuild-sidebar-mobile');

  function closeSidebar() {
    sidebar.classList.remove('open');
    document.removeEventListener('click', handleOutsideClick);
  }

  function handleOutsideClick(event) {
    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
      closeSidebar();
    }
  }

  sidebarToggle.addEventListener('click', function (event) {
    event.stopPropagation();
    if (sidebar.classList.contains('open')) {
      closeSidebar();
    } else {
      sidebar.classList.add('open');
      setTimeout(() => {
        document.addEventListener('click', handleOutsideClick);
      }, 0);
    }
  });

  sidebar.addEventListener('click', function (event) {
    event.stopPropagation();
  });

  // Add classes for android or ios devices to body for further CSS if needed
  const ua = navigator.userAgent || navigator.vendor || window.opera;
  if (/android/i.test(ua)) {
    document.body.classList.add('android-mobile');
  } else if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
    document.body.classList.add('ios-mobile');
  }

  // Mobile/desktop table view toggle
  function setupMobileView() {
    const rows = document.querySelectorAll('#pcbuild-table tbody tr');

    function isMobile() {
      const ua = navigator.userAgent;
      return (/Mobi|Android|iPhone|iPad|iPod/i.test(ua)) || window.innerWidth <= 768;
    }

    function updateView() {
      const mobile = isMobile();

      document.querySelectorAll('.mobile-only').forEach(el => {
        el.style.display = mobile ? 'flex' : 'none';
      });

      rows.forEach(row => {
        row.querySelectorAll('td:not(.mobile-only)').forEach(td => {
          td.style.display = mobile ? 'none' : '';
        });
      });

      const thead = document.querySelector('#pcbuild-table thead');
      if (thead) {
        thead.style.display = mobile ? 'none' : '';
      }
    }

    updateView();
    window.addEventListener('resize', updateView);
  }

  setupMobileView();
});
</script>
    
    <?php
    //include('parts-footer.php');
    return ob_get_clean();
}
add_shortcode('pcbuild_parts_case', 'aawp_pcbuild_display_parts_case');

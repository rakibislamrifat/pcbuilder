<?php
function aawp_pcbuild_display_parts_monitor($atts) {
    $atts = shortcode_atts(array('category' => 'monitor'), $atts);
    $input_category = sanitize_title($atts['category']);
    
    $category_map = [
        'monitor' => 'Monitor',
    ];
    
    $category = $category_map[$input_category] ?? 'Monitor';
    
    // Create transient key (consistent naming)
    $transient_key = 'aawp_pcbuild_cache_' . md5($category);
    
    // Clear cache if admin and ?clear_cache=1 in URL
    if (is_user_logged_in() && current_user_can('manage_options') && isset($_GET['clear_cache'])) {
        delete_transient($transient_key);
    }
    
    // Try to get products from cache
    $products = get_transient($transient_key);
    
    // If no cached products, fetch and cache them
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
    $items_per_page = 100;
    $current_page = isset($_GET['pcbuild_page']) ? max(1, intval($_GET['pcbuild_page'])) : 1;
    $total_pages = ceil($total_items / $items_per_page);
    $start = ($current_page - 1) * $items_per_page;
    $display_items = array_slice($all_items, $start, $items_per_page);

    ob_start();
    include('parts-header.php');
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
                        <strong>Screen Size</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="screen-size-filter" style="display: block;">
                        <div id="screen-size-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="screen-size-min-label">0"</span>
                            <span id="screen-size-max-label">0"</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Resolution</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="resolution-filter">
                        <label><input type="checkbox" id="resolution-all" checked> All</label><br/>
                        <!-- Checkboxes for different resolutions will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Refresh Rate</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="refresh-rate-filter" style="display: block;">
                        <div id="refresh-rate-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="refresh-rate-min-label">0</span>
                            <span id="refresh-rate-max-label">0</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Panel Type</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="panel-type-filter">
                        <label><input type="checkbox" id="panel-type-all" checked> All</label><br/>
                        <!-- Checkboxes for different panel types will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Aspect Ratio</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="aspect-ratio-filter">
                        <label><input type="checkbox" id="aspect-ratio-all" checked> All</label><br/>
                        <!-- Checkboxes for different aspect ratios will be inserted here by JS -->
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
                            <th class="sortable-header" data-key="screen-size"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Screen Size</span></th>
                            <th class="sortable-header" data-key="resolution"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Resolution</span></th>
                            <th class="sortable-header" data-key="refresh-rate"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Refresh Rate</span></th>
                            <!-- <th class="sortable-header" data-key="response-time"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Response Time (G2G)</span></th> -->
                            <th class="sortable-header" data-key="panel-type"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Panel Type</span></th>
                            <th class="sortable-header" data-key="aspect-ratio"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Aspect Ratio</span></th>
                            <th class="sortable-header" data-key="rating"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span>Seller Rating</span></th>
                            <th class="sortable-header" data-key="price"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Price</span></th>
                            <th>Action</th>
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
                        $combined_string = $features_string . ' ' . $full_title;
                        $manufacturer = $item['ItemInfo']['ByLineInfo']['Manufacturer']['DisplayValue'] ?? 'Unknown';
                        $sellerCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? 'Unknown';
                        $sellerRating = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackRating'] ?? 'Unknown';

                        // Extract monitor attributes
                        preg_match('/(\d+(\.\d+)?)\s*(inches?|")/i', $combined_string, $screen_size_match);
                        preg_match('/(\d{4}x\d{4}|\d{3}0p)/i', $combined_string, $resolution_match);
                        preg_match('/(\d+(\.\d+)?\s*Hz)/i', $combined_string, $refresh_rate_match);
                        //preg_match('/(\d+\s*ms)/i', $combined_string, $response_time_match);
                        preg_match('/(IPS|TN|VA|OLED)/i', $combined_string, $panel_type_match);
                        preg_match('/(16:9|21:9|4:3|32:9)/i', $combined_string, $aspect_ratio_match);
                        preg_match('/\b(16:9|21:9|32:9|16:10|4:3|5:4|3:2|1:1)\b/i', $combined_string, $aspect_ratio_match);

                        $screen_size = isset($screen_size_match[1]) ? number_format($screen_size_match[1], 1) . '"' : '-';
                        $resolution = $resolution_match[1] ?? '-';
                        $refresh_rate = $refresh_rate_match[1] ?? '-';
                        //$response_time = $response_time_match[1] ?? '-';
                        $panel_type = strtoupper($panel_type_match[1] ?? '-');
                        $aspect_ratio = isset($aspect_ratio_match[1]) ? trim($aspect_ratio_match[1]) : '-';
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
                        <td style="padding:10px;"><?php echo esc_html($screen_size); ?></td>
                        <td style="padding:10px;"><?php echo esc_html($resolution); ?></td>
                        <td style="padding:10px;"><?php echo esc_html($refresh_rate); ?></td>
                        <!-- <td style="padding:10px;"><?php //echo esc_html($response_time); ?></td> -->
                        <td style="padding:10px;"><?php echo esc_html($panel_type); ?></td>
                        <td style="padding:10px;"><?php echo esc_html($aspect_ratio); ?></td>
                        <td style="padding:10px;" data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"><?php echo $rating_count; ?></td>
                        <td style="padding:10px;"><?php echo esc_html($price); ?></td>
                        <td style="padding:10px;">
                            <button class="add-to-builder"
                                data-asin="<?php echo esc_attr($asin); ?>"
                                data-title="<?php echo esc_attr($full_title); ?>"
                                data-image="<?php echo esc_url($raw_image); ?>"
                                data-base="<?php echo esc_attr($base_price); ?>"
                                data-shipping="FREE"
                                data-availability="<?php echo esc_attr($availability); ?>"
                                data-price="<?php echo esc_attr($base_price); ?>"
                                data-category="Monitor"
                                data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                data-screen-size="<?php echo esc_attr($screen_size); ?>"
                                data-resolution="<?php echo esc_attr($resolution); ?>"
                                data-refresh-rate="<?php echo esc_attr($refresh_rate); ?>"
                                data-panel-type="<?php echo esc_attr($panel_type); ?>"
                                data-aspect-ratio="<?php echo esc_attr($aspect_ratio); ?>"
                                data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>">
                                <?php echo esc_html__('Add', 'aawp-pcbuild'); ?>
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
                                data-image="<?php echo esc_url($raw_image); ?>"
                                data-base="<?php echo esc_attr($base_price); ?>"
                                data-shipping="FREE"
                                data-availability="<?php echo esc_attr($availability); ?>"
                                data-price="<?php echo esc_attr($base_price); ?>"
                                data-category="Monitor"
                                data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                data-screen-size="<?php echo esc_attr($screen_size); ?>"
                                data-resolution="<?php echo esc_attr($resolution); ?>"
                                data-refresh-rate="<?php echo esc_attr($refresh_rate); ?>"
                                data-panel-type="<?php echo esc_attr($panel_type); ?>"
                                data-aspect-ratio="<?php echo esc_attr($aspect_ratio); ?>"
                                data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>">
                                <?php _e('Add', 'aawp-pcbuild'); ?>
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
        @media (max-width: 768px) {
            .pcbuilder-container {
                flex-direction: column;
            }
            .pcbuild-sidebar,
            .pcbuilder-main {
                width: 100% !important;
            }
            .pcbuilder-main {
                max-height: 80vh; /* Adjust based on your layout */
                overflow-y: auto;
            }
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
                row.style.backgroundColor = ""; // Reset hidden rows
            }
        });
    }

    function applyRatingFilter() {
        const selected = Array.from(ratingFilterInputs)
            .filter(input => input.checked && input.value !== "all")
            .map(input => input.value);

        const isAllChecked = document.querySelector('#rating-filter input[value="all"]').checked;
        let visibleCount = 0;

        productRows.forEach((row, index) => {
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
            row.style.backgroundColor = (visibleCount % 2 === 0) ? '#d4d4d4' : '#ebebeb';
            if (visible) {
                visibleCount++;
            }
        });
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
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("aspect-ratio-filter");
    const allCheckbox = document.getElementById("aspect-ratio-all");
    const aspectRatioSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique aspect ratios (case-insensitive)
    tableRows.forEach(row => {
        const aspectRatio = row.querySelector("button.add-to-builder")?.dataset.aspectRatio || "Unknown";
        aspectRatioSet.add(aspectRatio.trim().toLowerCase());
    });

    const aspectRatios = Array.from(aspectRatioSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    aspectRatios.forEach(aspectRatio => {
        const label = document.createElement("label");
        const displayName = aspectRatio.toUpperCase(); // Convert the aspect ratio to uppercase
        label.innerHTML = `<input type="checkbox" name="aspect-ratio" value="${aspectRatio}" checked> ${displayName}`;
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
        const allBoxes = Array.from(document.querySelectorAll("input[name='aspect-ratio']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyAspectRatioFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='aspect-ratio']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const aspectRatio = row.querySelector("button.add-to-builder")?.dataset.aspectRatio.trim().toLowerCase();
            row.style.display = selected.includes(aspectRatio) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='aspect-ratio']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyAspectRatioFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "aspect-ratio") {
            applyAspectRatioFilter();
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
    applyAspectRatioFilter();
});
</script>


<script>
// Panel Type Filtering
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("panel-type-filter");
    const allCheckbox = document.getElementById("panel-type-all");
    const panelTypeSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique panel types (case-insensitive)
    tableRows.forEach(row => {
        const panelType = row.querySelector("button.add-to-builder")?.dataset.panelType || "Unknown";
        panelTypeSet.add(panelType.trim().toLowerCase());
    });

    const panelTypes = Array.from(panelTypeSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    panelTypes.forEach(panelType => {
        const label = document.createElement("label");
        const displayName = panelType.toUpperCase(); // Convert the panel type to uppercase
        label.innerHTML = `<input type="checkbox" name="panel-type" value="${panelType}" checked> ${displayName}`;
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
        const allBoxes = Array.from(document.querySelectorAll("input[name='panel-type']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyPanelTypeFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='panel-type']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const panelType = row.querySelector("button.add-to-builder")?.dataset.panelType.trim().toLowerCase();
            row.style.display = selected.includes(panelType) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='panel-type']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyPanelTypeFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "panel-type") {
            applyPanelTypeFilter();
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
    applyPanelTypeFilter();
});
</script>


<script>
// Refresh Rate Filtering
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("refresh-rate-slider");
    const minLabel = document.getElementById("refresh-rate-min-label");
    const maxLabel = document.getElementById("refresh-rate-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));
    const refreshRates = rows.map(row => {
        // Assuming refresh rate is in the 7th column (index 8), adjust this index based on your table structure
        const refreshRateText = row.querySelector("td:nth-child(8)")?.textContent.replace(/[^0-9.]/g, '') || "0";
        return parseFloat(refreshRateText) || 0;
    });

    const minRate = Math.floor(Math.min(...refreshRates));
    const maxRate = Math.ceil(Math.max(...refreshRates));
    let currentMin = minRate;
    let currentMax = maxRate;

    // Set default labels
    minLabel.textContent = `${minRate}Hz`;
    maxLabel.textContent = `${maxRate}Hz`;

    // Create 2 sliders
    sliderContainer.innerHTML = `
        <input type="range" class="min-range-bg" id="min-refresh-rate" min="${minRate}" max="${maxRate}" value="${minRate}" step="1" style="width: 100%;">
        <input type="range" class="max-range-bg" id="max-refresh-rate" min="${minRate}" max="${maxRate}" value="${maxRate}" step="1" style="width: 100%; margin-top: 10px;">
    `;

    const minSlider = document.getElementById("min-refresh-rate");
    const maxSlider = document.getElementById("max-refresh-rate");

    function applyZebraStripes() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function filterByRefreshRate() {
        const minVal = parseFloat(minSlider.value);
        const maxVal = parseFloat(maxSlider.value);
        currentMin = minVal;
        currentMax = maxVal;

        minLabel.textContent = `${minVal}Hz`;
        maxLabel.textContent = `${maxVal}Hz`;

        rows.forEach(row => {
            const refreshRateText = row.querySelector("td:nth-child(8)")?.textContent.replace(/[^0-9.]/g, '') || "0";
            const refreshRate = parseFloat(refreshRateText) || 0;

            row.style.display = (refreshRate >= minVal && refreshRate <= maxVal) ? "" : "none";
        });

        applyZebraStripes();
    }

    minSlider.addEventListener("input", () => {
        if (parseFloat(minSlider.value) > parseFloat(maxSlider.value)) {
            minSlider.value = maxSlider.value;
        }
        filterByRefreshRate();
    });

    maxSlider.addEventListener("input", () => {
        if (parseFloat(maxSlider.value) < parseFloat(minSlider.value)) {
            maxSlider.value = minSlider.value;
        }
        filterByRefreshRate();
    });

    // Initial filter apply
    filterByRefreshRate();
});
</script>

<script>
// Resolution Filtering for the table
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("resolution-filter");
    const allCheckbox = document.getElementById("resolution-all");
    const resolutionSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique resolutions from data-resolution attribute
    tableRows.forEach(row => {
        const resolution = row.querySelector("button.add-to-builder")?.dataset.resolution?.trim().toLowerCase() || "unknown";
        resolutionSet.add(resolution);
    });

    const resolutions = Array.from(resolutionSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    resolutions.forEach(resolution => {
        const label = document.createElement("label");
        const displayName = resolution === "unknown"
            ? "Unknown"
            : resolution.charAt(0).toUpperCase() + resolution.slice(1);
        label.innerHTML = `<input type="checkbox" name="resolution" value="${resolution}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none';
        filterContainer.appendChild(el);
    });

    // Toggle link for show more/less
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Zebra striping function
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr"))
            .filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    // Update 'All' checkbox based on individual checkboxes
    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='resolution']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    // Apply filtering to rows
    function applyResolutionFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='resolution']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const resolution = row.querySelector("button.add-to-builder")?.dataset.resolution?.trim().toLowerCase() || "unknown";
            row.style.display = selected.includes(resolution) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // 'All' checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='resolution']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyResolutionFilter();
    });

    // Individual checkbox change logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "resolution") {
            applyResolutionFilter();
        }
    });

    // Toggle 'Show more' / 'Show less'
    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) {
                el.style.display = expanded ? "block" : "none";
            }
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filter application
    applyResolutionFilter();
});
</script>


<script>
// Screen Size Filtering
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("screen-size-slider");
    const minLabel = document.getElementById("screen-size-min-label");
    const maxLabel = document.getElementById("screen-size-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));
    const screenSizes = rows.map(row => {
        // Assuming screen size is stored in the data-screen-size attribute
        const size = row.querySelector("button.add-to-builder")?.dataset.screenSize || "0";
        return parseFloat(size) || 0;
    });

    const minSize = Math.floor(Math.min(...screenSizes));
    const maxSize = Math.ceil(Math.max(...screenSizes));
    let currentMin = minSize;
    let currentMax = maxSize;

    // Set default labels
    minLabel.textContent = `${minSize}"`;
    maxLabel.textContent = `${maxSize}"`;

    // Create 2 sliders
    sliderContainer.innerHTML = `
        <input type="range" class="min-range-bg" id="min-size" min="${minSize}" max="${maxSize}" value="${minSize}" step="1" style="width: 100%;">
        <input type="range" class="max-range-bg" id="max-size" min="${minSize}" max="${maxSize}" value="${maxSize}" step="1" style="width: 100%; margin-top: 10px;">
    `;

    const minSlider = document.getElementById("min-size");
    const maxSlider = document.getElementById("max-size");

    function applyZebraStripes() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function filterByScreenSize() {
        const minVal = parseFloat(minSlider.value);
        const maxVal = parseFloat(maxSlider.value);
        currentMin = minVal;
        currentMax = maxVal;

        minLabel.textContent = `${minVal}"`;
        maxLabel.textContent = `${maxVal}"`;

        rows.forEach(row => {
            const screenSize = row.querySelector("button.add-to-builder")?.dataset.screenSize || "0";
            const size = parseFloat(screenSize) || 0;

            row.style.display = (size >= minVal && size <= maxVal) ? "" : "none";
        });

        applyZebraStripes();
    }

    minSlider.addEventListener("input", () => {
        if (parseFloat(minSlider.value) > parseFloat(maxSlider.value)) {
            minSlider.value = maxSlider.value;
        }
        filterByScreenSize();
    });

    maxSlider.addEventListener("input", () => {
        if (parseFloat(maxSlider.value) < parseFloat(minSlider.value)) {
            maxSlider.value = minSlider.value;
        }
        filterByScreenSize();
    });

    // Initial filter apply
    filterByScreenSize();
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
        const priceText = row.querySelector("td:nth-child(8)")?.textContent.replace(/[^0-9.]/g, '') || "0";
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
            const priceText = row.querySelector("td:nth-child(8)")?.textContent.replace(/[^0-9.]/g, '') || "0";
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
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById("pcbuild-table");
    const headers = table.querySelectorAll(".sortable-header");
    const tbody = table.querySelector("tbody");

    let currentSort = { key: null, direction: 'asc' };

    const columnMap = {
        'name': 2,
        'screen-size': 3,
        'resolution': 4,
        'refresh-rate': 5,
        'panel-type': 6,
        'aspect-ratio': 7,
        'rating': 8,
        'price': 9
    };

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const key = header.dataset.key;
            const colIndex = columnMap[key];
            const direction = (currentSort.key === key && currentSort.direction === 'asc') ? 'desc' : 'asc';

            currentSort = { key, direction };

            // Update arrow indicators
            headers.forEach(h => h.querySelector('.sort-arrow').textContent = '▶');
            header.querySelector('.sort-arrow').textContent = direction === 'asc' ? '▲' : '▼';

            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((a, b) => {
                const getCellValue = (row, index) => {
                    const cell = row.children[index];
                    if (!cell) return '';
                    if (key === 'rating') {
                        return parseFloat(cell.dataset.rating || '0');
                    } else if (key === 'price') {
                        const txt = cell.textContent.replace(/[^0-9.]/g, '');
                        return parseFloat(txt) || 0;
                    } else if (key === 'refresh-rate' || key === 'screen-size') {
                        const txt = cell.textContent.replace(/[^0-9.]/g, '');
                        return parseFloat(txt) || 0;
                    } else {
                        return cell.textContent.trim().toLowerCase();
                    }
                };

                const valA = getCellValue(a, colIndex);
                const valB = getCellValue(b, colIndex);

                if (typeof valA === 'number' && typeof valB === 'number') {
                    return direction === 'asc' ? valA - valB : valB - valA;
                }

                return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            });

            // Append sorted rows and update zebra striping
            rows.forEach((row, i) => {
                row.style.backgroundColor = (i % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                tbody.appendChild(row);
            });
        });
    });
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
    include('parts-footer.php');
    return ob_get_clean();
}
add_shortcode('pcbuild_parts_monitor', 'aawp_pcbuild_display_parts_monitor');

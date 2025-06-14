<?php
function aawp_pcbuild_display_parts_memory($atts) {
    
    $atts = shortcode_atts(array('category' => 'memory'), $atts);
    $input_category = sanitize_title($atts['category']);
    
    // Define the category mapping
    $category_map = [
        'ram' => 'Memory',
        //'memory' => 'Memory',
    ];
    
    $category = $category_map[$input_category] ?? 'Memory';
    
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

    // Pagination setup
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
                        <strong>RAM Type</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="ramtype-filter" style="display: block; font-size:14px; margin-top: 10px;">
                        <div id="ramtype-options" style="display: flex; flex-direction: column; gap: 8px;">
                            <label><input type="checkbox" id="ramtype-all" checked> All</label>
                            <!-- RAM Type checkboxes will be dynamically inserted here -->
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>RAM Speed</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="ramspeed-filter" style="display: block; font-size:14px; margin-top: 10px;">
                        <div id="ramspeed-options" style="display: flex; flex-direction: column; gap: 8px;">
                            <label><input type="checkbox" id="ramspeed-all" checked> All</label>
                            <!-- RAM Speed checkboxes will be dynamically inserted here -->
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Modules</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="rammodules-filter" style="display: block; font-size:14px; margin-top: 10px;">
                        <div id="rammodules-options" style="display: flex; flex-direction: column; gap: 8px;">
                            <label><input type="checkbox" id="rammodules-all" checked> All</label>
                            <!-- Modules checkboxes will be dynamically inserted here -->
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Color</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="ramcolor-filter" style="display: block; font-size:14px; margin-top: 10px;">
                        <div id="ramcolor-options" style="display: flex; flex-direction: column; gap: 8px;">
                            <label><input type="checkbox" id="ramcolor-all" checked> All</label>
                            <!-- Color checkboxes will be dynamically inserted here -->
                        </div>
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
                            <th class="sortable-header" data-key="speed">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Speed
                                </span>
                            </th>
                            <th class="sortable-header" data-key="modules">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Modules
                                </span>
                            </th>
                            <th class="sortable-header" data-key="price_per_gb">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Price / GB
                                </span>
                            </th>
                            <th class="sortable-header" data-key="color">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Color
                                </span>
                            </th>
                            <th class="sortable-header" data-key="cas_latency">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> CAS Latency
                                </span>
                            </th>
                            <th class="sortable-header" data-key="rating">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Rating
                                </span>
                            </th>
                            <th class="sortable-header" data-key="price">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Price
                                </span>
                            </th>
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
                            $image = $item['Images']['Primary']['Large']['URL'] ?? '';
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

                            // Extract from features string or title
                            preg_match('/(DDR\d)/i', $features_string . ' ' . $full_title, $ram_type_match);
                            preg_match('/(\d{3,4})\s?MHz/i', $features_string . ' ' . $full_title, $mhz_match);

                            // Assign safely
                            $type  = isset($ram_type_match[1]) ? strtoupper($ram_type_match[1]) : '-';
                            $speed = isset($mhz_match[1]) ? $mhz_match[1] : '-';

                            // Combine if both parts are found
                            $type_speed = ($type !== '-' && $speed !== '-') ? $type . '-' . $speed : '-';

                            // Try to match "2x16GB", "2 x 16GB", etc.
                            preg_match('/(\d)\s?[xX×]\s?(\d+)\s?GB/i', $features_string . ' ' . $full_title, $modules_match);
                            $modules = isset($modules_match[1], $modules_match[2])
                                ? $modules_match[1] . ' x ' . $modules_match[2] . 'GB'
                                : '-';

                            preg_match('/(\d+)\s?[xX]\s?(\d+)\s?GB/i', $features_string, $modules_combo_match);
                            preg_match('/(Black|White|Red|Blue|Silver|Gray|RGB)/i', $features_string, $color_match);
                            preg_match('/(CL\d+)/i', $features_string, $cas_latency_match);

                            // Assign values
                            $total_gb = (isset($modules_combo_match[1], $modules_combo_match[2]))
                                ? (int)$modules_combo_match[1] * (int)$modules_combo_match[2]
                                : null;

                            $color = isset($color_match[1]) ? ucfirst(strtolower($color_match[1])) : '-';
                            if ($color === 'Rgb') {
                                $color = 'RGB';
                            }

                            $cas_latency = $cas_latency_match[1] ?? '-';
                            $first_word_latency = $first_word_latency_match[1] ?? '-';

                            // Calculate price per GB
                            $price_per_gb = '-';
                            if ($total_gb) {
                                $price_value = floatval(preg_replace('/[^\d.]/', '', $base_price));
                                if ($price_value > 0) {
                                    $price_per_gb = '$' . number_format($price_value / $total_gb, 3);
                                }
                            }

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
                            <td style="padding:10px;"><?php echo esc_html($type_speed); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($modules); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($price_per_gb); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($color); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($cas_latency); ?></td>
                            <td style="padding:10px;" data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"><?php echo $rating_count; ?></td>
                            <td style="padding:10px;"><?php echo esc_html($price); ?></td>
                            <td style="padding:10px;">
                                <button class="add-to-builder"
                                    data-asin="<?php echo esc_attr($asin); ?>"
                                    data-title="<?php echo esc_attr($full_title); ?>"
                                    data-image="<?php echo esc_url($image); ?>"
                                    data-base="<?php echo esc_attr($base_price); ?>"
                                    data-shipping="FREE"
                                    data-availability="<?php echo esc_attr($availability); ?>"
                                    data-price="<?php echo esc_attr($base_price); ?>"
                                    data-category="<?php echo esc_attr($category); ?>"
                                    data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                    data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                    data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                    data-ram_type="<?php echo esc_attr($type ?? ''); ?>"
                                    data-ram_speed="<?php echo esc_attr($speed ?? ''); ?>"
                                    data-modules="<?php echo esc_attr($modules ?? ''); ?>"
                                    data-color="<?php echo esc_attr($color ?? ''); ?>"
                                    data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"
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
                                    data-shipping="FREE"
                                    data-availability="<?php echo esc_attr($availability); ?>"
                                    data-price="<?php echo esc_attr($base_price); ?>"
                                    data-category="<?php echo esc_attr($category); ?>"
                                    data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                    data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                    data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                    data-ram_type="<?php echo esc_attr($type ?? ''); ?>"
                                    data-ram_speed="<?php echo esc_attr($speed ?? ''); ?>"
                                    data-modules="<?php echo esc_attr($modules ?? ''); ?>"
                                    data-color="<?php echo esc_attr($color ?? ''); ?>"
                                    data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>">
                                    <?php echo esc_html__('Add', 'aawp-pcbuild'); ?>
                                </button>
                             </div>
                         </td>
                     </tr>
                 <?php endforeach; ?>
             </tbody>
         </table>

                <!-- Pagination UI -->
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

    <!-- <style>
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
    </style> -->

<style>
    html, body {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .pcbuilder-container {
            display: flex;
            flex-direction: column;
            margin-top: 0 !important;
            padding-top: 0 !important;
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

        productRows.forEach(row => {
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
        });

        applyZebraStriping();
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
    const colorOptionsContainer = document.getElementById("ramcolor-options");

    const VISIBLE_COUNT = 4;
    let expanded = false;
    let ramColors = new Set();

    // Collect RAM colors from data attributes
    tableRows.forEach(row => {
        const btn = row.querySelector(".add-to-builder");
        if (btn && btn.dataset.color) {
            ramColors.add(btn.dataset.color);
        }
    });

    // Clear and rebuild color filter checkboxes
    colorOptionsContainer.innerHTML = `<label><input type="checkbox" id="ramcolor-all" checked> All</label>`;
    const checkboxElements = [];

    Array.from(ramColors).sort().forEach((color, index) => {
        const label = document.createElement("label");
        label.innerHTML = `<input type="checkbox" class="ramcolor-checkbox" value="${color}" checked> ${color}`;
        label.style.display = index < VISIBLE_COUNT ? "block" : "none";
        colorOptionsContainer.appendChild(label);
        checkboxElements.push(label);
    });

    // Show more / Show less link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    colorOptionsContainer.parentNode.appendChild(toggleLink);

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

    function applyZebraStriping() {
        let visibleIndex = 0;
        tableRows.forEach(row => {
            if (row.style.display !== "none") {
                row.style.backgroundColor = (visibleIndex % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                visibleIndex++;
            }
        });
    }

    function updateAllCheckboxState() {
        const allColorCheckboxes = document.querySelectorAll(".ramcolor-checkbox");
        const checkedColorCheckboxes = document.querySelectorAll(".ramcolor-checkbox:checked");
        const allChecked = checkedColorCheckboxes.length === allColorCheckboxes.length;
        document.getElementById("ramcolor-all").checked = allChecked;
    }

    function filterRowsByColor() {
        const colorAll = document.getElementById("ramcolor-all");
        const selectedColors = Array.from(document.querySelectorAll(".ramcolor-checkbox:checked")).map(cb => cb.value);

        tableRows.forEach(row => {
            const btn = row.querySelector(".add-to-builder");
            const rowColor = btn?.dataset.color || "";
            const match = colorAll.checked || selectedColors.includes(rowColor);
            row.style.display = match ? "" : "none";
        });

        applyZebraStriping();
    }

    // Handle checkbox changes
    colorOptionsContainer.addEventListener("change", function (e) {
        if (e.target.id === "ramcolor-all") {
            const checked = e.target.checked;
            document.querySelectorAll(".ramcolor-checkbox").forEach(cb => cb.checked = checked);
            filterRowsByColor();
        }

        if (e.target.classList.contains("ramcolor-checkbox")) {
            updateAllCheckboxState();
            filterRowsByColor();
        }
    });

    // Initial filtering and zebra striping
    filterRowsByColor();
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const tableBody = table.querySelector("tbody");
    const modulesOptionsContainer = document.getElementById("rammodules-options");

    const VISIBLE_COUNT = 4;
    let expanded = false;
    let ramModules = new Set();

    // Collect RAM modules
    tableRows.forEach(row => {
        const btn = row.querySelector(".add-to-builder");
        if (btn && btn.dataset.modules) {
            ramModules.add(btn.dataset.modules);
        }
    });

    // Clear and build options container
    modulesOptionsContainer.innerHTML = `<label><input type="checkbox" id="rammodules-all" checked> All</label>`;
    const allCheckbox = document.getElementById("rammodules-all");
    const checkboxElements = [];

    Array.from(ramModules).sort().forEach((modules, index) => {
        const label = document.createElement("label");
        label.innerHTML = `<input type="checkbox" class="rammodules-checkbox" value="${modules}" checked> ${modules}`;
        label.style.display = index < VISIBLE_COUNT ? "block" : "none";
        modulesOptionsContainer.appendChild(label);
        checkboxElements.push(label);
    });

    // Add Show more/less toggle
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    modulesOptionsContainer.parentNode.appendChild(toggleLink);

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

    function applyZebraStriping() {
        let visibleIndex = 0;
        tableRows.forEach(row => {
            if (row.style.display !== "none") {
                row.style.backgroundColor = (visibleIndex % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                visibleIndex++;
            }
        });
    }

    function filterRowsByModules() {
        const selectedModules = Array.from(document.querySelectorAll(".rammodules-checkbox:checked")).map(cb => cb.value);
        const showAll = allCheckbox.checked;
        let anyVisible = false;
        let visibleIndex = 0;

        if (!showAll && selectedModules.length === 0) {
            // Hide only tbody if no selection
            tableBody.style.display = "none";
            return;
        } else {
            tableBody.style.display = "";
        }

        tableRows.forEach(row => {
            const btn = row.querySelector(".add-to-builder");
            const modules = btn?.dataset.modules || "";
            const shouldShow = showAll || selectedModules.includes(modules);
            row.style.display = shouldShow ? "" : "none";

            if (shouldShow) {
                row.style.backgroundColor = (visibleIndex % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                visibleIndex++;
                anyVisible = true;
            }
        });

        // Optionally apply zebra striping
        applyZebraStriping();
    }

    function updateAllCheckboxState() {
        const allModulesCheckboxes = document.querySelectorAll(".rammodules-checkbox");
        const checkedModulesCheckboxes = document.querySelectorAll(".rammodules-checkbox:checked");
        allCheckbox.checked = checkedModulesCheckboxes.length === allModulesCheckboxes.length;
    }

    // Event listener for checkbox changes
    modulesOptionsContainer.addEventListener("change", function (e) {
        if (e.target.id === "rammodules-all") {
            const check = e.target.checked;
            document.querySelectorAll(".rammodules-checkbox").forEach(cb => cb.checked = check);
        } else if (e.target.classList.contains("rammodules-checkbox")) {
            updateAllCheckboxState();
        }

        filterRowsByModules();
    });

    // Initial filtering
    filterRowsByModules();
});
</script>



<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableBody = table.querySelector("tbody");
    const tableRows = tableBody.querySelectorAll("tr");
    const speedOptionsContainer = document.getElementById("ramspeed-options");

    const VISIBLE_COUNT = 4;
    let expanded = false;
    let ramSpeeds = new Set();

    // Collect RAM speeds
    tableRows.forEach(row => {
        const btn = row.querySelector(".add-to-builder");
        if (btn && btn.dataset.ram_speed) {
            ramSpeeds.add(btn.dataset.ram_speed);
        }
    });

    // Clear and build options container
    speedOptionsContainer.innerHTML = `<label><input type="checkbox" id="ramspeed-all" checked> All</label>`;
    const allCheckbox = document.getElementById("ramspeed-all");
    const checkboxElements = [];

    Array.from(ramSpeeds).sort((a, b) => parseInt(a) - parseInt(b)).forEach((speed, index) => {
        const label = document.createElement("label");
        label.innerHTML = `<input type="checkbox" class="ramspeed-checkbox" value="${speed}" checked> ${speed} MHz`;
        label.style.display = index < VISIBLE_COUNT ? "block" : "none";
        speedOptionsContainer.appendChild(label);
        checkboxElements.push(label);
    });

    // Add Show more/less toggle
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    speedOptionsContainer.parentNode.appendChild(toggleLink);

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

    function applyZebraStriping() {
        let visibleIndex = 0;
        tableRows.forEach(row => {
            if (row.style.display !== "none") {
                row.style.backgroundColor = (visibleIndex % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                visibleIndex++;
            }
        });
    }

    function filterRowsBySpeed() {
        const selectedSpeeds = Array.from(document.querySelectorAll(".ramspeed-checkbox:checked")).map(cb => cb.value);
        const showAll = allCheckbox.checked || selectedSpeeds.length === 0;

        if (!allCheckbox.checked && selectedSpeeds.length === 0) {
            tableBody.style.display = "none";
            return;
        } else {
            tableBody.style.display = "";
        }

        let visibleIndex = 0;
        tableRows.forEach(row => {
            const btn = row.querySelector(".add-to-builder");
            const ramSpeed = btn?.dataset.ram_speed || "";
            const shouldShow = showAll || selectedSpeeds.includes(ramSpeed);
            row.style.display = shouldShow ? "" : "none";

            if (shouldShow) {
                row.style.backgroundColor = (visibleIndex % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                visibleIndex++;
            }
        });

        applyZebraStriping();
    }

    function updateAllCheckboxState() {
        const allSpeedCheckboxes = document.querySelectorAll(".ramspeed-checkbox");
        const checkedSpeedCheckboxes = document.querySelectorAll(".ramspeed-checkbox:checked");
        allCheckbox.checked = checkedSpeedCheckboxes.length === allSpeedCheckboxes.length;
    }

    // Event listener for checkbox changes
    speedOptionsContainer.addEventListener("change", function (e) {
        if (e.target.id === "ramspeed-all") {
            const check = e.target.checked;
            document.querySelectorAll(".ramspeed-checkbox").forEach(cb => cb.checked = check);
        } else if (e.target.classList.contains("ramspeed-checkbox")) {
            updateAllCheckboxState();
        }

        filterRowsBySpeed();
    });

    // Initial filtering
    filterRowsBySpeed();
});
</script>

<script>
// RAM Type Filtering
document.addEventListener("DOMContentLoaded", function () {
    const tableRows = document.querySelectorAll("#pcbuild-table tbody tr");
    const ramTypeOptionsContainer = document.getElementById("ramtype-options");

    let ramTypes = new Set();

    // Collect RAM types from all the rows
    tableRows.forEach(row => {
        const btn = row.querySelector(".add-to-builder");
        if (btn && btn.dataset.ram_type) {
            ramTypes.add(btn.dataset.ram_type.toUpperCase());
        }
    });

    // Insert "All" checkbox
    ramTypeOptionsContainer.innerHTML = `
        <label><input type="checkbox" id="ramtype-all" checked> All</label>
    `;

    // Add individual RAM type checkboxes
    Array.from(ramTypes).sort().forEach(ramType => {
        const label = document.createElement("label");
        label.innerHTML = `<input type="checkbox" class="ramtype-checkbox" value="${ramType}" checked> ${ramType}`;
        ramTypeOptionsContainer.appendChild(label);
    });

    const ramTypeFilterAll = document.getElementById("ramtype-all");

    function applyZebraStriping() {
        let visibleIndex = 0;
        tableRows.forEach(row => {
            if (row.style.display !== "none") {
                row.style.backgroundColor = (visibleIndex % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                visibleIndex++;
            }
        });
    }

    function updateAllCheckboxState() {
        const allBoxes = document.querySelectorAll(".ramtype-checkbox");
        const checkedBoxes = document.querySelectorAll(".ramtype-checkbox:checked");
        ramTypeFilterAll.checked = checkedBoxes.length === allBoxes.length;
    }

    function filterRows() {
        const selectedTypes = Array.from(document.querySelectorAll(".ramtype-checkbox:checked")).map(cb => cb.value);

        tableRows.forEach(row => {
            const btn = row.querySelector(".add-to-builder");
            if (!btn) return;
            const ramType = btn.dataset.ram_type ? btn.dataset.ram_type.toUpperCase() : "";
            row.style.display = selectedTypes.includes(ramType) ? "" : "none";
        });

        applyZebraStriping();
    }

    // Handle "All" checkbox toggle
    ramTypeFilterAll.addEventListener("change", function () {
        const allChecked = ramTypeFilterAll.checked;
        document.querySelectorAll(".ramtype-checkbox").forEach(cb => cb.checked = allChecked);
        filterRows();
    });

    // Handle individual RAM type checkbox toggle
    ramTypeOptionsContainer.addEventListener("change", function (e) {
        if (e.target.classList.contains("ramtype-checkbox")) {
            updateAllCheckboxState();
            filterRows();
        }
    });

    // Initial filtering and striping
    filterRows();
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

            headers.forEach(h => {
                h.innerHTML = `&#9654; ${h.textContent.trim().replace(/^▲|▼|\▶/, '')}`;
            });

            this.innerHTML = `${currentSort.direction === 'asc' ? '▲' : '▼'} ${this.textContent.trim().replace(/^▲|▼|\▶/, '')}`;

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

                if (['price', 'price_per_gb', 'cas_latency'].includes(key)) {
                    const num = parseFloat(cell.textContent.replace(/[^0-9.]/g, ''));
                    return isNaN(num) ? 0 : num;
                }

                return cell.textContent.trim().toLowerCase();
            };

            const valA = getText(a, key);
            const valB = getText(b, key);

            if (typeof valA === 'number' && typeof valB === 'number') {
                return direction === 'asc' ? valA - valB : valB - valA;
            }

            return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
        });

        rows.forEach((row, i) => {
            row.style.backgroundColor = (i % 2 === 0) ? '#d4d4d4' : '#ebebeb';
            tbody.appendChild(row);
        });
    }

    function getColumnIndex(key) {
        const mapping = {
            name: 2,
            type_speed: 3,
            modules: 4,
            price_per_gb: 5,
            color: 6,
            cas_latency: 7,
            rating: 8,
            price: 9
        };
        return mapping[key];
    }
});
</script>


<script>
//Manufacturer Filtering
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table"); // Assuming your table has id 'pcbuild-table'
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("manufacturer-filter");
    const allCheckbox = document.getElementById("manufacturer-all");
    const manufacturerSet = new Set();

    const VISIBLE_COUNT = 4; // Number of manufacturers to show initially
    let expanded = false;

    // Collect unique manufacturers from the table rows (case-insensitive)
    tableRows.forEach(row => {
        const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer || "Unknown";
        manufacturerSet.add(manufacturer.toLowerCase()); // Lowercase to avoid duplicates
    });

    const manufacturers = Array.from(manufacturerSet).sort(); // Sort alphabetically
    const checkboxElements = [];

    // Create checkbox elements for each manufacturer
    manufacturers.forEach(manufacturer => {
        const label = document.createElement("label");
        const displayName = manufacturer.charAt(0).toUpperCase() + manufacturer.slice(1); // Capitalize first letter
        label.innerHTML = `<input type="checkbox" name="manufacturer" value="${manufacturer}" checked> ${displayName}`;
        label.style.display = 'block'; // Line break for each
        checkboxElements.push(label);
    });

    // Insert checkboxes into the filter container
    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) {
            el.style.display = 'none';
        }
        filterContainer.appendChild(el);
    });

    // Create "Show more / Show less" link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Zebra striping function
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
            const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer.toLowerCase();
            const show = selected.includes(manufacturer);
            row.style.display = show ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // Event: "All" checkbox
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='manufacturer']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyManufacturerFilter();
    });

    // Event: individual manufacturer checkboxes
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "manufacturer") {
            applyManufacturerFilter();
        }
    });

    // Event: Show more / Show less toggle
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

    // Initial apply
    applyManufacturerFilter();
});
</script>

<!-- PRICE RANGE SLIDER FILTER -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("price-slider");
    const minLabel = document.getElementById("price-min-label");
    const maxLabel = document.getElementById("price-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));
    const prices = rows.map(row => {
        // Assuming price is in the 8th column (index 8)
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
add_shortcode('pcbuild_parts_memory', 'aawp_pcbuild_display_parts_memory');

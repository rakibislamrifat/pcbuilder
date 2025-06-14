<?php
function aawp_pcbuild_display_parts_motherboard($atts) {

    $atts = shortcode_atts(array('category' => 'motherboard'), $atts);
    $input_category = sanitize_title($atts['category']);

    $category_map = [
        'motherboard' => 'Motherboard',
    ];

    $category = $category_map[$input_category] ?? 'Motherboard';

    // Create transient key
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

    // Pagination
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
                        <strong>Socket / CPU</strong>
                        <button id="socket-toggle" style="border:none; background:none; color:#0066cc; font-size: 18px; cursor:pointer;">−</button>
                    </div>
                    <div class="filter-options" id="socket-filter">
                        <label><input type="checkbox" id="socket-all" checked> All</label><br/>
                        <!-- Socket checkboxes will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Form Factor</strong>
                        <button id="formfactor-toggle" style="border:none; background:none; color:#0066cc; font-size: 18px; cursor:pointer;">−</button>
                    </div>
                    <div class="filter-options" id="formfactor-filter">
                        <label><input type="checkbox" id="formfactor-all" checked> All</label><br/>
                        <!-- Form Factor checkboxes will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Memory Max</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="memorymax-filter" style="display: block;">
                        <div id="memorymax-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="memorymax-min-label">0 GB</span>
                            <span id="memorymax-max-label">0 GB</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Memory Slots</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="memoryslots-filter" style="display: block;">
                        <div id="memoryslots-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="memoryslots-min-label">0</span>
                            <span id="memoryslots-max-label">0</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Color</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="color-filter" style="display: block; font-size:14px; margin-top: 10px;">
                        <div id="color-options" style="display: flex; flex-direction: column; gap: 8px;">
                            <label><input type="checkbox" id="color-all" checked> All</label>
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
                            <th class="sortable-header" data-key="socket">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Socket / CPU
                                </span>
                            </th>
                            <th class="sortable-header" data-key="form_factor">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Form Factor
                                </span>
                            </th>
                            <th class="sortable-header" data-key="memory_max">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Memory Max
                                </span>
                            </th>
                            <th class="sortable-header" data-key="memory_slots">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Memory Slots
                                </span>
                            </th>
                            <th class="sortable-header" data-key="color">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Color
                                </span>
                            </th>
                            <th class="sortable-header" data-key="rating">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Seller Rating
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
                            $manufacturer = $item['ItemInfo']['ByLineInfo']['Manufacturer']['DisplayValue'] ?? 'Unknown';
                            $sellerCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? 'Unknown';
                            $sellerRating = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackRating'] ?? 'Unknown';

                            //Color filtering
                            $known_colors = [
                                'black', 'white', 'red', 'blue', 'silver', 'gray', 'rgb', 'green', 'yellow', 'pink', 'purple', 'orange', 
                                'brown', 'beige', 'cyan', 'magenta', 'teal', 'navy', 'indigo', 'violet', 'lavender', 'gold', 'bronze',
                                'snow white', 'ceramic gray', 'charcoal', 'lime green', 'turquoise', 'peach', 'mint green', 'emerald green', 'cobalt blue'
                            ];
                            $raw_color = isset($item['ItemInfo']['ProductInfo']['Color']['DisplayValue']) ? strtolower($item['ItemInfo']['ProductInfo']['Color']['DisplayValue']) : '-';
                            $color = in_array($raw_color, $known_colors) ? ucwords($raw_color) : '-';
                            
                            $features_string = implode(' ', $features);

                            preg_match('/(LGA\s?\d+|AM\d+)/i', $features_string, $socket_match);
                            preg_match('/(E-?ATX|Extended\s?ATX|XL-?ATX|Micro\s?-?ATX|Mini\s?-?ITX|mATX|ITX|ATX)/i', $features_string . ' ' . $full_title, $form_match);
                            preg_match('/(\d+\s?GB)/i', $features_string, $memory_max_match);
                            preg_match('/(\d+)\s?(x\s?)?(DIMM|DDR)/i', $features_string, $memory_slots_match);
                            preg_match('/(B\d{3}|X\d{3}|Z\d{3}|H\d{3}|A\d{3})/i', $features_string, $chipset_match); // e.g., B550, X670

                            $socket = $socket_match[1] ?? '-';
                            $form_factor = $form_match[1] ?? '-';
                            $memory_max = $memory_max_match[1] ?? '-';
                            $memory_slots = $memory_slots_match[1] ?? '-';
                            $chipset = $chipset_match[1] ?? '-';
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
                            <td style="padding:10px;"><?php echo esc_html($socket); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($form_factor); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($memory_max); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($memory_slots); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($color); ?></td>
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
                                    data-socket="<?php echo esc_attr($socket); ?>"
                                    data-chipset="<?php echo esc_attr($chipset); ?>"
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
                                         <div class="spec-label">Socket</div>
                                         <div class="spec-value"><?php echo esc_html($socket); ?></div>
                                     </div>
                                     <div class="spec-group">
                                         <div class="spec-label">Form Factor</div>
                                         <div class="spec-value"><?php echo esc_html($form_factor); ?></div>
                                     </div>
                                     <div class="spec-group">
                                         <div class="spec-label">Memory Max</div>
                                         <div class="spec-value"><?php echo esc_html($memory_max); ?></div>
                                     </div>
                                     <div class="spec-group">
                                         <div class="spec-label">Memory Slots</div>
                                         <div class="spec-value"><?php echo esc_html($memory_slots); ?></div>
                                     </div>
                                     <div class="spec-group">
                                         <div class="spec-label">Color</div>
                                         <div class="spec-value"><?php echo esc_html($color); ?></div>
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
                                    data-socket="<?php echo esc_attr($socket); ?>"
                                    data-chipset="<?php echo esc_attr($chipset); ?>"
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
    // Color Filtering
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const tableRows = table.querySelectorAll("tbody tr");
        const colorContainer = document.getElementById("color-filter");
        const colorSet = new Set();

        const VISIBLE_COUNT = 4; // Number of colors to show initially
        let expanded = false;

        // 1. Collect unique Colors
        tableRows.forEach(row => {
            const color = row.querySelector("td:nth-child(7)")?.textContent.trim() || "Unknown"; // 6th column = Color
            colorSet.add(color.toLowerCase());
        });

        const colors = Array.from(colorSet).sort();
        const checkboxElements = [];

        // 2. Create checkboxes
        colors.forEach(color => {
            const label = document.createElement("label");
            const displayName = color.charAt(0).toUpperCase() + color.slice(1); // Capitalize first letter
            label.innerHTML = `<input type="checkbox" name="color" value="${color}" checked> ${displayName}`;
            label.style.display = 'block';
            checkboxElements.push(label);
        });

        // 3. Append checkboxes
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) {
                el.style.display = 'none'; // Hide extra by default
            }
            colorContainer.appendChild(el);
        });

        // 4. Add Show more/less toggle
        const toggleLink = document.createElement("a");
        toggleLink.href = "#";
        toggleLink.textContent = "Show more";
        toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
        toggleLink.style.marginTop = "5px";
        toggleLink.style.fontSize = "14px";
        toggleLink.style.color = "#0066cc";
        colorContainer.appendChild(toggleLink);

        // Zebra striping
        function applyZebraStriping() {
            const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        const allCheckbox = document.getElementById("color-all");

        function updateAllCheckboxState() {
            const allBoxes = Array.from(document.querySelectorAll("input[name='color']"));
            const checkedBoxes = allBoxes.filter(cb => cb.checked);
            allCheckbox.checked = checkedBoxes.length === allBoxes.length;
        }

        function applyColorFilter() {
            const selected = Array.from(document.querySelectorAll("input[name='color']:checked"))
                .map(cb => cb.value);

            tableRows.forEach(row => {
                const color = row.querySelector("td:nth-child(7)")?.textContent.trim().toLowerCase();
                const show = selected.includes(color);
                row.style.display = show ? "" : "none";
            });

            updateAllCheckboxState();
            applyZebraStriping();
        }

        // "All" checkbox behavior
        allCheckbox.addEventListener("change", function () {
            const allBoxes = document.querySelectorAll("input[name='color']");
            allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
            applyColorFilter();
        });

        // Individual checkbox behavior
        colorContainer.addEventListener("change", function (e) {
            if (e.target.name === "color") {
                applyColorFilter();
            }
        });

        // Show more/less toggle
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
        applyColorFilter();
    });
</script>


    <script>
        // Memory Slots filtering
        document.addEventListener("DOMContentLoaded", function () {
            const table = document.getElementById("pcbuild-table");
            const sliderContainer = document.getElementById("memoryslots-slider");
            const minLabel = document.getElementById("memoryslots-min-label");
            const maxLabel = document.getElementById("memoryslots-max-label");

            if (!table || !sliderContainer) return;

            const rows = Array.from(table.querySelectorAll("tbody tr"));

            // Get Memory Slots values (5th column — adjust if needed)
            const memorySlotsValues = rows.map(row => {
                const slotsText = row.querySelector("td:nth-child(6)")?.textContent.replace(/[^\d]/g, '') || "0";
                return parseInt(slotsText, 10) || 0;
            });

            const minSlots = Math.floor(Math.min(...memorySlotsValues));
            const maxSlots = Math.ceil(Math.max(...memorySlotsValues));
            let currentMin = minSlots;
            let currentMax = maxSlots;

            // Set default labels
            minLabel.textContent = `${minSlots}`;
            maxLabel.textContent = `${maxSlots}`;

            // Create two range sliders
            sliderContainer.innerHTML = `
                <input type="range" class="min-range-bg" id="min-memoryslots" min="${minSlots}" max="${maxSlots}" value="${minSlots}" step="1" style="width: 100%;">
                <input type="range" class="max-range-bg" id="max-memoryslots" min="${minSlots}" max="${maxSlots}" value="${maxSlots}" step="1" style="width: 100%; margin-top: 10px;">
            `;

            const minSlider = document.getElementById("min-memoryslots");
            const maxSlider = document.getElementById("max-memoryslots");

            function applyMemorySlotsFilter() {
                const minVal = parseInt(minSlider.value, 10);
                const maxVal = parseInt(maxSlider.value, 10);
                currentMin = minVal;
                currentMax = maxVal;

                minLabel.textContent = `${minVal}`;
                maxLabel.textContent = `${maxVal}`;

                rows.forEach(row => {
                    const slotsText = row.querySelector("td:nth-child(6)")?.textContent.replace(/[^\d]/g, '') || "0";
                    const slotsValue = parseInt(slotsText, 10) || 0;

                    row.style.display = (slotsValue >= minVal && slotsValue <= maxVal) ? "" : "none";
                });

                // 🦓 Zebra stripe after filtering
                const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
                visibleRows.forEach((row, index) => {
                    row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
                });
            }

            minSlider.addEventListener("input", () => {
                if (parseInt(minSlider.value) > parseInt(maxSlider.value)) {
                    minSlider.value = maxSlider.value;
                }
                applyMemorySlotsFilter();
            });

            maxSlider.addEventListener("input", () => {
                if (parseInt(maxSlider.value) < parseInt(minSlider.value)) {
                    maxSlider.value = minSlider.value;
                }
                applyMemorySlotsFilter();
            });

            // Initial filter apply
            applyMemorySlotsFilter();
        });
    </script>

    <script>
    // Memory Max filtering
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("memorymax-slider");
    const minLabel = document.getElementById("memorymax-min-label");
    const maxLabel = document.getElementById("memorymax-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));

    // Get Memory Max values (4th column — adjust if needed)
    const memoryMaxValues = rows.map(row => {
        const memoryText = row.querySelector("td:nth-child(5)")?.textContent.replace(/[^\d]/g, '') || "0";
        return parseInt(memoryText, 10) || 0;
    });

    const minMemory = Math.floor(Math.min(...memoryMaxValues));
    const maxMemory = Math.ceil(Math.max(...memoryMaxValues));
    let currentMin = minMemory;
    let currentMax = maxMemory;

    // Set default labels
    minLabel.textContent = `${minMemory} GB`;
    maxLabel.textContent = `${maxMemory} GB`;

    // Create two range sliders
    sliderContainer.innerHTML = `
        <input type="range" class="min-range-bg" id="min-memorymax" min="${minMemory}" max="${maxMemory}" value="${minMemory}" step="1" style="width: 100%;">
        <input type="range" class="max-range-bg" id="max-memorymax" min="${minMemory}" max="${maxMemory}" value="${maxMemory}" step="1" style="width: 100%; margin-top: 10px;">
    `;

    const minSlider = document.getElementById("min-memorymax");
    const maxSlider = document.getElementById("max-memorymax");

    function applyMemoryMaxFilter() {
        const minVal = parseInt(minSlider.value, 10);
        const maxVal = parseInt(maxSlider.value, 10);
        currentMin = minVal;
        currentMax = maxVal;

        minLabel.textContent = `${minVal} GB`;
        maxLabel.textContent = `${maxVal} GB`;

        rows.forEach(row => {
            const memoryText = row.querySelector("td:nth-child(5)")?.textContent.replace(/[^\d]/g, '') || "0";
            const memoryValue = parseInt(memoryText, 10) || 0;

            row.style.display = (memoryValue >= minVal && memoryValue <= maxVal) ? "" : "none";
        });

        // 🦓 Zebra stripe after filtering
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    minSlider.addEventListener("input", () => {
        if (parseInt(minSlider.value) > parseInt(maxSlider.value)) {
            minSlider.value = maxSlider.value;
        }
        applyMemoryMaxFilter();
    });

    maxSlider.addEventListener("input", () => {
        if (parseInt(maxSlider.value) < parseInt(minSlider.value)) {
            maxSlider.value = minSlider.value;
        }
        applyMemoryMaxFilter();
    });

    // Initial filter apply
    applyMemoryMaxFilter();
});
</script>


    <script>
    // Form Factor Filtering
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const formFactorContainer = document.getElementById("formfactor-filter");
    const formFactorSet = new Set();

    const VISIBLE_COUNT = 4; // Number of form factors to show initially
    let expanded = false;

    // 1. Collect unique Form Factors
    tableRows.forEach(row => {
        const formFactor = row.querySelector("td:nth-child(4)")?.textContent.trim() || "Unknown"; // 3rd column = Form Factor
        formFactorSet.add(formFactor.toLowerCase());
    });

    const formFactors = Array.from(formFactorSet).sort();
    const checkboxElements = [];

    // 2. Create checkboxes
    formFactors.forEach(formFactor => {
        const label = document.createElement("label");
        const displayName = formFactor.charAt(0).toUpperCase() + formFactor.slice(1); // Capitalize first letter
        label.innerHTML = `<input type="checkbox" name="formfactor" value="${formFactor}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    // 3. Append checkboxes
    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) {
            el.style.display = 'none'; // Hide extra by default
        }
        formFactorContainer.appendChild(el);
    });

    // 4. Add Show more/less toggle
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    formFactorContainer.appendChild(toggleLink);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    const allCheckbox = document.getElementById("formfactor-all");

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='formfactor']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyFormFactorFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='formfactor']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const formFactor = row.querySelector("td:nth-child(4)")?.textContent.trim().toLowerCase();
            const show = selected.includes(formFactor);
            row.style.display = show ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // "All" checkbox behavior
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='formfactor']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyFormFactorFilter();
    });

    // Individual checkbox behavior
    formFactorContainer.addEventListener("change", function (e) {
        if (e.target.name === "formfactor") {
            applyFormFactorFilter();
        }
    });

    // Show more/less toggle
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
    applyFormFactorFilter();
});
</script>

    <script>
    // Socket Filtering
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const tableRows = table.querySelectorAll("tbody tr");
        const socketFilterContainer = document.getElementById("socket-filter"); // Container for socket checkboxes
        const socketSet = new Set();

        const VISIBLE_COUNT = 4;
        let expanded = false;

        // Collect unique sockets from the table rows (case-insensitive)
        tableRows.forEach(row => {
            const socket = row.querySelector("button.add-to-builder")?.dataset.socket || "Unknown";
            socketSet.add(socket.toLowerCase());
        });

        const sockets = Array.from(socketSet).sort();
        const checkboxElements = [];

        // Create checkboxes
        sockets.forEach(socket => {
            const label = document.createElement("label");
            const displayName = socket.toUpperCase(); // Display socket in uppercase (e.g., "LGA1200", "AM5")
            label.innerHTML = `<input type="checkbox" name="socket" value="${socket}" checked> ${displayName}`;
            label.style.display = 'block';
            checkboxElements.push(label);
        });

        // Append checkboxes
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) {
                el.style.display = 'none';
            }
            socketFilterContainer.appendChild(el);
        });

        // Add Show more / Show less link
        const toggleLink = document.createElement("a");
        toggleLink.href = "#";
        toggleLink.textContent = "Show more";
        toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
        toggleLink.style.marginTop = "5px";
        toggleLink.style.fontSize = "14px";
        toggleLink.style.color = "#0066cc";
        socketFilterContainer.appendChild(toggleLink);

        // Zebra striping function
        function applyZebraStriping() {
            const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        const allCheckbox = document.getElementById("socket-all");

        function updateAllCheckboxState() {
            const allBoxes = Array.from(document.querySelectorAll("input[name='socket']"));
            const checkedBoxes = allBoxes.filter(cb => cb.checked);
            allCheckbox.checked = checkedBoxes.length === allBoxes.length;
        }

        function applySocketFilter() {
            const selected = Array.from(document.querySelectorAll("input[name='socket']:checked"))
                .map(cb => cb.value);

            tableRows.forEach(row => {
                const socket = row.querySelector("button.add-to-builder")?.dataset.socket.toLowerCase();
                const show = selected.includes(socket);
                row.style.display = show ? "" : "none";
            });

            updateAllCheckboxState();
            applyZebraStriping();
        }

        // Toggle "All"
        allCheckbox.addEventListener("change", function () {
            const allBoxes = document.querySelectorAll("input[name='socket']");
            allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
            applySocketFilter();
        });

        // Individual checkbox change
        socketFilterContainer.addEventListener("change", function (e) {
            if (e.target.name === "socket") {
                applySocketFilter();
            }
        });

        // Show more/less logic
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
        applySocketFilter();
    });
</script>

    <script>
    // Manufacturer filtering with duplicate removal for the Motherboard page
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table"); // Assuming table has id 'pcbuild-table'
        const tableRows = table.querySelectorAll("tbody tr");
        const filterContainer = document.getElementById("manufacturer-filter");
        const manufacturerSet = new Set();

        const VISIBLE_COUNT = 4; // Number of manufacturers to show initially
        let expanded = false;

        // Collect unique manufacturers from the Motherboard rows (case-insensitive)
        tableRows.forEach(row => {
            const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer || "Unknown";
            manufacturerSet.add(manufacturer.toLowerCase());  // Store in lowercase to avoid case-sensitive duplicates
        });

        // Prepare checkboxes for each unique manufacturer (case insensitive)
        const manufacturers = Array.from(manufacturerSet).sort(); // Sort alphabetically
        const checkboxElements = [];

        manufacturers.forEach(manufacturer => {
            const label = document.createElement("label");
            const displayName = manufacturer.charAt(0).toUpperCase() + manufacturer.slice(1); // Capitalize the first letter
            label.innerHTML = `<input type="checkbox" name="manufacturer" value="${manufacturer}" checked> ${displayName}`;
            label.style.display = 'block'; // Ensure each on its own line
            checkboxElements.push(label);
        });

        // Append checkboxes to the filter container
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) {
                el.style.display = 'none';
            }
            filterContainer.appendChild(el);
        });

        // Add Show more / Show less link
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

        const allCheckbox = document.getElementById("manufacturer-all");

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

        // Toggle "All"
        allCheckbox.addEventListener("change", function () {
            const allBoxes = document.querySelectorAll("input[name='manufacturer']");
            allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
            applyManufacturerFilter();
        });

        // Individual checkbox change
        filterContainer.addEventListener("change", function (e) {
            if (e.target.name === "manufacturer") {
                applyManufacturerFilter();
            }
        });

        // Show more/less logic
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

                // Show arrow on clicked header
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

                    if (key === 'price' || key === 'memory_max' || key === 'memory_slots') {
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

            rows.forEach((row, i) => {
                row.style.backgroundColor = (i % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                tbody.appendChild(row);
            });
        }

        function getColumnIndex(key) {
            const mapping = {
                name: 2,
                socket: 3,
                form_factor: 4,
                memory_max: 5,
                memory_slots: 6,
                color: 7,
                rating: 8,
                price: 9
            };
            return mapping[key];
        }
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

        applyZebraStripes(); // 🦓 Apply zebra after filtering
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
add_shortcode('pcbuild_parts_motherboard', 'aawp_pcbuild_display_parts_motherboard');

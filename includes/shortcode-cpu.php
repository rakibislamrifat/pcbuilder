<?php
function aawp_pcbuild_display_parts_cpu($atts) {
    
    $atts = shortcode_atts(array('category' => 'cpu'), $atts);
    $input_category = sanitize_title($atts['category']);
    
    // Define the category mapping
    $category_map = [
        'cpu' => 'CPU',
    ];
    
    $category = $category_map[$input_category] ?? 'CPU';
    
    // Create the transient cache key
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

    // Pagination setup
    $all_items = $products['SearchResult']['Items'];
    $total_items = count($all_items);
    $items_per_page = 100;
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
                        <span class="filter-title">MANUFACTURER</span>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="manufacturer-options">
                        <label><input type="checkbox" id="manufacturer-all" checked> All</label><br/>
                        <!-- Dynamic checkboxes will be injected here -->
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
                        <strong>CORE COUNT</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="core-filter" style="display: block;">
                        <div id="core-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="core-min-label">0</span>
                            <span id="core-max-label">0</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>BASE CLOCK</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="base-clock-filter" style="display: block;">
                        <div id="base-clock-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="base-clock-min-label">0</span>
                            <span id="base-clock-max-label">0</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>BOOST CLOCK</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="boost-clock-filter" style="display: block;">
                        <div id="boost-clock-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="boost-clock-min-label">0</span>
                            <span id="boost-clock-max-label">0</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>MICROARCHITECTURE</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="microarchitecture-filter" style="display: block; flex-direction: column; gap: 4px; margin-top: 10px;">
                        <!-- Dynamically generated checkboxes including "All" -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>SOCKET</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="socket-filter" style="display: block;"></div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top: 20px;">
                    <div class="filter-header">
                        <strong>SERIES</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="series-filter" style="display: block;">
                        <!-- Filter options will be dynamically populated here -->
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
                            <th class="sortable-header" data-key="core_count">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Core Count
                                </span>
                            </th>
                            <th class="sortable-header" data-key="base_clock">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Base Clock
                                </span>
                            </th>
                            <th class="sortable-header" data-key="boost_clock">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Boost Clock
                                </span>
                            </th>
                            <th class="sortable-header" data-key="microarch">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Microarchitecture
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
                            $availability = $item['Offers']['Listings'][0]['Availability']['Message'] ?? '—';
                            $product_url = $item['DetailPageURL'] ?? '#';
                            $features = $item['ItemInfo']['Features']['DisplayValues'] ?? [];
                            $features_string = implode(' ', $features);
                            $manufacturer = $item['ItemInfo']['ByLineInfo']['Manufacturer']['DisplayValue'] ?? 'Unknown';
                            $combined_string = $features_string . ' ' . $full_title;
                            $sellerCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? 'Unknown';
                            $sellerRating = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackRating'] ?? 'Unknown';

                            // Extract new data points
                            preg_match('/(\d+)[ -]?[Cc]ore/', $features_string, $core_match);
                            preg_match('/(\d+(\.\d+)?)[ ]?GHz/i', $features_string, $base_match);
                            preg_match('/(?:Boost Clock|Max Boost|Turbo Clock|Turbo Frequency|up to)[^\d]*([\d\.]+)\s?GHz/i', $features_string, $boost_match);
                            preg_match('/Zen\s?[\d\.]+|Zen\s?[a-zA-Z]+/', $features_string, $arch_match);
                            preg_match('/(AMD\s+(A6|A8|A10|A12|Athlon(?:\sII)?(?:\sX[2-4])?|E2-Series|EPYC|FX|Opteron|Phenom\sII\sX[2-6]|Ryzen\s[3-9](?:\sPRO)?|Sempron(?:\sX2)?|Threadripper)|Intel\s+(Celeron|Core\s(?:2\s(Duo|Extreme|Quad)|i[3-9]|i7\sExtreme|Core\sUltra\s[5-9])|Pentium(?:\sGold)?|Processor|Xeon\sE3?))/i', $features_string . ' ' . $full_title, $series_match);

                            // Replace your current socket detection with this:
                            $socket = '-';

                            // 1. Check TechnicalInfo first (most reliable)
                            if (isset($item['ItemInfo']['TechnicalInfo']['DisplayValues'])) {
                                foreach ($item['ItemInfo']['TechnicalInfo']['DisplayValues'] as $techInfo) {
                                    if (preg_match('/(AM[0-9]+|LGA\s?[0-9]+|sTRX4|TR4|sWRX8)/i', $techInfo, $matches)) {
                                        $socket = strtoupper(preg_replace('/^Socket\s*/i', '', $matches[0]));
                                        break;
                                    }
                                }
                            }

                            // 2. Fallback to Features
                            if ($socket === '-' && !empty($features_string)) {
                                preg_match('/(AM[0-9]+|LGA\s?[0-9]+|sTRX4|TR4|sWRX8)/i', $features_string, $matches);
                                if (!empty($matches)) {
                                    $socket = strtoupper(preg_replace('/^Socket\s*/i', '', $matches[0]));
                                }
                            }

                            // 3. Final fallback
                            if ($socket === '-' && !empty($product_url)) {
                                // Consider parsing from URL if needed
                            }

                            // Extract chipset
                            preg_match('/(?:X|B|A|Z|H)[0-9]{3}/i', $features_string, $chipset_match);
                            $chipset = $chipset_match[0] ?? '';

                            $core_count = $core_match[1] ?? '-';
                            $base_clock = $base_match[1] ?? '-';
                            $boost_clock = $boost_match[1] ?? '-';
                            $microarch = $arch_match[0] ?? '-';
                            $series = $series_match[0] ?? '-';
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
                            <td style="padding:10px;"><?php echo $core_count; ?></td>
                            <td style="padding:10px;"><?php echo $base_clock !== '-' ? $base_clock . ' GHz' : '-'; ?></td>
                            <td style="padding:10px;"><?php echo $boost_clock !== '-' ? $boost_clock . ' GHz' : '-'; ?></td>
                            <td style="padding:10px;"><?php echo $microarch; ?></td>
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
                                    data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"
                                    data-socket="<?php echo isset($socket) ? esc_attr($socket) : ''; ?>"
                                    data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                    data-microarchitecture="<?php echo esc_attr($microarch); ?>"
                                    data-chipset="<?php echo isset($chipset) ? esc_attr($chipset) : ''; ?>"
                                    data-series="<?php echo isset($series) ? esc_attr($series) : ''; ?>"
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
                                    data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"
                                    data-socket="<?php echo isset($socket) ? esc_attr($socket) : ''; ?>"
                                    data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                    data-microarchitecture="<?php echo esc_attr($microarch); ?>"
                                    data-chipset="<?php echo isset($chipset) ? esc_attr($chipset) : ''; ?>"
                                    data-series="<?php echo isset($series) ? esc_attr($series) : ''; ?>">
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
// SELLER FILTERING
document.addEventListener("DOMContentLoaded", function () {
    const ratingRanges = {
        "5": { min: 4.5, max: 5.0 },
        "4": { min: 3.5, max: 4.4 },
        "3": { min: 2.5, max: 3.4 },
        "unrated": "unrated"
    };

    const ratingFilterContainer = document.getElementById("rating-filter");
    const productRows = document.querySelectorAll("#pcbuild-table tbody tr");

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
                row.style.backgroundColor = "";
            }
        });
    }

    function applyRatingFilter() {
        const selected = Array.from(ratingFilterInputs)
            .filter(input => input.checked && input.value !== "all")
            .map(input => input.value);

        const isAllChecked = document.querySelector('#rating-filter input[value="all"]').checked;

        let visibleCount = 0;
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

            if (visible) {
                row.style.backgroundColor = (visibleCount % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                visibleCount++;
            } else {
                row.style.backgroundColor = "";
            }
        });
    }

    // 'All' checkbox logic
    document.querySelector('#rating-filter input[value="all"]').addEventListener("change", function () {
        if (this.checked) {
            ratingFilterInputs.forEach(input => {
                if (input.value !== "all") input.checked = false;
            });
        }
        applyRatingFilter();
    });

    // Other checkboxes logic
    ratingFilterInputs.forEach(input => {
        if (input.value !== "all") {
            input.addEventListener("change", function () {
                if (this.checked) {
                    document.querySelector('#rating-filter input[value="all"]').checked = false;
                }
                const anyChecked = Array.from(ratingFilterInputs)
                    .some(cb => cb.checked && cb.value !== "all");
                if (!anyChecked) {
                    document.querySelector('#rating-filter input[value="all"]').checked = true;
                }
                applyRatingFilter();
            });
        }
    });

    applyRatingFilter(); // Initial run
});
</script>

<script>
    // Manufacturer filtering
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const tableRows = table.querySelectorAll("tbody tr");
        const filterContainer = document.getElementById("manufacturer-options");
        const manufacturerSet = new Set();

        const VISIBLE_COUNT = 4; // How many manufacturers to show initially
        let expanded = false;

        // Normalize manufacturer values (group Intel variants under "Intel")
        function normalizeManufacturer(manufacturer) {
            const lowerCaseManufacturer = manufacturer.toLowerCase();
            if (lowerCaseManufacturer.includes("intel")) {
                return "Intel"; // All Intel-related values grouped under "Intel"
            }
            return manufacturer;
        }

        // Collect unique manufacturers
        tableRows.forEach(row => {
            const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer || "Unknown";
            manufacturerSet.add(normalizeManufacturer(manufacturer));
        });

        // Prepare checkboxes
        const manufacturers = Array.from(manufacturerSet).sort(); // Sort alphabetically
        const checkboxElements = [];

        manufacturers.forEach(manufacturer => {
            const label = document.createElement("label");
            label.innerHTML = `<input type="checkbox" name="manufacturer" value="${manufacturer}" checked> ${manufacturer}`;
            label.style.display = 'block'; // Ensure each on its own line
            checkboxElements.push(label);
        });

        // Append checkboxes to container
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

        // Zebra stripe function
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
                const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer;
                const normalizedManufacturer = normalizeManufacturer(manufacturer);
                const show = selected.includes(normalizedManufacturer);
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
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const seriesFilterContainer = document.getElementById("series-filter");

    if (!table || !seriesFilterContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));
    const seriesSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;
    const checkboxElements = [];

    // Extract unique series values
    rows.forEach(row => {
        const btn = row.querySelector(".add-to-builder");
        const series = btn?.getAttribute("data-series")?.trim();
        if (series) seriesSet.add(series);
    });

    const seriesList = Array.from(seriesSet).sort();

    // "All Series" checkbox
    const allSeriesCheckboxWrapper = document.createElement("label");
    allSeriesCheckboxWrapper.style.display = "block";
    allSeriesCheckboxWrapper.innerHTML = `
        <input type="checkbox" class="series-checkbox" value="all" checked>
        All Series
    `;
    seriesFilterContainer.appendChild(allSeriesCheckboxWrapper);

    const allCheckbox = () => seriesFilterContainer.querySelector(".series-checkbox[value='all']");

    // Create individual series checkboxes
    seriesList.forEach((series, index) => {
        const label = document.createElement("label");
        label.style.display = index >= VISIBLE_COUNT ? "none" : "block";
        label.innerHTML = `
            <input type="checkbox" class="series-checkbox" value="${series}" checked>
            ${series}
        `;
        checkboxElements.push(label);
        seriesFilterContainer.appendChild(label);
    });

    // Show more / Show less toggle
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    seriesFilterContainer.appendChild(toggleLink);

    const zebraStriping = () => {
        let visibleRows = rows.filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = index % 2 === 0 ? "#d4d4d4" : "#ebebeb";
        });
    };

    const checkboxes = () => seriesFilterContainer.querySelectorAll(".series-checkbox");

    function filterRows() {
        const selectedSeries = Array.from(checkboxes())
            .filter(cb => cb.checked && cb.value !== "all")
            .map(cb => cb.value);

        rows.forEach(row => {
            const btn = row.querySelector(".add-to-builder");
            const series = btn?.getAttribute("data-series")?.trim();

            if (selectedSeries.length === 0 || selectedSeries.includes(series)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });

        // Hide entire table if nothing is shown
        const anyVisible = rows.some(row => row.style.display !== "none");
        table.style.display = anyVisible ? "" : "none";

        zebraStriping();
    }

    seriesFilterContainer.addEventListener("change", function (e) {
        const target = e.target;

        if (target.value === "all") {
            checkboxes().forEach(cb => cb.checked = target.checked);
            table.style.display = target.checked ? "" : "none";
            if (!target.checked) {
                rows.forEach(row => row.style.display = "none");
            }
        } else {
            allCheckbox().checked = false;
            table.style.display = "";
        }

        filterRows();
    });

    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;

        checkboxElements.forEach((el, index) => {
            el.style.display = expanded || index < VISIBLE_COUNT ? "block" : "none";
        });

        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filtering
    filterRows();
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const filterContainer = document.getElementById("socket-filter");

    if (!table || !filterContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));
    const socketSet = new Set();

    const VISIBLE_COUNT = 4;
    let expanded = false;
    const checkboxElements = [];

    // Extract unique socket values from data attributes
    rows.forEach(row => {
        const btn = row.querySelector(".add-to-builder");
        const socket = btn?.getAttribute("data-socket")?.trim();
        if (socket) socketSet.add(socket);
    });

    const socketList = Array.from(socketSet).sort();

    // "All" checkbox
    const allCheckboxWrapper = document.createElement("label");
    allCheckboxWrapper.style.display = "block";
    allCheckboxWrapper.innerHTML = `
        <input type="checkbox" class="socket-checkbox" value="all" checked>
        All
    `;
    filterContainer.appendChild(allCheckboxWrapper);

    const allCheckbox = () => filterContainer.querySelector(".socket-checkbox[value='all']");

    // Create individual checkboxes
    socketList.forEach((socket, index) => {
        const label = document.createElement("label");
        label.style.display = index >= VISIBLE_COUNT ? "none" : "block";
        label.innerHTML = `
            <input type="checkbox" class="socket-checkbox" value="${socket}" checked>
            ${socket}
        `;
        checkboxElements.push(label);
        filterContainer.appendChild(label);
    });

    // Show more / Show less link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    function zebraStriping() {
        let visibleRows = rows.filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = index % 2 === 0 ? "#d4d4d4" : "#ebebeb";
        });
    }

    function checkboxes() {
        return filterContainer.querySelectorAll(".socket-checkbox");
    }

    function filterBySocket() {
        const selected = Array.from(checkboxes())
            .filter(cb => cb.checked && cb.value !== "all")
            .map(cb => cb.value);

        if (allCheckbox().checked || selected.length === 0) {
            rows.forEach(row => row.style.display = "");
        } else {
            rows.forEach(row => {
                const btn = row.querySelector(".add-to-builder");
                const socket = btn?.getAttribute("data-socket")?.trim();
                row.style.display = selected.includes(socket) ? "" : "none";
            });
        }

        zebraStriping();
    }

    filterContainer.addEventListener("change", function (e) {
        const target = e.target;

        if (target.value === "all") {
            checkboxes().forEach(cb => {
                if (cb.value !== "all") cb.checked = target.checked;
            });
        } else {
            allCheckbox().checked = false;
        }

        filterBySocket();
    });

    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;

        checkboxElements.forEach((el, index) => {
            el.style.display = expanded || index < VISIBLE_COUNT ? "block" : "none";
        });

        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial render
    filterBySocket();
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("microarchitecture-filter");
    const microSet = new Set();

    const VISIBLE_COUNT = 4; // How many to show initially
    let expanded = false;

    // Collect unique microarchitectures
    tableRows.forEach(row => {
        const micro = row.querySelector("td:nth-child(6)")?.textContent.trim();
        if (micro) microSet.add(micro);
    });

    const micros = Array.from(microSet).sort();
    const checkboxElements = [];

    // Create "All" checkbox
    const allLabel = document.createElement("label");
    allLabel.innerHTML = `<input type="checkbox" class="micro-checkbox" value="all" checked> All`;
    allLabel.style.display = "block";
    filterContainer.appendChild(allLabel);

    // Create microarchitecture checkboxes
    micros.forEach((micro, index) => {
        const label = document.createElement("label");
        label.innerHTML = `<input type="checkbox" class="micro-checkbox" value="${micro}" checked> ${micro}`;
        label.style.display = index >= VISIBLE_COUNT ? "none" : "block";
        filterContainer.appendChild(label);
        checkboxElements.push(label);
    });

    // Show more/less toggle
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            el.style.display = index >= VISIBLE_COUNT ? (expanded ? "block" : "none") : "block";
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Helpers
    const checkboxes = () => filterContainer.querySelectorAll(".micro-checkbox");
    const allCheckbox = () => filterContainer.querySelector(".micro-checkbox[value='all']");

    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function applyMicroFilter() {
        const selected = Array.from(checkboxes())
            .filter(cb => cb.checked && cb.value !== "all")
            .map(cb => cb.value);

        if (allCheckbox().checked || selected.length === 0) {
            tableRows.forEach(row => row.style.display = "");
        } else {
            tableRows.forEach(row => {
                const micro = row.querySelector("td:nth-child(6)")?.textContent.trim();
                row.style.display = selected.includes(micro) ? "" : "none";
            });
        }

        applyZebraStriping();
    }

    // Handle checkbox changes
    filterContainer.addEventListener("change", function (e) {
        const target = e.target;

        const allBox = allCheckbox();
        const allBoxes = Array.from(checkboxes());
        const individualBoxes = allBoxes.filter(cb => cb.value !== "all");

        if (target.value === "all") {
            // Master switch logic
            const checked = target.checked;
            individualBoxes.forEach(cb => cb.checked = checked);
        } else {
            // Individual toggles remove "All" if any is unchecked
            allBox.checked = individualBoxes.every(cb => cb.checked);
        }

        applyMicroFilter();
    });


    // Initial filter
    applyMicroFilter();
});
</script>


<script>
    // BOOST CLOCK RANGE SLIDER FILTER with Zebra Striping
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const sliderContainer = document.getElementById("boost-clock-slider");
        const minLabel = document.getElementById("boost-clock-min-label");
        const maxLabel = document.getElementById("boost-clock-max-label");

        if (!table || !sliderContainer) return;

        const rows = Array.from(table.querySelectorAll("tbody tr"));
        const boostClocks = rows.map(row => {
            const clockText = row.querySelector("td:nth-child(4)")?.textContent.replace(/[^\d.]/g, '') || "0";
            return parseFloat(clockText) || 0;
        });

        const minClock = Math.floor(Math.min(...boostClocks));
        const maxClock = Math.ceil(Math.max(...boostClocks));
        let currentMin = minClock;
        let currentMax = maxClock;

        // Set default labels
        minLabel.textContent = `${minClock} GHz`;
        maxLabel.textContent = `${maxClock} GHz`;

        // Create 2 sliders
        sliderContainer.innerHTML = `
            <input type="range" class="min-range-bg" id="min-boost-clock" min="${minClock}" max="${maxClock}" value="${minClock}" step="0.1" style="width: 100%;">
            <input type="range" class="max-range-bg" id="max-boost-clock" min="${minClock}" max="${maxClock}" value="${maxClock}" step="0.1" style="width: 100%; margin-top: 10px;">
        `;

        const minSlider = document.getElementById("min-boost-clock");
        const maxSlider = document.getElementById("max-boost-clock");

        // Apply zebra striping to visible rows
        function applyZebraStriping() {
            const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        function filterByBoostClock() {
            const minVal = parseFloat(minSlider.value);
            const maxVal = parseFloat(maxSlider.value);
            currentMin = minVal;
            currentMax = maxVal;

            minLabel.textContent = `${minVal} GHz`;
            maxLabel.textContent = `${maxVal} GHz`;

            rows.forEach(row => {
                const clockText = row.querySelector("td:nth-child(4)")?.textContent.replace(/[^\d.]/g, '') || "0";
                const boostClock = parseFloat(clockText) || 0;

                // Show or hide the row based on boost clock filter
                row.style.display = (boostClock >= minVal && boostClock <= maxVal) ? "" : "none";
            });

            // Apply zebra striping to the visible rows
            applyZebraStriping();
        }

        // Event listeners for sliders
        minSlider.addEventListener("input", () => {
            if (parseFloat(minSlider.value) > parseFloat(maxSlider.value)) {
                minSlider.value = maxSlider.value;
            }
            filterByBoostClock();
        });

        maxSlider.addEventListener("input", () => {
            if (parseFloat(maxSlider.value) < parseFloat(minSlider.value)) {
                maxSlider.value = minSlider.value;
            }
            filterByBoostClock();
        });

        // Initial filter and zebra striping apply
        filterByBoostClock();
    });
</script>


<script>
    // BASE CLOCK RANGE SLIDER FILTER with Zebra Striping
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const sliderContainer = document.getElementById("base-clock-slider");
        const minLabel = document.getElementById("base-clock-min-label");
        const maxLabel = document.getElementById("base-clock-max-label");

        if (!table || !sliderContainer) return;

        const rows = Array.from(table.querySelectorAll("tbody tr"));
        const baseClocks = rows.map(row => {
            const clockText = row.querySelector("td:nth-child(3)")?.textContent.replace(/[^\d.]/g, '') || "0";
            return parseFloat(clockText) || 0;
        });

        const minClock = Math.floor(Math.min(...baseClocks));
        const maxClock = Math.ceil(Math.max(...baseClocks));
        let currentMin = minClock;
        let currentMax = maxClock;

        // Set default labels
        minLabel.textContent = `${minClock} GHz`;
        maxLabel.textContent = `${maxClock} GHz`;

        // Create 2 sliders
        sliderContainer.innerHTML = `
            <input type="range" class="min-range-bg" id="min-base-clock" min="${minClock}" max="${maxClock}" value="${minClock}" step="0.1" style="width: 100%;">
            <input type="range" class="max-range-bg" id="max-base-clock" min="${minClock}" max="${maxClock}" value="${maxClock}" step="0.1" style="width: 100%; margin-top: 10px;">
        `;

        const minSlider = document.getElementById("min-base-clock");
        const maxSlider = document.getElementById("max-base-clock");

        // Apply zebra striping to visible rows
        function applyZebraStriping() {
            const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        function filterByBaseClock() {
            const minVal = parseFloat(minSlider.value);
            const maxVal = parseFloat(maxSlider.value);
            currentMin = minVal;
            currentMax = maxVal;

            minLabel.textContent = `${minVal} GHz`;
            maxLabel.textContent = `${maxVal} GHz`;

            rows.forEach(row => {
                const clockText = row.querySelector("td:nth-child(3)")?.textContent.replace(/[^\d.]/g, '') || "0";
                const baseClock = parseFloat(clockText) || 0;

                // Show or hide the row based on base clock filter
                row.style.display = (baseClock >= minVal && baseClock <= maxVal) ? "" : "none";
            });

            // Apply zebra striping to the visible rows
            applyZebraStriping();
        }

        // Event listeners for sliders
        minSlider.addEventListener("input", () => {
            if (parseFloat(minSlider.value) > parseFloat(maxSlider.value)) {
                minSlider.value = maxSlider.value;
            }
            filterByBaseClock();
        });

        maxSlider.addEventListener("input", () => {
            if (parseFloat(maxSlider.value) < parseFloat(minSlider.value)) {
                maxSlider.value = minSlider.value;
            }
            filterByBaseClock();
        });

        // Initial filter and zebra striping apply
        filterByBaseClock();
    });
</script>


<script>
    // CORE COUNT RANGE SLIDER FILTER with Zebra Striping
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const sliderContainer = document.getElementById("core-slider");
        const minLabel = document.getElementById("core-min-label");
        const maxLabel = document.getElementById("core-max-label");

        if (!table || !sliderContainer) return;

        const rows = Array.from(table.querySelectorAll("tbody tr"));
        const coreCounts = rows.map(row => {
            const coreText = row.querySelector("td:nth-child(3)")?.textContent.trim() || "0";
            return parseInt(coreText) || 0;
        });

        const minCore = Math.min(...coreCounts);
        const maxCore = Math.max(...coreCounts);
        let currentMin = minCore;
        let currentMax = maxCore;

        // Set default labels
        minLabel.textContent = `${minCore}`;
        maxLabel.textContent = `${maxCore}`;

        // Create 2 sliders
        sliderContainer.innerHTML = ` 
            <input type="range" class="min-range-bg" id="min-core" min="${minCore}" max="${maxCore}" value="${minCore}" step="1" style="width: 100%;"> 
            <input type="range" class="max-range-bg" id="max-core" min="${minCore}" max="${maxCore}" value="${maxCore}" step="1" style="width: 100%; margin-top: 10px;"> 
        `;

        const minSlider = document.getElementById("min-core");
        const maxSlider = document.getElementById("max-core");

        // Apply zebra striping to visible rows
        function applyZebraStriping() {
            const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        function filterByCore() {
            const minVal = parseInt(minSlider.value);
            const maxVal = parseInt(maxSlider.value);
            currentMin = minVal;
            currentMax = maxVal;

            minLabel.textContent = `${minVal}`;
            maxLabel.textContent = `${maxVal}`;

            rows.forEach(row => {
                const coreText = row.querySelector("td:nth-child(3)")?.textContent.trim() || "0";
                const coreCount = parseInt(coreText) || 0;

                // Show or hide the row based on core count filter
                row.style.display = (coreCount >= minVal && coreCount <= maxVal) ? "" : "none";
            });

            // Apply zebra striping to the visible rows
            applyZebraStriping();
        }

        // Event listeners for sliders
        minSlider.addEventListener("input", () => {
            if (parseInt(minSlider.value) > parseInt(maxSlider.value)) {
                minSlider.value = maxSlider.value;
            }
            filterByCore();
        });

        maxSlider.addEventListener("input", () => {
            if (parseInt(maxSlider.value) < parseInt(minSlider.value)) {
                maxSlider.value = minSlider.value;
            }
            filterByCore();
        });

        // Initial filter and zebra striping apply
        filterByCore();
    });
</script>


<script>
    //PRICE FILTERING
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const sliderContainer = document.getElementById("price-slider");
        const minLabel = document.getElementById("price-min-label");
        const maxLabel = document.getElementById("price-max-label");

        if (!table || !sliderContainer) return;

        const rows = Array.from(table.querySelectorAll("tbody tr"));
        const prices = rows.map(row => {
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

        function applyZebraStriping() {
            const visibleRows = rows.filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
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

            // Apply zebra striping after filtering
            applyZebraStriping();
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
    // SORTING LOGIC
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

        // Sort rows function
        function sortTableByKey(key, direction) {
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((a, b) => {
                const getText = (row, key) => {
                    const cell = row.querySelector(`td:nth-child(${getColumnIndex(key)})`);
                    if (key === 'rating') {
                        // Get the rating value from the data-rating attribute for sorting
                        const ratingValue = parseFloat(cell?.dataset.rating || '0');
                        return ratingValue;
                    }
                    return cell?.innerText.trim().toLowerCase() || '';
                };

                const valA = getText(a, key);
                const valB = getText(b, key);

                // If both values are numbers, sort numerically
                if (!isNaN(valA) && !isNaN(valB)) {
                    return direction === 'asc' ? valA - valB : valB - valA;
                }

                // Otherwise sort alphabetically
                return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            });

            // Apply alternating row backgrounds after sort
            rows.forEach((row, i) => {
                row.style.backgroundColor = (i % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                tbody.appendChild(row);
            });
        }

        // Column index mapping based on data-key
        function getColumnIndex(key) {
            const mapping = {
                name: 2,
                core_count: 3,
                base_clock: 4,
                boost_clock: 5,
                microarch: 6,
                rating: 7,
                price: 8
            };
            return mapping[key];
        }
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const selectAllBtn = document.getElementById("select_all");
    const clearSelectedBtn = document.getElementById("clear_selected");
    const compareSelectedBtn = document.getElementById("compare_selected");
    const checkboxes = document.querySelectorAll(".select-product");

    // Helper: Enable or disable link by adding/removing 'disabled' class
    function setLinkState(link, enabled) {
        if (enabled) {
            link.classList.remove("disabled");
        } else {
            link.classList.add("disabled");
        }
    }

    // Update button states based on selected checkboxes
    function updateButtonStates() {
        const anyChecked = [...checkboxes].some(cb => cb.checked);
        setLinkState(clearSelectedBtn, anyChecked);
        setLinkState(compareSelectedBtn, anyChecked);
    }

    // Select All click
    selectAllBtn.addEventListener("click", function (e) {
        e.preventDefault();
        checkboxes.forEach(cb => cb.checked = true);
        updateButtonStates();
    });

    // Clear Selected click
    clearSelectedBtn.addEventListener("click", function (e) {
        e.preventDefault();
        if (clearSelectedBtn.classList.contains("disabled")) return;
        checkboxes.forEach(cb => cb.checked = false);
        updateButtonStates();
    });

    // Compare Selected click (you can add actual compare logic here later)
    compareSelectedBtn.addEventListener("click", function (e) {
        e.preventDefault();
        if (compareSelectedBtn.classList.contains("disabled")) return;
        alert("Compare feature coming soon!");
    });

    // Update button states on checkbox change
    checkboxes.forEach(cb => {
        cb.addEventListener("change", updateButtonStates);
    });

    // Initial state: only Select All is enabled
    setLinkState(clearSelectedBtn, false);
    setLinkState(compareSelectedBtn, false);
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
add_shortcode('pcbuild_parts_cpu', 'aawp_pcbuild_display_parts_cpu');

<?php
function aawp_pcbuild_display_parts_os($atts) {
    $atts = shortcode_atts(array('category' => 'operating-system'), $atts);
    $input_category = sanitize_title($atts['category']);

    $category_map = [
        'os' => 'Operating System',
        'operating-system' => 'Operating System',
    ];

    $category = $category_map[$input_category] ?? 'Operating System';

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
        Choose An <?php echo esc_html($category); ?>
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
                        <strong>SELLER RATING</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="rating-filter">
                        <!-- Filters will be injected here -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Mode</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="mode-filter">
                        <label><input type="checkbox" id="mode-all" checked> All</label><br/>
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
                            <th class="sortable-header" data-key="mode"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Mode</span></th>
                            <!-- <th class="sortable-header" data-key="max-memory"><span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Maximum Supported Memory</span></th> -->
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
                        $words = explode(' ', $full_title);
                        $chunks = array_chunk($words, 10);
                        $formatted_title = '';

                        foreach ($chunks as $chunk) {
                            $formatted_title .= implode(' ', $chunk) . '<br>';
                        }

                        $title = wp_kses_post(trim($formatted_title));
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
                        $sellerCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? 'Unknown';
                        $sellerRating = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackRating'] ?? 'Unknown';

                        // Extract OS attributes
                        preg_match('/(64[-\s]?bit|32[-\s]?bit)/i', $combined_string, $mode_match);
                        $mode = isset($mode_match[1]) ? str_replace(['-',' '], '-', strtolower($mode_match[1])) : 'OEM / Retail';

                        /* preg_match('/(\d{2,4})\s?(GB|TB)\s?(?:max|maximum|\(max\)|supported)?/i', $combined_string, $memory_match);
                        $max_supported_memory = isset($memory_match[1], $memory_match[2]) ? $memory_match[1] . ' ' . strtoupper($memory_match[2]) : '—';  */                    

                        $mode = $mode_match[1] ?? '-';
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
                        <td style="padding:10px;"><?php echo esc_html($mode); ?></td>
                        <!-- <td style="padding:10px;"><?php //echo esc_html($max_supported_memory); ?></td> -->
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
                                data-category="Operating System"
                                data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                data-mode="<?php echo esc_attr($mode); ?>"
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
                                data-image="<?php echo esc_url($raw_image); ?>"
                                data-base="<?php echo esc_attr($base_price); ?>"
                                data-shipping="FREE"
                                data-availability="<?php echo esc_attr($availability); ?>"
                                data-price="<?php echo esc_attr($base_price); ?>"
                                data-category="Operating System"
                                data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                data-mode="<?php echo esc_attr($mode); ?>"
                                data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>">
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
// Mode Filtering for Storage Page
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("mode-filter");
    const allCheckbox = document.getElementById("mode-all");
    const modeSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique modes (case-insensitive) from data-mode attribute
    tableRows.forEach(row => {
        const mode = row.querySelector("button.add-to-builder")?.dataset.mode || "Unknown";
        modeSet.add(mode.trim().toLowerCase());
    });

    const modes = Array.from(modeSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    modes.forEach(mode => {
        const label = document.createElement("label");
        const displayName = mode.charAt(0).toUpperCase() + mode.slice(1); // Capitalizing first letter
        label.innerHTML = `<input type="checkbox" name="mode" value="${mode}" checked> ${displayName}`;
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
        const allBoxes = Array.from(document.querySelectorAll("input[name='mode']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyModeFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='mode']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const mode = row.querySelector("button.add-to-builder")?.dataset.mode.trim().toLowerCase();
            row.style.display = selected.includes(mode) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='mode']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyModeFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "mode") {
            applyModeFilter();
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
    applyModeFilter();
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
        const priceText = row.querySelector("td:nth-child(4)")?.textContent.replace(/[^0-9.]/g, '') || "0";
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
            const priceText = row.querySelector("td:nth-child(4)")?.textContent.replace(/[^0-9.]/g, '') || "0";
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

    let currentSort = { key: null, direction: 'asc' };

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const key = header.dataset.key;
            currentSort.direction = (currentSort.key === key && currentSort.direction === 'asc') ? 'desc' : 'asc';
            currentSort.key = key;

            headers.forEach(h => {
                h.innerHTML = `▶ ${h.textContent.trim().replace(/^▲|▼|▶/, '')}`;
            });

            header.innerHTML = `${currentSort.direction === 'asc' ? '▲' : '▼'} ${header.textContent.trim().replace(/^▲|▼|▶/, '')}`;

            sortTableByKey(key, currentSort.direction);
        });
    });

    function sortTableByKey(key, direction) {
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));

        rows.sort((a, b) => {
            const getValue = (row, key) => {
                const index = getColumnIndex(key);
                const cell = row.querySelector(`td:nth-child(${index})`);
                if (!cell) return '';

                if (key === 'rating') {
                    return parseFloat(cell.dataset.rating || '0');
                }

                if (key === 'price') {
                    const num = parseFloat(cell.textContent.replace(/[^0-9.]/g, ''));
                    return isNaN(num) ? 0 : num;
                }

                return cell.textContent.trim().toLowerCase();
            };

            const valA = getValue(a, key);
            const valB = getValue(b, key);

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
            mode: 3,
            rating: 4,
            price: 5
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
add_shortcode('pcbuild_parts_os', 'aawp_pcbuild_display_parts_os');

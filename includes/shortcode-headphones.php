<?php
function aawp_pcbuild_display_parts_headphones($atts) {

    $atts = shortcode_atts(array('category' => 'headphones'), $atts);
    $input_category = sanitize_title($atts['category']);
    
    // Define the category mapping
    $category_map = [
        'headphones' => 'headphones',
    ];
    
    $category = $category_map[$input_category] ?? 'headphones'; // Default to 'headphones' if not found in the map
    
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
    
    ?>

<div
    style="background-color:#41466c; padding:20px; color:#fff; font-size:24px; font-weight:bold; text-align:center; margin-bottom:40px">
    Choose A <?php echo esc_html($category); ?>
</div>
<div style="width:90%; margin:0 auto; font-family:sans-serif;">
    <div class="pcbuilder-container" style="display:flex; gap:20px; margin-top:20px;">

        <!-- Sidebar -->

        <button class="pcbuild-sidebar-toggle">Filters</button>

        <div class="pcbuild-sidebar pcbuild-sidebar-mobile"
            style="width:250px; background:#f9f9f9; padding:20px; border-radius:8px;">
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
                    <label><input type="checkbox" id="manufacturer-all" checked> All</label><br />
                    <!-- Dynamic checkboxes will be injected here -->
                </div>
            </div>
            <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                <div class="filter-header">
                    <strong>RATING</strong>
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
                    <!-- Filters will be injected here -->
                </div>
            </div>


            <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                <div class="filter-header">
                    <strong>MICROPHONE</strong>
                    <button class="filter-toggle">−</button>
                </div>
                <div class="filter-options" id="microphone-filter">
                    <!-- Filters will be injected here -->
                </div>
            </div>


            <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                <div class="filter-header">
                    <strong>WIRELESS</strong>
                    <button class="filter-toggle">−</button>
                </div>
                <div class="filter-options" id="wireless-filter">
                    <!-- Filters will be injected here -->
                </div>
            </div>

            <!-- <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                <div class="filter-header">
                    <strong>ENCLOSURE TYPE</strong>
                    <button class="filter-toggle">−</button>
                </div>
                <div class="filter-options" id="enclosure-type-filter">

                </div>
            </div> -->

            <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                <div class="filter-header">
                    <strong>COLOR</strong>
                    <button class="filter-toggle">−</button>
                </div>
                <div class="filter-options" id="color-filter">
                    <!-- No <label> for "All" here -->
                </div>
            </div>

            <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                <div class="filter-header">
                    <strong>ACTIVE NOISE CANCELLATION</strong>
                    <button class="filter-toggle">−</button>
                </div>
                <div class="filter-options" id="anc-filter">
                    <!-- Filters will be injected here -->
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
                        <th class="sortable-header" data-key="type">
                            <span class="sort-header-label">
                                <span class="sort-arrow">&#9654;</span> Type
                            </span>
                        </th>
                        <th class="sortable-header" data-key="frequency_response">
                            <span class="sort-header-label">Frequency Response</span>
                            <span class="sort-arrow">&#9654;</span>
                        </th>

                        <th class="sortable-header" data-key="microphone">
                            <span class="sort-header-label">Microphone</span>
                            <span class="sort-arrow">&#9654;</span>
                        </th>

                        <th class="sortable-header" colspan="2" data-key="wireless">
                            <span class="sort-header-label">Wireless</span>
                            <span class="sort-arrow">&#9654;</span>
                        </th>

                        <!-- <th class="sortable-header" colspan="2" data-key="enclosure_type">
                            <span class="sort-header-label">Enclosure Type</span>
                            <span class="sort-arrow">&#9654;</span>
                        </th> -->

                        <th class="sortable-header" data-key="color">
                            <span class="sort-header-label">Color</span>
                            <span class="sort-arrow">&#9654;</span>
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
    $image = $item['Images']['Primary']['Large']['URL'] ??
             $item['Images']['Primary']['Medium']['URL'] ??
             $item['Images']['Primary']['Small']['URL'] ?? '';
    $raw_image = esc_url($image);
    $price = $item['Offers']['Listings'][0]['Price']['DisplayAmount'] ?? 'N/A';
    $base_price = $price;
    $availability = $item['Offers']['Listings'][0]['Availability']['Message'] ?? '—';
    $product_url = $item['DetailPageURL'] ?? '#';

    $features = $item['ItemInfo']['Features']['DisplayValues'] ?? [];
    $features_string = implode(' ', $features);

    // ✅ ANC extraction
    $anc = (stripos($features_string, 'noise cancellation') !== false || stripos($features_string, 'ANC') !== false) ? 'Yes' : 'No';

    $manufacturer = $item['ItemInfo']['ByLineInfo']['Manufacturer']['DisplayValue'] ?? 'Unknown';
    $color = $item['ItemInfo']['ProductInfo']['Color']['DisplayValue'] ?? '-';

    // Feature-specific values
    $type = 'Over-Ear'; // fallback
    $frequency_response = '-';
    $microphone = 'No';
    $wireless = 'No';
    $enclosure_type = '-';

    foreach ($features as $feature) {
        if (preg_match('/\b(20\s?Hz\s?–?\s?40\s?kHz|\d{1,3}\s?Hz\s?[-–]\s?\d{2,4}\s?kHz?)\b/i', $feature, $match)) {
            $frequency_response = $match[0];
        }
        if (stripos($feature, 'microphone') !== false || stripos($feature, 'mic') !== false) {
            $microphone = 'Yes';
        }
        if (stripos($feature, 'wireless') !== false || stripos($feature, 'bluetooth') !== false) {
            $wireless = 'Yes';
        }
        if (stripos($feature, 'closed-back') !== false || stripos($feature, 'open-back') !== false) {
            $enclosure_type = ucfirst(explode(' ', $feature)[0]);
        }
    }

    // Seller info
    $sellerCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? '0';
    $sellerRating = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackRating'] ?? '0';
    $rating_count = display_rating_and_count($sellerRating, $sellerCount);
?>
                    <tr class="product-row" style="background-color: <?php echo $row_bg; ?>">
                        <td style="padding: 10px;">
                            <img src="<?php echo $raw_image; ?>" alt="<?php echo $title; ?>"
                                style="min-width:115px; height:115px; border-radius:4px;" />
                        </td>
                        <td style="font-weight:800;"><?php echo $title; ?></td>
                        <td class="type-cell" style="min-width:100px;"><?php echo esc_html($type); ?></td>
                        <td><?php echo esc_html($frequency_response); ?></td>
                        <td class="microphone-cell"><?php echo esc_html($microphone); ?></td>
                        <td class="wireless-cell"><?php echo esc_html($wireless); ?></td>
                        <!-- <td class="enclosure-type-cell"><?php echo esc_html($enclosure_type); ?></td> -->
                        <td class="anc-cell" style="padding:0; margin:0; border:0; width:0; font-size:0;">
                            <span style="display:none;"><?php echo esc_html($anc); ?></span>
                        </td>
                        <td class="color-cell"><?php echo esc_html($color); ?></td>
                        <td data-rating="<?php echo esc_attr($sellerRating); ?>"><?php echo $rating_count; ?></td>
                        <td class="price-cell"><?php echo esc_html($price); ?></td>
                        <td colspan="2">
                            <button class="add-to-builder" data-asin="<?php echo esc_attr($asin); ?>"
                                data-title="<?php echo esc_attr($full_title); ?>"
                                data-image="<?php echo esc_url($image); ?>"
                                data-base="<?php echo esc_attr($base_price); ?>" data-shipping="FREE"
                                data-availability="<?php echo esc_attr($availability); ?>"
                                data-price="<?php echo esc_attr($base_price); ?>"
                                data-category="<?php echo esc_attr($category); ?>"
                                data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                data-rating="<?php echo esc_attr($sellerRating); ?>"
                                data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                style="padding:10px 18px; background-color:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer;">
                                <?php _e('Add', 'aawp-pcbuild'); ?>
                            </button>
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
    font-weight: 700 !important;
    color: #000;
    font-size: 14px !important;
}
</style>

<script>
// SELLER FILTERING
document.addEventListener("DOMContentLoaded", function() {
    const ratingRanges = {
        "5": {
            min: 4.5,
            max: 5.0
        },
        "4": {
            min: 3.5,
            max: 4.4
        },
        "3": {
            min: 2.5,
            max: 3.4
        },
        "unrated": "unrated"
    };

    const ratingFilterContainer = document.getElementById("rating-filter");
    const productRows = document.querySelectorAll("#pcbuild-table tbody tr");

    const ratingOptions = [{
            value: "all",
            label: "All"
        },
        {
            value: "5",
            label: "★★★★★"
        },
        {
            value: "4",
            label: "★★★★☆"
        },
        {
            value: "3",
            label: "★★★☆☆"
        },
        {
            value: "unrated",
            label: "Unrated"
        }
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
                    if (range && typeof range === "object" && rating >= range.min && rating <= range
                        .max) {
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
    document.querySelector('#rating-filter input[value="all"]').addEventListener("change", function() {
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
            input.addEventListener("change", function() {
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
document.addEventListener("DOMContentLoaded", function() {
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
        const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer ||
            "Unknown";
        manufacturerSet.add(normalizeManufacturer(manufacturer));
    });

    // Prepare checkboxes
    const manufacturers = Array.from(manufacturerSet).sort(); // Sort alphabetically
    const checkboxElements = [];

    manufacturers.forEach(manufacturer => {
        const label = document.createElement("label");
        label.innerHTML =
            `<input type="checkbox" name="manufacturer" value="${manufacturer}" checked> ${manufacturer}`;
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
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !==
            "none");
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
    allCheckbox.addEventListener("change", function() {
        const allBoxes = document.querySelectorAll("input[name='manufacturer']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyManufacturerFilter();
    });

    // Individual checkbox change
    filterContainer.addEventListener("change", function(e) {
        if (e.target.name === "manufacturer") {
            applyManufacturerFilter();
        }
    });

    // Show more/less logic
    toggleLink.addEventListener("click", function(e) {
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
// TYPE filtering
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("type-filter");
    const typeSet = new Set();

    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Step 1: Get unique type values
    tableRows.forEach(row => {
        const type = row.querySelector(".type-cell")?.textContent.trim() || '-';
        typeSet.add(type);
    });

    const types = Array.from(typeSet).sort();
    const checkboxElements = [];

    // Step 2: Build checkboxes
    types.forEach(type => {
        const label = document.createElement("label");
        label.innerHTML =
            `<input type="checkbox" name="type" value="${type}" checked> ${type}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    // Step 3: Render checkboxes
    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) {
            el.style.display = 'none';
        }
        filterContainer.appendChild(el);
    });

    // Step 4: Add Show More/Show Less link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Step 5: Add "All" checkbox
    const allCheckbox = document.createElement("label");
    allCheckbox.innerHTML = `<input type="checkbox" id="type-all" checked> All`;
    filterContainer.insertBefore(allCheckbox, filterContainer.firstChild);

    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr"))
            .filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
        });
    }

    function applyTypeFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='type']:checked"))
            .map(cb => cb.value);
        const isAll = document.getElementById("type-all").checked;

        tableRows.forEach(row => {
            const type = row.querySelector(".type-cell")?.textContent.trim() || '-';
            const show = isAll || selected.includes(type);
            row.style.display = show ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='type']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        document.getElementById("type-all").checked = (checkedBoxes.length === allBoxes.length);
    }

    document.getElementById("type-all").addEventListener("change", function() {
        const allBoxes = document.querySelectorAll("input[name='type']");
        allBoxes.forEach(cb => cb.checked = this.checked);
        applyTypeFilter();
    });

    filterContainer.addEventListener("change", function(e) {
        if (e.target.name === "type") {
            applyTypeFilter();
        }
    });

    toggleLink.addEventListener("click", function(e) {
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
    applyTypeFilter();
});
</script>



<script>
// MICROPHONE filtering
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("microphone-filter");
    const micSet = new Set();

    // Step 1: Get unique microphone values
    tableRows.forEach(row => {
        const mic = row.querySelector(".microphone-cell")?.textContent.trim() || '-';
        micSet.add(mic);
    });

    const mics = Array.from(micSet).sort();
    const checkboxElements = [];

    // Step 2: Build checkboxes
    mics.forEach(mic => {
        const label = document.createElement("label");
        label.innerHTML =
            `<input type="checkbox" name="microphone" value="${mic}" checked> ${mic}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    // Step 3: Render checkboxes
    checkboxElements.forEach(el => {
        filterContainer.appendChild(el);
    });

    // Step 4: Add "All" checkbox
    const allCheckbox = document.createElement("label");
    allCheckbox.innerHTML = `<input type="checkbox" id="microphone-all" checked> All`;
    filterContainer.insertBefore(allCheckbox, filterContainer.firstChild);

    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr"))
            .filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
        });
    }

    function applyMicrophoneFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='microphone']:checked"))
            .map(cb => cb.value);
        const isAll = document.getElementById("microphone-all").checked;

        tableRows.forEach(row => {
            const mic = row.querySelector(".microphone-cell")?.textContent.trim() || '-';
            const show = isAll || selected.includes(mic);
            row.style.display = show ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='microphone']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        document.getElementById("microphone-all").checked = (checkedBoxes.length === allBoxes.length);
    }

    document.getElementById("microphone-all").addEventListener("change", function() {
        const allBoxes = document.querySelectorAll("input[name='microphone']");
        allBoxes.forEach(cb => cb.checked = this.checked);
        applyMicrophoneFilter();
    });

    filterContainer.addEventListener("change", function(e) {
        if (e.target.name === "microphone") {
            applyMicrophoneFilter();
        }
    });

    // Initial apply
    applyMicrophoneFilter();
});
</script>



<script>
// WIRELESS filtering
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("wireless-filter");
    const wirelessSet = new Set();

    // Step 1: Extract unique wireless values (Yes/No)
    tableRows.forEach(row => {
        const wireless = row.querySelector(".wireless-cell")?.textContent.trim() || '-';
        wirelessSet.add(wireless);
    });

    const options = Array.from(wirelessSet).sort();
    const checkboxElements = [];

    // Step 2: Create checkboxes
    options.forEach(option => {
        const label = document.createElement("label");
        label.innerHTML = `
            <input type="checkbox" name="wireless" value="${option}" checked> ${option}
        `;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    // Step 3: Render checkboxes
    checkboxElements.forEach(el => {
        filterContainer.appendChild(el);
    });

    // Step 4: Add "All" checkbox
    const allCheckbox = document.createElement("label");
    allCheckbox.innerHTML = `<input type="checkbox" id="wireless-all" checked> All`;
    filterContainer.insertBefore(allCheckbox, filterContainer.firstChild);

    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr"))
            .filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
        });
    }

    function applyWirelessFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='wireless']:checked"))
            .map(cb => cb.value);
        const isAll = document.getElementById("wireless-all").checked;

        tableRows.forEach(row => {
            const wireless = row.querySelector(".wireless-cell")?.textContent.trim() || '-';
            const show = isAll || selected.includes(wireless);
            row.style.display = show ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='wireless']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        document.getElementById("wireless-all").checked = (checkedBoxes.length === allBoxes.length);
    }

    document.getElementById("wireless-all").addEventListener("change", function() {
        const allBoxes = document.querySelectorAll("input[name='wireless']");
        allBoxes.forEach(cb => cb.checked = this.checked);
        applyWirelessFilter();
    });

    filterContainer.addEventListener("change", function(e) {
        if (e.target.name === "wireless") {
            applyWirelessFilter();
        }
    });

    // Initial filter
    applyWirelessFilter();
});
</script>


<script>
// ENCLOSURE TYPE filtering
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("enclosure-type-filter");
    const enclosureSet = new Set();

    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Step 1: Get unique enclosure values
    tableRows.forEach(row => {
        const enclosure = row.querySelector(".enclosure-type-cell")?.textContent.trim() || '-';
        enclosureSet.add(enclosure);
    });

    const enclosures = Array.from(enclosureSet).sort();
    const checkboxElements = [];

    // Step 2: Create checkboxes
    enclosures.forEach(type => {
        const label = document.createElement("label");
        label.innerHTML =
            `<input type="checkbox" name="enclosure-type" value="${type}" checked> ${type}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    // Step 3: Render checkboxes
    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none';
        filterContainer.appendChild(el);
    });

    // Step 4: Show More/Show Less link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Step 5: Add "All" checkbox
    const allCheckbox = document.createElement("label");
    allCheckbox.innerHTML = `<input type="checkbox" id="enclosure-type-all" checked> All`;
    filterContainer.insertBefore(allCheckbox, filterContainer.firstChild);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr"))
            .filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
        });
    }

    // Filter function
    function applyEnclosureFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='enclosure-type']:checked"))
            .map(cb => cb.value);
        const isAll = document.getElementById("enclosure-type-all").checked;

        tableRows.forEach(row => {
            const enclosure = row.querySelector(".enclosure-type-cell")?.textContent.trim() || '-';
            const show = isAll || selected.includes(enclosure);
            row.style.display = show ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // Sync "All" checkbox state
    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='enclosure-type']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        document.getElementById("enclosure-type-all").checked = (checkedBoxes.length === allBoxes.length);
    }

    // Handle "All" checkbox toggle
    document.getElementById("enclosure-type-all").addEventListener("change", function() {
        const allBoxes = document.querySelectorAll("input[name='enclosure-type']");
        allBoxes.forEach(cb => cb.checked = this.checked);
        applyEnclosureFilter();
    });

    // Listen for changes on individual checkboxes
    filterContainer.addEventListener("change", function(e) {
        if (e.target.name === "enclosure-type") {
            applyEnclosureFilter();
        }
    });

    // Show more / less toggle
    toggleLink.addEventListener("click", function(e) {
        e.preventDefault();
        expanded = !expanded;

        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) {
                el.style.display = expanded ? "block" : "none";
            }
        });

        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // ✅ Reset filter on page reload
    window.addEventListener("pageshow", function() {
        document.querySelectorAll("input[name='enclosure-type']").forEach(cb => cb.checked = true);
        document.getElementById("enclosure-type-all").checked = true;
        applyEnclosureFilter();
    });

    // Initial run
    applyEnclosureFilter();
});
</script>


<script>
// ACTIVE NOISE CANCELLATION filtering
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("anc-filter");
    const ancSet = new Set();

    // Step 1: Extract ANC values from .anc-cell
    tableRows.forEach(row => {
        const anc = row.querySelector(".anc-cell")?.textContent.trim() || '-';
        ancSet.add(anc);
    });

    const ancOptions = Array.from(ancSet).sort();
    const checkboxElements = [];

    // Step 2: Create checkboxes
    ancOptions.forEach(anc => {
        const label = document.createElement("label");
        label.innerHTML = `
            <input type="checkbox" name="anc" value="${anc}" checked> ${anc}
        `;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    // Step 3: Render checkboxes
    checkboxElements.forEach(el => {
        filterContainer.appendChild(el);
    });

    // Step 4: Add "All" checkbox
    const allCheckbox = document.createElement("label");
    allCheckbox.innerHTML = `<input type="checkbox" id="anc-all" checked> All`;
    filterContainer.insertBefore(allCheckbox, filterContainer.firstChild);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr"))
            .filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
        });
    }

    // Filter logic
    function applyANCFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='anc']:checked"))
            .map(cb => cb.value);
        const isAll = document.getElementById("anc-all").checked;

        tableRows.forEach(row => {
            const anc = row.querySelector(".anc-cell")?.textContent.trim() || '-';
            const show = isAll || selected.includes(anc);
            row.style.display = show ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // Sync "All" checkbox
    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='anc']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        document.getElementById("anc-all").checked = (checkedBoxes.length === allBoxes.length);
    }

    document.getElementById("anc-all").addEventListener("change", function() {
        document.querySelectorAll("input[name='anc']").forEach(cb => cb.checked = this.checked);
        applyANCFilter();
    });

    filterContainer.addEventListener("change", function(e) {
        if (e.target.name === "anc") {
            applyANCFilter();
        }
    });

    // Reset filters on page reload
    window.addEventListener("pageshow", function() {
        document.querySelectorAll("input[name='anc']").forEach(cb => cb.checked = true);
        document.getElementById("anc-all").checked = true;
        applyANCFilter();
    });

    // Initial apply
    applyANCFilter();
});
</script>




<script>
document.addEventListener("DOMContentLoaded", function() {
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

    seriesFilterContainer.addEventListener("change", function(e) {
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

    toggleLink.addEventListener("click", function(e) {
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
// PRICE FILTERING
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("price-slider");
    const minLabel = document.getElementById("price-min-label");
    const maxLabel = document.getElementById("price-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));
    const prices = rows.map(row => {
        const priceText = row.querySelector(".price-cell")?.textContent.replace(/[^0-9.]/g, '') || "0";
        return parseFloat(priceText) || 0;
    });

    const minPrice = Math.floor(Math.min(...prices));
    const maxPrice = Math.ceil(Math.max(...prices));
    let currentMin = minPrice;
    let currentMax = maxPrice;

    // Set default labels
    minLabel.textContent = `$${minPrice}`;
    maxLabel.textContent = `$${maxPrice}`;

    // Create sliders
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
        let minVal = parseFloat(minSlider.value);
        let maxVal = parseFloat(maxSlider.value);

        // Fix issue: If sliders overlap
        if (minVal > maxVal)[minVal, maxVal] = [maxVal, minVal];

        minLabel.textContent = `$${minVal}`;
        maxLabel.textContent = `$${maxVal}`;

        rows.forEach(row => {
            const priceText = row.querySelector(".price-cell")?.textContent.replace(/[^0-9.]/g, '') ||
                "0";
            const price = parseFloat(priceText) || 0;

            row.style.display = (price >= minVal && price <= maxVal) ? "" : "none";
        });

        applyZebraStriping();
    }

    // Handle slider input
    minSlider.addEventListener("input", filterByPrice);
    maxSlider.addEventListener("input", filterByPrice);

    // Initial filter after slight delay to allow rendering
    setTimeout(filterByPrice, 50);
});
</script>


<script>
// SORTING LOGIC
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById("pcbuild-table");
    const headers = table.querySelectorAll(".sortable-header");

    let currentSort = {
        key: null,
        direction: 'asc'
    };

    headers.forEach(header => {
        header.addEventListener('click', function() {
            const key = this.dataset.key;
            currentSort.direction = (currentSort.key === key && currentSort.direction ===
                'asc') ? 'desc' : 'asc';
            currentSort.key = key;

            // Reset header icons
            headers.forEach(h => {
                h.innerHTML = `&#9654; ${h.textContent.trim().replace(/^▲|▼|\▶/, '')}`;
            });

            // Show arrow direction on clicked header
            this.innerHTML =
                `${currentSort.direction === 'asc' ? '▲' : '▼'} ${this.textContent.trim().replace(/^▲|▼|\▶/, '')}`;

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
            type: 3,
            frequency_response: 4,
            microphone: 5,
            wireless: 6,
            enclosure_type: 7,
            color: 9,
            rating: 10,
            price: 11
        };
        return mapping[key];
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
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
    selectAllBtn.addEventListener("click", function(e) {
        e.preventDefault();
        checkboxes.forEach(cb => cb.checked = true);
        updateButtonStates();
    });

    // Clear Selected click
    clearSelectedBtn.addEventListener("click", function(e) {
        e.preventDefault();
        if (clearSelectedBtn.classList.contains("disabled")) return;
        checkboxes.forEach(cb => cb.checked = false);
        updateButtonStates();
    });

    // Compare Selected click (you can add actual compare logic here later)
    compareSelectedBtn.addEventListener("click", function(e) {
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
// COLOR filtering
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("color-filter");
    const colorSet = new Set();

    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Step 1: Get unique color values
    tableRows.forEach(row => {
        const color = row.querySelector(".color-cell")?.textContent.trim() || '-';
        colorSet.add(color);
    });

    const colors = Array.from(colorSet).sort();
    const checkboxElements = [];

    // Step 2: Build checkboxes
    colors.forEach(color => {
        const label = document.createElement("label");
        label.innerHTML =
            `<input type="checkbox" name="color" value="${color}" checked> ${color}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    // Step 3: Render checkboxes
    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) {
            el.style.display = 'none';
        }
        filterContainer.appendChild(el);
    });

    // Step 4: Add Show More/Show Less link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Step 5: Add "All" checkbox
    const allCheckbox = document.createElement("label");
    allCheckbox.innerHTML = `<input type="checkbox" id="color-all" checked> All`;
    filterContainer.insertBefore(allCheckbox, filterContainer.firstChild);

    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr"))
            .filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
        });
    }

    function applyColorFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='color']:checked"))
            .map(cb => cb.value);
        const isAll = document.getElementById("color-all").checked;

        tableRows.forEach(row => {
            const color = row.querySelector(".color-cell")?.textContent.trim() || '-';
            const show = isAll || selected.includes(color);
            row.style.display = show ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='color']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        document.getElementById("color-all").checked = (checkedBoxes.length === allBoxes.length);
    }

    document.getElementById("color-all").addEventListener("change", function() {
        const allBoxes = document.querySelectorAll("input[name='color']");
        allBoxes.forEach(cb => cb.checked = this.checked);
        applyColorFilter();
    });

    filterContainer.addEventListener("change", function(e) {
        if (e.target.name === "color") {
            applyColorFilter();
        }
    });

    toggleLink.addEventListener("click", function(e) {
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
// Searching logic with zebra striping
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("pcbuild-search");
    const tableRows = document.querySelectorAll("#pcbuild-table tbody tr");

    function applyZebraStriping() {
        const visibleRows = Array.from(tableRows).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    searchInput.addEventListener("input", function() {
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

    sidebarToggle.addEventListener('click', function(event) {
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

    sidebar.addEventListener('click', function(event) {
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
add_shortcode('pcbuild_headphones', 'aawp_pcbuild_display_parts_headphones');
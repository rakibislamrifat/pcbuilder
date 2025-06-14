<?php
function aawp_pcbuild_display_parts_cpu_cooler($atts)
{

    $atts = shortcode_atts(array('category' => 'cpu-cooler'), $atts);
    $input_category = sanitize_title($atts['category']);

    $category_map = [
        'cpu-cooler' => 'CPU Cooler',
    ];

    $category = $category_map[$input_category] ?? 'CPU Cooler';

    // Create transient key (MATCH naming)
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
    $items_per_page = 100;
    $current_page = isset($_GET['pcbuild_page']) ? max(1, intval($_GET['pcbuild_page'])) : 1;
    $total_pages = ceil($total_items / $items_per_page);
    $start = ($current_page - 1) * $items_per_page;
    $display_items = array_slice($all_items, $start, $items_per_page);

    ob_start();
    //include('parts-header.php');
    ?>
    <div
        style="background-color:#41466c; padding:40px; color:#fff; font-size:24px; font-weight:bold; text-align:center; margin-bottom:40px">
        Choose A <?php echo esc_html($category); ?>
    </div>
    <div style="width:90%; margin:0 auto; font-family:sans-serif;">
        <div class="pcbuilder-container" style="display:flex; gap:20px; margin-top:20px">
            
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
                    <label><input type="checkbox" id="manufacturer-all" checked> All</label><br />
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
                    <strong>COLOR</strong>
                    <button class="filter-toggle">−</button>
                </div>
                <div class="filter-options" id="color-filter">
                    <label><input type="checkbox" id="color-all" checked> All</label><br />
                    <!-- Checkboxes for colors will be inserted here by JS -->
                </div>
            </div>
            <div class="filter-group">
                <div class="filter-header">
                    <strong>HEIGHT</strong>
                    <button class="filter-toggle">−</button>
                </div>
                <div class="filter-options" id="height-filter" style="display: block;">
                    <div id="height-slider" style="margin-top: 15px;"></div>
                    <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                        <span id="height-min-label">0 mm</span>
                        <span id="height-max-label">0 mm</span>
                    </div>
                </div>
            </div>
            <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                <div class="filter-header">
                    <strong>CPU SOCKET</strong>
                    <button class="filter-toggle">−</button>
                </div>
                <div class="filter-options" id="socket-filter">
                    <label><input type="checkbox" id="socket-all" checked> All</label><br />
                    <!-- Checkboxes for sockets will be dynamically inserted by JS -->
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
                    <th class="sortable-header" data-key="fan_rpm">
                        <span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Fan RPM</span>
                    </th>
                    <th class="sortable-header" data-key="noise">
                        <span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Noise Level</span>
                    </th>
                    <th class="sortable-header" data-key="radiator">
                        <span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Radiator Size</span>
                    </th>
                    <th class="sortable-header" data-key="rating">
                        <span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Seller Rating</span>
                    </th>
                    <th class="sortable-header" data-key="price">
                        <span class="sort-header-label"><span class="sort-arrow">&#9654;</span> Price</span>
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
                    $color = $item['ItemInfo']['ProductInfo']['Color']['DisplayValue'] ?? '';
                    $sellerCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? 'Unknown';
                    $sellerRating = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackRating'] ?? 'Unknown';

                    // Get height and convert to mm (assuming it's in inches by default)
                    $height_in = $item['ItemInfo']['ProductInfo']['ItemDimensions']['Height']['DisplayValue'] ?? '';
                    $height_unit = $item['ItemInfo']['ProductInfo']['ItemDimensions']['Height']['Unit'] ?? '';
                    $height_mm = '';

                    if ($height_in !== '' && strtolower($height_unit) === 'inches') {
                        $height_mm = round(floatval($height_in) * 25.4, 1); // Convert inches to mm
                    } elseif ($height_in !== '' && strtolower($height_unit) === 'millimeters') {
                        $height_mm = floatval($height_in);
                    }

                    // Extract values
                    preg_match('/(\d{3,4})\s?RPM/i', $features_string, $rpm_match);
                    preg_match('/(\d+(\.\d+)?\s?dB)/i', $features_string, $noise_match);
                    preg_match('/(120|240|280|360)\s?mm/i', $features_string, $rad_match);
                    preg_match_all('/(AM4|AM5|FM2\+|TR4|sTRX4|LGA[\s-]?(1150|1151|1155|1156|1200|1700|1851|2066))/i', $features_string . ' ' . $full_title, $socket_matches);

                    $fan_rpm = $rpm_match[1] ?? '-';
                    $noise_level = $noise_match[1] ?? '-';
                    $radiator = $rad_match[1] ?? '-';
                    $compatible_sockets = array_map('trim', array_unique($socket_matches[1]));
                    if (empty($compatible_sockets))
                        $compatible_sockets[] = 'all';
                    $socket = implode(',', $compatible_sockets);
                    $rating_count = display_rating_and_count($sellerRating, $sellerCount);

                ?>
                    <!-- Mobile-responsive row structure to match the image exactly -->
                    <tr class="product-row" style="background-color: <?php echo $row_bg; ?>;" data-compatible-sockets="<?php echo esc_attr(implode(',', $compatible_sockets)); ?>">
                        <!-- Regular desktop view -->
                        <?php if(true): // Always show desktop version, it will be hidden via CSS on mobile ?>
                            <td style="padding: 10px 0 10px 10px; width: 150px!important" title="<?php echo $raw_title; ?>">
                                <img src="<?php echo $raw_image; ?>" alt="<?php echo $title; ?>" style="width:125px; height:125px; border-radius:4px;" />
                            </td>
                            <td style="font-weight:800;"><?php echo $title; ?></td>
                            <td style="padding:10px;"><?php echo esc_html($fan_rpm); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($noise_level); ?></td>
                            <td style="padding:10px;"><?php echo ($radiator !== '-') ? esc_html($radiator) . ' mm' : '-'; ?></td>
                            <td style="padding:10px;" data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>">
                                <?php echo $rating_count; ?>
                            </td>
                            <td style="padding:10px;"><?php echo esc_html($price); ?></td>
                            <td style="padding:10px;">
                                <button class="add-to-builder" data-asin="<?php echo esc_attr($asin); ?>"
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
                                    data-color="<?php echo esc_attr($color); ?>"
                                    data-height="<?php echo esc_attr($height_mm); ?>">
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
                                        <div class="spec-label">Fan RPM</div>
                                        <div class="spec-value"><?php echo esc_html($fan_rpm); ?></div>
                                    </div>
                                    <div class="spec-group">
                                        <div class="spec-label">Noise Level</div>
                                        <div class="spec-value"><?php echo esc_html($noise_level); ?></div>
                                    </div>
                                    <div class="spec-group">
                                        <div class="spec-label">Radiator Size</div>
                                        <div class="spec-value"><?php echo ($radiator !== '-') ? esc_html($radiator) . ' mm' : '-'; ?></div>
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
                                    data-color="<?php echo esc_attr($color); ?>"
                                    data-height="<?php echo esc_attr($height_mm); ?>">
                                    <?php _e('Add', 'aawp-pcbuild'); ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

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

        // Inject checkboxes
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
    // Socket filtering
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const tableRows = table.querySelectorAll("tbody tr");
        const socketFilterContainer = document.getElementById("socket-filter");
        const socketMap = new Map(); // Using Map to store normalized sockets
        const VISIBLE_COUNT = 4; // Number of sockets to show initially
        let expanded = false;

        // Function to normalize socket names
        function normalizeSocketName(socket) {
            if (!socket) return '';
            // Convert to uppercase and remove all spaces
            return socket.toUpperCase().replace(/\s+/g, '');
        }

        // Clear existing checkboxes (except the "All" checkbox)
        const existingCheckboxes = socketFilterContainer.querySelectorAll('input[name="socket"]');
        existingCheckboxes.forEach(checkbox => {
            if (checkbox.id !== 'socket-all') {
                checkbox.parentElement.remove();
            }
        });

        // Remove the existing "Show more/less" link if it exists
        const existingToggleLink = socketFilterContainer.querySelector('a[href="#"]');
        if (existingToggleLink) {
            existingToggleLink.remove();
        }

        // Collect unique socket values from all rows and normalize them
        tableRows.forEach(row => {
            const compatibleSockets = row.dataset.compatibleSockets.split(',');
            compatibleSockets.forEach(socket => {
                const trimmedSocket = socket.trim();
                if (trimmedSocket && trimmedSocket.toLowerCase() !== 'all') {
                    const normalized = normalizeSocketName(trimmedSocket);
                    // Store the original display name with the first occurrence
                    if (!socketMap.has(normalized)) {
                        socketMap.set(normalized, trimmedSocket);
                    }
                }
            });
        });

        // Sort sockets logically (Intel LGA first, then AMD, then others)
        const sortedSockets = Array.from(socketMap.entries()).sort(([aKey, aVal], [bKey, bVal]) => {
            const isIntelA = aKey.startsWith('LGA');
            const isIntelB = bKey.startsWith('LGA');
            const isAmdA = aKey.startsWith('AM');
            const isAmdB = bKey.startsWith('AM');

            if (isIntelA && !isIntelB) return -1;
            if (!isIntelA && isIntelB) return 1;
            if (isAmdA && !isAmdB) return -1;
            if (!isAmdA && isAmdB) return 1;

            // For same type, sort by number
            const numA = parseInt(aKey.replace(/\D/g, '')) || 0;
            const numB = parseInt(bKey.replace(/\D/g, '')) || 0;
            return numA - numB;
        });

        // Create checkbox elements
        const socketCheckboxElements = [];

        // Create individual socket checkboxes
        sortedSockets.forEach(([normalized, displayName]) => {
            const label = document.createElement("label");
            label.style.display = 'block';
            label.style.margin = '2px 0';
            label.innerHTML = `<input type="checkbox" name="socket" value="${normalized}" checked> ${displayName}`;
            socketCheckboxElements.push(label);
        });

        // Append socket checkboxes to container (after the "All" checkbox)
        const allCheckbox = socketFilterContainer.querySelector('#socket-all');
        const allLabel = allCheckbox.parentElement;

        // Insert checkboxes after the "All" checkbox
        let insertAfter = allLabel;

        socketCheckboxElements.forEach((el, index) => {
            insertAfter.after(el);
            insertAfter = el;

            // Hide sockets beyond the initial visible count
            if (index >= VISIBLE_COUNT) {
                el.style.display = 'none';
            }
        });

        // Add Show more / Show less link if there are more than VISIBLE_COUNT sockets
        if (sortedSockets.length > VISIBLE_COUNT) {
            const socketToggleLink = document.createElement("a");
            socketToggleLink.href = "#";
            socketToggleLink.textContent = "Show more";
            socketToggleLink.style.marginTop = "5px";
            socketToggleLink.style.fontSize = "14px";
            socketToggleLink.style.color = "#0066cc";
            socketToggleLink.style.display = "inline-block";
            socketFilterContainer.appendChild(socketToggleLink);

            // Toggle visibility of additional sockets
            socketToggleLink.addEventListener("click", function (e) {
                e.preventDefault();
                expanded = !expanded;

                socketCheckboxElements.forEach((el, index) => {
                    if (index >= VISIBLE_COUNT) {
                        el.style.display = expanded ? 'block' : 'none';
                    }
                });

                socketToggleLink.textContent = expanded ? "Show less" : "Show more";
            });

            // Remove any <br> tags inside the container
            const brTags = socketFilterContainer.getElementsByTagName('br');
            while (brTags[0]) {
                brTags[0].parentNode.removeChild(brTags[0]);
            }
        }

        // Handle "All" checkbox change
        allCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            socketFilterContainer.querySelectorAll('input[name="socket"]').forEach(checkbox => {
                if (checkbox !== this) {
                    checkbox.checked = isChecked;
                }
            });
            filterBySockets();
        });

        // Handle individual socket checkbox changes
        socketFilterContainer.addEventListener("change", function (e) {
            if (e.target.name === 'socket' && e.target.id !== 'socket-all') {
                // If unchecking a socket, uncheck "All"
                if (!e.target.checked) {
                    allCheckbox.checked = false;
                }
                // If all sockets are checked, check "All"
                const allSocketsChecked = Array.from(socketFilterContainer.querySelectorAll('input[name="socket"]:not(#socket-all)'))
                    .every(checkbox => checkbox.checked);
                if (allSocketsChecked) {
                    allCheckbox.checked = true;
                }
            }
            filterBySockets();
        });

        // Socket filtering function
        function filterBySockets() {
            const selectedSockets = Array.from(socketFilterContainer.querySelectorAll("input[name='socket']:checked"))
                .map(input => input.value);

            const showAll = selectedSockets.includes('all');

            tableRows.forEach(row => {
                const rowSockets = row.dataset.compatibleSockets.split(',')
                    .map(s => normalizeSocketName(s.trim()));

                if (showAll) {
                    row.style.display = '';
                } else {
                    const matchesSocket = selectedSockets.some(selectedSocket =>
                        rowSockets.includes(selectedSocket)
                    );
                    row.style.display = matchesSocket ? '' : 'none';
                }
            });

            updateProductCount();
            applyZebraStriping();
        }

        function updateProductCount() {
            const visibleCount = table.querySelectorAll("tbody tr:not([style*='display: none'])").length;
            document.getElementById("total_products").textContent = `${visibleCount} Products`;
        }

        function applyZebraStriping() {
            const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        // Initial setup
        applyZebraStriping();
    });
</script>

<script>
    // Compatibility Checking
    document.addEventListener('DOMContentLoaded', function () {

        const selectedCpuSocket = localStorage.getItem('selected_cpu_socket');
        console.log(selectedCpuSocket);

        const compatibilityToggle = document.createElement('div');
        compatibilityToggle.innerHTML = `
            <div style="margin-bottom:20px;">
                <label>
                    <input type="checkbox" id="compatibility-toggle" checked /> 
                    Compatibility Filter
                </label>
            </div>
        `;
        document.querySelector('.pcbuild-sidebar > div:first-child').after(compatibilityToggle);

        const noticeElement = document.createElement('div');
        noticeElement.id = 'compatibility-notice';
        noticeElement.style.display = 'none';
        noticeElement.style.marginBottom = '20px';
        noticeElement.style.padding = '10px';
        noticeElement.style.background = '#fff8e1';
        noticeElement.style.borderLeft = '4px solid #ffc107';
        noticeElement.innerHTML = '<strong>Compatibility Filter Active:</strong> <span id="compatibility-message"></span>';
        compatibilityToggle.after(noticeElement);

        filterCompatibleCoolers();

        document.getElementById('compatibility-toggle').addEventListener('change', function () {
            localStorage.setItem('cooler_compatibility_filter', this.checked ? 'on' : 'off');
            filterCompatibleCoolers();
        });

        // Delay to allow content to fully load before striping
        setTimeout(() => applyZebraStriping(), 50);
    });

    function filterCompatibleCoolers() {
        const compatibilityEnabled = localStorage.getItem('cooler_compatibility_filter') !== 'off';
        const noticeElement = document.getElementById('compatibility-notice');
        const messageElement = document.getElementById('compatibility-message');
        document.getElementById('compatibility-toggle').checked = compatibilityEnabled;

        const allRows = document.querySelectorAll('#pcbuild-table tbody tr');

        if (!compatibilityEnabled) {
            noticeElement.style.display = 'none';
            allRows.forEach(row => row.style.display = '');
            applyZebraStriping(); // Apply to all visible rows
            return;
        }

        const selectedCpuSocket = localStorage.getItem('selected_cpu_socket');
        if (selectedCpuSocket) {
            noticeElement.style.display = '';
            messageElement.textContent = `Showing only coolers compatible with ${selectedCpuSocket} socket`;

            let compatibleCount = 0;
            allRows.forEach(row => {
                const sockets = row.dataset.compatibleSockets?.toUpperCase().split(',') || [];
                if (sockets.includes(selectedCpuSocket.toUpperCase()) || sockets.includes('ALL')) {
                    row.style.display = '';
                    compatibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (compatibleCount === 0) {
                noticeElement.innerHTML = `
                    <strong>No compatible coolers found!</strong>
                    <p>We couldn't find any coolers compatible with your ${selectedCpuSocket} socket CPU.</p>
                    <button onclick="document.getElementById('compatibility-toggle').click()" 
                            style="padding:5px 10px; background:#f44336; color:white; border:none; cursor:pointer;">
                        Show All Coolers Anyway
                    </button>
                `;
            }

            applyZebraStriping(); // Apply to visible rows
        } else {
            noticeElement.style.display = 'none';
            allRows.forEach(row => row.style.display = '');
            applyZebraStriping(); // Apply to all
        }
    }

    function applyZebraStriping() {
        const visibleRows = Array.from(document.querySelectorAll('#pcbuild-table tbody tr')).filter(row => row.style.display !== 'none');
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
        });
    }
</script>

<script>
    // Height filtering with Zebra Striping
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const sliderContainer = document.getElementById("height-slider");
        const minLabel = document.getElementById("height-min-label");
        const maxLabel = document.getElementById("height-max-label");

        if (!table || !sliderContainer) return;

        const rows = Array.from(table.querySelectorAll("tbody tr"));

        // Extract height values from the "Add to Builder" button data-height attribute
        const heights = rows.map(row => {
            const button = row.querySelector(".add-to-builder");
            return button ? parseFloat(button.dataset.height) || 0 : 0;
        });

        const minHeight = Math.floor(Math.min(...heights));
        const maxHeight = Math.ceil(Math.max(...heights));

        // Set initial min and max height labels
        minLabel.textContent = `${minHeight} mm`;
        maxLabel.textContent = `${maxHeight} mm`;

        // Create the slider elements for height filtering
        sliderContainer.innerHTML = `
        <input type="range" class="min-range-bg" id="min-height" min="${minHeight}" max="${maxHeight}" value="${minHeight}" step="1" style="width: 100%;">
        <input type="range" class="max-range-bg" id="max-height" min="${minHeight}" max="${maxHeight}" value="${maxHeight}" step="1" style="width: 100%; margin-top: 10px;">
    `;

        const minSlider = document.getElementById("min-height");
        const maxSlider = document.getElementById("max-height");

        // Function to apply zebra striping to visible rows
        function applyZebraStripes() {
            const visibleRows = rows.filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        // Function to filter rows by height range
        function filterByHeight() {
            const minVal = parseFloat(minSlider.value);
            const maxVal = parseFloat(maxSlider.value);

            // Update min and max height labels
            minLabel.textContent = `${minVal} mm`;
            maxLabel.textContent = `${maxVal} mm`;

            // Show/hide rows based on the height range
            rows.forEach(row => {
                const button = row.querySelector(".add-to-builder");
                const height = button ? parseFloat(button.dataset.height) || 0 : 0;
                row.style.display = (height >= minVal && height <= maxVal) ? "" : "none";
            });

            applyZebraStripes(); // 🦓 Apply zebra stripes after filtering
        }

        // Event listeners for sliders
        minSlider.addEventListener("input", () => {
            if (parseFloat(minSlider.value) > parseFloat(maxSlider.value)) {
                minSlider.value = maxSlider.value;
            }
            filterByHeight();
        });

        maxSlider.addEventListener("input", () => {
            if (parseFloat(maxSlider.value) < parseFloat(minSlider.value)) {
                maxSlider.value = minSlider.value;
            }
            filterByHeight();
        });

        // Initialize the filter
        filterByHeight();
    });
</script>

<script>
    // Price filtering with Zebra striping
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const sliderContainer = document.getElementById("price-slider");
        const minLabel = document.getElementById("price-min-label");
        const maxLabel = document.getElementById("price-max-label");

        if (!table || !sliderContainer) return;

        const rows = Array.from(table.querySelectorAll("tbody tr"));
        const prices = rows.map(row => {
            const priceText = row.querySelector("td:nth-child(6)")?.textContent.replace(/[^0-9.]/g, '') || "0";
            return parseFloat(priceText) || 0;
        });

        const minPrice = Math.floor(Math.min(...prices));
        const maxPrice = Math.ceil(Math.max(...prices));
        let currentMin = minPrice;
        let currentMax = maxPrice;

        minLabel.textContent = `$${minPrice}`;
        maxLabel.textContent = `$${maxPrice}`;

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

            minLabel.textContent = `$${minVal}`;
            maxLabel.textContent = `$${maxVal}`;

            rows.forEach(row => {
                const priceText = row.querySelector("td:nth-child(6)")?.textContent.replace(/[^0-9.]/g, '') || "0";
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

        filterByPrice();
    });
</script>

<script>
    // Color filtering
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const tableRows = table.querySelectorAll("tbody tr");
        const colorFilterContainer = document.getElementById("color-filter");
        const colorSet = new Set();
        const VISIBLE_COUNT = 4;
        let expanded = false;

        // Normalize function (e.g., "BLACK", "black" -> "Black")
        function normalizeColor(color) {
            return color.charAt(0).toUpperCase() + color.slice(1).toLowerCase();
        }

        // Collect unique normalized colors
        tableRows.forEach(row => {
            let rawColor = row.querySelector("button.add-to-builder")?.dataset.color || "Unknown";
            let normalizedColor = normalizeColor(rawColor);
            row.querySelector("button.add-to-builder").dataset.colorNormalized = normalizedColor;
            colorSet.add(normalizedColor);
        });

        // Prepare color checkboxes
        const colors = Array.from(colorSet).sort();
        const colorCheckboxElements = [];
        colors.forEach(color => {
            const label = document.createElement("label");
            label.innerHTML = `<input type="checkbox" name="color" value="${color}" checked> ${color}`;
            label.style.display = 'block';
            colorCheckboxElements.push(label);
        });

        // Append color checkboxes to container
        colorCheckboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) {
                el.style.display = 'none';
            }
            colorFilterContainer.appendChild(el);
        });

        // Add Show more / Show less link
        const colorToggleLink = document.createElement("a");
        colorToggleLink.href = "#";
        colorToggleLink.textContent = "Show more";
        colorToggleLink.style.display = (colorCheckboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
        colorToggleLink.style.marginTop = "5px";
        colorToggleLink.style.fontSize = "14px";
        colorToggleLink.style.color = "#0066cc";
        colorFilterContainer.appendChild(colorToggleLink);

        // Zebra stripe function
        function applyZebraStriping() {
            const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        const allColorCheckbox = document.getElementById("color-all");

        function updateAllColorCheckboxState() {
            const allBoxes = Array.from(document.querySelectorAll("input[name='color']"));
            const checkedBoxes = allBoxes.filter(cb => cb.checked);
            allColorCheckbox.checked = checkedBoxes.length === allBoxes.length;
        }

        function applyColorFilter() {
            const selectedColors = Array.from(document.querySelectorAll("input[name='color']:checked"))
                .map(cb => cb.value);

            tableRows.forEach(row => {
                const color = row.querySelector("button.add-to-builder")?.dataset.colorNormalized;
                const show = selectedColors.includes(color);
                row.style.display = show ? "" : "none";
            });

            updateAllColorCheckboxState();
            applyZebraStriping();
        }

        // Toggle "All"
        allColorCheckbox.addEventListener("change", function () {
            const allBoxes = document.querySelectorAll("input[name='color']");
            allBoxes.forEach(cb => cb.checked = allColorCheckbox.checked);
            applyColorFilter();
        });

        // Individual checkbox change
        colorFilterContainer.addEventListener("change", function (e) {
            if (e.target.name === "color") {
                applyColorFilter();
            }
        });

        // Show more/less logic
        colorToggleLink.addEventListener("click", function (e) {
            e.preventDefault();
            expanded = !expanded;

            colorCheckboxElements.forEach((el, index) => {
                if (index >= VISIBLE_COUNT) {
                    el.style.display = expanded ? "block" : "none";
                }
            });

            colorToggleLink.textContent = expanded ? "Show less" : "Show more";
        });

        // Initial apply
        applyColorFilter();
    });
</script>

<script>
    // Manufacturer filtering
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const tableRows = table.querySelectorAll("tbody tr");
        const filterContainer = document.getElementById("manufacturer-filter");
        const manufacturerSet = new Set();

        const VISIBLE_COUNT = 4; // How many manufacturers to show initially
        let expanded = false;

        // Collect unique manufacturers
        tableRows.forEach(row => {
            const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer || "Unknown";
            manufacturerSet.add(manufacturer);
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

                    if (key === 'price' || key === 'fan_rpm' || key === 'noise_level' || key === 'radiator') {
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
                fan_rpm: 3,
                noise_level: 4,
                radiator: 5,
                rating: 6,
                price: 7
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
add_shortcode('pcbuild_parts_cpu_cooler', 'aawp_pcbuild_display_parts_cpu_cooler');

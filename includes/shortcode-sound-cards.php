<?php
function aawp_pcbuild_display_parts_sound_cards($atts) {
    $atts = shortcode_atts(array('category' => 'sound-cards'), $atts);
    $input_category = sanitize_title($atts['category']);
    
    $category_map = [
        'sound-cards' => 'Sound Cards',
    ];
    
    $category = $category_map[$input_category] ?? 'Sound Cards';
    
    $transient_key = 'aawp_pcbuild_cache_' . md5($category);

    if (is_user_logged_in() && current_user_can('manage_options') && isset($_GET['clear_cache'])) {
        delete_transient($transient_key);
    }

    $products = get_transient($transient_key);

    if ($products === false) {
        $products = aawp_pcbuild_get_products($category);
        set_transient($transient_key, $products, 12 * HOUR_IN_SECONDS);
    }

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
    ?>

    <div style="background-color:#41466c; padding:20px; color:#fff; font-size:24px; font-weight:bold; text-align:center; margin-bottom:40px">
        Choose A Sound Card
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
                        <strong>CHANNELS</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="channels-filter">
                        <!-- Filters will be injected here -->
                    </div>
                </div>

            </div>

            <div class="pcbuilder-main" style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div id="total_products" style="font-weight:bold;"><?php echo $total_items; ?> Products</div>
                    <div>
                        <input type="text" id="pcbuild-search" placeholder="Search..." style="padding:6px 10px; border-radius:6px; border:1px solid #ccc; margin-bottom: 15px" /><br>
                    </div>
                </div>

                <table id="pcbuild-table" style="width:100%; border-collapse: collapse; white-space: nowrap;">
                    <thead>
                        <tr>
                            <th style="padding: 10px;"></th>
                            <th class="sortable-header" data-key="name" style="padding: 10px;">Name</th>
                            <th class="sortable-header" data-key="channels" style="padding: 10px;">Channels</th>
                            <th class="sortable-header" data-key="digital_audio" style="padding: 10px;">Digital Audio</th>
                            <th class="sortable-header" data-key="snr" style="padding: 10px;">SNR</th>
                            <th class="sortable-header" data-key="sample_rate" style="padding: 10px;">Sample Rate</th>
                            <th class="sortable-header" data-key="chipset" style="padding: 10px;">Chipset</th>
                            <th class="sortable-header" data-key="interface" style="padding: 10px;">Interface</th>
                            <th class="sortable-header" data-key="rating" style="padding: 10px;">Seller Rating</th>
                            <th class="sortable-header" data-key="price" style="padding: 10px;">Price</th>
                            <th style="padding: 10px;">Action</th>
                        </tr>
                    </thead>

                    <?php include('rating-count.php'); ?>
                    <tbody>
                        <?php foreach ($display_items as $index => $item):
                        $row_bg = ($index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                        $asin = $item['ASIN'] ?? '';
                        $full_title = $item['ItemInfo']['Title']['DisplayValue'] ?? 'Unknown Product';
                        $title = esc_html(implode(' ', array_slice(explode(' ', $full_title), 0, 4)));
                        $image = $item['Images']['Primary']['Large']['URL'] ?? $item['Images']['Primary']['Medium']['URL'] ?? $item['Images']['Primary']['Small']['URL'] ?? '';

                        $price = $item['Offers']['Listings'][0]['Price']['DisplayAmount'] ?? 'N/A';
                        $cleaned_price = preg_replace('/[^0-9.]/', '', $price); // Keep only numbers and decimal points
                        $price = $cleaned_price ? '$' . number_format((float)$cleaned_price, 2) : 'N/A';

                        $availability = $item['Offers']['Listings'][0]['Availability']['Message'] ?? '—';
                        $product_url = $item['DetailPageURL'] ?? '#';
                        $manufacturer = $item['ItemInfo']['ByLineInfo']['Manufacturer']['DisplayValue'] ?? 'Unknown';
                        $sellerCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? 'Unknown';
                        $sellerRating = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackRating'] ?? 'Unknown';

                        // Default values for the properties
                        $channels = $digital_audio = $snr = $sample_rate = $chipset = $interface = '-';
                        $title_features = $item['ItemInfo']['Title']['DisplayValue'] ?? ''; // Title value
                        $features = $item['ItemInfo']['Features']['DisplayValues'] ?? []; // Feature values
                        $all_specs = array_merge([$title_features], $features);  // Merging Title and Features for spec extraction

                        // Define a list of known chipset names to match
                        $known_chipsets = [
                            'sound core3d', 'creative e-mu', 'asus av100', 'c-media cmi8786', 'asus av66',
                            'realtek alc1220x', 'realtek', 'creative sound blaster', 'qualcomm', 'intel'
                        ];

                        // Define known interface types
                        $known_interfaces = [
                            'pcie', 'pci', 'usb', 'usb-c', 'thunderbolt', 'firewire', 'hdmi'
                        ];

                        // Check the specs (from Title and Features)
                        foreach ($all_specs as $spec) {
                            $spec_lower = strtolower($spec);

                            // Logic for extracting the "Channels" value (e.g., 7.1 or 5.1)
                            if (strpos($spec_lower, 'channel') !== false) {
                                // Match "x.x" format, e.g., 7.1 or 5.1
                                if (preg_match('/\d+(\.\d)?/', $spec, $matches)) {
                                    $channels = esc_html($matches[0]);  // Capture the numeric value like "7.1" or "5.1"
                                }
                            } 
                            
                            // Logic for extracting "Digital Audio" value (e.g., 32-bit, 24-bit)
                            elseif (strpos($spec_lower, 'digital audio') !== false) {
                                // Match 32-bit, 24-bit, etc., for digital audio
                                if (preg_match('/\d{2}-bit/', $spec, $matches)) {
                                    $digital_audio = esc_html($matches[0]);  // Capture the bit value (e.g., 32-bit, 24-bit)
                                }
                            } 
                            
                            // Logic for extracting "SNR" value (e.g., 122 dB, 129 dB)
                            elseif (strpos($spec_lower, 'snr') !== false) {
                                // Match values like "122 dB", "129 dB", etc.
                                if (preg_match('/\d+\s*dB/', $spec, $matches)) {
                                    $snr = esc_html($matches[0]);  // Capture the dB value (e.g., 122 dB)
                                }
                            } 
                            
                            // Logic for extracting "Sample Rate" value (e.g., 192 kHz or 192kHz)
                            elseif (strpos($spec_lower, 'sample rate') !== false) {
                                // Match sample rate formats like 192 kHz, 192kHz, 384 kHz, etc.
                                if (preg_match_all('/\d{3,4}\s?kHz/i', $spec, $matches)) {
                                    // The $matches array will contain all matches, so we pick the first match or all of them as needed
                                    // For now, we are taking the first match
                                    $sample_rate = esc_html($matches[0][0]);  // Take the first match, or loop through if needed
                                }
                            }
                            
                            // Logic for extracting "Chipset" value (e.g., Sound Core3D, Creative E-MU, ASUS AV100)
                            elseif (strpos($spec_lower, 'chipset') !== false) {
                                foreach ($known_chipsets as $chipset_name) {
                                    if (strpos($spec_lower, $chipset_name) !== false) {
                                        $chipset = esc_html($chipset_name);
                                        break;  // Break as soon as a known chipset is found
                                    }
                                }
                            }
                            
                            // Logic for extracting "Interface" value (e.g., PCIe, USB, HDMI)
                            elseif (strpos($spec_lower, 'interface') !== false) {
                                foreach ($known_interfaces as $interface_name) {
                                    if (strpos($spec_lower, $interface_name) !== false) {
                                        $interface = esc_html($interface_name);  // Capture the interface type (e.g., PCIe, USB)
                                        break;  // Break as soon as a known interface is found
                                    }
                                }
                            }
                        }
                        $rating_count = display_rating_and_count($sellerRating, $sellerCount);
                    ?>

                    <tr style="background-color: <?php echo $row_bg; ?>;">
                        <td style="padding: 10px; text-align: center;">
                            <?php if ($image): ?>
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" style="width:125px; height:auto; border-radius:4px;">
                            <?php else: ?>
                                <span>No Image</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px; font-weight: 600;"><?php echo $title; ?></td>
                        <td style="padding: 10px;"><?php echo $channels; ?></td>
                        <td style="padding: 10px;"><?php echo $digital_audio; ?></td>
                        <td style="padding: 10px;"><?php echo $snr; ?></td>
                        <td style="padding: 10px;"><?php echo $sample_rate; ?></td>
                        <td style="padding: 10px;"><?php echo $chipset; ?></td>
                        <td style="padding: 10px;"><?php echo $interface; ?></td>
                        <td style="padding: 10px;" data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"><?php echo $rating_count; ?></td>
                        <td style="padding: 10px; font-weight: 600;"><?php echo esc_html($price); ?></td>
                        <td style="padding:10px;">
                            <button class="add-to-builder"
                                data-asin="<?php echo esc_attr($asin); ?>"
                                data-title="<?php echo esc_attr($full_title); ?>"
                                data-image="<?php echo esc_url($image); ?>"
                                data-base="<?php echo esc_attr($price); ?>"
                                data-shipping="FREE"
                                data-availability="<?php echo esc_attr($availability); ?>"
                                data-price="<?php echo esc_attr($price); ?>"
                                data-category="<?php echo esc_attr($category); ?>"
                                data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"
                                data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                data-channels="<?php echo esc_attr($channels); ?>"
                                data-digital-audio="<?php echo esc_attr($digital_audio); ?>"
                                data-snr="<?php echo esc_attr($snr); ?>"
                                data-sample-rate="<?php echo esc_attr($sample_rate); ?>"
                                data-interface="<?php echo esc_attr($interface); ?>"
                                style="padding:10px 18px; background-color:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer;">
                                <?php _e('Add', 'aawp-pcbuild'); ?>
                            </button>
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

<script>
    // Channels filtering
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const tableRows = table.querySelectorAll("tbody tr");
        const filterContainer = document.getElementById("channels-filter");
        const channelsSet = new Set();

        const VISIBLE_COUNT = 4; // How many channels to show initially
        let expanded = false;

        // Collect unique channels from the `data-channels` attribute
        tableRows.forEach(row => {
            const channel = row.querySelector("button.add-to-builder")?.dataset.channels || "Unknown";
            if (channel !== "Unknown" && channel !== "-") {
                channelsSet.add(channel); // Collect unique channel values (like "7.1", "10", etc.)
            }
        });

        // Prepare checkboxes for each unique channel
        const channels = Array.from(channelsSet).sort(); // Sort alphabetically
        const checkboxElements = [];

        channels.forEach(channel => {
            const label = document.createElement("label");
            label.innerHTML = `<input type="checkbox" name="channel" value="${channel}" checked> ${channel}`;
            label.style.display = 'block'; // Ensure each checkbox is on its own line
            checkboxElements.push(label);
        });

        // Append the checkboxes to the filter container
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) {
                el.style.display = 'none'; // Hide extra checkboxes initially
            }
            filterContainer.appendChild(el);
        });

        // Add Show more / Show less link for filtering
        const toggleLink = document.createElement("a");
        toggleLink.href = "#";
        toggleLink.textContent = "Show more";
        toggleLink.style.display = (checkboxElements.length > VISIBLE_COUNT) ? "inline-block" : "none";
        toggleLink.style.marginTop = "5px";
        toggleLink.style.fontSize = "14px";
        toggleLink.style.color = "#0066cc";
        filterContainer.appendChild(toggleLink);

        // Zebra stripe function to apply alternating row background
        function applyZebraStriping() {
            const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
            visibleRows.forEach((row, index) => {
                row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
            });
        }

        const allCheckbox = document.getElementById("channel-all");

        function updateAllCheckboxState() {
            const allBoxes = Array.from(document.querySelectorAll("input[name='channel']"));
            const checkedBoxes = allBoxes.filter(cb => cb.checked);
            allCheckbox.checked = checkedBoxes.length === allBoxes.length;
        }

        // Apply the channel filter based on selected checkboxes
        function applyChannelFilter() {
            const selected = Array.from(document.querySelectorAll("input[name='channel']:checked"))
                .map(cb => cb.value);  // Get selected channel values (e.g., "7.1", "10", "44.1")

            tableRows.forEach(row => {
                const channel = row.querySelector("button.add-to-builder")?.dataset.channels;  // Get the `data-channels` value
                const show = selected.includes(channel);  // Show or hide the row based on the selected channels
                row.style.display = show ? "" : "none";
            });

            updateAllCheckboxState();
            applyZebraStriping();
        }

        // Toggle "All" checkbox functionality
        allCheckbox.addEventListener("change", function () {
            const allBoxes = document.querySelectorAll("input[name='channel']");
            allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
            applyChannelFilter();
        });

        // Individual checkbox change functionality
        filterContainer.addEventListener("change", function (e) {
            if (e.target.name === "channel") {
                applyChannelFilter();
            }
        });

        // Show more/less logic for checkboxes
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

        // Initial apply of the filter
        applyChannelFilter();
    });
</script>


<script>
    // PRICE FILTERING
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("pcbuild-table");
        const sliderContainer = document.getElementById("price-slider");
        const minLabel = document.getElementById("price-min-label");
        const maxLabel = document.getElementById("price-max-label");

        if (!table || !sliderContainer) return;

        const rows = Array.from(table.querySelectorAll("tbody tr"));
        const prices = rows.map(row => {
            const priceText = row.querySelector("td:nth-child(10)")?.textContent.replace(/[^0-9.]/g, '') || "0";
            const price = parseFloat(priceText);  // Parse cleaned price
            return isNaN(price) ? 0 : price;  // Ensure valid number or 0
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
                const priceText = row.querySelector("td:nth-child(10)")?.textContent.replace(/[^0-9.]/g, '') || "0";
                const price = parseFloat(priceText);  // Get the cleaned price value

                // Show or hide rows based on price range
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

        // Set default arrows for headers on page load
        headers.forEach(header => {
            // Add the default arrow (▲) to the first column's sortable header
            header.innerHTML = `&#9654; ${header.textContent.trim()}`;
        });

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
                channels: 3,  // Column for "Channels"
                digital_audio: 4,  // Column for "Digital Audio"
                snr: 5,  // Column for "SNR"
                sample_rate: 6,  // Column for "Sample Rate"
                chipset: 7,  // Column for "Chipset"
                interface: 8,  // Column for "Interface"
                rating: 9,  // Column for "Seller Rating"
                price: 10  // Column for "Price"
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

    <?php
    return ob_get_clean();
}
add_shortcode('pcbuild_parts_sound_cards', 'aawp_pcbuild_display_parts_sound_cards');

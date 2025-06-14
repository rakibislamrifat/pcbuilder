<?php
set_time_limit(1000);

if (!defined('ABSPATH')) {
    exit;
}

function aawp_pcbuild_get_products($category) {
    $category = sanitize_text_field($category);
    $cache_key = 'aawp_pcbuild_cache_' . md5($category);
    $cache_time = 12 * HOUR_IN_SECONDS;

    // Check WordPress transient cache
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    // Fetch plugin settings
    $access_key    = get_option('aawp_pcbuild_amazon_access_key');
    $secret_key    = get_option('aawp_pcbuild_amazon_secret_key');
    $associate_tag = get_option('aawp_pcbuild_amazon_associate_tag');

    if (!$access_key || !$secret_key || !$associate_tag) {
        return 'Error: Amazon API credentials are missing. Please check settings.';
    }

    // Request Setup
    $region   = 'us-east-1';
    $endpoint = 'https://webservices.amazon.com/paapi5/searchitems';
    $host     = 'webservices.amazon.com';
    $uri_path = '/paapi5/searchitems';

    $search_data = aawp_pcbuild_get_search_index($category);
    $all_items = [];
    $max_pages = 10;

    for ($page = 1; $page <= $max_pages; $page++) {
        $request_payload = [
            'Keywords'     => $search_data['keywords'],
            'SearchIndex'  => $search_data['search_index'],
            'ItemPage'     => $page,
            'Resources' => [
                'Images.Primary.Large',
                'ItemInfo.Title',
                'ItemInfo.Features',
                'ItemInfo.ProductInfo',
                'ItemInfo.TechnicalInfo',
                "ItemInfo.ByLineInfo",
                'Offers.Listings.Price',
                'Offers.Listings.DeliveryInfo.IsFreeShippingEligible',
                'Offers.Listings.Promotions',
                'Offers.Listings.MerchantInfo',
                'Offers.Listings.Availability.Message',
                'CustomerReviews.Count',
                'CustomerReviews.StarRating',
            ],
            'PartnerTag'   => $associate_tag,
            'PartnerType'  => 'Associates',
            'Marketplace'  => 'www.amazon.com'
        ];

        $json_payload = json_encode($request_payload);
        $timestamp = gmdate('Ymd\THis\Z');

        // Build signed headers
        $headers = generateSignedHeaders_v2($access_key, $secret_key, $region, $host, $uri_path, $json_payload, $timestamp);

        // Make the request via cURL
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || !$response) {
            continue; // Skip this page on error
        }

        $data = json_decode($response, true);

        if (isset($data['Errors'])) {
            error_log('Amazon API Errors (Page ' . $page . '): ' . print_r($data['Errors'], true));
            continue; // Skip this page if it has errors
        }

        if (!empty($data['SearchResult']['Items'])) {
            $all_items = array_merge($all_items, $data['SearchResult']['Items']);
        }

        // Stop early if fewer than 10 items returned (likely last page)
        if (count($data['SearchResult']['Items']) < 10) {
            break;
        }

        // Optional: avoid throttling (Amazon PAAPI recommends delay)
        // sleep(1);
    }

    $final_data = [
        'SearchResult' => [
            'Items' => $all_items
        ]
    ];

    set_transient($cache_key, $final_data, $cache_time);
    return $final_data;
}

/* function aawp_pcbuild_get_search_index($category) {
    $category = strtolower($category);

    $mapping = [
        'cpu'              => 'Computers',
        'cpu cooler'       => 'Computers',
        'motherboard'      => 'Computers',
        'memory'           => 'Computers',
        'ram'              => 'Computers',
        'storage'          => 'Computers',
        'video card'       => 'Computers',
        'gpu'              => 'Computers',
        'case'             => 'Computers',
        'pc-case'          => 'Computers',
        'power supply'     => 'Computers',
        'operating system' => 'Software',
        'monitor'          => 'Electronics',
        'keyboard'         => 'Electronics',
        'mouse'            => 'Electronics',
    ];

    switch ($category) {
        case 'case':
		case 'pc-case':
			$keywords = 'ATX computer case';
			break;
        case 'storage':
    		$keywords = 'Internal SSD';
   			break;
        case 'memory':
        case 'ram':
            $keywords = 'RAM';
            break;
        case 'video card':
		case 'gpu':
			$keywords = 'graphics card';
			break;
		case 'power supply':
		case 'psu':
			$keywords = 'power supply unit';
			break;
        case 'operating system':
			$keywords = 'Windows 11';
			break;
        default:
            $keywords = $category;
            break;
    }

    return [
        'search_index' => $mapping[$category] ?? 'Electronics',
        'keywords'     => $keywords,
    ];
} */

function aawp_pcbuild_get_search_index($category) {
    $category = strtolower($category);

    $mapping = [
        'cpu'                  => 'Computers',
        'cpu cooler'           => 'Computers',
        'motherboard'          => 'Computers',
        'memory'               => 'Computers',
        'ram'                  => 'Computers',
        'storage'              => 'Computers',
        'video card'           => 'Computers',
        'gpu'                  => 'Computers',
        'case'                 => 'Computers',
        'pc-case'              => 'Computers',
        'power supply'         => 'Computers',
        'operating system'     => 'Software',
        'monitor'              => 'Electronics',
        'keyboard'             => 'Electronics',
        'mouse'                => 'Electronics',

        // New categories from Expansion Cards / Networking
        'sound cards'          => 'Electronics',
        'wired network adapters'    => 'Electronics',
        'wireless network adapters' => 'Electronics',

        // Peripherals
        'headphones'           => 'Electronics',
        'keyboards'            => 'Electronics',
        'mice'                 => 'Electronics',
        'speakers'             => 'Electronics',
        'webcams'              => 'Electronics',

        // Accessories / Other
        'case accessories'     => 'Computers',
        'case fans'            => 'Computers',
        'fan controllers'      => 'Computers',
        'thermal compound'     => 'Computers',
        'external storage'     => 'Computers',
        'optical drives'       => 'Computers',
        'ups systems'          => 'Electronics',
    ];

    switch ($category) {
        case 'case':
        case 'pc-case':
            $keywords = 'ATX computer case';
            break;
        case 'storage':
            $keywords = 'Internal SSD';
            break;
        case 'memory':
        case 'ram':
            $keywords = 'RAM';
            break;
        case 'video card':
        case 'gpu':
            $keywords = 'graphics card';
            break;
        case 'power supply':
        case 'psu':
            $keywords = 'power supply unit';
            break;
        case 'operating system':
            $keywords = 'Windows 11';
            break;

        // Added keywords for new categories
        case 'sound cards':
            $keywords = 'sound card audio';
            break;
        case 'wired network adapters':
            $keywords = 'ethernet network adapter';
            break;
        case 'wireless network adapters':
            $keywords = 'wifi network adapter';
            break;
        case 'headphones':
            $keywords = 'headphones headset';
            break;
        case 'keyboards':
            $keywords = 'computer keyboard mechanical keyboard';
            break;
        case 'mice':
            $keywords = 'computer mouse wireless mouse';
            break;
        case 'speakers':
            $keywords = 'computer speakers audio speakers';
            break;
        case 'webcams':
            $keywords = 'webcam HD camera';
            break;
        case 'case accessories':
            $keywords = 'pc case accessories';
            break;
        case 'case fans':
            $keywords = 'pc case fans cooling fans';
            break;
        case 'fan controllers':
            $keywords = 'fan controller pc cooling';
            break;
        case 'thermal compound':
            $keywords = 'thermal paste cpu cooler';
            break;
        case 'external storage':
            $keywords = 'external hard drive';
            break;
        case 'optical drives':
            $keywords = 'dvd cd burner drive';
            break;
        case 'ups systems':
            $keywords = 'uninterruptible power supply';
            break;

        default:
            $keywords = $category;
            break;
    }

    return [
        'search_index' => $mapping[$category] ?? 'Electronics',
        'keywords'     => $keywords,
    ];
}


function getSignatureKey($key, $dateStamp, $regionName, $serviceName) {
    $kSecret  = 'AWS4' . $key;
    $kDate    = hash_hmac('sha256', $dateStamp, $kSecret, true);
    $kRegion  = hash_hmac('sha256', $regionName, $kDate, true);
    $kService = hash_hmac('sha256', $serviceName, $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
    return $kSigning;
}

function generateSignedHeaders_v2($access_key, $secret_key, $region, $host, $uri_path, $payload, $timestamp) {
    $service = 'ProductAdvertisingAPI';
    $algorithm = 'AWS4-HMAC-SHA256';
    $date = gmdate('Ymd');
    $credential_scope = "$date/$region/$service/aws4_request";

    $canonical_headers = "content-encoding:amz-1.0\ncontent-type:application/json; charset=utf-8\nhost:$host\nx-amz-date:$timestamp\nx-amz-target:com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems\n";
    $signed_headers = "content-encoding;content-type;host;x-amz-date;x-amz-target";
    $payload_hash = hash('sha256', $payload);

    $canonical_request = "POST\n$uri_path\n\n$canonical_headers\n$signed_headers\n$payload_hash";
    $string_to_sign = "$algorithm\n$timestamp\n$credential_scope\n" . hash('sha256', $canonical_request);

    $signing_key = getSignatureKey($secret_key, $date, $region, $service);
    $signature = hash_hmac('sha256', $string_to_sign, $signing_key);

    $authorization_header = "$algorithm Credential=$access_key/$credential_scope, SignedHeaders=$signed_headers, Signature=$signature";

    return [
        "Content-Encoding: amz-1.0",
        "Content-Type: application/json; charset=utf-8",
        "Host: $host",
        "X-Amz-Date: $timestamp",
        "X-Amz-Target: com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems",
        "Authorization: $authorization_header"
    ];
}

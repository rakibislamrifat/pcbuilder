<?php
   // Function to display stars as ★★★☆☆ and show review count
    function display_rating_and_count($rating, $count) {
        // Round the rating based on Math.round() logic
        $roundedRating = round($rating); // This rounds the rating to nearest integer
        $fullStars = $roundedRating;
        $emptyStars = 5 - $fullStars;

        $starsHtml = '';

        // Full stars (★)
        for ($i = 0; $i < $fullStars; $i++) {
            $starsHtml .= '<span style="color: orange;">★</span>';
        }

        // Empty stars (☆)
        for ($i = 0; $i < $emptyStars; $i++) {
            $starsHtml .= '<span style="color: orange;">☆</span>';
        }

        // Add review count
        return $starsHtml . ' (' . number_format($count) . ')';
    }
?>
<?php
// Default to USD if nothing is set in the session
if (!isset($_SESSION['currency'])) {
    $_SESSION['currency'] = 'USD';
}

function formatCurrency($usd_amount) {
    $target_currency = $_SESSION['currency'];

    // Define your exchange rates (Base: 1 USD)
    // You can update these manually or hook them up to a live API later
    $rates = [
        'USD' => 1.00,
        'THB' => 34.50, // Thai Baht
        'JPY' => 150.20 // Japanese Yen
    ];

    // Define the symbols
    $symbols = [
        'USD' => '$',
        'THB' => '฿',
        'JPY' => '¥'
    ];

    $rate = $rates[$target_currency];
    $symbol = $symbols[$target_currency];

    // Calculate the converted amount
    $converted_amount = $usd_amount * $rate;

    // JPY typically doesn't use decimals, so we format accordingly
    if ($target_currency === 'JPY') {
        return $symbol . number_format($converted_amount, 0);
    } else {
        return $symbol . number_format($converted_amount, 2);
    }
}
?>
<?php
// Shared helper to get gym settings
function getSetting($pdo, $key, $default = '') {
    try {
        $s = $pdo->prepare("SELECT setting_value FROM GYM_SETTINGS WHERE setting_key=?");
        $s->execute([$key]);
        $r = $s->fetch();
        return $r ? $r['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function getAllSettings($pdo) {
    return [
        'gym_name'     => getSetting($pdo, 'gym_name', 'GymPro'),
        'gym_address'  => getSetting($pdo, 'gym_address', 'Jl. Sudirman No. 88, Jakarta'),
        'gym_phone'    => getSetting($pdo, 'gym_phone', '(021) 5555-1234'),
        'gym_email'    => getSetting($pdo, 'gym_email', 'info@gympro.id'),
        'gym_lat'      => getSetting($pdo, 'gym_lat', '-6.2175'),
        'gym_lng'      => getSetting($pdo, 'gym_lng', '106.8050'),
        'bank_name'    => getSetting($pdo, 'bank_name', 'BNI'),
        'bank_account' => getSetting($pdo, 'bank_account', '1924182745'),
        'bank_holder'  => getSetting($pdo, 'bank_holder', 'GYM PRO'),
        'dana_number'  => getSetting($pdo, 'dana_number', '082386210045'),
        'dana_holder'  => getSetting($pdo, 'dana_holder', 'GYM PRO'),
        'gopay_number' => getSetting($pdo, 'gopay_number', '082386210045'),
        'gopay_holder' => getSetting($pdo, 'gopay_holder', 'GYM PRO'),
        'qris_file'    => getSetting($pdo, 'qris_file', 'qris.jpeg'),
    ];
}
?>

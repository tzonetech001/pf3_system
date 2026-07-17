<?php
// includes/sms_helper.php - SMS sending functions for Beem API

// ============================================
// LANGUAGE DETECTION FOR SMS
// ============================================
function getCurrentLang() {
    // Check if language is set in session
    if (isset($_SESSION['public_lang'])) {
        return $_SESSION['public_lang'];
    }
    
    // Check if language is set in cookie
    if (isset($_COOKIE['public_lang'])) {
        return $_COOKIE['public_lang'];
    }
    
    // Default to English
    return 'en';
}

/**
 * Clean phone number to ensure it has 255 prefix
 * @param string $phone Raw phone number
 * @return string Cleaned phone number with 255 prefix
 */
function cleanPhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Remove leading zeros
    $phone = ltrim($phone, '0');
    
    // If phone starts with 255, keep it
    if (substr($phone, 0, 3) === '255') {
        return $phone;
    }
    
    // If phone has 12 digits and starts with 255 already
    if (strlen($phone) === 12 && substr($phone, 0, 3) === '255') {
        return $phone;
    }
    
    // If phone has 9 digits (6 or 7 followed by 8 digits)
    if (strlen($phone) === 9 && ($phone[0] === '6' || $phone[0] === '7')) {
        return '255' . $phone;
    }
    
    // If phone has 10 digits (0 followed by 9 digits)
    if (strlen($phone) === 10 && $phone[0] === '0') {
        $phone = substr($phone, 1);
        if (strlen($phone) === 9 && ($phone[0] === '6' || $phone[0] === '7')) {
            return '255' . $phone;
        }
    }
    
    // Default: just add 255 if not already there
    if (substr($phone, 0, 3) !== '255') {
        return '255' . $phone;
    }
    
    return $phone;
}

/**
 * Validate phone number format
 * @param string $phone Phone number
 * @return bool True if valid
 */
function validatePhoneNumber($phone) {
    $phone = cleanPhoneNumber($phone);
    // Must be 12 digits starting with 255
    return (strlen($phone) === 12 && substr($phone, 0, 3) === '255');
}

/**
 * Send SMS using Beem API
 * @param string $phone Recipient phone number
 * @param string $message SMS message (max 159 characters)
 * @return array Response data
 */
function sendSMS($phone, $message) {
    // Beem API credentials
    $api_key = "386bdc07eae64a53";
    $secret_key = "NWJmNmZkYTdhODRkYmFhNDY1YjQ4Mzg2NzBiNjEzNzYzMDU0OGE4MWUzOWM5Yjc2OTI5ZDAwNDZiYmQ1ZDY4NA==";
    $sender_id = "NKIGISHA";
    
    // Clean phone number
    $phone = cleanPhoneNumber($phone);
    
    // Validate phone (should be 12 digits starting with 255)
    if (strlen($phone) !== 12 || substr($phone, 0, 3) !== '255') {
        return ['success' => false, 'message' => 'Invalid phone number: ' . $phone];
    }
    
    // Limit message to 159 characters (Beem recommended max)
    if (strlen($message) > 159) {
        $message = substr($message, 0, 159);
    }
    
    // Prepare data for Beem API
    $postData = [
        'source_addr' => $sender_id,
        'encoding' => 0,
        'message' => $message,
        'recipients' => [
            [
                'recipient_id' => 1,
                'dest_addr' => $phone
            ]
        ]
    ];
    
    // Send to Beem API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://apisms.beem.africa/v1/send',
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode("$api_key:$secret_key"),
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        if (isset($result['successful']) && $result['successful']) {
            return ['success' => true, 'message' => 'SMS sent successfully'];
        } else {
            return ['success' => false, 'message' => $result['message'] ?? 'Unknown error from Beem'];
        }
    } else {
        return ['success' => false, 'message' => 'HTTP ' . $http_code . ($curl_error ? ' - ' . $curl_error : '')];
    }
}

/**
 * Send PF3 Application SMS Notification to Patient ONLY
 * @param string $phone Patient phone number (255 format)
 * @param string $name Patient name
 * @param string $pf3_number PF3 number
 * @return array Response data
 */
function sendPF3ApplicationSMS($phone, $name, $pf3_number) {
    $lang = getCurrentLang();
    
    // Clean phone number first
    $clean_phone = cleanPhoneNumber($phone);
    
    if (!validatePhoneNumber($clean_phone)) {
        return ['success' => false, 'message' => 'Invalid phone number: ' . $phone];
    }
    
    // Get language preference
    if ($lang === 'sw') {
        // KISWAHILI MESSAGE
        $message = "PF3 SYS: Mteja $name, maombi yako ya PF3 #$pf3_number yamewasilishwa. Tumia nambari hii kumalizia maombi na kufuatilia hali yake. Asante!";
        
        // Ensure message doesn't exceed 159 characters
        if (strlen($message) > 159) {
            $name_short = strlen($name) > 10 ? substr($name, 0, 10) : $name;
            $message = "PF3 SYS: $name_short, maombi #$pf3_number yamewasilishwa. Tumia nambari hii kumalizia na kufuatilia maombi yako. Asante!";
            if (strlen($message) > 159) {
                $message = "PF3 SYS: Maombi #$pf3_number yamewasilishwa. Tumia nambari hii kumalizia na kufuatilia. Asante!";
            }
        }
    } else {
        // ENGLISH MESSAGE
        $message = "PF3 SYS: Dear $name, your PF3 application #$pf3_number has been submitted. Use this number to complete your application and track its status. Thank you!";
        
        if (strlen($message) > 159) {
            $name_short = strlen($name) > 10 ? substr($name, 0, 10) : $name;
            $message = "PF3 SYS: Dear $name_short, application #$pf3_number submitted. Use this number to complete and track your application. Thank you!";
            if (strlen($message) > 159) {
                $message = "PF3 SYS: Application #$pf3_number submitted. Use this number to complete and track. Thank you!";
            }
        }
    }
    
    return sendSMS($clean_phone, $message);
}

/**
 * Send PF3 Status Update SMS Notification to Patient ONLY
 * @param string $phone Patient phone number (255 format)
 * @param string $name Patient name
 * @param string $pf3_number PF3 number
 * @param string $status New status (APPROVED, REJECTED, PENDING)
 * @param string $police_notes Optional police notes
 * @param string $rb_number Optional RB number
 * @return array Response data
 */
function sendPF3StatusUpdateSMS($phone, $name, $pf3_number, $status, $police_notes = '', $rb_number = '') {
    $lang = getCurrentLang();
    
    // Clean phone number
    $clean_phone = cleanPhoneNumber($phone);
    
    if (!validatePhoneNumber($clean_phone)) {
        return ['success' => false, 'message' => 'Invalid phone number: ' . $phone];
    }
    
    // Status translation
    $status_map_en = [
        'APPROVED' => 'APPROVED',
        'REJECTED' => 'REJECTED',
        'PENDING' => 'PENDING'
    ];
    
    $status_map_sw = [
        'APPROVED' => 'IMEKUBALIWA',
        'REJECTED' => 'IMEKATALIWA',
        'PENDING' => 'INASUBIRI'
    ];
    
    $status_display = ($lang === 'sw') ? ($status_map_sw[$status] ?? $status) : ($status_map_en[$status] ?? $status);
    
    if ($lang === 'sw') {
        // KISWAHILI MESSAGE
        if ($status === 'APPROVED') {
            $message = "PF3 SYS: Mteja $name, maombi yako #$pf3_number yamekubaliwa";
            if ($rb_number) {
                $message .= ". Nambari ya RB: $rb_number";
            }
            $message .= ". Endelea na hatua za matibabu. Asante!";
        } elseif ($status === 'REJECTED') {
            $message = "PF3 SYS: Mteja $name, maombi yako #$pf3_number yamekataliwa";
            if ($police_notes) {
                $message .= ". Sababu: " . substr($police_notes, 0, 30);
            }
            $message .= ". Tafadhali wasiliana na polisi. Asante!";
        } else {
            $message = "PF3 SYS: Mteja $name, maombi yako #$pf3_number yanasubiri ukaguzi wa polisi. Tutakujulisha. Asante!";
        }
        
        // Ensure message doesn't exceed 159 characters
        if (strlen($message) > 159) {
            $name_short = strlen($name) > 10 ? substr($name, 0, 10) : $name;
            $message = "PF3 SYS: $name_short, maombi #$pf3_number " . strtolower($status_display);
            if ($status === 'APPROVED' && $rb_number) {
                $message .= ". RB: $rb_number";
            }
            $message .= ". Asante!";
        }
    } else {
        // ENGLISH MESSAGE
        if ($status === 'APPROVED') {
            $message = "PF3 SYS: Dear $name, your application #$pf3_number has been APPROVED";
            if ($rb_number) {
                $message .= ". RB Number: $rb_number";
            }
            $message .= ". Please proceed with medical examination. Thank you!";
        } elseif ($status === 'REJECTED') {
            $message = "PF3 SYS: Dear $name, your application #$pf3_number has been REJECTED";
            if ($police_notes) {
                $message .= ". Reason: " . substr($police_notes, 0, 30);
            }
            $message .= ". Please contact the police station. Thank you!";
        } else {
            $message = "PF3 SYS: Dear $name, your application #$pf3_number is PENDING police review. We will notify you. Thank you!";
        }
        
        if (strlen($message) > 159) {
            $name_short = strlen($name) > 10 ? substr($name, 0, 10) : $name;
            $message = "PF3 SYS: $name_short, application #$pf3_number " . $status_display;
            if ($status === 'APPROVED' && $rb_number) {
                $message .= ". RB: $rb_number";
            }
            $message .= ". Thank you!";
        }
    }
    
    return sendSMS($clean_phone, $message);
}

/**
 * Check SMS balance from Beem API
 * @return array Balance data
 */
function checkSMSBalance() {
    $api_key = "386bdc07eae64a53";
    $secret_key = "NWJmNmZkYTdhODRkYmFhNDY1YjQ4Mzg2NzBiNjEzNzYzMDU0OGE4MWUzOWM5Yjc2OTI5ZDAwNDZiYmQ1ZDY4NA==";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://apisms.beem.africa/public/v1/vendors/balance',
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode("$api_key:$secret_key"),
            'Content-Type: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        return ['success' => true, 'balance' => $data['data']['credit_balance'] ?? 0];
    } else {
        return ['success' => false, 'balance' => 0, 'message' => 'Failed to fetch balance'];
    }
}
?>
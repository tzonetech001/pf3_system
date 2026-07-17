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
 * @param string $message SMS message (max 155 characters)
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
    
    // Limit message to 155 characters (max allowed)
    if (strlen($message) > 155) {
        $message = substr($message, 0, 155);
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
        // KISWAHILI MESSAGE - MAX 155 CHARACTERS
        $message = "PF3 SYS: Mteja $name, maombi #$pf3_number yamewasilishwa. Tumia nambari kufuatilia. Asante!";
        
        // Ensure message doesn't exceed 155 characters
        if (strlen($message) > 155) {
            $name_short = strlen($name) > 8 ? substr($name, 0, 8) : $name;
            $message = "PF3 SYS: $name_short, maombi #$pf3_number yamewasilishwa. Tumia nambari kufuatilia. Asante!";
            if (strlen($message) > 155) {
                $message = "PF3 SYS: Maombi #$pf3_number yamewasilishwa. Tumia nambari kufuatilia. Asante!";
            }
        }
    } else {
        // ENGLISH MESSAGE - MAX 155 CHARACTERS
        $message = "PF3 SYS: Dear $name, application #$pf3_number submitted. Use number to track. Thank you!";
        
        if (strlen($message) > 155) {
            $name_short = strlen($name) > 8 ? substr($name, 0, 8) : $name;
            $message = "PF3 SYS: Dear $name_short, app #$pf3_number submitted. Use number to track. Thank you!";
            if (strlen($message) > 155) {
                $message = "PF3 SYS: Application #$pf3_number submitted. Use number to track. Thank you!";
            }
        }
    }
    
    return sendSMS($clean_phone, $message);
}

/**
 * Send PF3 Status Update SMS - APPROVED or REJECTED only
 * @param string $phone Patient phone number (255 format)
 * @param string $name Patient name
 * @param string $pf3_number PF3 number
 * @param string $status New status (APPROVED or REJECTED only)
 * @param string $police_notes Optional police notes (for rejection)
 * @param string $rb_number Optional RB number (for approval)
 * @return array Response data
 */
function sendPF3StatusUpdateSMS($phone, $name, $pf3_number, $status, $police_notes = '', $rb_number = '') {
    $lang = getCurrentLang();
    
    // Clean phone number
    $clean_phone = cleanPhoneNumber($phone);
    
    if (!validatePhoneNumber($clean_phone)) {
        return ['success' => false, 'message' => 'Invalid phone number: ' . $phone];
    }
    
    // ============================================================
    // ONLY APPROVED AND REJECTED - NO PENDING
    // MAX 155 CHARACTERS
    // ============================================================
    
    if ($status === 'APPROVED') {
        // ============================================================
        // APPROVED SMS - Patient should visit doctor/hospital
        // ============================================================
        if ($lang === 'sw') {
            // KISWAHILI - APPROVED
            $rb_text = $rb_number ? " RB: $rb_number." : ".";
            $message = "PF3 SYS: Mteja $name, maombi #$pf3_number yamekubaliwa$rb_text Nenda hospitali kwa daktari. Asante!";
            
            // Shorten if needed (max 155 chars)
            if (strlen($message) > 155) {
                $name_short = strlen($name) > 6 ? substr($name, 0, 6) : $name;
                $rb_text = $rb_number ? " RB: $rb_number." : ".";
                $message = "PF3 SYS: $name_short, maombi #$pf3_number yamekubaliwa$rb_text Nenda hospitali. Asante!";
            }
            // Final truncation to 155 chars
            if (strlen($message) > 155) {
                $message = substr($message, 0, 152) . '...';
            }
            
        } else {
            // ENGLISH - APPROVED
            $rb_text = $rb_number ? " RB: $rb_number." : ".";
            $message = "PF3 SYS: Dear $name, application #$pf3_number APPROVED$rb_text Visit hospital for medical exam. Thank you!";
            
            // Shorten if needed (max 155 chars)
            if (strlen($message) > 155) {
                $name_short = strlen($name) > 6 ? substr($name, 0, 6) : $name;
                $rb_text = $rb_number ? " RB: $rb_number." : ".";
                $message = "PF3 SYS: $name_short, app #$pf3_number APPROVED$rb_text Visit hospital. Thank you!";
            }
            // Final truncation to 155 chars
            if (strlen($message) > 155) {
                $message = substr($message, 0, 152) . '...';
            }
        }
    }
    
    elseif ($status === 'REJECTED') {
        // ============================================================
        // REJECTED SMS - Clear message with reason
        // ============================================================
        // Truncate police notes for SMS
        $reason = $police_notes ? substr($police_notes, 0, 35) : 'No reason';
        if (strlen($police_notes) > 35) {
            $reason .= '...';
        }
        
        if ($lang === 'sw') {
            // KISWAHILI - REJECTED
            $reason_text = $police_notes ? " Sababu: $reason." : ".";
            $message = "PF3 SYS: Mteja $name, maombi #$pf3_number yamekataliwa$reason_text Wasiliana na polisi. Asante!";
            
            // Shorten if needed (max 155 chars)
            if (strlen($message) > 155) {
                $name_short = strlen($name) > 6 ? substr($name, 0, 6) : $name;
                $reason_short = $police_notes ? substr($police_notes, 0, 20) . '...' : '';
                $reason_text = $reason_short ? " Sababu: $reason_short." : ".";
                $message = "PF3 SYS: $name_short, maombi #$pf3_number yamekataliwa$reason_text Wasiliana polisi. Asante!";
            }
            // Final truncation to 155 chars
            if (strlen($message) > 155) {
                $message = substr($message, 0, 152) . '...';
            }
            
        } else {
            // ENGLISH - REJECTED
            $reason_text = $police_notes ? " Reason: $reason." : ".";
            $message = "PF3 SYS: Dear $name, application #$pf3_number REJECTED$reason_text Contact police station. Thank you!";
            
            // Shorten if needed (max 155 chars)
            if (strlen($message) > 155) {
                $name_short = strlen($name) > 6 ? substr($name, 0, 6) : $name;
                $reason_short = $police_notes ? substr($police_notes, 0, 20) . '...' : '';
                $reason_text = $reason_short ? " Reason: $reason_short." : ".";
                $message = "PF3 SYS: $name_short, app #$pf3_number REJECTED$reason_text Contact police. Thank you!";
            }
            // Final truncation to 155 chars
            if (strlen($message) > 155) {
                $message = substr($message, 0, 152) . '...';
            }
        }
    }
    
    // Return the SMS response
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
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
 * Send SMS using Beem API - SIMPLE VERSION (NO EMOJIS, NO SPECIAL CHARS)
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
    
    // ============================================================
    // CRITICAL: Remove ALL emojis and special characters
    // ============================================================
    // Remove emojis
    $message = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $message);
    $message = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $message);
    $message = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $message);
    $message = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $message);
    $message = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $message);
    $message = preg_replace('/[\x{1F900}-\x{1F9FF}]/u', '', $message);
    $message = preg_replace('/[\x{1F700}-\x{1F77F}]/u', '', $message);
    
    // Remove special characters that might cause issues
    $message = str_replace(['*', '_', '~', '`', '|', '>', '<', '✅', '❌', '📱', '📞'], '', $message);
    
    // Remove multiple spaces
    $message = preg_replace('/\s+/', ' ', $message);
    
    // Trim
    $message = trim($message);
    
    // ============================================================
    // CRITICAL FIX: If message is empty after cleaning, use a fallback
    // ============================================================
    if (empty($message)) {
        // Fallback message - simple and clean
        $message = "PF3 SYS: Your application status has been updated. Please contact your nearest police station for more information. Thank you!";
        error_log("SMS: Message was empty after cleaning, using fallback message");
    }
    
    // Limit message to 155 characters (max allowed by Beem)
    if (strlen($message) > 155) {
        $message = substr($message, 0, 152) . '...';
    }
    
    // Final check - if still empty, use emergency fallback
    if (empty($message)) {
        $message = "PF3 SYS: Your application status has been updated. Contact police for details.";
        error_log("SMS: Emergency fallback message used");
    }
    
    // Prepare data for Beem API
    $postData = [
        'source_addr' => $sender_id,
        'encoding' => 0, // 0 = plain text (no Unicode)
        'message' => $message,
        'recipients' => [
            [
                'recipient_id' => 1,
                'dest_addr' => $phone
            ]
        ]
    ];
    
    // Log the request for debugging
    error_log("SMS Request - Phone: $phone, Message: $message");
    
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
    
    // Log response for debugging
    error_log("SMS Response - HTTP: $http_code, Response: $response");
    
    if ($http_code == 200 || $http_code == 201) {
        $result = json_decode($response, true);
        if (isset($result['successful']) && $result['successful']) {
            return ['success' => true, 'message' => 'SMS sent successfully'];
        } else {
            $error_msg = $result['message'] ?? $result['error'] ?? 'Unknown error from Beem';
            return ['success' => false, 'message' => $error_msg];
        }
    } else {
        // If HTTP 400, try to get more details
        $error_detail = $response;
        if ($http_code == 400) {
            $result = json_decode($response, true);
            if (isset($result['message'])) {
                $error_detail = $result['message'];
            } elseif (isset($result['error'])) {
                $error_detail = $result['error'];
            }
        }
        return ['success' => false, 'message' => 'HTTP ' . $http_code . ' - ' . $error_detail];
    }
}

/**
 * Send PF3 Status Update SMS - APPROVED or REJECTED - NO EMOJIS
 * @param string $phone Patient phone number
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
    // Build the message based on status - KEEP IT SIMPLE
    // ============================================================
    if ($status === 'APPROVED') {
        // APPROVED SMS - Simple and clean
        if ($lang === 'sw') {
            if (!empty($rb_number)) {
                $message = "PF3 SYS: Mteja $name, maombi #$pf3_number yamekubaliwa. Namba RB: $rb_number. Nenda hospitali kwa daktari. Asante!";
            } else {
                $message = "PF3 SYS: Mteja $name, maombi #$pf3_number yamekubaliwa. Nenda hospitali kwa daktari. Asante!";
            }
        } else {
            if (!empty($rb_number)) {
                $message = "PF3 SYS: Dear $name, application #$pf3_number APPROVED. RB Number: $rb_number. Visit hospital. Thank you!";
            } else {
                $message = "PF3 SYS: Dear $name, application #$pf3_number APPROVED. Visit hospital. Thank you!";
            }
        }
    } 
    elseif ($status === 'REJECTED') {
        // REJECTED SMS - Simple and clean
        $reason = !empty($police_notes) ? substr($police_notes, 0, 40) : 'No reason provided';
        if (strlen($police_notes) > 40) {
            $reason .= '...';
        }
        
        if ($lang === 'sw') {
            $message = "PF3 SYS: Mteja $name, maombi #$pf3_number yamekataliwa. Sababu: $reason. Wasiliana na polisi. Asante!";
        } else {
            $message = "PF3 SYS: Dear $name, application #$pf3_number REJECTED. Reason: $reason. Contact police. Thank you!";
        }
    } else {
        // Fallback for any other status
        $message = "PF3 SYS: Your application #$pf3_number has been updated to $status. Contact police for details.";
    }
    
    // ============================================================
    // CLEAN THE MESSAGE - Remove ALL special characters
    // ============================================================
    // Remove emojis
    $message = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $message);
    $message = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $message);
    $message = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $message);
    $message = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $message);
    $message = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $message);
    $message = preg_replace('/[\x{1F900}-\x{1F9FF}]/u', '', $message);
    $message = preg_replace('/[\x{1F700}-\x{1F77F}]/u', '', $message);
    
    // Remove special characters
    $message = str_replace(['*', '_', '~', '`', '|', '>', '<', '✅', '❌', '📱', '📞'], '', $message);
    
    // Remove multiple spaces
    $message = preg_replace('/\s+/', ' ', $message);
    
    // Trim
    $message = trim($message);
    
    // ============================================================
    // FALLBACK - If message is empty, use a simple message
    // ============================================================
    if (empty($message)) {
        if ($status === 'APPROVED') {
            $message = "PF3 SYS: Application #$pf3_number approved. Visit hospital. Thank you!";
        } elseif ($status === 'REJECTED') {
            $message = "PF3 SYS: Application #$pf3_number rejected. Contact police. Thank you!";
        } else {
            $message = "PF3 SYS: Application #$pf3_number updated to $status. Contact police.";
        }
        error_log("SMS Status Update: Using fallback message for PF3 $pf3_number");
    }
    
    // ============================================================
    // ENSURE MESSAGE IS NOT EMPTY - Final safety check
    // ============================================================
    if (empty($message)) {
        $message = "PF3 SYS: Your application #$pf3_number has been updated. Contact police for details.";
        error_log("SMS Status Update: Emergency fallback used for PF3 $pf3_number");
    }
    
    // Limit to 155 characters
    if (strlen($message) > 155) {
        $message = substr($message, 0, 152) . '...';
    }
    
    // Log the final message
    error_log("SMS Status Update - Phone: $clean_phone, Status: $status, Message: $message");
    
    // Send the SMS
    return sendSMS($clean_phone, $message);
}

/**
 * Send PF3 Application SMS Notification - SIMPLE VERSION
 * @param string $phone Patient phone number
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
    
    // Simple message - no emojis
    if ($lang === 'sw') {
        $message = "PF3 SYS: Mteja $name, maombi #$pf3_number yamewasilishwa. Tumia nambari hii kufuatilia. Asante!";
    } else {
        $message = "PF3 SYS: Dear $name, application #$pf3_number submitted. Use number to track. Thank you!";
    }
    
    // Clean message
    $message = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $message);
    $message = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $message);
    $message = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $message);
    $message = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $message);
    $message = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $message);
    $message = str_replace(['*', '_', '~', '`', '|', '>', '<', '✅', '❌', '📱', '📞'], '', $message);
    $message = preg_replace('/\s+/', ' ', $message);
    $message = trim($message);
    
    // Fallback if empty
    if (empty($message)) {
        $message = "PF3 SYS: Application #$pf3_number submitted. Thank you!";
    }
    
    // Limit to 155 characters
    if (strlen($message) > 155) {
        $message = substr($message, 0, 152) . '...';
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
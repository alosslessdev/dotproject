<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * TOTP (Time-Based One-Time Password) Class for dotProject
 */
class TOTP {
    private $secret;
    private $digits;
    private $period;
    private $algorithm;

    /**
     * Constructor
     * @param string $secret Base32 encoded secret (optional)
     * @param int $digits Number of digits in TOTP code (default: 6)
     * @param int $period TOTP code validity period in seconds (default: 30)
     * @param string $algorithm Hash algorithm (default: 'sha1')
     */
    public function __construct($secret = null, $digits = 6, $period = 30, $algorithm = 'sha1') {
        if ($secret === null) {
            $secret = $this->generateSecret();
        }
        $this->secret = $secret;
        $this->digits = $digits;
        $this->period = $period;
        $this->algorithm = $algorithm;
    }

    /**
     * Generate a random secret key
     * @return string Base32 encoded secret key
     */
    public function generateSecret($length = 16) {
        $validChars = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
            'Y', 'Z', '2', '3', '4', '5', '6', '7'
        );

        $secret = '';
        $random = random_bytes($length);
        
        for ($i = 0; $i < $length; $i++) {
            $secret .= $validChars[ord($random[$i]) & 31];
        }

        return $secret;
    }

    /**
     * Get current TOTP code
     * @return string
     */
    public function getCurrentCode() {
        return $this->generateCode($this->getTimestamp());
    }

    /**
     * Verify TOTP code
     * @param string $code Code to verify
     * @param int $discrepancy Time window in periods to check before/after current time
     * @return bool
     */
    public function verifyCode($code, $discrepancy = 1) {
        $currentTs = $this->getTimestamp();

        // Check codes in time window
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->generateCode($currentTs + ($i * $this->period));
            if ($this->timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current timestamp divided by period
     * @return int
     */
    private function getTimestamp() {
        return floor(time() / $this->period);
    }

    /**
     * Generate TOTP code for a specific counter value
     * @param int $counter Counter value
     * @return string
     */
    private function generateCode($counter) {
        // Pack counter into binary string
        $input = pack('N*', 0) . pack('N*', $counter);
        
        // Get hash
        $hash = hash_hmac($this->algorithm, $input, $this->base32Decode($this->secret), true);
        
        // Get offset
        $offset = ord($hash[strlen($hash) - 1]) & 0xF;
        
        // Generate 4-byte code at offset
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % pow(10, $this->digits);

        // Pad with leading zeros if necessary
        return str_pad($code, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Decode base32 string
     * @param string $base32
     * @return string
     */
    private function base32Decode($base32) {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32 = strtoupper($base32);
        $buffer = 0;
        $bufferBits = 0;
        $result = '';

        for ($i = 0; $i < strlen($base32); $i++) {
            $buffer = ($buffer << 5) | strpos($base32chars, $base32[$i]);
            $bufferBits += 5;
            if ($bufferBits >= 8) {
                $bufferBits -= 8;
                $result .= chr(($buffer >> $bufferBits) & 0xFF);
            }
        }

        return $result;
    }

    /**
     * Get provisioning URI for QR codes
     * @param string $accountName Account name for authenticator app
     * @param string $issuer Issuer name for authenticator app
     * @return string
     */
    public function getProvisioningUri($accountName, $issuer = 'dotProject') {
        $params = array(
            'secret' => $this->secret,
            'algorithm' => strtoupper($this->algorithm),
            'digits' => $this->digits,
            'period' => $this->period,
            'issuer' => $issuer
        );
        
        $query = http_build_query($params);
        return sprintf('otpauth://totp/%s:%s?%s',
            rawurlencode($issuer),
            rawurlencode($accountName),
            $query
        );
    }

    /**
     * Timing safe string comparison
     * @param string $safe Known string
     * @param string $user User-supplied string
     * @return bool
     */
    private function timingSafeEquals($safe, $user) {
        if (function_exists('hash_equals')) {
            return hash_equals($safe, $user);
        }
        
        // Prevent issues if strings are not the same length
        $safeLen = strlen($safe);
        $userLen = strlen($user);
        
        if ($userLen != $safeLen) {
            return false;
        }
        
        $result = 0;
        
        for ($i = 0; $i < $userLen; $i++) {
            $result |= (ord($safe[$i]) ^ ord($user[$i]));
        }
        
        return $result === 0;
    }

    /**
     * Get the current secret
     * @return string
     */
    public function getSecret() {
        return $this->secret;
    }
}
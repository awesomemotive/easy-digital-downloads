<?php
namespace EDD\Vendor\Square\Utils;

use Exception;

/**
 * Utility to help with EDD\Vendor\Square Webhooks
 */
class WebhooksHelper {
    /**
     * Verifies and validates an event notification.
     * See the documentation for more details.
     *
     * @param string $requestBody       The JSON body of the request.
     * @param string $signatureHeader   The value for the `x-square-hmacsha256-signature` header.
     * @param string $signatureKey      The signature key from the EDD\Vendor\Square Developer portal for the webhook subscription.
     * @param string $notificationUrl   The notification endpoint URL as defined in the EDD\Vendor\Square Developer portal for the webhook subscription.
     * @return bool                     `true` if the signature is valid, indicating that the event can be trusted as it came from Square. `false` if the signature validation fails, indicating that the event did not come from Square, so it may be malicious and should be discarded.
     * @throws Exception                If the signatureKey or notificationUrl is null or empty.
     */
    public static function isValidWebhookEventSignature(
        string $requestBody,
        string $signatureHeader,
        string $signatureKey,
        string $notificationUrl
    ): bool {

        if ($requestBody === null) {
            return false;
        }

        if ($signatureKey === null || strlen($signatureKey) === 0) {
            throw new Exception('signatureKey is null or empty');
        }
        if ($notificationUrl === null || strlen($notificationUrl) === 0) {
            throw new Exception('notificationUrl is null or empty');
        }

        // Perform UTF-8 encoding to bytes
        $payload = $notificationUrl . $requestBody;
        $payloadBytes = mb_convert_encoding($payload, 'UTF-8');
        $signatureKeyBytes = mb_convert_encoding($signatureKey, 'UTF-8');

        // Compute the hash value
        $hash = hash_hmac('sha256', $payloadBytes, $signatureKeyBytes, true);
        
        // Compare the computed hash vs the value in the signature header
        $hashBase64 = base64_encode($hash);

        return $hashBase64 === $signatureHeader;
    }
}

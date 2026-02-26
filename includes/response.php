<?php
/**
 * Send JSON response and terminate script when $exit is true.
 */
function json_response(array $payload, int $statusCode = 200, bool $exit = true): void
{
    // Ensure no accidental output (warnings/whitespace) corrupts JSON.
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);

    if ($exit) {
        exit;
    }
}

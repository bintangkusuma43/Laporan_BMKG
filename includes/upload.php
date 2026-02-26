<?php
/**
 * Handle multiple file uploads and return saved paths.
 */
function handle_uploads(string $fieldName, string $targetDir, array $allowedMimeTypes = [], array $allowedExtensions = []): array
{
    if (empty($_FILES[$fieldName]) || !is_array($_FILES[$fieldName]['name'])) {
        return [];
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $savedFiles = [];

    $finfo = null;
    if ($allowedMimeTypes) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
    }

    foreach ($_FILES[$fieldName]['name'] as $index => $originalName) {
        $tmpName = $_FILES[$fieldName]['tmp_name'][$index] ?? '';
        $error = $_FILES[$fieldName]['error'][$index] ?? UPLOAD_ERR_NO_FILE;

        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($tmpName)) {
            continue;
        }

        if (!is_allowed_upload($tmpName, $originalName, $allowedMimeTypes, $allowedExtensions, $finfo)) {
            continue;
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', strtolower($originalName));
        $uniqueName = uniqid('doc_', true) . '_' . $safeName;
        $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $uniqueName;

        if (move_uploaded_file($tmpName, $destination)) {
            $savedFiles[] = $uniqueName;
        }
    }

    return $savedFiles;
}

/**
 * Handle a single file upload and return saved filename (without folder prefix).
 */
function handle_single_upload(string $fieldName, string $targetDir, array $allowedMimeTypes = [], array $allowedExtensions = []): ?string
{
    if (empty($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    $tmpName = $_FILES[$fieldName]['tmp_name'] ?? '';
    $error = $_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE;
    $originalName = $_FILES[$fieldName]['name'] ?? '';

    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($tmpName)) {
        return null;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $finfo = null;
    if ($allowedMimeTypes) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
    }

    if (!is_allowed_upload($tmpName, $originalName, $allowedMimeTypes, $allowedExtensions, $finfo)) {
        return null;
    }

    $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', strtolower($originalName));
    $uniqueName = uniqid('doc_', true) . '_' . $safeName;
    $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $uniqueName;

    if (!move_uploaded_file($tmpName, $destination)) {
        return null;
    }

    return $uniqueName;
}

function is_allowed_upload(string $tmpName, string $originalName, array $allowedMimeTypes, array $allowedExtensions, ?finfo $finfo): bool
{
    if ($allowedExtensions) {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($ext === '' || !in_array($ext, $allowedExtensions, true)) {
            return false;
        }
    }

    if ($allowedMimeTypes && $finfo) {
        $mime = $finfo->file($tmpName) ?: '';
        if (!in_array($mime, $allowedMimeTypes, true)) {
            return false;
        }
    }

    return true;
}

<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/fs/directories.php'); }

if (!function_exists('deleteDirectory')) {
    function deleteDirectory($directory)
    {
        if (!file_exists($directory)) {
            return true;
        }

        if (!is_dir($directory)) {
            return @unlink($directory);
        }

        $items = scandir($directory);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                if (!deleteDirectory($path)) {
                    return false;
                }
            } else {
                if (!@unlink($path)) {
                    return false;
                }
            }
        }

        return @rmdir($directory);
    }
}


if (!function_exists('copyDirectoryContents')) {
    function copyDirectoryContents($source, $destination)
    {
        if (!is_dir($source)) {
            return false;
        }

        if (!is_dir($destination) && !mkdir($destination, 0777, true) && !is_dir($destination)) {
            return false;
        }

        $items = scandir($source);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $sourcePath = $source . DIRECTORY_SEPARATOR . $item;
            $destinationPath = $destination . DIRECTORY_SEPARATOR . $item;

            if (is_dir($sourcePath)) {
                if (!copyDirectoryContents($sourcePath, $destinationPath)) {
                    return false;
                }
            } else {
                if (!@copy($sourcePath, $destinationPath)) {
                    return false;
                }
            }
        }

        return true;
    }
}


if (!function_exists('deleteFolder')) {
    function deleteFolder($folderPath)
    {
        if (!is_dir($folderPath))
            return false;

        $files = array_diff(scandir($folderPath), ['.', '..']);

        foreach ($files as $file) {
            $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                deleteFolder($filePath);
            } else {
                unlink($filePath);
            }
        }

        return rmdir($folderPath);
    }
}


if (!function_exists('deleteInvoiceFromList')) {
    function deleteInvoiceFromList($invoiceId, $userId)
    {
        global $pdo;
        try {
            $stmt = $pdo->prepare("DELETE FROM invoice WHERE id_invoice = :invoiceId AND id_user = :userId");
            $stmt->bindParam(':invoiceId', $invoiceId);
            $stmt->bindParam(':userId', $userId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Failed to delete invoice: ' . $e->getMessage());
            return false;
        }
    }
}

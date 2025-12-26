<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/media/images_qrcode.php'); }

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

if (!function_exists('addBackgroundImage')) {
    function addBackgroundImage($urlimage, $qrCodeResult, $backgroundPath)
    {
        if (!is_object($qrCodeResult) || !method_exists($qrCodeResult, 'getString')) {
            error_log('Invalid QR code data provided to addBackgroundImage.');
            return false;
        }

        $candidates = [];
        if (is_string($backgroundPath) && $backgroundPath !== '') {
            $candidates[] = $backgroundPath;
            $extension = strtolower(pathinfo($backgroundPath, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg'], true)) {
                $base = substr($backgroundPath, 0, -strlen($extension) - 1);
                $candidates[] = $base . '.jpg';
                $candidates[] = $base . '.jpeg';
            } else {
                $candidates[] = $backgroundPath . '.jpg';
                $candidates[] = $backgroundPath . '.jpeg';
            }
        }

        $resolvedPath = null;
        foreach (array_unique($candidates) as $candidate) {
            $pathsToCheck = [$candidate];
            if ($candidate[0] !== DIRECTORY_SEPARATOR) {
                $pathsToCheck[] = __DIR__ . DIRECTORY_SEPARATOR . ltrim($candidate, DIRECTORY_SEPARATOR);
            }
            foreach ($pathsToCheck as $path) {
                if (is_file($path) && is_readable($path)) {
                    $resolvedPath = $path;
                    break 2;
                }
            }
        }

        if ($resolvedPath === null) {
            error_log("Background image not found for QR code generation: {$backgroundPath}");
            return false;
        }

        $qrCodeImage = @imagecreatefromstring($qrCodeResult->getString());
        if ($qrCodeImage === false) {
            error_log('Unable to create QR code image resource.');
            return false;
        }

        $backgroundData = @file_get_contents($resolvedPath);
        if ($backgroundData === false) {
            imagedestroy($qrCodeImage);
            error_log("Unable to read background image: {$resolvedPath}");
            return false;
        }

        $backgroundImage = @imagecreatefromstring($backgroundData);
        if ($backgroundImage === false) {
            imagedestroy($qrCodeImage);
            error_log("Unable to create background image resource from file: {$resolvedPath}");
            return false;
        }

        $qrCodeWidth = imagesx($qrCodeImage);
        $qrCodeHeight = imagesy($qrCodeImage);
        $backgroundWidth = imagesx($backgroundImage);
        $backgroundHeight = imagesy($backgroundImage);

        $x = ($backgroundWidth - $qrCodeWidth) / 2;
        $y = ($backgroundHeight - $qrCodeHeight) / 2;

        imagecopy($backgroundImage, $qrCodeImage, (int) $x, (int) $y, 0, 0, $qrCodeWidth, $qrCodeHeight);

        $result = imagepng($backgroundImage, $urlimage);

        imagedestroy($qrCodeImage);
        imagedestroy($backgroundImage);

        if ($result === false) {
            error_log("Failed to save QR code with background to {$urlimage}");
        }

        return $result !== false;
    }
}


if (!function_exists('createqrcode')) {
    function createqrcode($contents)
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            data: $contents,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 500,
            margin: 10,
        );

        $result = $builder->build();
        return $result;
    }
}

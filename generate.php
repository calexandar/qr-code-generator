<?php
require_once __DIR__ . '/vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

function buildPayload(array $post): string {
    $type = $post['type'] ?? 'text';

    switch ($type) {
        case 'url':
            return $post['url'] ?? '';

        case 'text':
            return $post['text'] ?? '';

        case 'email':
            $to = $post['email_to'] ?? '';
            $subject = rawurlencode($post['email_subject'] ?? '');
            $body = rawurlencode($post['email_body'] ?? '');
            $query = [];
            if ($subject) $query[] = "subject={$subject}";
            if ($body) $query[] = "body={$body}";
            $queryString = $query ? '?' . implode('&', $query) : '';
            return "mailto:{$to}{$queryString}";

        case 'sms':
            $phone = $post['sms_phone'] ?? '';
            $message = rawurlencode($post['sms_message'] ?? '');
            return $message ? "sms:{$phone}?body={$message}" : "sms:{$phone}";

        case 'vcard':
            $firstname = trim($post['vcard_firstname'] ?? '');
            $lastname = trim($post['vcard_lastname'] ?? '');
            $email = trim($post['vcard_email'] ?? '');
            $phone = trim($post['vcard_phone'] ?? '');
            $org = trim($post['vcard_org'] ?? '');

            $vcard = "BEGIN:VCARD\r\n";
            $vcard .= "VERSION:3.0\r\n";
            if ($firstname || $lastname) {
                $vcard .= "N:{$lastname};{$firstname};;;\r\n";
                $vcard .= "FN:{$firstname} {$lastname}\r\n";
            }
            if ($email) {
                $vcard .= "EMAIL:{$email}\r\n";
            }
            if ($phone) {
                $vcard .= "TEL;TYPE=CELL:{$phone}\r\n";
            }
            if ($org) {
                $vcard .= "ORG:{$org}\r\n";
            }
            $vcard .= "END:VCARD";
            return $vcard;

        case 'event':
            $title = $post['event_title'] ?? '';
            $start = $post['event_start'] ?? '';
            $end = $post['event_end'] ?? '';
            $location = $post['event_location'] ?? '';
            $description = $post['event_description'] ?? '';

            $dtstart = $start ? date('Ymd\THis', strtotime($start)) : date('Ymd\THis');
            $dtend = $end ? date('Ymd\THis', strtotime($end)) : date('Ymd\THis', strtotime('+1 hour'));

            $ical = "BEGIN:VCALENDAR\r\n";
            $ical .= "VERSION:2.0\r\n";
            $ical .= "PRODID:-//QR Code Generator//EN\r\n";
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "DTSTART:{$dtstart}\r\n";
            $ical .= "DTEND:{$dtend}\r\n";
            $ical .= "SUMMARY:{$title}\r\n";
            if ($location) {
                $ical .= "LOCATION:{$location}\r\n";
            }
            if ($description) {
                $ical .= "DESCRIPTION:{$description}\r\n";
            }
            $ical .= "END:VEVENT\r\n";
            $ical .= "END:VCALENDAR";
            return $ical;

        default:
            return '';
    }
}

function getEccLevel(string $ecc): int {
    return match($ecc) {
        'L' => QRCode::ECC_L,
        'M' => QRCode::ECC_M,
        'Q' => QRCode::ECC_Q,
        'H' => QRCode::ECC_H,
        default => QRCode::ECC_M,
    };
}

function generateQRCode(array $data, string $format = 'png', int $size = 5, string $ecc = 'M'): string {
    $payload = buildPayload($data);
    
    if (empty($payload)) {
        return '';
    }

    $options = new QROptions;
    $options->version = QRCode::VERSION_AUTO;
    $options->eccLevel = getEccLevel($ecc);
    $options->outputType = $format === 'svg' ? 'svg' : 'png';
    $options->scale = $size;

    $qrcode = new QRCode($options);
    return $qrcode->render($payload);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'preview') {
    $format = $_POST['format'] ?? 'png';
    $size = isset($_POST['size']) ? (int)$_POST['size'] : 5;
    $ecc = $_POST['ecc'] ?? 'M';

    $qrData = generateQRCode($_POST, $format, $size, $ecc);
    
    header('Content-Type: application/json');
    echo json_encode(['qr' => $qrData]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'download') {
    $format = $_POST['format'] ?? 'png';
    $size = isset($_POST['size']) ? (int)$_POST['size'] : 5;
    $ecc = $_POST['ecc'] ?? 'M';

    $qrData = generateQRCode($_POST, $format, $size, $ecc);
    $filename = "qrcode_" . date('Ymd_His') . ".{$format}";

    switch ($format) {
        case 'svg':
            header('Content-Type: image/svg+xml');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            break;
        case 'png':
        default:
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            break;
    }
    
    echo $qrData;
    exit;
}

$type = $_POST['type'] ?? 'text';
$format = $_POST['format'] ?? 'png';
$size = isset($_POST['size']) ? (int)$_POST['size'] : 5;
$ecc = $_POST['ecc'] ?? 'M';

$payload = buildPayload($_POST);

if (empty($payload)) {
    die('No data provided');
}

$options = new QROptions;
$options->version = QRCode::VERSION_AUTO;
$options->eccLevel = getEccLevel($ecc);
$options->outputType = $format === 'svg' ? 'svg' : 'png';
$options->scale = $size;

$qrcode = new QRCode($options);
$qrImage = $qrcode->render($payload);

$filename = "qrcode_" . date('Ymd_His') . ".{$format}";

switch ($format) {
    case 'svg':
        header('Content-Type: image/svg+xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $qrImage;
        break;

    case 'png':
    default:
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $qrImage;
        break;
}

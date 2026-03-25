<?php session_start();

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

function generateQRCode(string $payload, string $format = 'png', int $size = 5, string $ecc = 'M'): string {
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

function sanitizeValue($value): string {
    if (is_array($value)) {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$type = $_POST['type'] ?? 'text';
$format = $_POST['format'] ?? 'png';
$size = isset($_POST['size']) ? (int)$_POST['size'] : 5;
$ecc = $_POST['ecc'] ?? 'M';

$_SESSION['qr_data'] = $_POST;

$payload = buildPayload($_POST);

if (empty($payload)) {
    header('Location: index.php');
    exit;
}

$qrImage = generateQRCode($payload, $format, $size, $ecc);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Preview</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container preview-container">
        <div class="preview-box">
            <h1 class="preview-title">QR Code Preview</h1>
            
            <div class="qr-preview">
                <?php if ($format === 'svg'): ?>
                    <?php echo $qrImage; ?>
                <?php else: ?>
                    <img src="<?php echo $qrImage; ?>" alt="QR Code">
                <?php endif; ?>
            </div>

            <div class="download-buttons">
                <form method="POST" action="generate.php" target="_blank">
                    <input type="hidden" name="action" value="download">
                    <input type="hidden" name="type" value="<?php echo sanitizeValue($type); ?>">
                    <input type="hidden" name="format" value="png">
                    <input type="hidden" name="size" value="<?php echo $size; ?>">
                    <input type="hidden" name="ecc" value="<?php echo sanitizeValue($ecc); ?>">
                    
                    <?php if ($type === 'url'): ?>
                        <input type="hidden" name="url" value="<?php echo sanitizeValue($_POST['url'] ?? ''); ?>">
                    <?php elseif ($type === 'text'): ?>
                        <input type="hidden" name="text" value="<?php echo sanitizeValue($_POST['text'] ?? ''); ?>">
                    <?php elseif ($type === 'email'): ?>
                        <input type="hidden" name="email_to" value="<?php echo sanitizeValue($_POST['email_to'] ?? ''); ?>">
                        <input type="hidden" name="email_subject" value="<?php echo sanitizeValue($_POST['email_subject'] ?? ''); ?>">
                        <input type="hidden" name="email_body" value="<?php echo sanitizeValue($_POST['email_body'] ?? ''); ?>">
                    <?php elseif ($type === 'sms'): ?>
                        <input type="hidden" name="sms_phone" value="<?php echo sanitizeValue($_POST['sms_phone'] ?? ''); ?>">
                        <input type="hidden" name="sms_message" value="<?php echo sanitizeValue($_POST['sms_message'] ?? ''); ?>">
                    <?php elseif ($type === 'vcard'): ?>
                        <input type="hidden" name="vcard_firstname" value="<?php echo sanitizeValue($_POST['vcard_firstname'] ?? ''); ?>">
                        <input type="hidden" name="vcard_lastname" value="<?php echo sanitizeValue($_POST['vcard_lastname'] ?? ''); ?>">
                        <input type="hidden" name="vcard_email" value="<?php echo sanitizeValue($_POST['vcard_email'] ?? ''); ?>">
                        <input type="hidden" name="vcard_phone" value="<?php echo sanitizeValue($_POST['vcard_phone'] ?? ''); ?>">
                        <input type="hidden" name="vcard_org" value="<?php echo sanitizeValue($_POST['vcard_org'] ?? ''); ?>">
                    <?php elseif ($type === 'event'): ?>
                        <input type="hidden" name="event_title" value="<?php echo sanitizeValue($_POST['event_title'] ?? ''); ?>">
                        <input type="hidden" name="event_start" value="<?php echo sanitizeValue($_POST['event_start'] ?? ''); ?>">
                        <input type="hidden" name="event_end" value="<?php echo sanitizeValue($_POST['event_end'] ?? ''); ?>">
                        <input type="hidden" name="event_location" value="<?php echo sanitizeValue($_POST['event_location'] ?? ''); ?>">
                        <input type="hidden" name="event_description" value="<?php echo sanitizeValue($_POST['event_description'] ?? ''); ?>">
                    <?php endif; ?>
                    
                    <button type="submit" class="btn-download">Download PNG</button>
                </form>

                <form method="POST" action="generate.php" target="_blank">
                    <input type="hidden" name="action" value="download">
                    <input type="hidden" name="type" value="<?php echo sanitizeValue($type); ?>">
                    <input type="hidden" name="format" value="svg">
                    <input type="hidden" name="size" value="<?php echo $size; ?>">
                    <input type="hidden" name="ecc" value="<?php echo sanitizeValue($ecc); ?>">
                    
                    <?php if ($type === 'url'): ?>
                        <input type="hidden" name="url" value="<?php echo sanitizeValue($_POST['url'] ?? ''); ?>">
                    <?php elseif ($type === 'text'): ?>
                        <input type="hidden" name="text" value="<?php echo sanitizeValue($_POST['text'] ?? ''); ?>">
                    <?php elseif ($type === 'email'): ?>
                        <input type="hidden" name="email_to" value="<?php echo sanitizeValue($_POST['email_to'] ?? ''); ?>">
                        <input type="hidden" name="email_subject" value="<?php echo sanitizeValue($_POST['email_subject'] ?? ''); ?>">
                        <input type="hidden" name="email_body" value="<?php echo sanitizeValue($_POST['email_body'] ?? ''); ?>">
                    <?php elseif ($type === 'sms'): ?>
                        <input type="hidden" name="sms_phone" value="<?php echo sanitizeValue($_POST['sms_phone'] ?? ''); ?>">
                        <input type="hidden" name="sms_message" value="<?php echo sanitizeValue($_POST['sms_message'] ?? ''); ?>">
                    <?php elseif ($type === 'vcard'): ?>
                        <input type="hidden" name="vcard_firstname" value="<?php echo sanitizeValue($_POST['vcard_firstname'] ?? ''); ?>">
                        <input type="hidden" name="vcard_lastname" value="<?php echo sanitizeValue($_POST['vcard_lastname'] ?? ''); ?>">
                        <input type="hidden" name="vcard_email" value="<?php echo sanitizeValue($_POST['vcard_email'] ?? ''); ?>">
                        <input type="hidden" name="vcard_phone" value="<?php echo sanitizeValue($_POST['vcard_phone'] ?? ''); ?>">
                        <input type="hidden" name="vcard_org" value="<?php echo sanitizeValue($_POST['vcard_org'] ?? ''); ?>">
                    <?php elseif ($type === 'event'): ?>
                        <input type="hidden" name="event_title" value="<?php echo sanitizeValue($_POST['event_title'] ?? ''); ?>">
                        <input type="hidden" name="event_start" value="<?php echo sanitizeValue($_POST['event_start'] ?? ''); ?>">
                        <input type="hidden" name="event_end" value="<?php echo sanitizeValue($_POST['event_end'] ?? ''); ?>">
                        <input type="hidden" name="event_location" value="<?php echo sanitizeValue($_POST['event_location'] ?? ''); ?>">
                        <input type="hidden" name="event_description" value="<?php echo sanitizeValue($_POST['event_description'] ?? ''); ?>">
                    <?php endif; ?>
                    
                    <button type="submit" class="btn-download">Download SVG</button>
                </form>
            </div>

            <a href="index.php" class="btn-new">Generate New QR Code</a>
        </div>
    </div>
</body>
</html>

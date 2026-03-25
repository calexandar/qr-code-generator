<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>QR Code Generator</h1>
            <p>Generate QR codes for any content type</p>
        </header>

        <form action="preview.php" method="POST" class="qr-form">
            <div class="type-selector">
                <h3>Select Type</h3>
                <div class="type-options">
                    <label class="type-option">
                        <input type="radio" name="type" value="url" checked>
                        <span class="type-label">URL</span>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="type" value="text">
                        <span class="type-label">Text</span>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="type" value="email">
                        <span class="type-label">Email</span>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="type" value="sms">
                        <span class="type-label">SMS</span>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="type" value="vcard">
                        <span class="type-label">vCard</span>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="type" value="event">
                        <span class="type-label">Event</span>
                    </label>
                </div>
            </div>

            <div class="form-sections">
                <div class="form-section section-url active">
                    <h3>URL</h3>
                    <div class="form-group">
                        <label for="url">Website URL</label>
                        <input type="url" name="url" id="url" placeholder="https://example.com" required>
                    </div>
                </div>

                <div class="form-section section-text">
                    <h3>Text</h3>
                    <div class="form-group">
                        <label for="text">Plain Text</label>
                        <textarea name="text" id="text" rows="4" placeholder="Enter your text here"></textarea>
                    </div>
                </div>

                <div class="form-section section-email">
                    <h3>Email</h3>
                    <div class="form-group">
                        <label for="email_to">To (Email Address)</label>
                        <input type="email" name="email_to" id="email_to" placeholder="recipient@example.com">
                    </div>
                    <div class="form-group">
                        <label for="email_subject">Subject</label>
                        <input type="text" name="email_subject" id="email_subject" placeholder="Subject line">
                    </div>
                    <div class="form-group">
                        <label for="email_body">Body</label>
                        <textarea name="email_body" id="email_body" rows="3" placeholder="Email body"></textarea>
                    </div>
                </div>

                <div class="form-section section-sms">
                    <h3>SMS</h3>
                    <div class="form-group">
                        <label for="sms_phone">Phone Number</label>
                        <input type="tel" name="sms_phone" id="sms_phone" placeholder="+1234567890">
                    </div>
                    <div class="form-group">
                        <label for="sms_message">Message</label>
                        <textarea name="sms_message" id="sms_message" rows="3" placeholder="SMS message"></textarea>
                    </div>
                </div>

                <div class="form-section section-vcard">
                    <h3>vCard (Contact)</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vcard_firstname">First Name</label>
                            <input type="text" name="vcard_firstname" id="vcard_firstname" placeholder="John">
                        </div>
                        <div class="form-group">
                            <label for="vcard_lastname">Last Name</label>
                            <input type="text" name="vcard_lastname" id="vcard_lastname" placeholder="Doe">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="vcard_email">Email</label>
                        <input type="email" name="vcard_email" id="vcard_email" placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label for="vcard_phone">Phone</label>
                        <input type="tel" name="vcard_phone" id="vcard_phone" placeholder="+1234567890">
                    </div>
                    <div class="form-group">
                        <label for="vcard_org">Organization (Optional)</label>
                        <input type="text" name="vcard_org" id="vcard_org" placeholder="Company Name">
                    </div>
                </div>

                <div class="form-section section-event">
                    <h3>Event</h3>
                    <div class="form-group">
                        <label for="event_title">Event Title</label>
                        <input type="text" name="event_title" id="event_title" placeholder="Meeting">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_start">Start Date/Time</label>
                            <input type="datetime-local" name="event_start" id="event_start">
                        </div>
                        <div class="form-group">
                            <label for="event_end">End Date/Time</label>
                            <input type="datetime-local" name="event_end" id="event_end">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="event_location">Location</label>
                        <input type="text" name="event_location" id="event_location" placeholder="Conference Room">
                    </div>
                    <div class="form-group">
                        <label for="event_description">Description</label>
                        <textarea name="event_description" id="event_description" rows="2" placeholder="Event description"></textarea>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h3>Settings</h3>
                <div class="settings-row">
                    <div class="setting-group">
                        <label for="size">Size</label>
                        <select name="size" id="size">
                            <option value="3">Small</option>
                            <option value="5" selected>Medium</option>
                            <option value="8">Large</option>
                            <option value="10">Extra Large</option>
                        </select>
                    </div>
                    <div class="setting-group">
                        <label for="ecc">Error Correction</label>
                        <select name="ecc" id="ecc">
                            <option value="L">Low (L) - 7%</option>
                            <option value="M" selected>Medium (M) - 15%</option>
                            <option value="Q">Quartile (Q) - 25%</option>
                            <option value="H">High (H) - 30%</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="format-section">
                <h3>Download Format</h3>
                <div class="format-options">
                    <label class="format-option">
                        <input type="radio" name="format" value="png" checked>
                        <span>PNG</span>
                    </label>
                    <label class="format-option">
                        <input type="radio" name="format" value="svg">
                        <span>SVG</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-generate">Generate QR Code</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('input[name="type"]');
            const sections = document.querySelectorAll('.form-section');

            function showSection(type) {
                sections.forEach(section => {
                    section.classList.remove('active');
                });
                const activeSection = document.querySelector('.section-' + type);
                if (activeSection) {
                    activeSection.classList.add('active');
                }
            }

            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    showSection(this.value);
                });
            });

            showSection('url');
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Deletion Instructions - ShortSight</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }
        h1 {
            color: #2563eb;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        h2 {
            color: #374151;
            margin-top: 30px;
        }
        .contact-info {
            background-color: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .steps {
            background-color: #f8fafc;
            padding: 20px;
            border-left: 4px solid #2563eb;
            margin: 20px 0;
        }
        .step-number {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            margin-right: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Data Deletion Instructions</h1>

    <p>At ShortSight, we respect your privacy and give you control over your data. This page explains how to request deletion of your account and associated data.</p>

    <h2>What Data Will Be Deleted</h2>
    <p>When you request account deletion, we will permanently delete:</p>
    <ul>
        <li>Your account information (name, email, registration date)</li>
        <li>All shortened URLs you created</li>
        <li>All analytics and click tracking data associated with your links</li>
        <li>Social media login connections (Google, Facebook)</li>
    </ul>

    <p><strong>Note:</strong> Once deleted, this data cannot be recovered. Shortened URLs will stop working immediately.</p>

    <h2>How to Request Data Deletion</h2>

    <div class="steps">
        <h3><span class="step-number">1</span>Log into your account</h3>
        <p>Visit <a href="{{ url('/') }}">ShortSight</a> and log in to your account.</p>

        <h3><span class="step-number">2</span>Access account settings</h3>
        <p>Navigate to your account dashboard and look for account settings or profile options.</p>

        <h3><span class="step-number">3</span>Request deletion</h3>
        <p>Use the "Delete Account" or "Request Data Deletion" option. You may be asked to confirm your identity.</p>

        <h3><span class="step-number">4</span>Confirmation</h3>
        <p>You will receive an email confirmation when your account and data have been permanently deleted.</p>
    </div>

    <h2>Alternative Contact Method</h2>
    <p>If you're unable to access your account or need assistance, you can also request data deletion by contacting our support team.</p>

    <div class="contact-info">
        <h3>Contact Information</h3>
        <p><strong>Email:</strong> support@shortsight.com</p>
        <p><strong>Subject:</strong> Data Deletion Request</p>
        <p>Please include your registered email address and any relevant account details to help us process your request quickly.</p>
    </div>

    <h2>Processing Time</h2>
    <p>We typically process data deletion requests within 30 days of receiving your request. You will receive confirmation via email once the deletion is complete.</p>

    <h2>GDPR Data Portability</h2>
    <p>Before requesting deletion, you may want to export your data first. If you're logged in, you can use our <a href="{{ url('/api/user/data-export/download') }}">data export feature</a> to download all your data in a structured format.</p>

    <p><a href="{{ url('/privacy-policy') }}">← Back to Privacy Policy</a></p>
</body>
</html>

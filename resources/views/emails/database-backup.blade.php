<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance App Database Backup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #6750A4, #7D5260);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .body {
            padding: 30px;
            color: #333;
            line-height: 1.6;
        }
        .filename {
            background: #F3EDF7;
            border-radius: 8px;
            padding: 12px 16px;
            font-family: Consolas, monospace;
            font-weight: 600;
            color: #21005D;
            margin: 12px 0;
        }
        .meta {
            background: #f9f9f9;
            border-left: 4px solid #6750A4;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            color: #555;
            margin: 16px 0;
        }
        .footer {
            text-align: center;
            padding: 16px;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Database Backup</h1>
            <p>Finance App</p>
        </div>
        <div class="body">
            <p>Hello,</p>
            <p>Please find attached your Finance App database backup:</p>
            <div class="filename">{{ $filename }}</div>
            <p>This <strong>.sql</strong> file contains the complete database structure and data. You can restore it into another environment using phpMyAdmin, the MySQL command line, or any SQL import tool.</p>
            <div class="meta">
                <strong>Generated:</strong> {{ now()->format('Y-m-d H:i:s') }}<br>
                <strong>File size:</strong> {{ number_format(strlen($dump) / 1024, 2) }} KB
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Finance App. This is an automated email.
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #374151; line-height: 1.6; }
        .header { background: linear-gradient(to right, #CC0000, #990000); color: white; padding: 20px; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 12px; }
        th { background: #CC0000; color: white; padding: 8px 12px; text-align: left; }
        td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        tr:nth-child(even) { background-color: #fafafa; }
        h1, h2, h3 { color: #111827; }
        .footer { padding: 15px; text-align: center; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Banque Populaire — Prompt Manager</h2>
        <p>{{ $promptTitle }}</p>
    </div>
    <div class="content">
        {!! $htmlContent !!}
    </div>
    <div class="footer">
        Généré automatiquement par la plateforme Prompt Manager
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { 
            margin: 10px; 
            size: A3 landscape;
        }
        body { 
            font-family: sans-serif; 
            font-size: 7px; 
            color: #374151; 
        }
        .header { 
            background: #CC0000; 
            color: white; 
            padding: 12px; 
            margin-bottom: 12px; 
        }
        h1, h2, h3 { color: #111827; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 8px 0;
            table-layout: fixed;
        }
        th { 
            background: #CC0000; 
            color: white; 
            padding: 4px; 
            text-align: left; 
            font-size: 6.5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        td { 
            padding: 4px; 
            border-bottom: 1px solid #eee; 
            font-size: 6.5px; 
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        tr:nth-child(even) { background: #fafafa; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Banque Populaire — {{ $promptTitle }}</h2>
    </div>
    {!! $htmlContent !!}
</body>
</html>
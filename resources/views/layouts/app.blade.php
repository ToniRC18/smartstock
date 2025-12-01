{{-- Layout base para SmartStock con Tailwind CDN y sección de contenido --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStock</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ss-emerald': '#22c55e',
                        'ss-emerald-light': '#86efac',
                        'ss-dark': '#0f172a',
                        'ss-gray': '#1f2937',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <div class="min-h-screen flex flex-col">
        @yield('content')
    </div>
</body>
</html>

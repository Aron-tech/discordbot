<!DOCTYPE html>
<html lang="hu" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DutyManager 2.0 - Discord Bot')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        discord: '#5865F2',
                        'discord-dark': '#4752C4',
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-slate-100 min-h-screen">

<!-- Navigation -->
<x-navigation />

<!-- Main Content -->
<main class="@yield('main-class', 'pt-20')">
    @yield('content')
</main>

<x-footer />

@stack('scripts')

<script>
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Header background opacity on scroll
    window.addEventListener('scroll', () => {
        const header = document.querySelector('nav');
        if (window.scrollY > 100) {
            header.classList.add('bg-slate-900/98');
            header.classList.remove('bg-slate-900/95');
        } else {
            header.classList.add('bg-slate-900/95');
            header.classList.remove('bg-slate-900/98');
        }
    });

    @stack('inline-scripts')
</script>
</body>
</html>

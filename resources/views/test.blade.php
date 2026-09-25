<!DOCTYPE html>
<html>
<head>
    <title>Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="bg-red-500 text-white p-4">
        If you see this red box with white text, Tailwind is working!
    </div>
    <h1 class="text-3xl font-bold bg-blue-500 text-white p-4 mt-4">
        If you see this blue box, it's working!
    </h1>
    <div class="bg-[#ff0000] text-[#00ff00] p-4 mt-4">
        If you see this red box with green text, arbitrary values work!
    </div>
</body>
</html>
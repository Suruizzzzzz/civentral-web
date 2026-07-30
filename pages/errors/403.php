<?php
if (!headers_sent()) {
    http_response_code(403);
}
$basePath = $basePath ?? '../../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 Forbidden - Civentral</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <style type="text/tailwindcss">
    @theme {
      --color-brand-light: #EEF5FF;
      --color-brand-border: #B4D4FF;
      --color-brand-medium: #86B6F6;
      --color-brand-dark: #176B87;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800 min-h-screen flex items-center justify-center p-6">

  <div class="max-w-md w-full bg-white border border-slate-200/80 rounded-3xl p-8 shadow-xl text-center space-y-6">
    <div class="h-20 w-20 rounded-2xl bg-rose-50 border border-rose-100 mx-auto flex items-center justify-center text-rose-500 shadow-sm">
      <i class="fa-solid fa-shield-cat text-4xl"></i>
    </div>

    <div class="space-y-2">
      <span class="inline-block px-3 py-1 bg-rose-100/60 text-rose-700 font-extrabold text-[10px] uppercase tracking-widest rounded-full">
        Error 403 • Access Denied
      </span>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight">403 Forbidden</h1>
      <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
        You do not have the required role permissions or security clearance to access this module page or resource.
      </p>
    </div>

    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-3">
      <a href="<?php echo htmlspecialchars($basePath); ?>pages/dashboard.php" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-bold px-4 py-3 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2">
        <i class="fa-solid fa-house text-xs"></i>
        <span>Back to Dashboard</span>
      </a>
      <button onclick="window.history.back()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-3 rounded-xl text-xs transition border border-slate-200 flex items-center justify-center gap-2 cursor-pointer">
        <i class="fa-solid fa-arrow-left text-xs"></i>
        <span>Previous Page</span>
      </button>
    </div>
  </div>

</body>
</html>

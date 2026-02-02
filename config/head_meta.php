<?php
/**
 * Meta tags comunes para todos los archivos
 * Incluir este archivo en el <head> de cada página
 */
?>
<!-- Meta tags básicos -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<!-- Soporte para emojis en todos los dispositivos -->
<meta name="format-detection" content="telephone=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#667eea">

<!-- Preconnect para mejor rendimiento -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="<?php echo isset($base_path) ? $base_path : ''; ?>assets/favicon.svg">
<link rel="alternate icon" href="<?php echo isset($base_path) ? $base_path : ''; ?>assets/favicon.ico">

<!-- Estilos -->
<link rel="stylesheet" href="<?php echo isset($base_path) ? $base_path : ''; ?>assets/css/style.css">

<!-- Soporte para emojis - CSS inline para mejor compatibilidad -->
<style>
    /* Soporte universal para emojis */
    body, html {
        font-family: -apple-system, BlinkMacSystemFont, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", "Android Emoji", "EmojiSymbols", "EmojiOne Mozilla", "Twemoji Mozilla", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    
    /* Asegurar que los emojis se rendericen correctamente */
    .emoji, 
    [class*="emoji"],
    h1, h2, h3, h4, h5, h6,
    .logo-title,
    .stat-card-icon,
    .quick-action-icon,
    .action-icon,
    .bank-icon,
    .upload-icon {
        font-family: "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", "Android Emoji", "EmojiSymbols", "EmojiOne Mozilla", "Twemoji Mozilla", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-variant-emoji: emoji;
        -webkit-font-feature-settings: "liga", "kern";
        font-feature-settings: "liga", "kern";
        text-rendering: optimizeLegibility;
        font-style: normal;
    }
    
    /* Mejorar renderizado de emojis en iOS */
    @supports (-webkit-touch-callout: none) {
        .emoji, 
        [class*="emoji"],
        h1, h2, h3, h4, h5, h6,
        .logo-title span {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    }
</style>

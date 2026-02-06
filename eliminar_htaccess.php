<?php
// Eliminar .htaccess temporalmente
if (file_exists('.htaccess')) {
    if (unlink('.htaccess')) {
        echo "OK: .htaccess eliminado. Recarga la página ahora.";
    } else {
        echo "ERROR: No se pudo eliminar .htaccess. Elimínalo manualmente.";
    }
} else {
    echo "INFO: .htaccess no existe.";
}
?>

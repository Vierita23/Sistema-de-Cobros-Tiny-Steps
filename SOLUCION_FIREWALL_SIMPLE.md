# Solución Simple para el Firewall

## Si Node.js NO está instalado (solo tienes el instalador):

### Opción 1: Instalar Node.js
1. Ve a tu carpeta de Descargas
2. Busca el archivo de instalación de Node.js (ejemplo: `node-v25.5.0-x64.msi`)
3. Haz doble clic para instalarlo
4. Sigue el asistente de instalación
5. Reinicia la terminal después de instalar

### Opción 2: Desactivar temporalmente el firewall (SOLO PARA PROBAR)
1. En la ventana del Firewall
2. Haz clic en: **"Activar o desactivar el Firewall de Windows Defender"**
3. Desactiva temporalmente para "Redes públicas"
4. Prueba el túnel de LocalTunnel
5. **IMPORTANTE:** Vuelve a activarlo después

## Si Node.js SÍ está instalado pero no aparece en el firewall:

### Cómo encontrar "Permitir otra aplicación":
1. En la ventana del Firewall que tienes abierta
2. Mira la lista de la izquierda
3. Haz clic en: **"Permitir que una aplicación o una característica a través de Firewall de Windows Defender"**
4. Se abrirá una nueva ventana
5. En esa nueva ventana, arriba a la derecha, haz clic en: **"Cambiar configuración"**
6. Ahora verás un botón abajo que dice: **"Permitir otra aplicación..."**
7. Haz clic ahí
8. Luego haz clic en **"Examinar..."**
9. Navega a: `C:\Program Files\nodejs\`
10. Selecciona `node.exe`

## Alternativa: Usar el link que ya funcionó
Si ya obtuviste un link de LocalTunnel que funcionó (como `vast-clubs-sip.loca.lt`), simplemente úsalo mientras la terminal esté abierta. No necesitas configurar el firewall si el túnel ya está funcionando.

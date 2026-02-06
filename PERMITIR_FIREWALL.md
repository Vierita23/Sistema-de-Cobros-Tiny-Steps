# Cómo Permitir Node.js en el Firewall de Windows

## Pasos Detallados:

### Paso 1: Ir a Permitir Aplicaciones
1. En la ventana del Firewall que tienes abierta
2. Haz clic en: **"Permitir que una aplicación o una característica a través de Firewall de Windows Defender"**
   (Está en la lista de la izquierda, segunda opción)

### Paso 2: Buscar Node.js
1. Se abrirá una nueva ventana con una lista de aplicaciones
2. Busca en la lista: **"Node.js"** o **"Node.js JavaScript Runtime"**
3. Si lo encuentras:
   - Marca las casillas para "Privada" y "Pública"
   - Haz clic en "Aceptar"

### Paso 3: Si NO encuentras Node.js
1. Haz clic en el botón **"Cambiar configuración"** (arriba a la derecha)
2. Haz clic en **"Permitir otra aplicación..."** (abajo)
3. Haz clic en **"Examinar..."**
4. Navega a: `C:\Program Files\nodejs\` o `C:\Users\Jesus\AppData\Roaming\npm\`
5. Busca el archivo: **node.exe**
6. Selecciónalo y haz clic en "Abrir"
7. Haz clic en "Agregar"
8. Marca las casillas "Privada" y "Pública"
9. Haz clic en "Aceptar"

### Paso 4: Reiniciar el Túnel
Después de permitir Node.js, reinicia LocalTunnel:
```bash
lt --port 80
```

## Alternativa Rápida (Temporal)
Si necesitas algo rápido, puedes desactivar temporalmente el firewall solo para probar:
1. Haz clic en: **"Activar o desactivar el Firewall de Windows Defender"**
2. Desactiva temporalmente para "Redes públicas"
3. Prueba el túnel
4. **IMPORTANTE:** Vuelve a activarlo después

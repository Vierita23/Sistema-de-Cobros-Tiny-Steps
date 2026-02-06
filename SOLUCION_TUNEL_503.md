# Solución Error 503 - Tunnel Unavailable

## 🔴 Problema
El túnel de LocalTunnel se desconectó o no está corriendo.

## ✅ Solución Rápida

### Paso 1: Verificar la Terminal
1. Ve a la terminal donde ejecutaste `lt --port 80`
2. Verifica si el proceso sigue corriendo
3. Si ves algún error, el túnel se desconectó

### Paso 2: Reiniciar el Túnel
1. Si el túnel se detuvo, ejecuta de nuevo:
   ```bash
   lt --port 80
   ```
2. Te dará un nuevo link (puede ser diferente)
3. Usa ese nuevo link para acceder

### Paso 3: Si Apache usa otro puerto
Si XAMPP usa el puerto 8080, usa:
```bash
lt --port 8080
```

## 🔧 Solución Permanente

### Opción 1: Usar un subdominio fijo
```bash
lt --port 80 --subdomain tinysteps
```
Esto intentará usar: `https://tinysteps.loca.lt`

### Opción 2: Verificar que Apache esté corriendo
1. Abre XAMPP Control Panel
2. Verifica que Apache esté en verde (corriendo)
3. Si no está corriendo, inícialo

### Opción 3: Verificar firewall
El error puede ser por el firewall de Windows bloqueando la conexión.

## 📝 Checklist

- [ ] Apache está corriendo en XAMPP
- [ ] La terminal con LocalTunnel está abierta
- [ ] No hay errores en la terminal
- [ ] El puerto es correcto (80 o 8080)

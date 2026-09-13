# 📅 INTEGRACIÓN GOOGLE CALENDAR - GUÍA DE CONFIGURACIÓN

## 🎯 ¿Qué hace esta integración?

Esta integración permite que **cada usuario del CRM pueda conectar su agenda personal de Google** con el sistema Tony CRM para:

✅ **Sincronización bidireccional**: Eventos creados en el CRM aparecen en Google Calendar y viceversa  
✅ **Agenda unificada**: Ver todas las citas en un solo lugar  
✅ **Notificaciones**: Recibir recordatorios en el celular a través de Google  
✅ **Acceso móvil**: Ver la agenda desde cualquier dispositivo  
✅ **Backup automático**: Los eventos se respaldan en la nube de Google  

## ⚡ CONFIGURACIÓN RÁPIDA

### 1. Crear proyecto en Google Cloud Console

1. Ve a: https://console.cloud.google.com/
2. Crea un **nuevo proyecto** o selecciona uno existente
3. En el nombre pon: `Tony CRM Calendar Integration`

### 2. Activar Google Calendar API

1. En el menú lateral, ve a: **APIs y servicios > Biblioteca**
2. Busca: `Google Calendar API`
3. Click en **HABILITAR**

### 3. Crear credenciales OAuth 2.0

1. Ve a: **APIs y servicios > Credenciales**
2. Click en **+ CREAR CREDENCIALES**
3. Selecciona: **ID de cliente de OAuth 2.0**
4. Tipo de aplicación: **Aplicación web**
5. Nombre: `Tony CRM`
6. **URIs de redireccionamiento autorizados**:
   ```
   http://tony.test/agenda/google/callback
   http://localhost:8080/agenda/google/callback
   ```

### 4. Configurar variables de entorno

1. Copia el archivo de ejemplo:
   ```bash
   copy .env.google.example .env.local
   ```

2. Edita el archivo `.env` y agrega estas líneas:
   ```
   GOOGLE_CLIENT_ID=tu_client_id_aqui.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=tu_client_secret_aqui
   ```

### 5. ¡Listo para usar! 🎉

## 🚀 CÓMO USAR LA INTEGRACIÓN

### Para cada usuario:

1. **Conectar Google Calendar**:
   - Ir a `/agenda`
   - Click en "Conectt_cat_clientesar Google Calendar"
   - Autorizar el acceso
   - ¡Listo!

2. **Crear eventos**:
   - Los eventos creados en Tony CRM aparecerán automáticamente en Google Calendar
   - Los eventos de Google Calendar se pueden importar con el botón "Sincronizar"

3. **Indicadores visuales**:
   - 🔄 = Evento sincronizado con Google
   - ⚠️ = Evento solo local

## 🔧 FUNCIONALIDADES TÉCNICAS

### Sincronización automática:
- **Al crear actividad**: Se sincroniza automáticamente a Google
- **Al actualizar actividad**: Se actualiza en Google también
- **Al importar**: Eventos de Google se importan al CRM

### Estados de color en Google Calendar:
- **🔵 Azul**: Citas pendientes
- **🟢 Verde**: Citas completadas  
- **⚪ Gris**: Citas canceladas

### Campos sincronizados:
- **Título**: Nombre de la actividad
- **Descripción**: Detalles + "[Creado desde Tony CRM]"
- **Fecha/hora**: Inicio y fin
- **Cliente**: Se incluye en el título si existe

## 🛡️ SEGURIDAD Y PRIVACIDAD

- **Cada usuario conecta su propia cuenta de Google**
- **Los tokens se almacenan encriptados en la base de datos**
- **Solo se sincronizan eventos del CRM, no todos los de Google**
- **Se pueden desconectar en cualquier momento**

## ⚠️ TROUBLESHOOTING

### Error: "The OAuth client was not found"
- Verificar que el GOOGLE_CLIENT_ID esté correcto
- Verificar que la URL de callback esté autorizada

### Error: "Calendar API has not been used"
- Activar Google Calendar API en Google Cloud Console

### Error: "Access blocked: This app's request is invalid"
- Configurar pantalla de consentimiento OAuth
- Agregar usuarios de prueba si está en modo desarrollo

### No se sincronizan eventos:
- Verificar que el usuario esté conectado (icono verde)
- Revisar logs en `writable/logs/` para errores específicos

## 📱 PRÓXIMAS MEJORAS

- [ ] Sincronización en tiempo real con webhooks
- [ ] Soporte para calendarios específicos (no solo el principal)
- [ ] Invitaciones automáticas a clientes
- [ ] Sincronización de contactos
- [ ] Notificaciones push personalizadas

---

**¿Necesitas ayuda?** Revisa los logs en `writable/logs/log-YYYY-MM-DD.php` para ver errores específicos.
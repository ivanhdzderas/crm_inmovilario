# Sistema de Matching: Inmuebles - Prospectos

## Descripción General

El sistema de matching conecta automáticamente inmuebles disponibles con prospectos que cumplen criterios específicos de compatibilidad.

## Ubicación de la Funcionalidad

**Botón "Prospectos Interesados"** aparece en:
- **Vista:** `app/Views/admin/real_estates/index.php`
- **Tabla:** Gestión de Inmuebles
- **Condición:** Solo visible para inmuebles con estado "Disponible" o "Nuevo"

## Criterios de Matching

El sistema aplica tres criterios principales para encontrar prospectos compatibles:

### 1. Zona Geográfica
- **Campo inmueble:** `zone_id`
- **Campo prospecto:** `zone_id`
- **Criterio:** Coincidencia exacta de zona de interés

### 2. Capacidad de Crédito
- **Campo inmueble:** `price`
- **Campo prospecto:** `credit_amount`
- **Criterio:** El crédito del prospecto debe estar dentro del rango:
  - **Mínimo:** 80% del precio del inmueble
  - **Máximo:** 120% del precio del inmueble
- **Ejemplo:** Para un inmueble de $13,000:
  - Crédito mínimo aceptado: $10,400
  - Crédito máximo aceptado: $15,600

### 3. Método de Pago
- **Campo inmueble:** `payment_methods` (JSON array de IDs)
- **Campo prospecto:** `payment_method_id`
- **Criterio:** El método de pago del prospecto debe estar en la lista de métodos aceptados por el inmueble

### 4. Estado del Prospecto
- Solo se consideran prospectos con estado **"activo"**

## Arquitectura Técnica

### Backend
**Controlador:** `app/Controllers/Admin/RealEstatesController.php`

**Método:** `getMatchingProspects($id)`
- **Tipo:** GET
- **Retorna:** JSON con información del inmueble y prospectos coincidentes
- **Query:** Utiliza Query Builder con JOINS a tablas `zones` y `users`

### Frontend
**Componentes:**

1. **Botón de Acción**
   ```php
   <button onclick="showMatchingProspects(<?= $inmueble['id'] ?>, '<?= esc($inmueble['clave']) ?>')">
       Prospectos Interesados
   </button>
   ```

2. **Modal Bootstrap 4**
   - ID: `matchingProspectsModal`
   - Tamaño: Extra Large (modal-xl)
   - Incluye loading spinner durante la búsqueda

3. **Función JavaScript**
   - `showMatchingProspects(realEstateId, clave)`
   - Realiza petición AJAX
   - Formatea y muestra resultados en tabla dinámica

### Ruta
```php
$routes->get('real_estates/getMatchingProspects/(:num)', 'Admin\RealEstatesController::getMatchingProspects/$1');
```

## Flujo de Uso

1. Usuario accede a **Admin → Gestión de Inmuebles**
2. Identifica un inmueble disponible (estado: "Disponible" o "Nuevo")
3. Click en botón **"Prospectos Interesados"**
4. Sistema busca automáticamente prospectos compatibles
5. Modal muestra resultados con:
   - Total de prospectos encontrados
   - Tabla con datos de cada prospecto
   - Botón para ver perfil completo del prospecto

## Información Mostrada

Por cada prospecto coincidente:
- ✅ Nombre completo
- ✅ Teléfono (con link para llamar)
- ✅ Email (con link para enviar correo)
- ✅ Monto de crédito (formateado como MXN)
- ✅ Zona de interés
- ✅ Asesor asignado
- ✅ Botón "Ver" para acceder al perfil completo

## Ejemplo de Uso

### Caso: Inmueble en Zona Centro de $13,000

**Inmueble:**
- Zona: Centro
- Precio: $13,000 MXN
- Métodos de pago: [Contado, Crédito Bancario]

**Prospectos que aparecerán:**
- ✅ Zona de interés: Centro
- ✅ Crédito entre $10,400 y $15,600
- ✅ Método de pago: Contado O Crédito Bancario
- ✅ Estado: Activo

**Prospectos que NO aparecerán:**
- ❌ Interesados en otras zonas
- ❌ Crédito menor a $10,400 o mayor a $15,600
- ❌ Método de pago no compatible (ej: Solo INFONAVIT)
- ❌ Estado inactivo o descartado

## Mensajes del Sistema

### Éxito
- "Total de prospectos encontrados: X"
- Tabla con listado completo

### Sin Resultados
- "No se encontraron prospectos que coincidan con este inmueble"

### Error
- "Error al cargar los prospectos. Por favor intente nuevamente"

## Modelos Relacionados

- **RealEstateModel** - Inmuebles
- **ClientModel** - Prospectos/Clientes
- **ZoneModel** - Catálogo de zonas

## Consideraciones

- El rango de crédito del ±20% es configurable en el método `getMatchingProspects()`
- Solo inmuebles con estados específicos muestran el botón
- La búsqueda considera múltiples métodos de pago por inmueble
- Los resultados se ordenan por fecha de creación del prospecto (más recientes primero)

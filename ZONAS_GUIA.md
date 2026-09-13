# 📍 Catálogo de Zonas - Guía de Acceso

## 🔍 ¿Dónde encontrar el Catálogo de Zonas?

### **Opción 1: Menú Principal (Sidebar)**
1. En el menú lateral izquierdo
2. Sección **"ADMINISTRACIÓN"** 
3. Clic en **"Zonas"** (con badge "NEW")
4. **Ruta:** `Sidebar → Administración → Zonas`

### **Opción 2: Dashboard** 
1. Ir al **Dashboard** principal
2. Sección **"Acciones Rápidas"**
3. Clic en **"Catálogo de Zonas"** (solo administradores)
4. **Ruta:** `Dashboard → Acciones Rápidas → Catálogo de Zonas`

### **Opción 3: URL Directa**
- Navegar directamente a: `/admin/zones`

---

## 👥 Permisos de Acceso

| Rol | Acceso |
|-----|--------|
| **Administrador** (`role_id = 1`) | ✅ Completo (crear, editar, eliminar) |
| **Super Usuario** | ✅ Completo (crear, editar, eliminar) |
| **Asesor Regular** | ❌ No tiene acceso |

---

## 🚀 Funcionalidades Disponibles

### **📋 Lista de Zonas**
- Ver todas las zonas registradas
- Contador de clientes por zona
- Búsqueda y filtrado
- Ordenamiento por columnas

### **➕ Crear Nueva Zona**
- Formulario intuitivo
- Validación en tiempo real
- Prevención de nombres duplicados

### **✏️ Editar Zona**
- Modificar nombre de zona existente
- Preservar referencias de clientes
- Validación de unicidad

### **🗑️ Eliminar Zona**
- Verificación de dependencias
- Confirmación con SweetAlert
- Protección ante eliminación accidental

---

## 🎯 Ejemplos de Uso

### **Zonas Comunes:**
- Norte, Sur, Este, Oeste
- Centro, Periferia
- Zona Industrial, Residencial
- Por colonias: Polanco, Condesa, Roma
- Por municipios: Tlalnepantla, Naucalpan
- Por códigos postales: CP-01000, CP-02000

### **Casos de Uso:**
1. **Organización Territorial:** Dividir la ciudad en zonas de trabajo
2. **Asignación de Asesores:** Cada asesor maneja ciertas zonas
3. **Reportes Geográficos:** Análisis de ventas por zona
4. **Filtrado de Inmuebles:** Buscar propiedades por zona

---

## ⚠️ Notas Importantes

- **Solo Administradores** pueden gestionar zonas
- **No se pueden eliminar** zonas que tengan clientes asignados
- **Nombres únicos** - no se permiten duplicados
- **Integración automática** - las zonas aparecen en formularios de prospectos

---

## 🔄 Integración con el Sistema

Las zonas se utilizan automáticamente en:
- ✅ **Formulario de Prospectos** (campo obligatorio)
- ✅ **Listado de Prospectos** (columna de zona)
- ✅ **Filtros de búsqueda** (buscar por zona)
- ✅ **Reportes y estadísticas** (análisis por zona)

---

*Creado: <?= date('d/m/Y H:i') ?>*
*Sistema: Tony CRM - Módulo de Zonas*
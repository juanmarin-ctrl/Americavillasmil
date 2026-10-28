# AV Studio Maestro — Un solo clic

## Qué hace el botón

1. Sube **todas las fotos** a Medios  
2. Crea **plantillas Elementor**  
3. Crea **todas las páginas** ya diseñadas (Inicio, Cursos, Contacto, Sobre nosotros, Servicios, Legales, Blog)  
4. Crea **menú principal + menú legal** y los asigna  
5. Pone **Inicio** como portada y **Blog** como entradas  
6. Publica **3 posts** de ejemplo  
7. Aplica el **CSS** de marca  
8. Configura **Header/Footer** (Theme Builder si hay Elementor Pro; si no, los embebe en las páginas)

---

## Cómo usarlo

1. Instala y activa **Elementor** (Pro recomendado)  
2. Tema recomendado: **Hello Elementor**  
3. **Plugins → Subir** → `av-studio-importer.zip` (**versión 2.6.8**)  
4. Activa **AV Studio Maestro**  
5. Menú lateral: **AV Studio**  
6. Pulsa **REPARAR / INSTALAR SITIO COMPLETO**  
7. Espera 1–2 minutos y lee el **Log de instalación** en esa misma pantalla  
8. Abre el sitio (Ctrl+F5)

### Si ya tenías una versión anterior

**Sube el ZIP 2.6.8** (reemplaza el plugin). El blog personalizado aplica al activar (sin reimportar).  
Para categorías en posts de ejemplo, sí pulsa **REPARAR** otra vez.

1. Plugins → **AV Studio Maestro → Desactivar → Eliminar** (no borra páginas/medios)  
2. Plugins → Subir → `av-studio-importer.zip` **2.6.8** → Activar  
3. Ctrl+F5 en el sitio (y opcional: AV Studio → REPARAR)

Qué cambia la **2.6.8**:

- **Blog:** plantilla AV (hero + grid de tarjetas) en lugar del listado nativo de WordPress  
- Entradas individuales con el mismo look; categorías en posts de ejemplo al reparar

El log también queda en `wp-content/uploads/avsi-import-log.txt`.  
Si una página dice `FALLBACK-generic` o `verify_els=0`, copia el log y envíalo.  
En el log verás `Pro: Header #… / Footer #… → Entire Site` cuando el header/footer quedan activos.

Listo. Para editar: Páginas → Editar con Elementor.

---

## Archivo (elige uno) — versión 2.6.8

- Fácil: `C:\Users\jagmr\Downloads\av-studio-importer.zip`
- Proyecto: `wordpress-elementor/av-studio-importer.zip`

Sube el **.zip completo**, sin descomprimir.

---

## Si sale «El archivo del plugin no existe»

1. Borra el plugin si aparece a medias: Plugins → AV Studio Maestro → Eliminar  
2. Sube de nuevo **solo** `av-studio-importer.zip` (no la carpeta suelta)  
3. Si Hostinger corta la subida: File Manager → `wp-content/plugins/` → Subir el ZIP → Extraer ahí → Activar en WordPress  
4. Dentro debe quedar: `wp-content/plugins/av-studio-importer/av-studio-importer.php`

# WP-Visor Wordpress (Project: 04-MAY-2026)

## 📋 Contexto y Propósito
Este proyecto nace como una respuesta de emergencia y una herramienta de auditoría técnica el **4 de mayo de 2026**. 

Actualmente, varios sitios basados en WordPress bajo mi administración están experimentando ataques persistentes de fuerza bruta y denegación de servicio dirigidos específicamente a los puntos de entrada nativos (`wp-login.php`, `xmlrpc.php`). 

Al mismo tiempo, abril 2026 a mayo 2026, ha bajado la calidad de los LLM disponibles en chats online y he pensado hacer un censo con los que medianamente funcionan. Mas informes en 
[(https://vibecodingmexico.com/la-prueba-de-la-semana/)](https://vibecodingmexico.com/la-prueba-de-la-semana/)


Se elige un poryecto y se probará en 
1 Qwen
2 Kimi
3 Claude
4 Gemini
5 Grok
6 Meta.ai (por imagenes)
7 Minimax si puedo usarlo en el modo que he usado antes

Se pondrá un direcrtorio por cada uno de ellos.

No espero que Mata.ai y minimax den algo funcional y la gran incóginta es GROK. Hago notar que el modo de razonamiento de GEMINIes muy inferior a Claude o KIMI incluso en la planeación de un proyecto sencillo com oeste.

**Visor Wordpress** es un sistema de visualización de contenido independiente, escrito en **PHP 8.x Procedural**, diseñado para operar de forma aislada a la estructura de archivos de WordPress, pero aprovechando su base de datos (MariaDB).

## 🧪 El Benchmark de Razonamiento (Stress Test LLM)
Más allá de la utilidad técnica, este repositorio sirve como un **Benchmark de Fuerza Bruta Lógica** para evaluar la degradación reciente observada en diversos Modelos de Lenguaje Grande (LLMs). 

En el último trimestre (febrero-mayo 2026), se ha detectado un fenómeno de *Model Drift* o alucinaciones críticas en tareas de seguimiento secuencial. Este proyecto evalúa si un LLM es capaz de:
1. **Mantener coherencia** en un stack técnico restringido (PHP Procedural / Bootstrap 4.6).
2. **Realizar ingeniería inversa** de archivos (`wp-config.php`) y bases de datos sin usar funciones nativas del CMS.
3. **Procesar datos complejos** (arrays serializados de plugins y JOINs de SQL para imágenes destacadas).

## 🛠 Especificaciones Técnicas
*   **Backend:** PHP 8.x (Estrictamente Procedural).
*   **Frontend:** Bootstrap 4.6.x (vía jsDelivr CDN), FontAwesome 5.15.4.
*   **Base de Datos:** MariaDB (Schema nativo de WordPress).
*   **Seguridad:** Aislamiento total. No requiere `wp-load.php` ni el núcleo de WordPress para funcionar.
*   **SEO:** Incluye lógica de redirección vía `.htaccess` para preservar la indexación previa mediante el uso de slugs.

## 📂 Estructura del Sistema
*   `newconfig.php`: Extractor de credenciales del sistema original.
*   `functions.php`: Motor de lógica de negocio y consultas SQL de bajo nivel.
*   `index.php`: Interfaz de usuario dinámica con sidebar inteligente.
*   `admin_info.php`: Panel informativo de plugins y temas activos.
*   `style.css`: Control centralizado de layout y posicionamiento.

## ⚖️ Licencia
Este proyecto se distribuye bajo la **Licencia MIT**. 
## ⚖️ Prompt Original
MASTER PROMPT: Benchmark de Razonamiento y Desarrollo Estructural (WP-Shield Viewer)
CONTEXTO Y OBJETIVO
El objetivo es desarrollar un sistema de visualización de contenido ligero y autónomo que funcione de manera independiente a una instalación de WordPress existente (que se encuentra bajo ataque en sus rutas nativas). El sistema debe leer la base de datos de WP pero NO puede cargar el entorno de WordPress (wp-load.php).

IDENTIFICACIÓN Y LICENCIA (REQUERIMIENTO OBLIGATORIO)
Identificación: El modelo debe iniciar su respuesta indicando claramente su Nombre y Versión exacta (ej. GPT-4o, Gemini 1.5 Pro, Claude 3.5 Sonnet, etc.).

Firma en Código: Esta identificación debe aparecer en el encabezado de cada archivo generado como un comentario de PHP.

Licencia: Todos los archivos deben incluir el encabezado de la Licencia MIT al inicio.

STACK TÉCNICO
Lenguaje: PHP 8.x (Estilo Procedural obligatorio, uso de mysqli).

CSS/UI: Bootstrap 4.6.x (vía jsDelivr), FontAwesome 5.15.4.

Base de Datos: MariaDB/MySQL (Esquema nativo de WordPress).

Arquitectura: CSS Centralizado y lógica de archivos separados.

ESTRUCTURA DE ARCHIVOS Y LÓGICA
1. newconfig.php
Debe abrir el archivo ../wp-config.php y extraer mediante lógica de parseo (regex o inclusión controlada) las constantes de conexión: DB_NAME, DB_USER, DB_PASSWORD, DB_HOST y la variable $table_prefix.

Establecer la conexión $conn mediante mysqli_connect.

Si falla la extracción o la conexión, debe mostrar un error limpio.

2. functions.php (El motor de razonamiento)
Sidebar Dinámico: Crear una lógica que cuente post_type. Si predominan 'posts', generar una lista de Archivo por Mes. Si predominan 'pages', generar Categorías con contador.

Recent Posts: Función para obtener los 5 posts más recientes con sus enlaces.

Admin Info: Función que consulte wp_options para listar el tema activo y los plugins activados (debe procesar el string serializado de active_plugins).

Imagen Destacada: Lógica SQL para obtener la URL de la imagen destacada (JOIN entre posts y postmeta).

3. style.css
Layout con Navbar fija (superior) y Footer fijo (inferior).

Diseño de dos columnas adaptable.

Variable de Control: Definir una clase o variable que permita cambiar la posición del sidebar (izquierda o derecha) de forma global.

4. index.php
Estructura principal usando el CSS y las funciones creadas.

Visualización de contenidos en formato Cards de Bootstrap ordenados por fecha descendente.

Barra de búsqueda funcional que filtre por post_title o post_content.

5. .htaccess
Generar reglas para redirigir las URLs amigables del sitio antiguo hacia este nuevo visor (index.php?slug=$1), preservando el SEO.

REGLAS CRÍTICAS (FALLO INMEDIATO SI SE INCUMPLEN)
PROHIBIDO usar funciones nativas de WordPress (get_header, the_content, wp_query, etc.).

PROHIBIDO el uso de Programación Orientada a Objetos (POO) o frameworks modernos (React, Vue, etc.).

PROHIBIDO el uso de NPM; todas las librerías deben ser vía CDN.

REQUERIDO: El código debe ser limpio, comentado y funcional para PHP 8.x.

¿Cómo usar este documento?
Te sugiero probarlo primero en Gemini y luego en Kimi (que mencionaste que aún responden bien). Los puntos donde verás quién "gana" el benchmark son:

El parseo del wp-config.php: ¿Realmente lee el archivo o te pide que lo llenes tú?

El unserialize() de los plugins: ¿Muestra la lista de nombres de plugins o un código roto?

El .htaccess: ¿Entiende cómo pasar el slug a un sistema procedural?

---
**Nota de Auditoría:** Este repositorio es parte de un experimento de benchmarking para evaluar la capacidad de respuesta de modelos como Gemini, Kimi, Claude y otros ante entornos de "austeridad técnica" y restricciones de tokens en el contexto de desarrollo en México.

---
**Nota de Auditoría:** Este repositorio es parte de un experimento de benchmarking para evaluar la capacidad de respuesta de modelos como Gemini, Kimi, Claude y otros ante entornos de "austeridad técnica" y restricciones de tokens en el contexto de desarrollo en México.

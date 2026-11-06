# Inventario IberoTech

Sistema CRUD de inventario desarrollado con PHP y MySQL.

## Funcionalidades
- Registrar productos.
- Consultar y buscar productos por nombre o categoría.
- Editar y eliminar productos.
- Gestionar nombre, marca, categoría, precio y stock.
- Incrementar y disminuir el stock mediante botones +1 y -1.
- Mostrar alertas de inventario bajo.
- Clasificar productos por categoría y marca.
- Consultar la cantidad de productos, unidades y valor total del inventario.

## Tecnologías utilizadas
- PHP
- MySQL
- HTML y CSS
- Laragon
- HeidiSQL
- Git y GitHub

## Archivos principales

- `conexion.php`: establece la conexión con MySQL mediante PDO.
- `index.php`: contiene la lógica CRUD y la interfaz web.
- `database.sql`: contiene la estructura de la base de datos y la tabla de productos.

## Ejecución local

1. Instalar e iniciar Laragon y MySQL.
2. Importar el archivo `database.sql` en MySQL.
3. Verificar la configuración de conexión a MySQL en `conexion.php`.
4. Guardar el proyecto en `C:\laragon\www\crud-ibero`.
5. Abrir `http://localhost/crud-ibero/index.php` en el navegador.
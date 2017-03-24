# PHP-PDO
Clase PDO para MySQL
## Configuración previa (a,b,c)

### a. El archivo de credenciales

- **Crear un archivo `bd.php.ini` en una carpeta fuera del root**. En este caso se usa una carpeta llamada `.credenciales`, situada fuera del root (para que no se pueda acceder al archivo por url, aunque de todos modos el archivo no muestra nada).
- Copiar en ese archivo el contenido de: [db.php.ini](https://github.com/padrecedano/PHP-PDO/blob/master/db.php.ini) y actualizarlo con las credenciales propias. La primera línea es para evitar que, en caso de que alguien pueda acceder al archivo por url, no muestre nada. Aunque es imposible acceder, porque estaría fuera del root. Si la contraseña de la base de datos contiene caracteres como comillas simples u otros, debe ponerse entre comillas, de lo contrario habrá conflictos a la hora de autentificar. No dejar espacios entre los signos de =

**Nota:** Este es un nivel de seguridad suplementario. Si no se quiere usar, es posible simplemente editar el archivo de la clase DbPDO y poner las credenciales de conexión directamente.

### b. El archivo de conexión
Copiar el archivo [DbPDO.class.php](https://github.com/padrecedano/PHP-PDO/blob/master/DbPDO.class.php) en la carpeta que deseemos. Generalmente se suele tener una carpeta dedicada a las clases. Es más fácil luego cargarlas con Autoloader.</p>

### c. El archivo log para escribir las excepciones
La clase incluye un archivo log en el cual se escribirán las excepciones. Es necesario pues copiar el archivo [DbLog.class.php](https://github.com/padrecedano/PHP-PDO/blob/master/DbLog.class.php) en la misma carpeta en que copiamos el archivo de conexión.

**Nota:** Si por algún motivo no se quiere verificar las posibles excepciones o hacerlo directamente en el log de PHP, la clase se puede modificar.



## 2. Modo y ejemplos de uso
### a. Incluir el archivo y crear una nueva instancia
Teniendo las credenciales correctas en nuestro archivo `db.php.ini` y nuestro archivo de conexión, para usar la clase procedemos como de costumbre: lo primero es incluir la clase. Como dije antes, se puede hacer por Autoloader o con `require` o `requiere_once`. Luego se crea una instancia de la clase con `new`

```php
require_once("DbPDO.class.php");
$mipdo=new DbPDO();
```
### b. Usar los métodos de la clase
La clase tiene los métodos principales para los que necesitamos acceder a la base de datos. Se usan además consultas preparadas que previenen de la Inyección SQL.

#### Consulta general, sin filtros WHERE

```php
$datos = $mipdo->query("SELECT * FROM tabla");

```

#### Consulta particular, con filtros WHERE

Por lo general necesitamos aplicar filtros a las consultas. Un grave peligro es la Inyección SQL. Para prevenirla se recomienda usar consultas preparadas que envían separados la consulta y los datos del filtro. La clase proporciona varias formas de hacer esto.

**_A. Haciendo el binding uno por uno:_**

```php
$mipdo->bind("idprovincia","1");
$mipdo->bind("idestado","5");
$datos=$db->query("SELECT * FROM tabla WHERE id_provincia = :idprovincia AND id_estado = :idestado");

```

**_B. Haciendo el binding mediante el método bindMas:_**

Nótese que la consulta aquí no cambia, sólo la forma de hacer el binding.
```php
$mipdo->bindMas(array("id_provincia"=>"1","id_estado"=>"5"));
$datos=$db->query("SELECT * FROM tabla WHERE id_provincia = :idprovincia AND id_estado = :idestado");
```

**_C. Enviando el binding directamente al método:_**

La SQL y los parámetros van por separado, aunque son enviados con una sola llamada al método

```php
$datos=$mipdo->query(
                   "SELECT * FROM tabla WHERE id_provincia = :idprovincia AND id_estado = :idestado",
                   array("id_provincia"=>"1","id_estado"=>"5")
                  );

```

### Tipos de resultado

#### _a. Arreglo asociativo_

La clase devuelve por defecto un arreglo asociativo con el conjunto de los datos. Es quizá la forma más práctica, de todos modos la clase se puede modificar creando métodos que devuelvan los datos como nosotros los querramos.

En un código como éste, tendremos en la variable `$datos` un arreglo asociativo con los datos de la tabla que cumplan el criterio.

```php
$datos=$mipdo->query(
                   "SELECT * FROM tabla WHERE id_provincia = :idprovincia AND id_estado = :idestado",
                   array("id_provincia"=>"1","id_estado"=>"5")
                  );

```

#### _b. Una columna en específico_

```php
$columna=$mipdo->column("SELECT columna FROM tabla");

```

#### _c. Una fila en específico_

```php
$fila=$mipdo->row("SELECT * FROM tabla WHERE  id = :id", array("id"=>"1"));

```


### Continuará

La clase tiene más métodos y usos interesantes. 

Continuará...

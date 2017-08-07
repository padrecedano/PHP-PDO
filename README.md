# PHP-PDO
PHP-PDO es una clase que nos permitirá manejar con suma facilidad todas nuestras operaciones relativas a cualquier base de datos MySQL (u otra) en cualquier aplicación PHP, usando la Clase PDO.

## ¿Por qué una clase dedicada al manejo de la base de datos?
Esta clase aprovecha varias de las ventajas de la Programación Orientada a Objetos (POO). 

Para poner un ejemplo muy simple: imaginemos una aplicación que en sus orígenes podría ser pequeña y va creciendo con el tiempo, o una aplicación extensa desde sus orígenes. Es posible que en varias partes de esa aplicación necesitemos conectarnos a la base de datos para consultarla, insertar o actualizar datos. ¿Qué pasa si nos conectamos directamente allí donde lo necesitamos, pasándole las credenciales y creando nuestro nuevo objeto conexión? Aparentemente no pasa nada... pero con el tiempo, tendremos por todas partes conexiones así. Pero... ¿qué ocurrirá si se decide cambiar la contraseña de la base de datos? ¡Habrá que ir en busca de todas las partes del código en la que hemos decidido crear nuestro objeto conexión directamente y cambiar la contraseá!, de lo contrario, tendremos errores por todas partes. En cambio, tener una clase de conexión nos permite **tener todo lo relativo a la conexión en un solo lugar**. Cuando sea necesario cambiar algo, sólo tendremos que cambiarlo en ese lugar.

Esto es algo quizá _banal_. Pues pueden ocurrir cosas peores, como que en una de esas muchas partes donde creamos nuestro objeto conexión nos olvidemos de algo importante con respecto a la seguridad, y tengamos en nuestra aplicación, por descuido, algunas conexiones que sean inseguras. Tener un único objeto de conexión bien configurado, nos evitará situaciones de ese tipo que podrían ser muy peligrosas.

La POO ofrece otras ventajas, mucho más importantes. Pero no considero necesario ahondar ahora en eso.

# 1. Configuración previa

Lo más complicado, sobre todo si estás iniciando en PHP, es configurar adecuadamente la clase. Afortunadamente esto sólo tienes que hacerlo una sola vez y no es tan complicado como parece.

Necesitaremos solamente dos archivos para tener nuestra clase funcionando.

## 1.1 El archivo de credenciales

Para darle más seguridad a las credenciales de conexión, la clase almacena dichos datos **en un solo archivo de credenciales**. Si por algún motivo hay que cambiar la contraseña u otros elementos de la conexión sólo habría que cambiarlos en ese archivo, como comentaba en la introducción.

Para preparar nuestro archivo de configuración sólo tendremos que seguir dos sencillos pasos:

1. **Crear un archivo llamado `bd.php.ini` en una carpeta fuera del root**. En este caso se usa una carpeta llamada `.credenciales`. El punto `.` delante crea una carpeta oculta, la cual, al estar situada fuera del root impide que se pueda acceder al archivo de configuración por url, aunque de todos modos el archivo no muestra nada.

2. Copiar en **`bd.php.ini`** el contenido de: [db.php.ini](https://github.com/padrecedano/PHP-PDO/blob/master/db.php.ini) y actualizarlo con las credenciales propias. La primera línea es para evitar que, en caso de que alguien pueda acceder al archivo por url, no pueda ver nada de lo que hay dentro del archivo... Aunque es imposible acceder, porque estaría fuera del root. Si la contraseña de la base de datos contiene caracteres como comillas simples u otros, debe ponerse entre comillas, de lo contrario habrá conflictos a la hora de autentificar. No dejar espacios entre los signos de =


## 1.2 El archivo de conexión

Copiar el archivo [DbPDO.class.php](https://github.com/padrecedano/PHP-PDO/blob/master/DbPDO.class.php) en la carpeta que deseemos. Generalmente se suele tener una carpeta dedicada a las clases. Es más fácil luego cargarlas con Autoloader.</p>


# 2. Modo y ejemplos de uso

Cada vez que necesitemos usar la clase necesitaremos:

1. Incluir el archivo antes de crear una nueva instancia con `new` (a no ser que estemos cargando clases con Autoloader, pues en ese caso no haría falta incluir la clase). 

2. Usamos el objeto instanciado para pasar las consultas y manejar los datos.
Nótese que la clase funciona del mismo modo para cualquier tipo de consulta: `SELECT, INSERT, UPDATE, DELETE ...` se trata de escribir en cada caso la consulta preparada y pasar los parámetros usando el estilo que más nos convenga. Aunque los ejemplos citados más abajo se usan con consultas `SELECT`, servirían para los otros tipos de consultas.


## 2.1 Incluir el archivo y crear una nueva instancia

Teniendo las credenciales correctas en nuestro archivo `db.php.ini` y nuestro archivo de conexión, para usar la clase procedemos como de costumbre: lo primero es incluir la clase. Como dije antes, se puede hacer por Autoloader o con `require` o `requiere_once`. Luego se crea una instancia de la clase con `new`

```php
require_once("DbPDO.class.php");
$mipdo=new DbPDO();
```
## 2.2 Usar los métodos de la clase

Creada la instancia de la conexión, podremos empezar a usarla. 
La clase tiene los métodos principales para los que necesitamos acceder a la base de datos. Se usan además consultas preparadas que previenen de la Inyección SQL.

## 2.3 Algunos ejemplos de uso: Consultas

Veamos sólo algunos ejemplos de uso.

### 2.3.1 Consulta general, sin filtros WHERE

```php
$datos = $mipdo->query("SELECT * FROM padres");

```

### 2.3.2 Consulta particular, con filtros WHERE

Por lo general necesitamos aplicar filtros a las consultas. Un grave peligro es la Inyección SQL. Para prevenirla se recomienda usar consultas preparadas que envían separados la consulta y los datos del filtro. La clase proporciona varias formas de hacer esto.

**_a. Haciendo el binding uno por uno:_**

```php
$mipdo->bind("idprovincia","1");
$mipdo->bind("idestado","5");
$datos=$db->query("SELECT * FROM tabla WHERE id_provincia = :idprovincia AND id_estado = :idestado");

```

**_b. Haciendo el binding mediante el método bindMas:_**

Nótese que la consulta aquí no cambia, sólo la forma de hacer el binding.
```php
$mipdo->bindMas(array("id_provincia"=>"1","id_estado"=>"5"));
$datos=$mipdo->query("SELECT * FROM tabla WHERE id_provincia = :idprovincia AND id_estado = :idestado");
```

**_c. Enviando el binding directamente al método:_**

La SQL y los parámetros van por separado, aunque son enviados con una sola llamada al método

```php
$datos=$mipdo->query(
                   "SELECT * FROM tabla WHERE id_provincia = :idprovincia AND id_estado = :idestado",
                   array("id_provincia"=>"1","id_estado"=>"5")
                  );

```

### 2.4 Obteniendo los datos: Tipos de resultado obtenidos

Veamos sólo algunas posibilidades de cómo obtener los datos obtenidos en la consulta y presentarlos en pantalla.

#### 2.4.1 _Arreglo asociativo_

La clase devuelve por defecto un arreglo asociativo con el conjunto de los datos. Es quizá la forma más práctica, de todos modos la clase se puede modificar creando métodos que devuelvan los datos como nosotros los querramos.

En un código como éste (y en cualquier consulta SELECT con o sin parámetros), tendremos en la variable `$datos` un arreglo asociativo con los datos de la tabla que cumplan el criterio.

```php
$datos=$mipdo->query("SELECT * FROM padres;");

```

_Resultado almacenado en la variable `$datos` usando `print_r()`_
```php
Array
(
    [0] => Array
        (
            [id_padre] => 1
            [padre] => Juan Crisóstomo
            [id_grupo] => 1
        )

    [1] => Array
        (
            [id_padre] => 2
            [padre] => Agustín
            [id_grupo] => 1
        )

    [2] => Array
        (
            [id_padre] => 3
            [padre] => Teofilacto
            [id_grupo] => 1
        )

)
```
_Resultado almacenado en la variable `$datos` representado en una tabla_

<table><th>id_padre</th><th>padre</th><th>id_grupo</th><tr><td>1</td><td>Juan Crisóstomo</td><td>1</td></tr><tr><td>2</td><td>Agustín</td><td>1</td></tr><tr><td>3</td><td>Teofilacto</td><td>1</td></tr></table>

#### 2.4.2 _Una columna en específico_
Si queremos obtener un columna en específico, podríamos enviar una consulta como esta. Como hemos dicho antes, se pueden pasar en una sola llamada, mediante el método `column` tanto la instrucción SQL como los parámetros. El hecho de que se pasen en una sola llamada, no significa que se pasen juntos, la clase se encarga de separarlos, para cumplir con los criterios de consultas preparadas.

```php
$columna=$mipdo->column("SELECT padre FROM padres WHERE id_padre = :id_padre", array("id_padre"=>"50"));

```

_Resultado almacenado en la variable `$columna` usando `print_r()`_
```php
Array
(
    [0] => Pascasio Radberto
)
```
_Resultado almacenado en la variable `$columna` representado en una tabla_

<table><th>padre</th><tr><td>Pascasio Radberto</td></tr></table>

#### 2.4.3 _Una fila en específico_
Si queremos obtener un fila en específico, podríamos enviar una consulta como esta. También aquí podemos pasar en una sola llamada, mediante el método `row` tanto la instrucción SQL como los parámetros.

```php
$fila=$mipdo->row("SELECT id_padre, padre FROM padres WHERE id_padre = :id_padre", array("id_padre"=>"70");

```

_Resultado almacenado en la variable `$fila` usando `print_r()`_
```php
Array
(
    [id_padre] => 70
    [padre] => Alfonso María de Ligorio
)
```

_Resultado almacenado en la variable `$fila` representado en una tabla_

<table><th>id_padre</th><th>padre</th><tr><td>70</td><td>Alfonso María de Ligorio</td></tr></table>

### 3. Continuará

La clase tiene más métodos y usos interesantes. Puedes modificarla a tu gusto...

Continuará...

<?php
/**
 *  DB - Clase para Base de Datos usando PDO 
 *
 * @author		A. Cedano
 * @git 		https://github.com/padrecedano/PHP-PDO
 * @version      1.1
 *               En la versión 1.1 se suprime la escritura de errores en un log particular
 *               Escribiéndolos en el error_log por defecto de php junto con los demás mensajes de error
 */
class DbPDO
{
    # @object, Objeto PDO 
    private $pdo;
    
    # @object, Consulta preparada PDO  
    private $sSQL;
    
	# @strings, credenciales de conexión
	/*
		* Colocar los valores propios de conexión
	*/
	private $host="localhost";
	private $usr="aquí-nombre-de-usuario-de-la-bd";
	private $pwd="aquí-la-clave";
	private $dbname="aquí-el-nombre-de-la-bd";
	    
    # @bool ,  Si conectado a la BD
    private $isConnected = false;
    
    # @array, Parámetros de la consulta SQL
    private $parametros;
    
    /**
     *   Constructor por defecto 
     *
     *	1. Instancia la clase Log.
     *	2. Conecta a la base de datos.
     *	3. Crea la matriz (array) con los parámetros.
     */
    public function __construct()
    {
        $this->Connect();
        $this->parametros = array();
    }
    
    /**
     *	Este método realiza la conexión a la BD.
     *	
     *	1. Lee las credenciales de la BD desde un archivo .ini. 
     *	2. Coloca el contenido del archivo ini en un arreglo (credenciales).
     *	3. Intenta conectarse a la BD.
     *	4. Si la conexión falla, despliega una excepción y escribe el mensaje de error en el archivo log creado.
     */
    private function Connect()
    {
        $dsn = 'mysql:dbname=' . $this->dbname . ';host=' . $this->host . '';
        $pwd = $this->pwd;
        $usr = $this->usr;

    /**
     *	El array $options es muy importante para tener un PDO bien configurado
     *	
     *	1. PDO::ATTR_PERSISTENT => true: sirve para usar conexiones persistentes
     *      se puede establecer a false si no se quiere usar este tipo de conexión. Ver: https://es.stackoverflow.com/a/50097/29967 
     *	2. PDO::ATTR_EMULATE_PREPARES => false: Se usa para desactivar emulación de consultas preparadas 
     *      forzando el uso real de consultas preparadas. 
     *      Es muy importante establecerlo a false para prevenir Inyección SQL. Ver: https://es.stackoverflow.com/a/53280/29967
     *	3. PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION También muy importante para un correcto manejo de las excepciones. 
     *      Si no se usa bien, cuando hay algún error este se podría escribir en el log revelando datos como la contraseña !!!
     *	4. PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'": establece el juego de caracteres a utf8, 
     *      evitando caracteres extraños en pantalla. Ver: https://es.stackoverflow.com/a/59510/29967
     */

        $options = array(
            PDO::ATTR_PERSISTENT => true, 
            PDO::ATTR_EMULATE_PREPARES => false, 
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
        );

        try {
			# Intentar la conexión 
            $this->pdo = new PDO($dsn, $usr, $pwd, $options);
                       
            # Conexión exitosa, asignar true a la variable booleana isConnected.
            $this->isConnected = true;
        }
        catch (PDOException $e) {
            # Escribir posibles excepciones en el error_log
            $this->error = $e->getMessage();
        }
    }

    /*
     *   Este método cierra la conexión
     *   No es obligatorio, ya que PHP la cierra cuando termina el script
     *   Ver: http://es.stackoverflow.com/questions/50083/50097#50097
     */

    public function closeConnection()
    {
        # Setea el objeto PDO a null para cerrar la conexion
        # http://www.php.net/manual/en/pdo.connections.php
        $this->pdo = null;
    }
    
    /**
     *	Método que será usado para enviar cualquier consulta a la BD.
     *	
     *	1. Si no hay conexión, conectar a la BD.
     *	2. Preparar la consulta.
     *	3. Parametrizar la consulta.
     *	4. Ejecutar la consulta.	
     *	5. Si ocurre una excepción: Escribirla en el archivo log junto con la consulta.
     *	6. Resetear los parámetros.
     */
     
    private function Init($sql, $parametros = "")
    {
        # Conecta a la BD
        if (!$this->isConnected) {
            $this->Connect();
        }
        try {
            # Preparar la consulta
            $this->sSQL = $this->pdo->prepare($sql);
            
            # Agregar parámetros a la matriz de parámetros	
            $this->bindMas($parametros);
            
            # Asignar parámetros
            if (!empty($this->parametros)) {
                foreach ($this->parametros as $param => $value) {
                    if(is_int($value[1])) {
                        $type = PDO::PARAM_INT;
                    } else if(is_bool($value[1])) {
                        $type = PDO::PARAM_BOOL;
                    } else if(is_null($value[1])) {
                        $type = PDO::PARAM_NULL;
                    } else {
                        $type = PDO::PARAM_STR;
                    }
                    // Añade el tipo cuando asigna los valores a la columna 
                    $this->sSQL->bindValue($value[0], $value[1], $type);
                }
            }
            
            # Ejecuta la consulta SQL 
            $this->sSQL->execute();
        }
        catch (PDOException $e) {
            # Escribe en el archivo log si ocurre un excepción
            echo $this->ExceptionLog($e->getMessage(), $sql);
            die();
        }
        
        # Resetea los parámetros
        $this->parametros = array();
    }
    
    /**
     *	@void 
     *
     *	Agrega un parámetro al arreglo de parámetros
     *	@param string $parametro  
     *	@param string $valor 
     */
    public function bind($parametro, $valor)
    {
        $this->parametros[sizeof($this->parametros)] = [":" . $parametro , $valor];
    }
    /**
     *	@void
     *	
     *	Agrega más parámetros al arreglo de parámetros
     *	@param array $parray
     */
    public function bindMas($parray)
    {
        if (empty($this->parametros) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }
    /**
     *  Si la consulta SQL contiene un SELECT o SHOW, devolverá un arreglo conteniendo todas las filas del resultado
     *     Nota: Si se requieren otros tipos de resultados la clase puede modificarse, 
     *           agregandolos o se pueden crear otros métodos que devuelvan los resultados como los necesitemos
     *           en nuesta aplicación. Para tipos de resultados ver: http://php.net/manual/es/pdostatement.fetch.php 
     *	Si la consulta SQL es un DELETE, INSERT o UPDATE, retornará el número de filas afectadas
     *
     *  @param  string $sql
     *	@param  array  $params
     *	@param  int    $fetchmode
     *	@return mixed
     */

    public function query($sql, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $sql = trim(str_replace("\r", " ", $sql));
        
        $this->Init($sql, $params);
        
        $rawStatement = explode(" ", preg_replace("/\s+|\t+|\n+/", " ", $sql));
        
        # Determina el tipo de SQL 
        $statement = strtolower($rawStatement[0]);
        
        if ($statement === 'select' || $statement === 'show') {
            return $this->sSQL->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sSQL->rowCount();
        } else {
            return NULL;
        }
    }


    /**
     *	Devuelve un arreglo que representa una columna específica del resultado 
     *
     *	@param  string $sql
     *	@param  array  $params
     *	@return array
     */
     
    public function column($sql, $params = null)
    {
        $this->Init($sql, $params);
        $Columns = $this->sSQL->fetchAll(PDO::FETCH_NUM);
        
        $column = null;
        
        foreach ($Columns as $cells) {
            $column[] = $cells[0];
        }
        
        return $column;
        
    }

    /**
     *	Devuelve un arreglo que representa una fila del resultado
     *
     *	@param  string $sql
     *	@param  array  $params
     *  @param  int    $fetchmode
     *	@return array
     */
    public function row($sql, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $this->Init($sql, $params);
        $result = $this->sSQL->fetch($fetchmode);
        $this->sSQL->closeCursor(); // Libera la conexión para evitar algún conflicto con otra solicitud al servidor
        return $result;
    }
    /**
     *	Devuelve un valor simple campo o columna
     *
     *	@param  string $sql
     *	@param  array  $params
     *	@return string
     */
    public function single($sql, $params = null)
    {
        $this->Init($sql, $params);
        $result = $this->sSQL->fetchColumn();
        $this->sSQL->closeCursor(); // Libera la conexión para evitar algún conflicto con otra solicitud al servidor
        return $result;
    }

    
    /**
     *  Devuelve el último id insertado.
     *  @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Inicia una transacción
     * @return boolean, true si la transacción fue exitosa, false si hubo algún fallo
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     *  Ejecuta una transacciónn
     *  @return boolean, true si la transacción fue exitosa, false si hubo algún fallo
     */
    public function executeTransaction()
    {
        return $this->pdo->commit();
    }
    
    /**
     *  Rollback de una transacción
     *  @return boolean, true si la transacción fue exitosa, false si hubo algún fallo
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
    
}
?>
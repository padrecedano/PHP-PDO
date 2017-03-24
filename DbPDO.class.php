<?php 
       /* *
	* DbLog 		Una clase log para manejar las excepciones de la clase DbPDO.
	* @author		A. Cedano
	* @git 			https://github.com/padrecedano/PHP-PDO
	* @version      1.0
	*/
	class DbLog {
			
		    # @string, Directorio para el archivo log
		    	private $path = '/logs/';
			
		    # @void, Constructor que asigna la zona horaria y la ruta del archivo log.
			public function __construct() {
				date_default_timezone_set('Europe/Madrid');	
				$this->path  = dirname(__FILE__)  . $this->path;	
			}
			
		   /**
		    *   @void 
		    *	Crear el log
		    *
		    *   @param string $mensaje El mensaje que será escrito en el log.
		    *	@descripción:
		    *	 1. Verifica que existe el directorio, si no existe lo crea.
	        *	 2. Verifica si el archivo log existe.
		    *	 3. Si no existe crea un archivo log.
		    *	 4. Le asigna el nombre de la fecha actual(Año - Mes - Día).
		    *	 5. Si el log existe llama al método edit ().
		    *	 6. El método edit modifica el log actual.
		    */	
			public function write($mensaje) {
				$date = new DateTime();
				$log = $this->path . $date->format('Y-m-d').".log";

				if(is_dir($this->path)) {
					if(!file_exists($log)) {
						$fh  = fopen($log, 'a+') or die("!Error Fatal!");
						$logcontent = "Hora : " . $date->format('H:i:s')."\r\n" . $mensaje ."\r\n";
						fwrite($fh, $logcontent);
						fclose($fh);
					}
					else {
						$this->edit($log,$date, $mensaje);
					}
				}
				else {
					  if(mkdir($this->path,0777) === true) 
					  {
 						 $this->write($mensaje);  
					  }	
				}
			 }
			
			/** 
			 *  @void
			 *  Este método es llamado cuando el log existe. 
			 *  Modifica el log actual añadiendo el contenido del mensaje.
			 *
			 * @param string $log
			 * @param DateTimeObject $date
			 * @param string $mensaje
			 */
			    private function edit($log,$date,$mensaje) {
				$logcontent = "Hora : " . $date->format('H:i:s')."\r\n" . $mensaje ."\r\n\r\n";
				$logcontent = $logcontent . file_get_contents($log);
				file_put_contents($log, $logcontent);
			    }
		}
?>

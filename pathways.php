<?php
/**
 * pathways.php
 *
 * Searches for strains inside pathways
 *
 * @author     Jos� Luis Ram�rez
 * @copyright  2015 Jos� Luis Ram�rez
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    1.0
 */

//Esta funci�n es la que procesa todos los archivos Strain de un directorio
function procesaDir($dirPath, $outFile, $pathways)
{
	
	$dir = new DirectoryIterator($dirPath);
	$fpout = fopen($outFile, 'w');
	
	//Agregamos un primer rengl�n con los nombres de las columnas
	fputs($fpout, "SALIN,HIT START,STRAIN,STRAIN,PATHWAY,START,STOP\r\n");

	//Iteramos sobre cada archivo del directorio
	foreach($dir as $fileinfo)	{
		
		//Omitimos '.' y '..'
		if ($fileinfo->isDot())
			continue;
		
		//Abrimos el archivo del strain
		$fpin = fopen($dirPath.$fileinfo->getFilename(), 'r');
		
		//Se imprime en pantalla el archivo que estamos procesando
	  echo 'En archivo: '.$fileinfo->getFilename().'<br>';

		//Iteramos sobre todas las l�neas del archivo
		while($line = fgets($fpin)) {
			//Separamos la l�nea en sus partes (separada por comas)
			list($hitStartTxt, $strain) = explode(',',$line);
			
			//Omitimos el primer rengl�n
			if($hitStartTxt == 'Hit start') continue;
			
			//Le quitamos los caracteres especiales al campo strain
			$strain = str_replace(array("\n", "\t", "\r"), '', $strain);
			
			//Separamos el texto del strain, quit�ndole lo que est� despu�s del guion bajo
			//La variable $onlyStrain es la que nos servir� para buscarla en los pathways
			list($onlyStrain, $extra) = explode('_', $strain);
			
			//convertimos en texto el $hitStart a n�mero
			$hitStart = intval($hitStartTxt);
			
			//Iteramos por todos los pathways
			foreach($pathways as $key => $pathway)	{
				
				//Se verifica que el hitStart est� entre el start y stop del pathway 
				//y el strain sea el mismo
	      if( ($hitStart >= $pathway['start']) && 
	      		($hitStart <= $pathway['stop']) && 
	      		($onlyStrain == $pathway['strain']) )	{
	      			
	      	//Si se cumple la condici�n, generamos la l�nea que ser� guardada en el archivo de salida con la informaci�n del strain
	    		$lineOut = $fileinfo->getFilename().','.$hitStartTxt.','.$strain.','.$pathway['strain'].','.$pathway['pathway'].','.strval($pathway['start']).','.strval($pathway['stop'])."\r\n";	
					
					//Se imprime en pantalla la l�nea del strain solo para fines informativos	    		
	    		echo 'Encontrado: '.$lineOut.'<br>';  
	    		
	    		//Se guarda la l�nea en el archivo
	    		fputs($fpout, $lineOut);
	    	}
	    }
	  }
	  
	  //Cerramos el archivo del strain
	  fclose($fpin);
	}	
	
	//Cerramos el archivo de resultados
	fclose($fpout);
}

//Indicamos el directorio que contiene los strains que queremos procesar
$dir_datos = './pathways5/';

//Indicamos el archivo de Pathways
$pathways_file = './Salinispora_Pathways.csv';

//Abrimos el archivo Pathways
$fp_pathways = fopen($pathways_file, 'r');

//Guardaremos en el array toda la estructura del archivo Pathways
$pathways = array();

//Iteranos cada l�nea del archivo Pathways
while($line = fgets($fp_pathways))	{
	//Separamos por comas la l�nea que estamos procesando
	list($Strain,$Pathway,$Start,$Stop) = explode(",", $line);
	
	//Omitimos el primer rengl�n
	if ($Strain == 'Strain') continue;

	//Guardamos en el array $pathways la l�nea de manera asociativa
	$pathways[] = array('strain' => $Strain, 'pathway' => $Pathway, 'start' => intval($Start), 'stop' => intval($Stop));
}
//Cerramos el archivo Pathways
fclose($fp_pathways);

$dir = new DirectoryIterator($dir_datos);
//Iteramos por cada directorio
foreach ($dir as $fileinfo) {
		//Ignoraremos '.' y '..'
    if (!$fileinfo->isDot()) {
    	//Solo procesaremos los directorios
    	if ($fileinfo->isDir())	{
    		//Imprimimos en pantalla informaci�n del directorio que estamos a punto de procesar
    		echo 'Procesando dir: '.$dir_datos.$fileinfo->getFilename().'\\<br>';
    		//Y el archivo de salida que se generar�
    		echo 'Archivo de salida: '.$dir_datos.$fileinfo->getFilename().'.csv'.'<br>';
				//Mandamos a llamar a la funci�n procesaDir, indic�ndole la carpeta a procesar, 
				//el archivo de salidas donde debe guardar los resultados y el arreglo $pathways
				procesaDir($dir_datos.$fileinfo->getFilename().'\\',$dir_datos.$fileinfo->getFilename().'.csv', $pathways);
				
    	}	
    }
}

?>
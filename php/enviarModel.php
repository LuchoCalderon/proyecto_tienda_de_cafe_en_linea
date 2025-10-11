<?php
    function limpiartextos($datos){
        $palabras=["=","script","</script>","<",">","SELECT","FROM"];
        $datos=trim($datos);
        $datos=stripslashes($datos);
        foreach ($palabras as $palabra) {
            $datos=str_ireplace($palabra,"",$datos);
        }
        $datos=trim($datos);
        $datos=stripslashes($datos);
        return $datos;
    }

    function verificar_datos($filtro,$cadena){
		if(preg_match("/^".$filtro."$/", $cadena)){
			return false;
        }else{
            return true;
        }
	}   
   
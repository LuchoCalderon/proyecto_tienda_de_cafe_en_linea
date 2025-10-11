<?php
    require_once"./enviarModel.php";

    $nombre=limpiartextos($_POST['nombres']);
    $apellido=limpiartextos($_POST['apellidos']);
    $cedula=limpiartextos($_POST['cedula']);
    $contraseña=($_POST['password']);
    $confirmar_contraseña=($_POST['confirmPassword']);
 
    if(verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$nombre)){

         echo "El NOMBRE no coincide con el formato solicitado";
        exit();
   }else{
    echo $nombre;
   }

   if(verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$apellido)){

         echo "El NOMBRE no coincide con el formato solicitado";
        exit();
   }else{
    echo $apellido;
   }
   
if($contraseña!=$confirmar_contraseña){
    echo "Las contraseñas no coinciden";
    exit();
}else{
    echo "Las contraseñas coinciden";
     
}

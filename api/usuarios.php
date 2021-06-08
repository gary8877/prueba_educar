<?php

    header( 'Content-Type: application/json' );
    include_once("../class/usuario.php");

    switch( $_SERVER['REQUEST_METHOD'] ){
        case "POST": // Evaluación de peticiones vía Post - Guardado de usuarios nuevos

            // Obtención de datos envíados desde el json
            $_POST = json_decode( file_get_contents('php://input'), true );

            // Validaciones sobre campos necesarios para el guardado de los datos del usuarios
            if( isset( $_POST['id'] ) && isset( $_POST['nombre'] ) && isset( $_POST['fechaNacimiento'] ) ){

                $usuario = new Usuario(); // Creación del objeto de Tipo Usuario

                // Se realiza una consulta para verificar la existencia del usuario para creación
                $res = $usuario->consultarUsuario( $_POST['id'] );

                if( $res->rowCount() == 0 ){ // Si el usuario no existe, se procede con la creación 
                    $item = array(
                        'id' => $_POST['id'],
                        'nombre' => $_POST['nombre'],
                        'apellido' => ( isset( $_POST['apellido'] ) ) ? $_POST['apellido'] : '',
                        'fechaNacimiento' => $_POST['fechaNacimiento'],
                        'saldo' => ( isset( $_POST['saldo'] ) ) ? $_POST['saldo'] : '0',
                    );

                    // Proceso para realizar el guardado del usuario nuevo
                    $usuario->guardarUsuario( $item );

                    // Se realiza una consulta para verificar el guardado del usuario nuevo
                    $res = $usuario->consultarUsuario( $_POST['id'] );

                    if( $res->rowCount() == 1 ){ // Mensaje de confirmación del usuario creado
                        echo json_encode( array('mensaje' => 'Se guardó con éxito el usuario con id '.$_POST['id'] ));
                    }
                    else{ // Se envia una notificación de error al crear el usuario
                        $item = array(
                            'id' => $_POST['id'],
                            'nombre' => $_POST['nombre'],
                            'apellido' => ( isset( $_POST['apellido'] ) ) ? $_POST['apellido'] : '',
                            'fechaNacimiento' => $_POST['fechaNacimiento'],
                            'saldo' => ( isset( $_POST['saldo'] ) ) ? $_POST['saldo'] : '0',
                            'mensaje' => "No fue posible crear el usuario"
                        );

                        echo json_encode($item);
                    }
                }
                else{ // En caso de existir el usuario, me envia un mensaje notificando
                    echo json_encode( array('mensaje' => 'Ya se encuentra creado en el sistema el usuario con id '.$_POST['id'] ));
                }
            }else{ // Se muestra un mensaje indicando que se presentan campos obligatorios por ser diligenciados
                echo json_encode( array('mensaje' => 'Faltan datos obligatorios para guardar el usuario. ' ));
            }


            break;

        case "GET": // Evaluación de peticiones vía Get - Peticiones de consulta
            if ( isset( $_GET['id'] ) ){ // Consulta de usuario cuando el ID de este, se encuentra definido

            
                $usuario = new Usuario(); // Creación del objeto de Tipo Usuario
                $res = $usuario->consultarUsuario( $_GET['id'] );
                
                // Declaración de arreglo para retornar la respuesta
                $usuarios = array();
                $usuarios["usuario"] = array();

                // Evaluación de cantidad de resultados encontrados - Se espera 1 resultado
                if( $res->rowCount() == 1 ){
                    $row = $res->fetch();
                
                    $item=array(
                        "id" => $row['id'],
                        "nombre" => $row['nombre'],
                        "apellido" => $row['apellido'],
                        "saldo" => $row['saldo'],
                        "creado" => $row['created'],
                        "actualizado" => $row['modified'],
                        "estado" => ( $row['estado'] == 1 ) ? 'Activo' : 'Inactivo',
                    );

                    // Adición de usuarios a arreglo general
                    array_push($usuarios["usuario"], $item);

                    // Impresión de resultados
                    echo json_encode($usuarios);

                }else{ // Impresión de mensaje de error en caso de no encontrar resultados
                    echo json_encode( array('mensaje' => 'No se encontraron usuarios disponibles para el código '.$_GET['id'] ));
                }
            
            }
            else{  // Consulta de todos los usuarios disponibles en el sistema

                $usuario = new Usuario(); // Creación del objeto de Tipo Usuario
                $res = $usuario->consultarUsuarios();

                // Declaración de arreglo para retornar la respuesta
                $usuarios = array();
                $usuarios["usuario"] = array();

                // Evaluación de cantidad de resultados encontrados
                if( $res->rowCount() )
                {
                    while ($row = $res->fetch(PDO::FETCH_ASSOC)){
            
                        $item=array(
                            "id" => $row['id'],
                            "nombre" => $row['nombre'],
                            "apellido" => $row['apellido'],
                            "saldo" => $row['saldo'],
                            "creado" => $row['created'],
                            "actualizado" => $row['modified'],
                            "estado" => ( $row['estado'] == 1 ) ? 'Activo' : 'Inactivo',
                        );

                        // Adición de usuarios a arreglo general
                        array_push($usuarios["usuario"], $item);
                    }
                
                    // Impresión de resultados
                    echo json_encode($usuarios);

                }else{ // Impresión de mensaje de error en caso de no encontrar resultados
                    echo json_encode(array('mensaje' => 'No se encontraron usuarios disponibles'));
                }
            }
            break;

        case "PUT": // Evaluación de peticiones vía Put - Actualización de saldo

            // Proceso de conversión de datos enviados vía Put
            parse_str(file_get_contents("php://input"), $_PUT);

            foreach ($_PUT as $key => $value)
            {
                unset($_PUT[$key]);

                $_PUT[str_replace('amp;', '', $key)] = $value;
            }

            $_REQUEST = array_merge($_REQUEST, $_PUT);

            // Validaciones sobre campos necesarios para el guardado de los datos del usuarios
            if( isset( $_REQUEST['id'] ) && isset( $_REQUEST['saldo'] ) ){

                $usuario = new Usuario(); // Creación del objeto de Tipo Usuario
                
                // Se realiza una consulta para verificar la existencia del usuario para creación
                $res = $usuario->consultarUsuario( $_REQUEST['id'] );
                
                if( $res->rowCount() == 1 ){ // Si el usuario existe, se procede con la actualización de datos
                    
                    // Verificación de si el saldo ingresado es un valor numérico para continuar el proceso
                    if ( is_numeric( $_REQUEST['saldo'] ) ){

                        $row = $res->fetch();

                        if ( !isset( $_GET['idDestino'] ) ){ // Verificación de ingreso para transferencia de saldo entre dos usuarios

                            $nuevoSaldo =  $row['saldo'] + $_REQUEST['saldo'];

                            // Solo se ingresa si hay saldo suficiente para la transacción, esto en caso de ingreso de valores negativos
                            if ( $nuevoSaldo >= 0 ){
                                $item = array(
                                    'id' => $_REQUEST['id'],
                                    'saldo' => $nuevoSaldo,
                                );

                                // Llamado a la función para realizar la actualización de saldo
                                $usuario->actualizarSaldoUsuario( $item );

                                // Se realiza una consulta para verificar el guardado del usuario nuevo
                                $resAct = $usuario->consultarUsuario( $_REQUEST['id'] );

                                if( $resAct->rowCount() == 1 ){ // Verificación de existencia de registros

                                    $rowAct = $resAct->fetch();

                                    $item=array(
                                        "id" => $rowAct['id'],
                                        "nombre" => $rowAct['nombre'],
                                        "apellido" => $rowAct['apellido'],
                                        "valorIngresado" => $_REQUEST['saldo'],
                                        "saldoAnterior" => $row['saldo'],
                                        "nuevoSaldo" => $rowAct['saldo'],
                                        "actualizado" => $rowAct['modified'],
                                    );

                                    echo json_encode($item);
                                }
                            }
                            else{ // Se muestra mensaje para indicar que no es posible realizar la transacción por valor negativo o saldo insuficiente
                                echo json_encode( array('mensaje' => 'No hay saldo disponible para realizar esta transacción.' ));
                            }
                        }
                        else{ // Ingresa si existe el id de destinatario dentro del bloque de parámetros

                            // Se realiza una consulta para verificar la existencia del usuario para creación
                            $resDestino = $usuario->consultarUsuario( $_REQUEST['idDestino'] );

                            if( $resDestino->rowCount() == 1 ){ // Si el usuario existe, se procede con la transacción de saldo

                                // Verificación para solo movimientos con valor numérico positivo - manejo de valor absoluto
                                $_REQUEST['saldo'] = abs( $_REQUEST['saldo'] ); 

                                $rowDestino = $resDestino->fetch();

                                // Resta de valor para la transacción
                                $nuevoSaldoOrigen   = $row['saldo'] - $_REQUEST['saldo'];
                                $nuevoSaldoDestino  = $rowDestino['saldo'] + $_REQUEST['saldo'];

                                // Se evalua si es posible realizar esta transacción
                                if ( $nuevoSaldoOrigen >= 0 )
                                {
                                    $item = array(
                                        'idOrigen' => $_REQUEST['id'],
                                        'saldo' => $nuevoSaldoOrigen,
                                        'idDestino' => $_REQUEST['idDestino'],
                                        'saldoDestino' => $nuevoSaldoDestino,
                                    );
    
                                    // Llamado a la función para realizar la actualización de saldo
                                    $usuario->realizarTrasladoSaldo( $item );

                                    $resAct = $usuario->consultarUsuario( $_REQUEST['idDestino'] );

                                    if( $resAct->rowCount() == 1 ){ // Verificación de existencia de registros

                                        $rowAct = $resAct->fetch();
    
                                        $item=array(
                                            "id" => $rowAct['id'],
                                            "nombre" => $rowAct['nombre'],
                                            "apellido" => $rowAct['apellido'],
                                            "valorIngresado" => $_REQUEST['saldo'],
                                            "saldoAnterior" => $rowDestino['saldo'],
                                            "nuevoSaldo" => $rowAct['saldo'],
                                            "actualizado" => $rowAct['modified'],
                                        );
    
                                        echo json_encode($item);
                                    }
                                }
                                else{
                                    echo 'No se tienen fondos suficientes para realizar la transacción, por favor verifique e inténtelo nuevamente.';
                                }

                            }
                            else{
                                echo json_encode( array('mensaje' => 'El usuario de destino de la transferencia no existe en el sistema, por favor verifique e inténtelo nuevamente.' ));
                            }

                        }
                    }
                    else{ // Se muestra un mensaje indicando que el usuario no existe para el proceso de actualización de datos
                        echo json_encode( array('mensaje' => 'El valor ingresado no es un valor válido, por favor verifique e inténtelo nuevamente.' ));
                    }
                }
                else{ // Mensaje en caso de que el valor ingresado no 
                    echo json_encode( array('mensaje' => 'No se encuentra creado en el sistema el usuario con id '.$_REQUEST['id'] ));
                }
            }
            else{ // Se muestra un mensaje indicando que se presentan campos obligatorios por ser diligenciados
                echo json_encode( array('mensaje' => 'Faltan datos obligatorios para realizar el movimiento. ' ));
            }

            // actualizarSaldoUsuario
            break;
        
        case "DELETE": // Evaluación de peticiones vía Delete - Inactivación de Usuarios
            // $_GET['id']

            // Proceso de conversión de datos enviados vía Put
            parse_str(file_get_contents("php://input"), $_DELETE);

            foreach ($_DELETE as $key => $value)
            {
                unset($_DELETE[$key]);

                $_DELETE[str_replace('amp;', '', $key)] = $value;
            }

            $_REQUEST = array_merge($_REQUEST, $_DELETE);

            // Verificación de datos obligatorios
            if( isset( $_REQUEST['id'] ) ){
                $usuario = new Usuario(); // Creación del objeto de Tipo Usuario
                
                // Se realiza una consulta para verificar la existencia del usuario para creación
                $res = $usuario->consultarUsuario( $_REQUEST['id'] );

                if( $res->rowCount() == 1 ){ // Si el usuario existe, se procede con la actualización de datos
                    
                    $row = $res->fetch();
                    $saldo = (int)$row['saldo'];

                    // Validación para solo realizar inactivaciones de usuarios para quienes tienen saldo en cero
                    if ( $saldo  == 0 ){
                        
                        $usuario->inactivarUsuario( $_REQUEST['id'] );

                        // Se realiza una consulta para verificar la existencia del usuario para creación
                        $resAct = $usuario->consultarUsuario( $_REQUEST['id'] );

                        if( $resAct->rowCount() == 0 ){ // Si el usuario existe, se procede con la actualización de datos
                            echo json_encode( array('mensaje' => 'El usuario con id: '.$_REQUEST['id'].' se encuentra inactivado en el sistema.')  );
                        }
                        else
                        {
                            echo json_encode( array('mensaje' => 'El usuario con id: '.$_REQUEST['id'].' aún se encuentra activo en el sistema.')  );
                        }
                        
                    }
                    else{ // Mensaje de error por no ser posible la inactivación
                        echo json_encode( array('mensaje' => 'El usuario con id: '.$_REQUEST['id'].' no puede ser inactivado, aún posee saldo en el sistema.')  );
                    }
                }
                else{
                    echo json_encode( array('mensaje' => 'No se encuentra disponible para inactivación en el sistema el usuario con id '.$_REQUEST['id'] ));
                }
            }


            break;
    }

?>
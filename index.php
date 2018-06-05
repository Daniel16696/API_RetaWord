<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php'; 

require 'vendor/autoload.php';
require 'conexion.php';
require 'modelos/usuarios.php';
require 'modelos/preguntas.php';
require 'modelos/imagenesAsociadas.php';
require 'modelos/partidasRegistradas.php';
// require 'PHPMailer/class.phpmailer.php';


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$c = new \Slim\Container();
$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $error = array('error' => $exception -> getMessage());
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write(json_encode($error));
    };
};
$app = new \Slim\App($c);

function sendOkResponse($message,$response){
    $newResponse = $response->withStatus(200)->withHeader('Content-Type','application/json');
    $newResponse ->getBody()->write($message);
    return $newResponse;
}

function randomGen($min, $max, $quantity) {
    $numbers = range($min, $max);
    shuffle($numbers);
    return array_slice($numbers, 0, $quantity);
}

$app->post('/nuevoUsuario',function(Request $request, Response $response, $args){
    $datos = $request -> getParsedBody();
    var_dump($datos);
    $Usuario = new Usuarios();
    $Usuario -> nickname = $datos['nickname'];
    $Usuario -> imagenAsociada = $datos['imagenAsociada'];
    $Usuario -> victoriasRondas = $datos['victoriasRondas'];
    $Usuario -> derrotasRondas = $datos['derrotasRondas'];
    $Usuario -> victoriaPorcentaje = $datos['victoriaPorcentaje'];
    $Usuario -> sala = $datos['sala'];
    $Usuario -> idContrincante = $datos['idContrincante'];
    $Usuario -> ocupado = $datos['ocupado'];
    $Usuario -> IdAsignacionDePregunta = $datos['IdAsignacionDePregunta'];
    $Usuario -> contadorTemporalDeAciertos = $datos['contadorTemporalDeAciertos'];
    $Usuario -> respuestasDelUsuarioTemporal = $datos['respuestasDelUsuarioTemporal'];
    $Usuario -> IconosDeRespuestasDelUsuarioTemporal = $datos['IconosDeRespuestasDelUsuarioTemporal'];
    
    var_dump($Usuario);
    $Usuario -> save();
});

$app->get('/obtenerUsuariosEnConcreto/{id}', function (Request $request, Response $response, $args) {
    // Para devolverlos todos los usuarios
    // $Usuarios = Usuarios::get();

    // para meterle un where y solo devolver lo que queramos
    $Usuarios = Usuarios::where('id', '=', $args['id'])->get();

    // imprime en json cada vez que viene aqui
    // print_r($Usuarios -> toJson());

    // mas recomendado usar esta forma
    return sendOkResponse($Usuarios ->toJson(),$response);
});

// obtener el ususario en concreto por vía nickname
$app->get('/obtenerUsuariosEnConcretoPorNickName/{nickname}', function (Request $request, Response $response, $args) {
    try{
        $Usuarios = Usuarios::where('nickname', '=', $args['nickname'])->get();
        return sendOkResponse($Usuarios ->toJson(),$response);
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});

// Recoger todos los usuarios rescatando quien tiene mas victorias de rondas
$app->get('/obtenerTodosUsuariosRanking', function (Request $request, Response $response, $args) {
    try{
        $Usuarios = Usuarios::OrderBy('victoriasRondas', 'DESC')->get();
        return sendOkResponse($Usuarios ->toJson(),$response);
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});

// eliminar la cuenta del usuario por lo que borra el usuario del localStorage y a la vez el usuario que tiene en la base de datos
$app->delete('/borrarUsuarioConfiguracion/{id}', function ($request, $response, $args) use($app){
    try{
        $UsuarioBorrado = Usuarios::where('id', '=', $args['id'])->delete();
        if ($UsuarioBorrado) {
            echo json_encode(array('status'=> 'success', 'message' =>'Borrado correctamentee'));
        }else{
            throw new Exception("Error al borrar el usuario");
        }
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});

// $app->post('/enviarEmailSugerencias',function(Request $request, Response $response, $args){
   
//     $mail = new PHPMailer(); // create a new object
//     $mail->IsSMTP(); // enable SMTP
//     $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
//     $mail->SMTPAuth = true; // authentication enabled
//     $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
//     $mail->Host = "smtp.gmail.com";
//     $mail->Port = 465; // or 587
//     $mail->IsHTML(true);
//     $mail->Username = "infoxdgaxdupportplays@gmail.com";
//     $mail->Password = "2C\L%T-Z5p4vS1Thh;v^";
//     $mail->SetFrom("infoxdgaxdupportplays@gmail.com");
//     $mail->Subject = "Test";
//     $mail->Body = "hello";
//     $mail->AddAddress("xdgaxd@gmail.com");
    
//      if(!$mail->Send()) {
//         echo "Mailer Error: " . $mail->ErrorInfo;
//      } else {
//         echo "Message has been sent";
//      }
// });
// Enviar un mensaje de sugerencia del usuario a mi correo 
$app->post('/enviarEmailSugerencias',function(Request $request, Response $response, $args){
    //Load composer's autoloader
    require_once('PHPMailer/PHPMailerAutoload.php'); 
    $datos = $request -> getParsedBody();
    $mail = new PHPMailer(true); 
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->SMTPDebug = 2; 
    $mail->IsSMTP();
    $mail->Timeout = 60;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;// TCP port to connect to
    $mail->CharSet = 'UTF-8';
    $mail->Username ='infoxdgaxdupportplays@gmail.com'; //Email que usa para enviar mensajes
    $mail->Password = '2C\L%T-Z5p4vS1Thh;v^'; //Su password
    //Agregar destinatario
    $mail->setFrom('infoxdgaxdupportplays@gmail.com', 'Soporte de RetaWord');
    $mail->AddAddress('infoxdgaxdupportplays@gmail.com');//El usuario al que manda el email
    $mail->SMTPKeepAlive = true;  
    $mail->Mailer = "smtp"; 

    //Content
    $mail->isHTML(true); // Set email format to HTML

    $mail->Subject = 'RETAWORD: Sugerencia de categoría';
    // $mail->Body    = 'Esta es la sugerencia que ha tenido un usuario: <br><b>Comentario 1:</b> '.$datos['comentario1'].'<br> <b>Comentario 2:</b> '.$datos['comentario2'];
    $mail->Body    = 'Esta es la sugerencia que ha tenido un usuario: <br><b>Comentario :</b> '.$datos['comentario'];
    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if(!$mail->send()) {
        echo 'Error al enviar email';
        echo 'Mailer error: ' . $mail->ErrorInfo;
    } else {
        echo 'Mail enviado correctamente';
    }
});

// Enviar un mensaje de contacto del usuario a mi correo 
$app->post('/enviarEmailContactanos',function(Request $request, Response $response, $args){
    //Load composer's autoloader
    require_once('PHPMailer/PHPMailerAutoload.php'); 
    $datos = $request -> getParsedBody();
    $mail = new PHPMailer(true); 
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->SMTPDebug = 2; 
    $mail->IsSMTP();
    $mail->Timeout = 60;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;// TCP port to connect to
    $mail->CharSet = 'UTF-8';
    $mail->Username ='infoxdgaxdupportplays@gmail.com'; //Email que usa para enviar mensajes
    $mail->Password = '2C\L%T-Z5p4vS1Thh;v^'; //Su password
    //Agregar destinatario
    $mail->setFrom('infoxdgaxdupportplays@gmail.com', 'Soporte de RetaWord');
    $mail->AddAddress('infoxdgaxdupportplays@gmail.com');//El usuario al que manda el email
    $mail->SMTPKeepAlive = true;  
    $mail->Mailer = "smtp"; 

    //Content
    $mail->isHTML(true); // Set email format to HTML

    $mail->Subject = 'RETAWORD: Email de contacto del usuario: '.$datos['nombre'];
    $mail->Body    = 'Email para contactarnos: <br><b>Nombre :</b> '.$datos['nombre'].'<br><b>Email :</b> '.$datos['email'].'<br><b>Comentario :</b> '.$datos['comentario'];

    if(!$mail->send()) {
        echo 'Error al enviar email';
        echo 'Mailer error: ' . $mail->ErrorInfo;
    } else {
        echo 'Mail enviado correctamente';
    }
});
// EDITAR EL NICKNAME DEL USUARIO
$app->put('/cambiarNicknameDelUsuario/{id}', function (Request $request, Response $response, $args) {
    $datos = $request -> getParsedBody();
    $Usuario = Usuarios::find($args['id']);
    $Usuario -> nickname = $datos['nickname'];
    $Usuario -> imagenAsociada = $datos['imagenAsociada'];
    $Usuario -> victoriasRondas = $datos['victoriasRondas'];
    $Usuario -> derrotasRondas = $datos['derrotasRondas'];
    $Usuario -> victoriaPorcentaje = $datos['victoriaPorcentaje'];
    $Usuario -> sala = $datos['sala'];
    $Usuario -> idContrincante = $datos['idContrincante'];
    $Usuario -> ocupado = $datos['ocupado'];
    $Usuario -> IdAsignacionDePregunta = $datos['IdAsignacionDePregunta'];
    $Usuario -> contadorTemporalDeAciertos = $datos['contadorTemporalDeAciertos'];
    $Usuario -> respuestasDelUsuarioTemporal = $datos['respuestasDelUsuarioTemporal'];
    $Usuario -> IconosDeRespuestasDelUsuarioTemporal = $datos['IconosDeRespuestasDelUsuarioTemporal'];
    $Usuario -> save();
    return sendOkResponse($Usuario ->toJson(),$response);
});

// Cambiar el estado de Conectado del ususario
$app->put('/cambiarElEstadoDeConectado/{id}', function (Request $request, Response $response, $args) {

    $datos = $request -> getParsedBody();
    $Usuario = Usuarios::find($args['id']);
    $Usuario -> nickname = $datos['nickname'];
    $Usuario -> imagenAsociada = $datos['imagenAsociada'];
    $Usuario -> victoriasRondas = $datos['victoriasRondas'];
    $Usuario -> derrotasRondas = $datos['derrotasRondas'];
    $Usuario -> victoriaPorcentaje = $datos['victoriaPorcentaje'];
    $Usuario -> sala = $datos['sala'];
    $Usuario -> idContrincante = $datos['idContrincante'];
    $Usuario -> ocupado = $datos['ocupado'];
    $Usuario -> IdAsignacionDePregunta = $datos['IdAsignacionDePregunta'];
    $Usuario -> contadorTemporalDeAciertos = $datos['contadorTemporalDeAciertos'];
    $Usuario -> respuestasDelUsuarioTemporal = $datos['respuestasDelUsuarioTemporal'];
    $Usuario -> IconosDeRespuestasDelUsuarioTemporal = $datos['IconosDeRespuestasDelUsuarioTemporal'];
    $Usuario -> save();
    return sendOkResponse($Usuario ->toJson(),$response);

});

// Obteniendo la pregunta que se le va asignar a los dos usuarios para que estén vinculados.
$app->get('/obtenerLaPreguntaAsignada/{idSala}', function (Request $request, Response $response, $args) {
    try{
        $Preguntas = Preguntas::get();
        $CountPreguntas = Preguntas::count();

        $Usuario = Usuarios::where([
            ['sala', '=', $args['idSala']],
            ['ocupado', '=', '1'],
            ['IdAsignacionDePregunta', '=', '0']
        ])->get();

        $array = randomGen(1,$CountPreguntas,1);

        foreach ($Usuario as &$valor) {

            $datos = $request -> getParsedBody();
            $Usuario = Usuarios::find($valor["id"]);
            $Usuario -> IdAsignacionDePregunta = $array[0];
            $Usuario -> save();

        }
        return sendOkResponse($Usuario ->toJson(),$response);
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});

// Obtener todos los datos de la pregunta a la que se va a jugar para mostrar en la partida
$app->get('/obtenerLaPreguntaDefinitiva/{idPregunta}', function (Request $request, Response $response, $args) {
    try{
        $Pregunta = Preguntas::where('id', '=', $args['idPregunta'])->get();
        return sendOkResponse($Pregunta ->toJson(),$response);
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});

// BUSCAR USUARIOS ENTRELAZADOS PARA ENVIARLOS A JUGAR 
$app->get('/buscarSalaNoOcupada/{id}', function (Request $request, Response $response, $args) {
    try{
        $ArraySalaNoOcupada = [];
        //$Usuario = Usuarios::get();
        $CountUsuarios = Usuarios::count();
    
        // $array = randomGen(1,$CountUsuarios,1);
        
        // foreach ($array as &$valor) {
            $Usuario = Usuarios::where([
                ['id', '!=',  $args['id']],
                ['sala', '!=', '0'],
                ['idContrincante', '=', '0'],
                ['ocupado', '=', '0']
            ])->limit(1)->get();
            return sendOkResponse($Usuario ->toJson(),$response);
            // array_push($ArraySalaNoOcupada,$Usuario[0]);
    
        // }
        
        // return json_encode(array_values($ArraySalaNoOcupada));
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }

});

// Esperando a que se llene la sala para mandarlo a partida
$app->get('/esperarAsalaCompleta/{idSala}/{idUsuario}', function (Request $request, Response $response, $args) {
    try{
        $Usuario = Usuarios::where([
            ['sala', '=',  $args['idSala']],
            ['idContrincante', '=',  $args['idUsuario']]
        ])->get();

        return json_encode($Usuario);
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }

});

// obtener la pregunta y sus respuestas para el boton de revelar respuestas
$app->get('/relevarRespuestasDeLaPreguntaJugada/{idPregunta}', function (Request $request, Response $response, $args) {
    try{
        $Pregunta = Preguntas::where('id', '=', $args['idPregunta'])->get();
        return sendOkResponse($Pregunta ->toJson(),$response);
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});

// Obtener todas las imagenes para mostrar al elegir imagen asociada al usuario
$app->get('/obtenerImagenes', function (Request $request, Response $response, $args) {
    try{
        $Imagenes = Imagenes::get();
        return sendOkResponse($Imagenes ->toJson(),$response);
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});

// Hacer un registro de partida, para posteriormente rescatar las tres ultimas 
$app->post('/registrarPartida',function(Request $request, Response $response, $args){
    try{
        $datos = $request -> getParsedBody();
        var_dump($datos);
        $Partida = new Partidas();
        $Partida -> idUsuario = $datos['idUsuario'];
        $Partida -> idUsuarioContrincante = $datos['idUsuarioContrincante'];
        $Partida -> nicknameUsuarioContrincante = $datos['nicknameUsuarioContrincante'];
        $Partida -> imagenUsuarioContrincante = $datos['imagenUsuarioContrincante'];
        $Partida -> EstadoDePartida = $datos['EstadoDePartida'];
        $Partida -> PalabraDelEstadoDeLaPartida = $datos['PalabraDelEstadoDeLaPartida'];
        $Partida -> ContadorDelUsuarioAplicacion = $datos['ContadorDelUsuarioAplicacion'];
        $Partida -> ContadorDelUsuarioContrincante = $datos['ContadorDelUsuarioContrincante'];
        $Partida -> fechaDeLaPartida = $datos['fechaDeLaPartida'];
        var_dump($Partida);
        $Partida -> save();
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }
});

// Rescatar las tres ultimas partidas por fecha y hora
$app->get('/cogerTresUltimasPartidas/{idUsuario}', function (Request $request, Response $response, $args) {
    try{
        $Partida = Partidas::OrderBy('fechaDeLaPartida', 'DESC')->where([
            ['idUsuario', '=',  $args['idUsuario']]
        ])->limit(3)->get();

        // elimino todos los registros menos los tres ultimos que se van a mostrar en el cliente
        // for ($x = 3; $x <= count($Partida) ; $x++) {

        //     // echo $Partida[$x]["id"];
        //     $PartidaBorrada = Partidas::where('id', '=', $Partida[$x]["id"])->delete();

        // }

        return sendOkResponse($Partida ->toJson(),$response);
        
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }

});

$app->get('/contraQuienHaJugadoMas/{idUsuario}', function (Request $request, Response $response, $args) {
    try{
// $Partida = Partidas::where('idUsuario', $args['idUsuario'])->groupBy('idUsuarioContrincante')->OrderBy('idUsuarioContrincante','DESC')->get();
$Partida = Partidas::where('idUsuario', $args['idUsuario'])->selectRaw('idUsuarioContrincante')->groupBy('idUsuarioContrincante')->get();
        // elimino todos los registros menos los tres ultimos que se van a mostrar en el cliente
        // for ($x = 3; $x <= count($Partida) ; $x++) {

        //     // echo $Partida[$x]["id"];
        //     $PartidaBorrada = Partidas::where('id', '=', $Partida[$x]["id"])->delete();

        // }

        return sendOkResponse($Partida ->toJson(),$response);
        
    }catch(Exception $e){
        $app -> status(400);
        echo json_encode(array('status'=> 'error', 'message' => $e ->getMessage()));
    }

});

$app->run();
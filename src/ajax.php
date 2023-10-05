<?php
require_once "../conexion.php";
session_start();
//--- esto me muestra en pedido
if (isset($_GET['detalle'])) {
    $id = $_SESSION['idUser'];
    $datos = array();

    // Consulta para obtener detalles de platos
    $platosDetalle = mysqli_query($conexion, "SELECT d.*, p.nombre, p.precio, p.imagen FROM temp_pedidos d INNER JOIN platos p ON d.id_producto = p.id WHERE d.id_usuario = $id");

    // Consulta para obtener detalles de baguettes
    $baguettesDetalle = mysqli_query($conexion, "SELECT d.*, b.nombre, b.precio, b.imagen FROM temp_pedidos d INNER JOIN baguette b ON d.id_producto = b.id WHERE d.id_usuario = $id");

    // Recopila los detalles de platos en el arreglo de datos
    while ($row = mysqli_fetch_assoc($platosDetalle)) {
        $data['id'] = $row['id'];
        $data['nombre'] = $row['nombre'];
        $data['cantidad'] = $row['cantidad'];
        $data['precio'] = $row['precio'];
        $data['imagen'] = ($row['imagen'] == null) ? '../assets/img/default.png' : $row['imagen'];
        $data['total'] = $data['precio'] * $data['cantidad'];
        array_push($datos, $data);
    }

    // Recopila los detalles de baguettes en el arreglo de datos
    while ($row = mysqli_fetch_assoc($baguettesDetalle)) {
        $data['id'] = $row['id'];
        $data['nombre'] = $row['nombre'];
        $data['cantidad'] = $row['cantidad'];
        $data['precio'] = $row['precio'];
        $data['imagen'] = ($row['imagen'] == null) ? '../assets/img/default.png' : $row['imagen'];
        $data['total'] = $data['precio'] * $data['cantidad'];
        array_push($datos, $data);
    }

    echo json_encode($datos);
    die();
}

 else if (isset($_GET['delete_detalle'])) {
    $id_detalle = $_GET['id'];
    $query = mysqli_query($conexion, "DELETE FROM temp_pedidos WHERE id = $id_detalle");
    if ($query) {
        $msg = "ok";
    } else {
        $msg = "Error";
    }
    echo $msg;
    die();
} 




// ------- esta parte del codigo realiza el pedido y la finacilazcion del pedido
else if (isset($_GET['procesarPedido'])) {
    $id_sala = $_GET['id_sala'];
    $id_user = $_SESSION['idUser'];
    $mesa = $_GET['mesa'];
    $observacion = $_GET['observacion'];
    
    // Consulta para obtener detalles de platos
    $consultaPlatos = mysqli_query($conexion, "SELECT d.*, p.nombre FROM temp_pedidos d INNER JOIN platos p ON d.id_producto = p.id WHERE d.id_usuario = $id_user");
    
    // Consulta para obtener detalles de baguettes
    $consultaBaguettes = mysqli_query($conexion, "SELECT d.*, p.nombre FROM temp_pedidos d INNER JOIN baguette p ON d.id_producto = p.id WHERE d.id_usuario = $id_user");
    
    $total = 0;
    while ($row = mysqli_fetch_assoc($consultaPlatos)) {
        $total += $row['cantidad'] * $row['precio'];
    }
    
    while ($row = mysqli_fetch_assoc($consultaBaguettes)) {
        $total += $row['cantidad'] * $row['precio'];
    }
    
    $insertar = mysqli_query($conexion, "INSERT INTO pedidos (id_sala, num_mesa, total, observacion, id_usuario) VALUES ($id_sala, $mesa, '$total', '$observacion', $id_user)");
    
    $id_pedido = mysqli_insert_id($conexion);
    
    if ($insertar == 1) {
        $consultaPlatos = mysqli_query($conexion, "SELECT d.*, p.nombre FROM temp_pedidos d INNER JOIN platos p ON d.id_producto = p.id WHERE d.id_usuario = $id_user");
        
        $consultaBaguettes = mysqli_query($conexion, "SELECT d.*, p.nombre FROM temp_pedidos d INNER JOIN baguette p ON d.id_producto = p.id WHERE d.id_usuario = $id_user");
        
        while ($dato = mysqli_fetch_assoc($consultaPlatos)) {
            $nombre = $dato['nombre'];
            $cantidad = $dato['cantidad'];
            $precio = $dato['precio'];
            $insertarDet = mysqli_query($conexion, "INSERT INTO detalle_pedidos (nombre, precio, cantidad, id_pedido) VALUES ('$nombre', '$precio', $cantidad, $id_pedido)");
        }
        
        while ($dato = mysqli_fetch_assoc($consultaBaguettes)) {
            $nombre = $dato['nombre'];
            $cantidad = $dato['cantidad'];
            $precio = $dato['precio'];
            $insertarDet = mysqli_query($conexion, "INSERT INTO detalle_pedidos (nombre, precio, cantidad, id_pedido) VALUES ('$nombre', '$precio', $cantidad, $id_pedido)");
        }
        
        if ($insertarDet > 0) {
            $eliminar = mysqli_query($conexion, "DELETE FROM temp_pedidos WHERE id_usuario = $id_user");
            $sala = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id_sala");
            $resultSala = mysqli_fetch_assoc($sala);
            $msg = array('mensaje' => $resultSala['mesas']);
        }
    } else {
        $msg = array('mensaje' => 'error');
    }

// para editar tablas
    echo json_encode($msg);
    die();
} else if (isset($_GET['editarUsuario'])) {
    $idusuario = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id = $idusuario"); 
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;

    } else if (isset($_GET['editarSalas'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id"); 
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;


} else if (isset($_GET['editarProducto'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM platos WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
    

} else if (isset($_GET['editarProducto2'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM baguette WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;

    //----finalizar el pedido
} else if (isset($_GET['finalizarPedido'])) {
    $id_sala = $_GET['id_sala'];
    $id_user = $_SESSION['idUser'];
    $mesa = $_GET['mesa'];
    $insertar = mysqli_query($conexion, "UPDATE pedidos SET estado='FINALIZADO' WHERE id_sala=$id_sala AND num_mesa=$mesa AND estado='PENDIENTE' AND id_usuario=$id_user");
    if ($insertar) {
        $sala = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id_sala");
        $resultSala = mysqli_fetch_assoc($sala);
        $msg = array('mensaje' => $resultSala['mesas']);
    } else {
        $msg = array('mensaje' => 'error');
    }

    echo json_encode($msg);
    die();
}







//-------------esto es para seleccionar el producto desde la sala,mesa,platos
if (isset($_POST['regDetalle'])) {
    $id_producto = $_POST['id'];
    $id_user = $_SESSION['idUser'];
    
    // Check if the product exists in 'temp_pedidos'
    $consulta = mysqli_query($conexion, "SELECT * FROM temp_pedidos WHERE id_producto = $id_producto AND id_usuario = $id_user");
    $row = mysqli_fetch_assoc($consulta);
    
    if (empty($row)) {
        // Product doesn't exist in 'temp_pedidos', so let's fetch its data
        $producto = mysqli_query($conexion, "SELECT * FROM platos WHERE id = $id_producto");
        
        // If 'platos' doesn't have the product, try 'baguette'
        if (mysqli_num_rows($producto) == 0) {
            $producto = mysqli_query($conexion, "SELECT * FROM baguette WHERE id = $id_producto");
        }
        // Fetch the product data and calculate the price
        $result = mysqli_fetch_assoc($producto);
        $precio = $result['precio'];

        // Insert the product into 'temp_pedidos'
        $query = mysqli_query($conexion, "INSERT INTO temp_pedidos (cantidad, precio, id_producto, id_usuario) VALUES (1, $precio, $id_producto, $id_user)");
    } else {
        // Update the existing product quantity
        $nueva = $row['cantidad'] + 1;
        $query = mysqli_query($conexion, "UPDATE temp_pedidos SET cantidad = $nueva WHERE id_producto = $id_producto AND id_usuario = $id_user");
    }

    if ($query) {
        $msg = "registrado";
    } else {
        $msg = "Error al ingresar";
    }

    echo json_encode($msg);
    die();
}

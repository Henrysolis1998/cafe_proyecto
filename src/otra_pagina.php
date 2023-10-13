<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    require_once "../conexion.php";
    $id_user = $_SESSION['idUser'];
    $query = mysqli_query($conexion, "SELECT dp.id, dp.nombre, dp.precio, dp.cantidad, p.estado AS estado_pedido FROM detalle_pedidos dp INNER JOIN pedidos p ON dp.id_pedido = p.id;");
    include_once "includes/header.php";
?>
    <div class="card">
        <div class="card-header">
            Historial pedidos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tbl">
                    <thead>
                        <tr>
                        <th>id</th>
                            <th>Nombre</th>
                            <th>precio</th>
                            <th>Cantidad</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($query)) {
                        ?>
                            <tr>
                                 <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nombre']; ?></td>
                                <td><?php echo $row['precio']; ?></td>
                                <td><?php echo $row['cantidad']; ?></td>
                                <td><?php echo $row['estado_pedido']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
}
?>
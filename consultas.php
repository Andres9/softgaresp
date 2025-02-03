<?php include_once 'encabezado.php'?>


<section>

    <h2>Consultas Personalizadas</h2>

    <form >
      <label>Mostrar</label>
       <select name="" id="tabla" class="select">
        <option value="">Selecciona una opcion</option>
        <option value="notas">Notas</option>
        <option value="ventas">Ventas</option>
      </select> 
      <button type="submit" onclick="buscar_filtro($('#tabla').val())">Buscar</button>
    </form>

    <section id="resultado_busqueda"></section>



    <?php include_once 'footer.php'; ?>
    
    <script src="busquedaReporte.js"></script>
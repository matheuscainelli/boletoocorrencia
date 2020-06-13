<?php
    require 'header.php';

    !ConsultaPermissao('TAR_MANUTENCAO') ? require 'ocorrencia.php' : require 'dashboard.php';
?>
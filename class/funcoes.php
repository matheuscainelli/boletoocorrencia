<?php
function printr($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

function vardump($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

function QuotedStr($str) {
    return "'".addslashes($str)."'";
}

function RemoveAcento($str) {
    $comAcento = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ";
    $semAcento = "AAAAAAACEEEEIIIIDNOOOOOOUUUUYbsaaaaaaaceeeeiiiidnoooooouuuyybyRr";

    return utf8_encode(strtr(utf8_decode($str), utf8_decode($comAcento), $semAcento));
}

function Div($valor1, $valor2) {
    if (intval($valor1) === 0 || intval($valor2) === 0)
        return 0;

    return $valor1 / $valor2;
}

function GetSystemName() {
    $system = substr(dirname($_SERVER['PHP_SELF']), 1);
    $posEnd = strpos($system, '/') !== false ? strpos($system, '/') : strlen($system);
    $system = substr($system, 0, $posEnd);

    $system = preg_replace('@[^\w|^\-|^,|^\d]@', '', $system);

    return $system;
}

function ValidaData($data) {
    if (preg_match("/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/", $data)) {
        list($day, $month, $year) = explode('/', $data);

        return checkdate($month, $day, $year);
    } else if (preg_match("/^\d{4}(\-)(((0)[0-9])|((1)[0-2]))(\-)([0-2][0-9]|(3)[0-1])$/", $data)) {
        list($year, $month, $day) = explode('-', $data);

        return checkdate($month, $day, $year);
    }

    return false;
}

function ValidaDataHora($dataHora) {
    if (preg_match("/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}( )(([0-1][0-9])|((2)[0-3]))(:)(([0-4][0-9])|((5)[0-9]))(:)(([0-4][0-9])|((5)[0-9]))$/", $dataHora) ||
        preg_match("/^\d{4}(\-)(((0)[0-9])|((1)[0-2]))(\-)([0-2][0-9]|(3)[0-1])( )(([0-1][0-9])|((2)[0-3]))(:)(([0-4][0-9])|((5)[0-9]))(:)(([0-4][0-9])|((5)[0-9]))$/", $dataHora)) {
        return true;
    }

    return false;
}

function ValidaNumero($valor) {
    if (preg_match("/^(\d{1,3}\.)*?\d{1,3}\,\d{2}$/", $valor) ||
        preg_match("/^(\d*\.)?\d*$/", $valor)) {
        return true;
    }

    return false;
}

function ValidaInteger($valor) {
    if (preg_match("/^\d*$/", $valor) ||
        preg_match("/^(\d{1,3}\.)*?\d{1,3}$/", $valor)) {
        return true;
    }

    return false;
}

function FormataDataBD($data) {
    if (ValidaData($data)) {
        $data = implode('-', array_reverse(explode('/', $data)));

        return $data;
    }
}

function FormataDataUsuario($data) {
    if (ValidaData($data)) {
        $data = implode('/', array_reverse(explode('-', $data)));

        return $data;
    }
}

function FormataDataHoraBD($dataHora) {
    if (ValidaDataHora($dataHora)) {
        $arrDataHora = explode(' ', $dataHora);
        $data = FormataDataBD($arrDataHora[0]);
        if (isset($data)) {
            return $data.' '.$arrDataHora[1];
        }
    }
}

function FormataDataHoraUsuario($dataHora) {
    if (ValidaDataHora($dataHora)) {
        $arrDataHora = explode(' ', $dataHora);
        $data = FormataDataUsuario($arrDataHora[0]);
        if (isset($data)) {
            return substr($data.' '.$arrDataHora[1], 0, 16);
        }
    }
}

function FormataNumeroBD($valor) {
    if (ValidaNumero($valor)) {
        $valor = str_replace(['.', ','], [''], $valor);
        $valor = wordwrap($valor, strlen($valor) - 2, '.', true);

        return $valor;
    }
}

function FormataNumeroMoeda($valor) {
    if (ValidaNumero($valor)) {
        if (strpos($valor, ',') === false) {
            return number_format((float)$valor, 2, ',', '.');
        }

        return $valor;
    }
}

function FormataIntegerBD($valor) {
    if (ValidaInteger($valor)) {
        $valor = str_replace('.', '', $valor);

        return $valor;
    }
}

function FormataIntegerUsuario($valor) {
    if (ValidaInteger($valor)) {
        $valor = str_replace('.', '', $valor);

        return number_format((float)$valor, 0, '.', '.');
    }
}

function FormataValorUsuario($tpCampo, $val) {
    if (!empty($val)) {
        switch ($tpCampo) {
            case 'VL':
                $val = FormataNumeroMoeda($val);
                break;
            case 'QT':
                $val = FormataIntegerUsuario($val);
                break;
            case 'PC':
                $val = FormataNumeroMoeda($val);
                break;
            case 'DA':
                $val = FormataDataUsuario($val);
                break;
            case 'DT':
                $val = FormataDataHoraUsuario($val);
                break;
            case 'FL':
                $val = in_array(substr($val, 0, 1), ['S', 'N']) ? ($val === 'S' ? 'Sim' : 'Não') : null;
                break;
        }

        return $val;
    }
}
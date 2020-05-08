<?php

require_once './config/dbconfig.php';
require_once 'class/funcoes.php';
class Database {
    private static $conn;

    private static function AjustaCampoBinds(&$nmCampo) {
        if (substr($nmCampo, 0, 1) != ':') {
            $nmCampo = ":$nmCampo";
        }
    }

    private static function FormataValorBD($tpCampo, $val) {
        switch ($tpCampo) {
            case 'ID':
                $val = is_array($val) ? implode(',', $val) : $val;
                break;
            case 'FL':
                $val = (in_array(substr($val, 0, 1), ['S', 'N']) ? substr($val, 0, 1) : 'N');
                break;
            case 'VL':
                $val = FormataNumeroBD($val);
                break;
            case 'QT':
                $val = FormataIntegerBD($val);
                break;
            case 'PC':
                $val = FormataNumeroBD($val);
                break;
            case 'PW':
                $val = md5($val);
                break;
            case 'DA':
                $val = FormataDataBD($val);
                break;
            case 'DT':
                $val = FormataDataHoraBD($val);
                break;
            case 'IN':
                $val = str_replace([' ', "'"], '', $val);
                break;
        }

        return isset($val) && $val !== '' ? $val : null;
    }

    private static function AjustaValorTypeBinds($key, &$arr) {
        if (empty($arr[1]) || !in_array($arr[1], ['PARAM_INT', 'PARAM_BOOL', 'PARAM_NULL', 'PARAM_STR'])) {
            $arr = [$arr, null];
        }

        $arr[0] = Database::FormataValorBD(substr($key, 1, 2), $arr[0]);

        if (empty($arr[1])) {
            switch (true) {
                case is_int($arr[0]):
                    $type = 'PARAM_INT';
                    break;
                case is_bool($arr[0]):
                    $type = 'PARAM_BOOL';
                    break;
                case is_null($arr[0]):
                    $type = 'PARAM_NULL';
                    break;
                default:
                    $type = 'PARAM_STR';
                    break;
            }

            $arr[1] = $type;
        }
    }

    private static function AjustaBinds(&$arrBinds, &$arrBindsIn) {
        foreach ($arrBinds as $key => $arr) {
            unset($arrBinds[$key]);
            Database::AjustaCampoBinds($key);
            Database::AjustaValorTypeBinds($key, $arr);

            if (substr($key, 1, 2) == 'IN') {
                $keyOriginal = $key;
                $arrBindsIn[$key] = [];
                foreach (explode(',', $arr[0]) as $i => $val) {
                    $arrBinds[substr($key, 3).$i] = [$val, $arr[1]];
                    $arrBindsIn[$key][] = substr($key, 3).$i;
                }

                Database::AjustaBinds($arrBinds, $arrBindsIn);
                continue;
            }

            $arrBinds[$key] = $arr;
        }
    }

    private static function AjustaSQLBinds(&$sql, &$arrBinds) {
        Database::AjustaBinds($arrBinds, $arrBindsIn);
        if (!empty($arrBindsIn)) {
            foreach ($arrBindsIn as $key => $arr) {
                $sql = str_replace($key, ':'.implode(', :', $arr), $sql);
            }
        }
    }

    private static function AdicionaCamposPadrao(&$arrDados, $arrCamposPadrao) {
        foreach ($arrCamposPadrao as $nmCampo) {
            if (!isset($arrDados[$nmCampo])) {
                switch (substr($nmCampo, 0, 2)) {
                    case 'ID':
                        $val = Session::Get('IDUSUARIO');
                        break;
                    case 'DT':
                        $val = date('Y-m-d H:i:s');
                        break;
                }

                $arrDados[$nmCampo] = $val;
            }
        }
    }

    private static function MontaCamposArraySelect($arrVal, $arrCampos) {
        $arr = [];

        foreach ($arrCampos as $campo1 => $campo2) {
            if (is_array($campo2)) {
                $arr[$arrVal[$campo1]] = Database::MontaCamposArraySelect($arrVal, $campo2);
            } else {
                if (!is_numeric($campo1)) {
                    $arr[$arrVal[$campo1]] = $arrVal[$campo2];
                } else {
                    $arr[$campo2] = $arrVal[$campo2];
                }
            }
        }

        return $arr;
    }

    private static function MergeArraySelect(&$arr1, $arr2) {
        foreach ($arr2 as $campo1 => $campo2) {
            if (array_key_exists($campo1, $arr1)) {
                if (is_array($campo2)) {
                    Database::MergeArraySelect($arr1[$campo1], $campo2);
                } else {
                    $arr1[$campo1] = $campo2;
                }
            } else {
                $arr1 += $arr2;
            }
        }
    }

    public static function ConnectaBD() {
        $options = [
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT=>false
        ];

        Database::$conn = new PDO("mysql:host=".dbHost.";dbname=".dbName.";charset=utf8", dbUser, dbPassword, $options);
    }

   public static function DesconectaBD() {
        Database::$conn->close();
   }

    public static function Debug($sql, $arrBinds = []) {
        Database::AjustaSQLBinds($sql, $arrBinds);

        foreach ($arrBinds as $key => $arr) {
            switch ($arr[1]) {
                case 'PARAM_INT':
                    $sql = preg_replace("/$key\b/", $arr[0], $sql);
                    break;
                case 'PARAM_STR':
                default:
                    $sql = preg_replace("/$key\b/", QuotedStr($arr[0]), $sql);
                    break;
            }
        }

        return $sql;
    }

    public static function TransactionStart() {
        Database::ExecutaSQL("SET SESSION AUTOCOMMIT = 0");
    }

    public static function TransactionCommit() {
        Database::ExecutaSQL("COMMIT");
        Database::ExecutaSQL("SET SESSION AUTOCOMMIT = 1");
    }

    public static function ExecutaSQL($sql, $arrBinds = []) {
        Database::AjustaSQLBinds($sql, $arrBinds);
        $stmt = Database::$conn->prepare($sql);

        if (!empty($arrBinds)) {
            foreach ($arrBinds as $key => $arr) {
                switch ($arr[1]) {
                    case 'PARAM_INT':
                        $type = PDO::PARAM_INT;
                        break;
                    case 'PARAM_BOOL':
                        $type = PDO::PARAM_BOOL;
                        break;
                    case 'PARAM_NULL':
                        $type = PDO::PARAM_NULL;
                        break;
                    default:
                        $type = PDO::PARAM_STR;
                        break;
                }

                $stmt->bindValue($key, $arr[0], $type);
            }
        }

        $stmt->execute();

        return $stmt;
    }

    public static function ExecutaSQLDados($sql, $arrBinds = []) {
        $result = Database::ExecutaSQL($sql, $arrBinds);

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function MontaArraySelectDinamico($sql, $arrBinds = [], $arrCampos) {
        $result = Database::ExecutaSQL($sql, $arrBinds);

        $arr = [];
        foreach ($result as $seq => $arrVal) {
            Database::MergeArraySelect($arr, Database::MontaCamposArraySelect($arrVal, $arrCampos));
        }

        return $arr;
    }

    public static function MontaArraySelect($sql, $arrBinds = [], $campo1, $campo2) {
        return Database::MontaArraySelectDinamico($sql, $arrBinds, [$campo1=>$campo2]);
    }

    public static function MontaArrayNCampos($sql, $arrBinds, $campo1, $campo2 = []) {
        return Database::MontaArraySelectDinamico($sql, $arrBinds, [$campo1=>$campo2]);
    }

    public static function MontaArrayChaveComposta($sql, $arrBinds, $campo1, $campo2, $campo3 = []) {
        return Database::MontaArraySelectDinamico($sql, $arrBinds, [$campo1=>[$campo2=>$campo3]]);
    }

    public static function BuscaMaxSequencia($nmTabela) {
        $nmCampo = 'ID'.strtoupper($nmTabela);
        $sql = "SELECT COALESCE(MAX(pa.$nmCampo), 0)+1 IDSEQUENCIA
                FROM $nmTabela pa";
        $result = Database::ExecutaSQLDados($sql);

        return $result[0]['IDSEQUENCIA'] ?? null;
    }

    public static function Insere($nmTabela, $arrDados) {
        Database::AdicionaCamposPadrao($arrDados, ['IDUSUARIOINC', 'DTINCLUSAO', 'IDUSUARIOALT', 'DTALTERACAO']);

        $arrBinds = $arrDados;
        $arrCampos = array_keys($arrDados);

        $sql = sprintf("INSERT INTO $nmTabela (%s)
                            VALUES (:%s)",
                        implode(', ', $arrCampos), implode(', :', $arrCampos));
        Database::ExecutaSQL($sql, $arrBinds);
    }

    public static function Edita($nmTabela, $arrDados, $arrDadosWhere) {
        Database::AdicionaCamposPadrao($arrDados, ['IDUSUARIOALT', 'DTALTERACAO']);
        $arrBinds = array_merge($arrDados, $arrDadosWhere);
        $arrCampos = [];
        $arrCamposWhere = [];

        foreach ($arrDados as $key => $value) {
            $arrCampos[] = "$key = :$key";
        }

        foreach ($arrDadosWhere as $key => $value) {
            $arrCamposWhere[] = "$key = :$key";
        }

        $sql = sprintf("UPDATE $nmTabela
                        SET %s
                        WHERE %s",
                        implode(', ', $arrCampos), implode(' AND ', $arrCamposWhere));
        Database::ExecutaSQL($sql, $arrBinds);
    }

    public static function Deleta($nmTabela, $arrDadosWhere) {
        $arrBinds = $arrDadosWhere;
        $arrCampos = [];

        foreach ($arrDadosWhere as $key => $value) {
            $arrCampos[] = "$key = :$key";
        }

        $sql = sprintf("DELETE
                        FROM $nmTabela
                        WHERE %s",
                        implode(' AND ', $arrCampos));
        Database::ExecutaSQL($sql, $arrBinds);
    }
}

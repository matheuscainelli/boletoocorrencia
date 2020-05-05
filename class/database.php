<?php
class Database {
    public static function ConectaBD() {
        try {
            $banco = $db != "" ? ";dbname=$db" : "";
            $conn = new PDO("mysql:host="dbHost.";","dbname=".dbName."", dbUser, dbPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (defined( 'PDO::MYSQL_ATTR_MAX_BUFFER_SIZE'))
                $conn->setAttribute(PDO::MYSQL_ATTR_MAX_BUFFER_SIZE, 1024*1024*50);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, $multipleQuery);
    
        } catch (PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }

    public static function DesconectaBD($conn) {
        $conn->close();
    }

    public static function Insere($tabela, $campos) {

    }

    public static function Edita($tabela, $campos, $where = array()) {

    }

    public static function Deleta($tabela, $where = array()) {

    }

    public static function ExecutaSql($sql, $arrBinds) {

    }

    public static function ExecutaSqlDados($sql, $arrBinds) {

    }

    public static function MontaArraySelect($sql, $arrBinds = array(), $chave, $chave) {
        $result = Database::ExecutaSqlDados($sql, $arrBinds);
        $array = array();

        for ($i=0; $i < count($result); $i++) {
            $array[$result[$i][$chave]] = $result[$i][$chave];
        }

        if (!isset($array)) {
            $array = array();
        }

        return $array;
    }

    public static function is_date($data) {
        if ($data && strpos($data, "-")) {
            $arr = explode("-", $data);
            return checkdate(intval($arr[1]), intval($arr[2]), intval($arr[0]));
        } else
            return false;
    }

    public static function is_datetime($date, $strict = true){
        $dateTime = new DateTime(date('Y-m-d H:i:s', strtotime($date)));
        return $dateTime->format('Y-m-d H:i:s') == $date;
    }

    public static function TrataCampos($tabela, $campos, $pdoDataType = array()) {
        $fields =  Database::MontaArraySelect("SHOW FIELDS FROM $tabela", "Field", 'Type');
        $types = Database::MontaArraySelect("SHOW FIELDS FROM $tabela", "Field", "Key");

        foreach ($campos as $campo=>$valor) {
            if (in_array($campo, array_keys($fields))) {
                $columns[] = $campo;
                $stop = strpos($fields[$campo], "(") ? strpos($fields[$campo], "(") : strlen($fields[$campo]);
                $value = $valor;
                if ($valor !== NULL) {
                    switch (substr($fields[$campo], 0, $stop)) {
                        case 'decimal':
                        case 'int':
                            if (!is_numeric($value))
                                $value = FormataNumeroBD($valor);
                            break;
                        case 'datetime':
                        case 'timestamp':
                            if (!Database::is_datetime($value))
                                $value = QuotedStr(FormataDataHoraBD($valor));
                            else
                                $value = QuotedStr($valor);
                            break;
                        case 'date':
                            if (!Database::is_date($value))
                                $value = QuotedStr(FormataDataBD($valor));
                            else
                                $value = QuotedStr($valor);
                            break;
                        default:
                                $value = QuotedStr($valor);
                            break;
                    }
                } else
                    $value = null;

                $values[] = $value;
                $arrBinds[":".$campo] = str_replace("'", "", $value);
                $arrBindsDataType[":".$campo] = array($pdoDataType[$campo] == 'integer' ? intval(str_replace("'", "", $value)) : str_replace("'", "", $value), $pdoDataType[$campo] == 'integer' ? 'PARAM_INT' : 'PARAM_STR');
                $arrBindsUpdateField[$campo] = $campo." = :".$campo;
            } else {
                if ($GLOBALS['errorDatabase'] !== false)
                    die("<br/>$campo não existe na tabela '$tabela'");
            }
        }

        return array('campos'=>$columns, 'valores'=>$values, 'chave'=>$chave, 'binds'=>$arrBinds, 'bindsUpdateField'=>$arrBindsUpdateField, 'bindsDataType'=>$arrBindsDataType);
    }
}
?>
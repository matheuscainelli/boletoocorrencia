<?php

class HTML {
    private static function AjustaAttrDiv($id, $attrDiv) {
        $id = $attrDiv['id'] ?? "div$id";
        $class = $attrDiv['class'] ?? 'col-auto';

        return array_merge(compact('id', 'class'), $attrDiv);
    }

    private static function AjustaAttrLabel($id, $attrLabel) {
        $for = $attrLabel['for'] ?? $id;
        $id = $attrLabel['id'] ?? "label$id";

        return array_merge(compact('for', 'id'), $attrLabel);
    }

    private static function MontaHtmlElemento($elmt, $caption, $attrElmt, $attrDiv, $attrLabel) {
        $str = '';

        if (!is_null($caption)) {
            $id = $attrElmt['id'];
            $flrequired = $attrElmt['required'] ?? false;
            $attrDiv = self::AjustaAttrDiv($id, $attrDiv);
            $attrLabel = self::AjustaAttrLabel($id, $attrLabel);

            $str .= "<div ".self::MontaAttrHtml($attrDiv).">";
                if (isset($attrElmt['type']) && $attrElmt['type'] == 'checkbox') {
                    $str .= $elmt;
                    unset($elmt);
                }

                $str .= "<label ".self::MontaAttrHtml($attrLabel).">$caption".($flrequired && !empty(trim($caption)) ? "<span class=\"is-required\">*</span>" : null)."</label>";

                if (isset($elmt)) {
                    $str .= $elmt;
                }
            $str .= "</div>";

            return $str;
        }

        return $elmt;
    }

    public static function AjustaHTMLDados($html, $arr = []) {
        if (!empty($arr)) {
            return str_replace(array_map(function($key) { return ":$key:"; }, array_keys($arr)), array_values($arr), $html);
        }

        return $html;
    }

    public static function MontaAttrHtml($arrAttr) {
        if (array_key_exists('required', $arrAttr)) {
            $arrAttr['data-required'] = true;
        }

        $arr = [];
        foreach ($arrAttr as $name => $value) {
            $attr = null;
            switch (true) {
                case is_bool($value) && $name !== 'value':
                    $attr = $value ? "$name=\"$name\"" : null;
                    break;
                case !is_null($value):
                    $attr = "$name=\"$value\"";
                    break;
            }

            if (!is_null($attr)) {
                $arr[] = $attr;
            }
        }

        return !empty($arr) ? implode(' ', $arr) : null;
    }

    public static function GetLargura($tipo = null) {
        switch ($tipo) {
            case 1:
                return 'col-lg-1 col-md-2 col-sm-2 col-xs-4';
            case 2:
                return 'col-lg-2 col-md-3 col-sm-4 col-xs-8';
            case 3:
                return 'col-lg-3 col-md-4 col-sm-6 col-xs-12';
            case 4:
                return 'col-lg-4 col-md-5 col-sm-8 col-xs-12';
            case 5:
                return 'col-lg-5 col-md-6 col-sm-10 col-xs-12';
            case 6:
                return 'col-lg-6 col-md-7 col-sm-12 col-xs-12';
            case 7:
                return 'col-lg-7 col-md-8 col-sm-12 col-xs-12';
            case 8:
                return 'col-lg-8 col-md-9 col-sm-12 col-xs-12';
            case 9:
                return 'col-lg-9 col-md-10 col-sm-12 col-xs-12';
            case 10:
                return 'col-lg-10 col-md-11 col-sm-12 col-xs-12';
            case 11:
                return 'col-lg-11 col-md-12 col-sm-12 col-xs-12';
            case 12:
                return 'col-lg-12 col-md-12 col-sm-12 col-xs-12';
            default:
                return 'col-auto';
        }
    }

    public static function AddButton($type, $name, $caption = null, $attrButton = []) {
        $id = $attrButton['id'] ?? $name;
        $attrButton = array_merge(compact('type', 'name', 'id'), $attrButton);

        $str = "<button ".self::MontaAttrHtml($attrButton)." value='true'>$caption</button>";

        return $str;
    }

    public static function AddInput($type, $name, $caption = null, $attrInput = [], $attrDiv = [], $attrLabel = []) {
        $id = $attrInput['id'] ?? $name;
        $attrInput = array_merge(compact('type', 'name', 'id'), $attrInput);
        switch ($type) {
            case 'search':
                $attrInput['type'] = 'text';
                $attrInput['autocomplete'] = 'off';
                $attrDiv['class'] = 'inputSearch '.($attrDiv['class'] ?? self::GetLargura());
                break;
            case 'checkbox':
                $attrInput['value'] = 'S';
                break;
        }

        switch ($type) {
            case 'search':
                $str = "<div class=\"dropdown\">";
                    $str .= "<input ".self::MontaAttrHtml($attrInput).">";
                    $str .= self::AddButton('button', "btnSearch_$id", "<i class=\"fas fa-search\"></i>", ['class'=>"btn-icon"]);
                    $str .= "<ul class=\"dropdown-menu\"></ul>";
                $str .= "</div>";
                break;
            default:
                $str = "<input ".self::MontaAttrHtml($attrInput).">";
                break;
        }

        return self::MontaHtmlElemento($str, $caption, $attrInput, $attrDiv, $attrLabel);
    }

    public static function AddSelect($name, $caption = null, $selected = null, $arrOption = [], $attrSelect = [], $attrDiv = [], $attrLabel = []) {
        $id = $attrSelect['id'] ?? $name;

        if (isset($attrSelect['placeholder'])) {
            $arrOption = [''=>$attrSelect['placeholder']] + $arrOption;
            unset($attrSelect['placeholder']);
        }

        $attrSelect = array_merge(compact('name', 'id'), $attrSelect);

        $str = '';
        $str .= "<select ".self::MontaAttrHtml($attrSelect).">";
        foreach ($arrOption as $val => $text) {
            $str .= "<option value=\"$val\" ".(!empty($selected) && strval($val) === strval($selected) ? "selected=\"selected\"" : null).">$text</option>";
        }
        $str .= "</select>";

        return self::MontaHtmlElemento($str, $caption, $attrSelect, $attrDiv, $attrLabel);
    }

    public static function AddTextArea($name, $caption = null, $attrTextArea = [], $attrDiv = [], $attrLabel = []) {
        $id = $attrTextArea['id'] ?? $name;
        $value = null;
        if (isset($attrTextArea['value'])) {
            $value = $attrTextArea['value'];
            unset($attrTextArea['value']);
        }

        $attrTextArea = array_merge(compact('name','id'), $attrTextArea);

        $str = "<textarea ".self::MontaAttrHtml($attrTextArea).">$value</textarea>";

        return self::MontaHtmlElemento($str, $caption, $attrTextArea, $attrDiv, $attrLabel);
    }

    public static function AddAlerta($type, $text, $icon = null) {
        $arrTypeIcon = [
            'success'=>'fa fa-check',
            'warning'=>'fa fa-exclamation',
            'danger'=>'fa fa-exclamation-triangle'
        ];

        $str = "<div class='alerta $type'>";
            $str .= "<span class='alerta-icone'>";
                $str .= "<i class='".($icon ?? $arrTypeIcon[$type])."'></i>";
            $str .= "</span>";
            $str .= "<span class='alerta-texto'>";
                $str .= $text;
            $str .= "</span>";
        $str .= "</div>";

        return $str;
    }
}
<?php
class Session {
    public static function Start() {
        if (!self::Isset()) {
            session_name(nmSistema);
            session_start();
            session_regenerate_id();
        }
    }

    public static function Isset() {
        return session_status() === 2;
    }

    public static function GetAll() {
        return self::Isset() ? $_SESSION : [];
    }

    public static function Set($field, $val) {
        if (!isset($_SESSION[$field]) || $_SESSION[$field] !== $val) {
            $_SESSION[$field] = $val;
        }
    }

    public static function Get($field) {
        return $_SESSION[$field] ?? null;
    }

    public static function Unset($field) {
        if (isset($_SESSION[$field])) {
            unset($_SESSION[$field]);
        }
    }

    public static function Destroy() {
        if (self::Isset()) {
            session_destroy();
        }
    }
}
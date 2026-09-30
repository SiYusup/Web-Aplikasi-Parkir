<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\App;


class View
{

    public static ?string $lastRedirect = null;

    public static function render(string $view, $model)
    {
        require __DIR__ . '/../View/header.php';
        require __DIR__ . '/../View/' . $view . '.php';
        require __DIR__ . '/../View/footer.php';
    }

    public static function redirect(string $url, ?array $model = null)
    {
        self::$lastRedirect = $url;
        header("Location: $url");
        if (getenv("mode") != "test") {
            exit();
        }
    }

}
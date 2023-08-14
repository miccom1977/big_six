<?php

if (!function_exists('wda')) {
    /** Metoda pomocnicza wypisująca parametry
     * @param $sText dane do wyświetlenia
     * @return void
     */
    function wda($sText) {
        $plik = fopen('../storage/logs/debugLog.txt', 'a+');
        if (is_array($sText) || is_object($sText)) {
            $sText = print_r($sText, true);
        } elseif (!is_string($sText) || strlen($sText) == 0) {
            $sText = var_export($sText, true);
        }
        if ($plik) {
            fwrite($plik, $sText . "\n");
            fclose($plik);
        }
    }
}

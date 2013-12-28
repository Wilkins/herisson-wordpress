<?php
/**
 * Functions for theming and templating.
 *
 * @package Herisson
 */

/**
 * Récupération et escaping d'une variable en POST
 *
 * @param string $var le nom de la variable
 *
 * @return la variable POST escapée
 */
function post($var)
{
    return (! isset($_POST[$var]) ? '' : escape($_POST[$var]));
}


/**
 * Récupération et escaping d'une variable en GET
 *
 * @param string $var le nom de la variable
 *
 * @return la variable GET escapée
 */
function get($var)
{
    return (! isset($_GET[$var]) ? '' : escape($_GET[$var])); 
}

/**
 * Récupération et escaping d'une variable en POST (ou GET si pas de POST)
 *
 * @param string $var le nom de la variable
 *
 * @return la variable escapée
 */
function param($var)
{
    $p = post($var);
    return $p ? $p : get($var);
}

/**
 * Escaping en fonction du type de la variable, et de l'environnement.
 *
 * @param string $data la variable string a escapée
 *
 * @return la variable str escapée correctement
 */
function escape($data)
{
    return $data;
    // esc_sql($str);
}

function errorsDispatch($content, $errors)
{
    $error_code = $content->get_error_data("herisson");
    foreach ($errors as $code=>$message) {
        if ($error_code == $code) {
            HerissonMessage::i()->addError($message);
        }
    }
    HerissonMessage::i()->addError(__($content->get_error_message("herisson"), HERISSON_TD));
}

function formatSize($size)
{
    $units = array(' B', ' KB', ' MB', ' GB', ' TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) {
        $size /= 1024;
    }
    return round($size, 2).$units[$i];
}

function includePartial($view, $data)
{
    foreach ($data as $var=>$value) {
        $$var = $value;
    }
    include $view;
}



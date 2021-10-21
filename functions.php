<?php

require 'decode.php';

/* const utils */
const ALPHABET = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
const MONTHS = array('A', 'B', 'C', 'D', 'E', 'H', 'L', 'M', 'P', 'R', 'S', 'T');
const REGEX_FISCAL_CODE = '/^[a-z]{6}[0-9]{2}[a-z][0-9]{2}[a-z][0-9]{3}[a-z]$/i';
const VOCALI = array('A', 'E', 'I', 'O', 'U', 'Y');
const CONSONANTI = array('B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Z');

/* Validate fiscal code */
function validate_tax_code($tax_code)
{
    return preg_match(REGEX_FISCAL_CODE, $tax_code);
}

/* Validate request */
function validate_request($request)
{
    if (empty($request->name) ||
        empty($request->surname) ||
        empty($request->dateOfBirth) ||
        empty($request->cityOfBirth) ||
        empty($request->gender))
    {
        return false;
    }

    return true;
}

/* Get city description by cod */
function get_city_of_birth($tax_code)
{
    $string = file_get_contents("db/comuni.json");
    $json = json_decode($string);

    $codComune = substr($tax_code, 11, 4);
    foreach ($json as $item) {
        if (strcmp($item->CodFisco, $codComune) == 0)
        {
            return $item->Comune;
        }
    }

    return '';
}

/* Get city code from des */
function get_city_code($city_of_birth)
{
    $string = file_get_contents("db/comuni.json");
    $json = json_decode($string);

    foreach ($json as $item) {
        if (strcmp(strtoupper($item->Comune), $city_of_birth) == 0)
        {
            return $item->CodFisco;
        }
    }

    return '';
}

/* Get date of birth from tax code */
function get_date_of_birth($tax_code)
{
    $year = substr($tax_code, 6, 2);
    $month = str_pad(intval(array_search(substr($tax_code,  8, 1), MONTHS) + 1), 2, 0, STR_PAD_LEFT);
    $day = str_pad(intval(substr($tax_code,  9, 2)) > 40 ? intval(substr($tax_code,  9, 2)) - 40 : intval(substr($tax_code,  9, 2)), 2, 0, STR_PAD_LEFT);
    return "{$day}/{$month}/{$year}";
}

/* Get gender */
function get_gender($tax_code)
{
    return intval(substr($tax_code,  9, 2)) > 40 ? "F" : "M";
}

/* Generate tax code */
function generate_tax_code($request)
{
    $name = str_replace(' ', '', strtoupper($request->name));
    $surname = str_replace(' ', '', strtoupper($request->surname));
    $date_of_birth = DateTime::createFromFormat('!d/m/Y', $request->dateOfBirth)->getTimestamp();
    $city_of_birth = strtoupper($request->cityOfBirth);
    $gender = strtoupper($request->gender);

    /* Calculate surname */
    $code_surname = calculate_surname($surname);    

    /* Calculate name */
    $code_name = calculate_name($name);

    /* Get year of birth */
    $year = substr(date('Y', $date_of_birth), 2, 2);

    /* Get month code */
    $month = MONTHS[intval(date('m', $date_of_birth)) - 1];

    /* Get day of birth */
    $day = str_pad($gender == 'F' ? intval(date('d', $date_of_birth)) + 40 : intval(date('d', $date_of_birth)), 2, 0, STR_PAD_LEFT);

    /* Get city code */
    $city_code = get_city_code($city_of_birth);

    /* Set tax code */
    $tax_code = "{$code_surname}{$code_name}{$year}{$month}{$day}{$city_code}";

    /* Get control char */
    $control_char = get_control_char($tax_code);
    $tax_code .= "{$control_char}";    

    return $tax_code;
}

/* Calculate surname */
function calculate_surname($surname)
{
    $res = '';

    /* Check consonanti */
    for ($i = 0; $i < strlen($surname); $i++)
    {
        if (in_array($surname[$i], CONSONANTI))
        {
            $res .= $surname[$i];
        }
    }

    /* Check vocali */
    if (strlen($res) < 3)    
    {
        for ($i = 0; $i < strlen($surname); $i++)
        {
            if (in_array($surname[$i], VOCALI))
            {
                $res .= $surname[$i];
            }
        }
    }

    /* Check X char */
    if (strlen($res) < 3)    
    {
        $res = str_pad($res, 3, 'X', STR_PAD_RIGHT);
    }    
    
    return substr($res, 0, 3);
}

/* Calculate name */
function calculate_name($name)
{
    $res = '';

    /* Count consonanti */
    $consonanti = array();
    for ($i = 0; $i < strlen($name); $i++)
    {
        if (in_array($name[$i], CONSONANTI))
        {
            array_push($consonanti, $name[$i]);
        }
    }

    /* Check consonanti */
    if (count($consonanti) >= 4)
    {
        for ($c = 0; $c < count($consonanti); $c++)
        {
            if (in_array($c, array(0, 2, 3)))
            {
                $res .= $consonanti[$c];
            }
        }
    }
    else
    {
        for ($c = 0; $c < count($consonanti); $c++)
        {
            $res .= $consonanti[$c];
        }
    }

    /* Check vocali */
    if (strlen($res) < 3)    
    {
        for ($i = 0; $i < strlen($name); $i++)
        {
            if (in_array($name[$i], VOCALI))
            {
                $res .= $name[$i];
            }
        }
    }

    /* Check X char */
    if (strlen($res) < 3)    
    {
        $res = str_pad($res, 3, 'X', STR_PAD_RIGHT);
    }    
    
    return substr($res, 0, 3);
}

/* Get control char */
function get_control_char($tax_code)
{
    /* odd & even chars */
    $odd_chars = array();
    $even_chars = array();
    for ($i = 0; $i < strlen($tax_code); $i ++)
    {
        $i % 2 == 0 ? array_push($odd_chars, $tax_code[$i]) : array_push($even_chars, $tax_code[$i]);
    }

    /* Sum odd values */
    $sum = 0;
    foreach ($odd_chars as $odd)
    {
        $sum += ODD_VALUES[$odd];
    }

    /* Sum even values */
    foreach ($even_chars as $even)
    {
        $sum += EVEN_VALUES[$even];
    }

    /* Get division */
    $result = $sum % 26;

    /* Return control char */
    return ALPHABET[$result];
}

?>
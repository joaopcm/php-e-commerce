<?php

/**
 * Formata os valores do sistema
 */
function formatPrice(float $vlprice)
{
    return number_format($vlprice, 2, ',', '.');
}
<?php
function calculateSum(array $array):int {
    $sum = 0;
    foreach ($array as $value) {
        $sum += $value;
    }
    return $sum;
}
$numbers = [1, 2, 3, 4, 5];
echo 'Сумма чисел: ' . calculateSum($numbers);
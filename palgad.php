<?php

// Obtains the year from command line argument
if (!isset($argv[1])) {
    echo "Aasta tuleb sisestada käsurea argumendina.\n";
    exit;
}
$inputYear = (int)$argv[1];

// Fetches holidays from riigipühad.ee dataset
$holidays = [];
$json = @file_get_contents('https://xn--riigiphad-v9a.ee/?output=json');
if ($json === false) {
    echo "Puudub ühendus andmestikuga!\n";
    exit;
}
$allDates = json_decode($json);

// Filters inputted year from dataset
$validateYear = array_filter($allDates, static function ($holiday) use ($inputYear) {
    return (int)substr($holiday->date, 0, 4) === $inputYear;
});
// Checks if inputted year exists in dataset
if (count($validateYear) === 0) {
    echo "Aastat {$inputYear} ei leitud riigipühade andmestikust.\n";
    exit;
}

// Removes dates, which are working days
foreach ($allDates as $holiday) {
    if ($holiday->kind_id == '1' || $holiday->kind_id == '2') {
        $holidays[] = $holiday->date;
    }
}

// Checks if a date is a working day
function isWorkingDay($date)
{
    $dayOfWeek = date('N', strtotime($date));
    return ($dayOfWeek >= 1 && $dayOfWeek <= 5);
}

// Finds the previous working day for a given date and excludes non working days
function getPreviousWorkingDay($date, $holidays, $daysBack = 1)
{
    $maxDaysBack = 7;
    while ($daysBack <= $maxDaysBack) {
        $prevDay = date('Y-m-d', strtotime("-{$daysBack} day", strtotime($date)));
        if (isWorkingDay($prevDay) && !in_array($prevDay, $holidays)) {
            return $prevDay;
        }
        $daysBack++;
    }
    return null;
}

// Estonian month names
$months = [
    'Jaanuar', 'Veebruar', 'Märts', 'Aprill', 'Mai', 'Juuni',
    'Juuli', 'August', 'September', 'Oktoober', 'November', 'Detsember'
];

// Generates the salary payment and reminder dates for each month of the year
$table = [];
for ($month = 1; $month <= 12; $month++) {

    $paymentDate = date('Y-m-10', strtotime("{$inputYear}-{$month}-01"));
    if (!isWorkingDay($paymentDate) || in_array($paymentDate, $holidays)) {
        $paymentDate = getPreviousWorkingDay($paymentDate, $holidays);
    }
    // Sets the reminder to 3 working days prior
    $reminderDate = getPreviousWorkingDay($paymentDate, $holidays, 3);

    $table[] = [
        'Kuu' => $months[$month - 1],
        'Palgapäev' => strftime('%d.%m.%y', strtotime($paymentDate)),
        'Meeldetuletus' => strftime('%d.%m.%y', strtotime($reminderDate)),
    ];
}

// Writes the output to a CSV file
$filename = "{$inputYear}.csv";
$fp = fopen($filename, 'w');

// Adds BOM to the beginning of the file, so that estonian characters are correctly displayed in Excel
fwrite($fp, pack("CCC", 0xef, 0xbb, 0xbf));

fputcsv($fp, array_keys($table[0]));
foreach ($table as $row) {
    fputcsv($fp, $row);
}
fclose($fp);
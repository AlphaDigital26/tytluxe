<?php

/*
 * Only this key is changed from the package's defaults (Laravel merges the
 * rest of vendor/maatwebsite/excel/config/excel.php in).
 *
 * Admin "Download as Excel" files include text guests type on the website
 * (names, messages). The default binder turns any cell starting with "=" into
 * a live formula, so a guest could plant one that runs when staff open the
 * file. Every cell is written as plain text instead.
 */
return [

    'value_binder' => [
        'default' => PhpOffice\PhpSpreadsheet\Cell\StringValueBinder::class,
    ],

];

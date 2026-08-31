<?php

namespace App\Support;

use ZipArchive;

/**
 * Minimal spreadsheet reader (no external dependency).
 * Reads .csv/.txt via fgetcsv and .xlsx via ZipArchive.
 * Returns an array of rows; each row is an array of cell values indexed by column.
 */
class Spreadsheet
{
    public static function rows(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        return in_array($extension, ['csv', 'txt'], true)
            ? self::readCsv($path)
            : self::readXlsx($path);
    }

    private static function readCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        // Strip a UTF-8 BOM from the very first cell if present.
        if (! empty($rows) && isset($rows[0][0])) {
            $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]);
        }

        return $rows;
    }

    private static function readXlsx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $shared = [];
        if ($xml = $zip->getFromName('xl/sharedStrings.xml')) {
            $ss = simplexml_load_string($xml);
            foreach ($ss->si as $si) {
                if (isset($si->t)) {
                    $shared[] = (string) $si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string) $r->t;
                    }
                    $shared[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (! $sheetXml) {
            return [];
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $idx = self::columnIndex((string) $c['r']);
                $value = (string) $c->v;
                if ((string) $c['t'] === 's') {
                    $value = $shared[(int) $value] ?? '';
                }
                $cells[$idx] = $value;
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    private static function columnIndex(string $ref): int
    {
        preg_match('/^([A-Z]+)/', $ref, $m);
        $n = 0;
        foreach (str_split($m[1]) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return $n - 1;
    }
}
